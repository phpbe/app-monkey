<?php

namespace Be\App\Monkey\Service\Admin;

use Be\App\ServiceException;
use Be\Be;

class PullDriver
{

    /**
     * 获取采集器列表
     *
     * @return array
     */
    public function getPullDrivers(): array
    {
        $sql = 'SELECT * FROM monkey_pull_driver WHERE is_delete = 0 ORDER BY `ordering` DESC';
        $pullDrivers = Be::getDb()->getObjects($sql);
        return $pullDrivers;
    }

    /**
     * 获取采集器
     *
     * @param string $pullDriverId
     * @return object
     */
    public function getPullDriver(string $pullDriverId): object
    {
        $db = Be::getDb();

        $sql = 'SELECT * FROM monkey_pull_driver WHERE id=? AND is_delete = 0';
        $pullDriver = $db->getObject($sql, [$pullDriverId]);
        if (!$pullDriver) {
            throw new ServiceException('采集器（# ' . $pullDriverId . '）不存在！');
        }

        $pullDriver->ordering = (int)$pullDriver->ordering;
        $pullDriver->is_enable = (int)$pullDriver->is_enable;
        $pullDriver->is_delete = (int)$pullDriver->is_delete;

        $fields = unserialize($pullDriver->fields);
        foreach ($fields as &$field) {
            $field['is_title'] = (int)$field['is_title'];
        }
        unset($field);
        $pullDriver->fields = $fields;

        return $pullDriver;
    }

    /**
     * 获取采集器键值对
     *
     * @return array
     */
    public function getPullDriverKeyValues(): array
    {
        $sql = 'SELECT id, `name` FROM monkey_pull_driver WHERE is_delete = 0 ORDER BY `ordering` DESC';
        return Be::getDb()->getKeyValues($sql);
    }


    /**
     * 编辑采集器
     *
     * @param array $data 采集器数据
     * @return object
     */
    public function edit(array $data): object
    {
        $db = Be::getDb();

        $isNew = true;
        $pullDriverId = null;
        if (isset($data['id']) && is_string($data['id']) && $data['id'] !== '') {
            $isNew = false;
            $pullDriverId = $data['id'];
        }

        $tupleRule = Be::getTuple('monkey_pull_driver');
        if (!$isNew) {
            try {
                $tupleRule->load($pullDriverId);
            } catch (\Throwable $t) {
                throw new ServiceException('采集器（# ' . $pullDriverId . '）不存在！');
            }

            if ($tupleRule->is_delete === 1) {
                throw new ServiceException('采集器（# ' . $pullDriverId . '）不存在！');
            }
        }

        if (!isset($data['name']) || !is_string($data['name'])) {
            throw new ServiceException('采集器名称未填写！');
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

        if (!isset($data['version']) || !is_string($data['version'])) {
            $data['version'] = '';
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


        $fields = [];

        $isTitleFields = 0;

        $i = 0;
        foreach ($data['fields'] as $field) {
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

            $field['is_title'] = (int)$field['is_title'];

            if (!in_array($field['is_title'], [0, 1])) {
                $field['is_title'] = 0;
            }

            if ($field['is_title'] === 1) {
                $isTitleFields++;
            }

            $fields[] = [
                'name' => $field['name'],
                'script' => $field['script'],
                'is_title' => $field['is_title'],
            ];
        }
        $data['fields'] = $fields;

        if ($isTitleFields !== 1) {
            throw new ServiceException('采集字段必须且仅设置一个标题字段！');
        }

        $db->startTransaction();
        try {
            $now = date('Y-m-d H:i:s');
            $tupleRule->name = $data['name'];
            $tupleRule->description = $data['description'];
            $tupleRule->match_1 = $data['match_1'];
            $tupleRule->match_2 = $data['match_2'];
            $tupleRule->match_3 = $data['match_3'];
            $tupleRule->start_page = $data['start_page'];
            $tupleRule->get_next_page_script = $data['get_next_page_script'];
            $tupleRule->get_links_script = $data['get_links_script'];
            $tupleRule->interval = $data['interval'];
            $tupleRule->version = $data['version'];
            $tupleRule->ordering = $data['ordering'];
            $tupleRule->fields = serialize($data['fields']);
            $tupleRule->is_enable = $data['is_enable'];
            $tupleRule->update_time = $now;
            if ($isNew) {
                $tupleRule->is_delete = 0;
                $tupleRule->create_time = $now;
                $tupleRule->insert();
            } else {
                $tupleRule->update();
            }

            $db->commit();

        } catch (\Throwable $t) {
            $db->rollback();
            Be::getLog()->error($t);

            throw new ServiceException(($isNew ? '新建' : '编辑') . '采集器发生异常！');
        }

        return $tupleRule->toObject();
    }


}
