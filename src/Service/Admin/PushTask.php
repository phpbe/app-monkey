<?php

namespace Be\App\Monkey\Service\Admin;

use Be\App\ServiceException;
use Be\Be;

class PushTask
{

    /**
     * 获取发布任务列表
     *
     * @return array
     */
    public function getPushTasks(): array
    {
        $sql = 'SELECT * FROM monkey_push_task WHERE is_delete = 0 ORDER BY `ordering` DESC';
        $pushTasks = Be::getDb()->getObjects($sql);
        return $pushTasks;
    }

    /**
     * 获取可用的发布任务列表
     *
     * @return array
     */
    public function getEnabledPushTasks(): array
    {
        $sql = 'SELECT * FROM monkey_push_task WHERE is_enable = 1 AND is_delete = 0 ORDER BY `ordering` DESC';
        $pushTasks = Be::getDb()->getObjects($sql);
        return $pushTasks;
    }

    /**
     * 获取发布任务
     *
     * @param string $pushTaskId
     * @return object
     */
    public function getPushTask(string $pushTaskId): object
    {
        $db = Be::getDb();

        $sql = 'SELECT * FROM monkey_push_task WHERE id=? AND is_delete = 0';
        $pushDriver = $db->getObject($sql, [$pushTaskId]);
        if (!$pushDriver) {
            throw new ServiceException('发布任务（# ' . $pushTaskId . '）不存在！');
        }

        $pushDriver->ordering = (int)$pushDriver->ordering;
        $pushDriver->is_enable = (int)$pushDriver->is_enable;
        $pushDriver->is_delete = (int)$pushDriver->is_delete;

        if ($pushDriver->headers === '') {
            $pushDriver->headers = [];
        } else {
            $headers = unserialize($pushDriver->headers);
            if (is_array($headers)) {
                $pushDriver->headers = $headers;
            } else {
                $pushDriver->headers = [];
            }
        }

        if ($pushDriver->fields === '') {
            $pushDriver->fields = [];
        } else {
            $fields = unserialize($pushDriver->fields);
            if (is_array($fields)) {
                $pushDriver->fields = $fields;
            } else {
                $pushDriver->fields = [];
            }
        }

        return $pushDriver;
    }

    /**
     * 获取发布任务键值对
     *
     * @return array
     */
    public function getPushTaskKeyValues(): array
    {
        $sql = 'SELECT id, `name` FROM monkey_push_task WHERE is_delete = 0 ORDER BY `ordering` DESC';
        return Be::getDb()->getKeyValues($sql);
    }

    /**
     * 获取可用的发布任务键值对
     *
     * @return array
     */
    public function getEnabledPushTaskKeyValues(): array
    {
        $sql = 'SELECT id, `name` FROM monkey_push_task WHERE is_enable = 1 AND is_delete = 0 ORDER BY `ordering` DESC';
        return Be::getDb()->getKeyValues($sql);
    }

    /**
     * 编辑发布任务
     *
     * @param array $data 发布任务数据
     * @return object
     */
    public function edit(array $data): object
    {
        $db = Be::getDb();

        $isNew = true;
        $pushTaskId = null;
        if (isset($data['id']) && is_string($data['id']) && $data['id'] !== '') {
            $isNew = false;
            $pushTaskId = $data['id'];
        }

        if (!isset($data['push_driver_id']) || !is_string($data['push_driver_id']) || $data['push_driver_id'] === '') {
            throw new ServiceException('参数（push_driver_id）缺失！');
        }

        $tuplePushTask = Be::getTuple('monkey_push_task');
        if (!$isNew) {
            try {
                $tuplePushTask->load($pushTaskId);
            } catch (\Throwable $t) {
                throw new ServiceException('发布任务（# ' . $pushTaskId . '）不存在！');
            }

            if ($tuplePushTask->is_delete === 1) {
                throw new ServiceException('发布任务（# ' . $pushTaskId . '）不存在！');
            }

            if ($tuplePushTask->push_driver_id !== $data['push_driver_id']) {
                throw new ServiceException('参数（push_driver_id）错误！');
            }
        }

        $tuplePushDriver = Be::getTuple('monkey_push_driver');
        try {
            $tuplePushDriver->load($data['push_driver_id']);
        } catch (\Throwable $t) {
            throw new ServiceException('发布器（# ' . $data['push_driver_id'] . '）不存在！');
        }

        if ($tuplePushDriver->is_delete === 1) {
            throw new ServiceException('发布器（# ' . $data['push_driver_id'] . '）不存在！');
        }

        $tuplePushDriver->headers = unserialize($tuplePushDriver->headers);
        $tuplePushDriver->fields = unserialize($tuplePushDriver->fields);

        if (!isset($data['name']) || !is_string($data['name'])) {
            throw new ServiceException('发布任务名称未填写！');
        }

        if (!isset($data['url']) || !is_string($data['url'])) {
            throw new ServiceException('发布网址未填写！');
        }

        // ------------------------------------------------------------------------------------------------------------- 检查请求头
        $headers = [];
        if (isset($data['headers']) && is_array($data['headers'])) {
            $i = 0;
            foreach ($data['headers'] as $header) {
                $i++;
                if (!isset($header['name']) || !is_string($header['name'])) {
                    throw new ServiceException('第' . $i . '个请求头名称缺失！');
                }

                $header['name'] = trim($header['name']);

                if ($header['name'] === '') {
                    throw new ServiceException('第' . $i . '个请求头名称未填写！');
                }

                if (!isset($header['value']) || !is_string($header['value'])) {
                    throw new ServiceException('第' . $i . '个请求头的值缺失！');
                }

                $header['value'] = trim($header['value']);

                if ($header['value'] === '') {
                    throw new ServiceException('第' . $i . '个请求头的值缺失！');
                }

                $found = false;
                foreach ($tuplePushDriver->headers as $pushDriverHeader) {
                    if ($header['name'] === $pushDriverHeader['name']) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    throw new ServiceException('请求头（' . $header['name'] . '）无法识别，在发布器请求头中不存在！');
                }

                $headers[] = [
                    'name' => $header['name'],
                    'value' => $header['value'],
                ];
            }

            foreach ($tuplePushDriver->headers as $pushDriverHeader) {
                $found = false;
                foreach ($data['headers'] as $header) {
                    if ($header['name'] === $pushDriverHeader['name']) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    throw new ServiceException('发布器请求头（' . $pushDriverHeader['name'] . '）缺失！');
                }
            }

            $data['headers'] = $headers;
        } else {
            if (count($tuplePushDriver->headers) > 0) {
                throw new ServiceException('请求头缺失！');
            }
        }
        // ------------------------------------------------------------------------------------------------------------- 检查请求头


        // ------------------------------------------------------------------------------------------------------------- 检查请求字段
        if (!isset($data['fields']) || !is_array($data['fields'])) {
            throw new ServiceException('发布字段缺失！');
        }

        $fields = [];
        $i = 0;
        foreach ($data['fields'] as $field) {
            $i++;

            $foundPushDriverField = false;
            foreach ($tuplePushDriver->fields as $pushDriverField) {
                if ($field['name'] === $pushDriverField['name']) {
                    $foundPushDriverField = $pushDriverField;
                    break;
                }
            }

            if (!$foundPushDriverField) {
                throw new ServiceException('发布字段（' . $field['name'] . '）无法识别，在发布器字段中不存在！');
            }

            if (!isset($field['is_enable']) || !is_numeric($field['is_enable'])) {
                $field['is_enable'] = $foundPushDriverField['required'];
            }

            $field['is_enable'] = (int)$field['is_enable'];
            if (!in_array($field['is_enable'], [0, 1])) {
                $field['is_enable'] = $foundPushDriverField['required'];
            }

            if ($foundPushDriverField['required'] === 1) {
                if ($field['is_enable'] !== 1) {
                    throw new ServiceException('第' . $i . '个发布字段是否必填数据异常！');
                }
            }

            if (!isset($field['name']) || !is_string($field['name'])) {
                throw new ServiceException('第' . $i . '个发布字段名称缺失！');
            }

            $field['name'] = trim($field['name']);

            if ($field['name'] === '') {
                throw new ServiceException('第' . $i . '个发布字段名称未填写！');
            }

            if (!isset($field['label']) || !is_string($field['label'])) {
                throw new ServiceException('第' . $i . '个发布字段脚本缺失！');
            }

            $field['label'] = trim($field['label']);

            if ($field['label'] === '') {
                throw new ServiceException('第' . $i . '个发布字段脚本未填写！');
            }

            if (!isset($field['default']) || !is_string($field['default'])) {
                $field['default'] = '';
            }

            if (!isset($field['required']) || !is_numeric($field['required'])) {
                $field['required'] = 0;
            }
            $field['required'] = (int)$field['required'];
            if (!in_array($field['required'], [0, 1])) {
                $field['required'] = 0;
            }

            if (!isset($field['value_type']) || !is_string($field['value_type'])) {
                throw new ServiceException('第' . $i . '个发布字段类型缺失！');
            }

            if (!in_array($field['value_type'], ['pull_driver_field', 'default', 'custom'])) {
                throw new ServiceException('第' . $i . '个发布字段类型无法识别！');
            }

            switch ($field['value_type']) {
                case 'pull_driver_field':
                    if (!isset($field['value_pull_driver_field']) || !is_string($field['value_pull_driver_field'])) {
                        throw new ServiceException('第' . $i . '个发布字段值未选择！');
                    }

                    $field['value_default'] = '';
                    $field['value_custom'] = '';
                    break;
                case 'default':
                    if (!isset($field['value_default']) || !is_string($field['value_default'])) {
                        throw new ServiceException('第' . $i . '个发布字段默认值缺失！');
                    }

                    $field['value_pull_driver_field'] = '';
                    $field['value_custom'] = '';
                    break;
                case 'custom':
                    if (!isset($field['value_custom']) || !is_string($field['value_custom'])) {
                        throw new ServiceException('第' . $i . '个发布字段自定义值未填写！');
                    }

                    $field['value_pull_driver_field'] = '';
                    $field['value_default'] = '';
                    break;
            }


            $fields[] = [
                'is_enable' => $field['is_enable'],
                'name' => $field['name'],
                'label' => $field['label'],
                'default' => $field['default'],
                'required' => $field['required'],
                'value_type' => $field['value_type'],
                'value_pull_driver_field' => $field['value_pull_driver_field'],
                'value_default' => $field['value_default'],
                'value_custom' => $field['value_custom'],
            ];
        }

        foreach ($tuplePushDriver->fields as $pushDriverField) {
            $found = false;
            foreach ($data['fields'] as $field) {
                if ($field['name'] === $pushDriverField['name']) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                throw new ServiceException('发布器发布字段（' . $pushDriverField['name'] . '）缺失！');
            }
        }

        $data['fields'] = $fields;
        // ------------------------------------------------------------------------------------------------------------- 检查请求字段


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

        $db->startTransaction();
        try {
            $now = date('Y-m-d H:i:s');
            $tuplePushTask->push_driver_id = $data['push_driver_id'];
            $tuplePushTask->name = $data['name'];
            $tuplePushTask->url = $data['url'];
            $tuplePushTask->headers = serialize($data['headers']);
            $tuplePushTask->fields = serialize($data['fields']);
            $tuplePushTask->interval = $data['interval'];
            $tuplePushTask->ordering = $data['ordering'];
            $tuplePushTask->is_enable = $data['is_enable'];
            $tuplePushTask->update_time = $now;
            if ($isNew) {
                $tuplePushTask->status = 'created';
                $tuplePushTask->is_delete = 0;
                $tuplePushTask->create_time = $now;
                $tuplePushTask->insert();
            } else {
                $tuplePushTask->update();
            }

            $db->commit();

        } catch (\Throwable $t) {
            $db->rollback();
            Be::getLog()->error($t);

            throw new ServiceException(($isNew ? '新建' : '编辑') . '发布任务发生异常！');
        }

        return $tuplePushTask->toObject();
    }

    /**
     * 启动
     *
     * @return void
     */
    public function run(string $pushTaskId)
    {
        $tuplePushTask = Be::getTuple('monkey_push_task');

        try {
            $tuplePushTask->load($pushTaskId);
        } catch (\Throwable $t) {
            throw new ServiceException('发布任务（# ' . $pushTaskId . '）不存在！');
        }

        if ($tuplePushTask->is_delete === 1) {
            throw new ServiceException('发布任务（# ' . $pushTaskId . '）不存在！');
        }

        $db = Be::getDb();
        $db->startTransaction();
        try {
            $sql = 'DELETE FROM monkey_push_task_log WHERE push_task_id=?';
            $db->query($sql, [$pushTaskId]);

            $tuplePushTask->status = 'prepared';
            $tuplePushTask->update_time = date('Y-m-d H:i:s');
            $tuplePushTask->update();

            $db->commit();

            Be::getService('App.System.Task')->trigger('Monkey.PushTask');

        } catch (\Throwable $t) {
            $db->rollback();
            Be::getLog()->error($t);

            throw new ServiceException( '发布任务启动失败！');
        }

    }

}
