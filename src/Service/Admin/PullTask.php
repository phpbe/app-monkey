<?php

namespace Be\App\Monkey\Service\Admin;

use Be\App\ServiceException;
use Be\Be;

class PullTask
{

    /**
     * 获取采集任务列表
     *
     * @return array
     */
    public function getPullTasks(): array
    {
        $sql = 'SELECT * FROM monkey_pull_task WHERE is_delete = 0 ORDER BY `ordering` DESC';
        $pullTasks = Be::getDb()->getObjects($sql);
        return $pullTasks;
    }

    /**
     * 获取采集任务
     *
     * @param string $pullTaskId
     * @return object
     */
    public function getPullTask(string $pullTaskId): object
    {
        $db = Be::getDb();

        $sql = 'SELECT * FROM monkey_pull_task WHERE id=? AND is_delete = 0';
        $pullTask = $db->getObject($sql, [$pullTaskId]);
        if (!$pullTask) {
            throw new ServiceException('采集任务（# ' . $pullTaskId . '）不存在！');
        }

        $pullTask->ordering = (int)$pullTask->ordering;
        $pullTask->is_enable = (int)$pullTask->is_enable;
        $pullTask->is_delete = (int)$pullTask->is_delete;
        $pullTask->fields = unserialize($pullTask->fields);

        return $pullTask;
    }

    /**
     * 获取采集任务键值对
     *
     * @return array
     */
    public function getPullTaskKeyValues(): array
    {
        $sql = 'SELECT id, `name` FROM monkey_pull_task WHERE is_delete = 0 ORDER BY `ordering` DESC';
        return Be::getDb()->getKeyValues($sql);
    }


    /**
     * 编辑采集任务
     *
     * @param array $data 采集任务数据
     * @return object
     */
    public function edit(array $data): object
    {
        $db = Be::getDb();

        $isNew = true;
        $pullTaskId = null;
        if (isset($data['id']) && is_string($data['id']) && $data['id'] !== '') {
            $isNew = false;
            $pullTaskId = $data['id'];
        }

        $tuplePullTask = Be::getTuple('monkey_pull_task');
        if (!$isNew) {
            try {
                $tuplePullTask->load($pullTaskId);
            } catch (\Throwable $t) {
                throw new ServiceException('采集任务（# ' . $pullTaskId . '）不存在！');
            }

            if ($tuplePullTask->is_delete === 1) {
                throw new ServiceException('采集任务（# ' . $pullTaskId . '）不存在！');
            }
        }


        $tuplePullDriver = Be::getTuple('monkey_pull_driver');
        if ($isNew) {
            if (!isset($data['pull_driver_id']) || !is_string($data['pull_driver_id'])) {
                throw new ServiceException('采集器参数缺失！');
            }

            try {
                $tuplePullDriver->load($data['pull_driver_id']);
            } catch (\Throwable $t) {
                throw new ServiceException('采集器（# ' . $data['pull_driver_id'] . '）不存在！');
            }
        } else {
            try {
                $tuplePullDriver->load($tuplePullTask->pull_driver_id);
            } catch (\Throwable $t) {
                throw new ServiceException('采集器（# ' . $data['pull_driver_id'] . '）不存在！');
            }
        }

        if ($tuplePullDriver->is_delete === 1) {
            throw new ServiceException('采集器（# ' . $data['pull_driver_id'] . '）不存在！！！');
        }

        if (!isset($data['name']) || !is_string($data['name'])) {
            throw new ServiceException('采集任务名称未填写！');
        }

        if (!isset($data['description']) || !is_string($data['description'])) {
            $data['description'] = '';
        }

        if (!isset($data['match_1']) || !is_string($data['match_1'])) {
            throw new ServiceException('匹配网址1未填写！');
        }

        if (!isset($data['match_2']) || !is_string($data['match_2'])) {
            $data['match_2'] = '';
        }

        if (!isset($data['match_3']) || !is_string($data['match_3'])) {
            $data['match_3'] = '';
        }

        if (!isset($data['start_page']) || !is_string($data['start_page'])) {
            throw new ServiceException('起始页未填写！');
        }

        if (!isset($data['get_next_page_script']) || !is_string($data['get_next_page_script'])) {
            throw new ServiceException('获取下一页脚本未填写！');
        }

        if (!isset($data['get_links_script']) || !is_string($data['get_links_script'])) {
            throw new ServiceException('获取页面链接脚本未填写！');
        }

        if (!isset($data['interval']) || !is_numeric($data['interval'])) {
            $data['interval'] = 1000;
        }

        $data['interval'] = (int)$data['interval'];

        if ($data['interval'] <= 0) {
            $data['interval'] = 1000;
        }

        if (!isset($data['ordering']) || !is_numeric($data['ordering'])) {
            $data['ordering'] = 0;
        }

        $data['ordering'] = (int)$data['ordering'];

        if (!isset($data['is_enable']) || !is_numeric($data['is_enable'])) {
            $data['is_enable'] = 0;
        }

        if (!isset($data['fields']) || !is_array($data['fields'])) {
            throw new ServiceException('采集字段缺失！');
        }

        $isTitleFields = 0;

        $i = 0;
        foreach ($data['fields'] as &$field) {
            $i++;
            if (!isset($field['name']) || !is_string($field['name'])) {
                throw new ServiceException('第' . $i . '个采集字段名称缺失！');
            }

            $field['name'] = trim($field['name']);

            if ($field['name'] === '') {
                throw new ServiceException('第' . $i . '个采集字段名称未填写！');
            }

            if (!isset($field['script']) || !is_string($field['script'])) {
                throw new ServiceException('第' . $i . '个采集字段脚本缺失！');
            }

            $field['script'] = trim($field['script']);

            if ($field['script'] === '') {
                throw new ServiceException('第' . $i . '个采集字段脚本未填写！');
            }

            if (!isset($field['is_title']) || !is_numeric($field['is_title'])) {
                $field['is_title'] = 0;
            }

            if (!in_array($field['is_title'], [0, 1])) {
                $field['is_title'] = 0;
            }

            if ($field['is_title'] === 1) {
                $isTitleFields++;
            }
        }
        unset($field);

        if ($isTitleFields !== 1) {
            throw new ServiceException('采集字段必须且仅设置一个标题字段！');
        }

        $db->startTransaction();
        try {
            $now = date('Y-m-d H:i:s');

            $tuplePullTask->pull_driver_id = $data['pull_driver_id'];

            $tuplePullTask->name = $data['name'];
            $tuplePullTask->description = $data['description'];
            $tuplePullTask->match_1 = $data['match_1'];
            $tuplePullTask->match_2 = $data['match_2'];
            $tuplePullTask->match_3 = $data['match_3'];
            $tuplePullTask->start_page = $data['start_page'];
            $tuplePullTask->get_next_page_script = $data['get_next_page_script'];
            $tuplePullTask->get_links_script = $data['get_links_script'];
            $tuplePullTask->interval = $data['interval'];
            $tuplePullTask->fields = serialize($data['fields']);

            if ($isNew) {
                $tuplePullTask->version = 1;
            } else {
                $tuplePullTask->version = $tuplePullTask->version + 1;;
            }

            $tuplePullTask->ordering = $data['ordering'];
            $tuplePullTask->is_enable = $data['is_enable'];
            $tuplePullTask->update_time = $now;
            if ($isNew) {
                $tuplePullTask->is_delete = 0;
                $tuplePullTask->create_time = $now;
                $tuplePullTask->insert();
            } else {
                $tuplePullTask->update();
            }

            $db->commit();

        } catch (\Throwable $t) {
            $db->rollback();
            Be::getLog()->error($t);

            throw new ServiceException(($isNew ? '新建' : '编辑') . '采集任务发生异常！');
        }

        return $tuplePullTask->toObject();
    }


}
