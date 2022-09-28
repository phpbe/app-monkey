<?php

namespace Be\App\Monkey\Service\Admin;

use Be\App\ServiceException;
use Be\Be;

class Task
{

    /**
     * 获取任务列表
     *
     * @return array
     */
    public function getTasks(): array
    {
        $sql = 'SELECT * FROM monkey_task WHERE is_delete = 0 ORDER BY `ordering` DESC';
        $tasks = Be::getDb()->getObjects($sql);
        return $tasks;
    }

    /**
     * 获取任务
     *
     * @param string $taskId
     * @return object
     */
    public function getTask(string $taskId): object
    {
        $db = Be::getDb();

        $sql = 'SELECT * FROM monkey_task WHERE id=? AND is_delete = 0';
        $task = $db->getObject($sql, [$taskId]);
        if (!$task) {
            throw new ServiceException('任务（# ' . $taskId . '）不存在！');
        }

        $task->ordering = (int)$task->ordering;
        $task->is_enable = (int)$task->is_enable;
        $task->is_delete = (int)$task->is_delete;

        $sql = 'SELECT * FROM monkey_task_field WHERE task_id=? ORDER BY `ordering` ASC';
        $fields = $db->getObjects($sql, [$taskId]);
        $task->fields = $fields;

        return $task;
    }

    /**
     * 获取任务键值对
     *
     * @return array
     */
    public function getTaskKeyValues(): array
    {
        $sql = 'SELECT id, `name` FROM monkey_task WHERE is_delete = 0 ORDER BY `ordering` DESC';
        return Be::getDb()->getKeyValues($sql);
    }


    /**
     * 编辑任务
     *
     * @param array $data 任务数据
     * @return object
     */
    public function edit(array $data): object
    {
        $db = Be::getDb();

        $isNew = true;
        $taskId = null;
        if (isset($data['id']) && is_string($data['id']) && $data['id'] !== '') {
            $isNew = false;
            $taskId = $data['id'];
        }

        $tupleTask = Be::getTuple('monkey_task');
        if (!$isNew) {
            try {
                $tupleTask->load($taskId);
            } catch (\Throwable $t) {
                throw new ServiceException('任务（# ' . $taskId . '）不存在！');
            }

            if ($tupleTask->is_delete === 1) {
                throw new ServiceException('任务（# ' . $taskId . '）不存在！');
            }
        }


        $tupleRule = Be::getTuple('monkey_rule');
        if ($isNew) {
            if (!isset($data['rule_id']) || !is_string($data['rule_id'])) {
                throw new ServiceException('采集规则参数缺失！');
            }

            try {
                $tupleRule->load($data['rule_id']);
            } catch (\Throwable $t) {
                throw new ServiceException('采集规则（# ' . $data['rule_id'] . '）不存在！');
            }
        } else {
            try {
                $tupleRule->load($tupleTask->rule_id);
            } catch (\Throwable $t) {
                throw new ServiceException('采集规则（# ' . $data['rule_id'] . '）不存在！');
            }
        }

        if ($tupleRule->is_delete === 1) {
            throw new ServiceException('采集规则（# ' . $data['rule_id'] . '）不存在！！！');
        }

        if (!isset($data['name']) || !is_string($data['name'])) {
            throw new ServiceException('任务名称未填写！');
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

            $tupleTask->rule_id = $data['rule_id'];

            $tupleTask->name = $data['name'];
            $tupleTask->description = $data['description'];
            $tupleTask->match_1 = $data['match_1'];
            $tupleTask->match_2 = $data['match_2'];
            $tupleTask->match_3 = $data['match_3'];
            $tupleTask->start_page = $data['start_page'];
            $tupleTask->get_next_page_script = $data['get_next_page_script'];
            $tupleTask->get_links_script = $data['get_links_script'];
            $tupleTask->interval = $data['interval'];

            if ($isNew) {
                $tupleTask->version = 1;
            } else {
                $tupleTask->version = $tupleTask->version + 1;;
            }

            $tupleTask->ordering = $data['ordering'];
            $tupleTask->is_enable = $data['is_enable'];
            $tupleTask->update_time = $now;
            if ($isNew) {
                $tupleTask->is_delete = 0;
                $tupleTask->create_time = $now;
                $tupleTask->insert();
            } else {
                $tupleTask->update();
            }

            // 采集字段
            if ($isNew) {
                $ordering = 0;
                foreach ($data['fields'] as $field) {
                    $tupleTaskField = Be::getTuple('monkey_task_field');
                    $tupleTaskField->task_id = $tupleTask->id;
                    $tupleTaskField->name = $field['name'];
                    $tupleTaskField->script = $field['script'];
                    $tupleTaskField->is_title = $field['is_title'];
                    $tupleTaskField->ordering = $ordering++;
                    $tupleTaskField->insert();
                }
            } else {
                $keepIds = [];
                foreach ($data['fields'] as $field) {
                    if (isset($field['id']) && $field['id'] !== '') {
                        $keepIds[] = $field['id'];
                    }
                }

                if (count($keepIds) > 0) {
                    Be::getTable('monkey_task_field')
                        ->where('task_id', $taskId)
                        ->where('id', 'NOT IN', $keepIds)
                        ->delete();
                } else {
                    Be::getTable('monkey_task_field')
                        ->where('task_id', $taskId)
                        ->delete();
                }

                $ordering = 0;
                foreach ($data['fields'] as $field) {
                    $tupleTaskField = Be::getTuple('monkey_task_field');
                    if (isset($field['id']) && $field['id'] !== '') {
                        try {
                            $tupleTaskField->loadBy([
                                'id' => $field['id'],
                                'task_id' => $tupleTask->id,
                            ]);
                        } catch (\Throwable $t) {
                            throw new ServiceException('采集任务（# ' . $taskId . ' ' . $tupleTask->name . '）下的采集字段（# ' . $field['id'] . '）不存在！');
                        }
                    }

                    $tupleTaskField->task_id = $tupleTask->id;
                    $tupleTaskField->name = $field['name'];
                    $tupleTaskField->script = $field['script'];
                    $tupleTaskField->is_title = $field['is_title'];
                    $tupleTaskField->ordering = $ordering++;

                    if (!isset($field['id']) || $field['id'] === '') {
                        $tupleTaskField->create_time = $now;
                    }

                    $tupleTaskField->update_time = $now;
                    $tupleTaskField->save();
                }
            }

            $db->commit();

        } catch (\Throwable $t) {
            $db->rollback();
            Be::getLog()->error($t);

            throw new ServiceException(($isNew ? '新建' : '编辑') . '任务发生异常！');
        }

        return $tupleTask->toObject();
    }


}
