<?php

namespace Be\App\Monkey\Service\Admin;

use Be\App\ServiceException;
use Be\Be;

class PushDriver
{

    /**
     * 获取发布器列表
     *
     * @return array
     */
    public function getPushDrivers(): array
    {
        $sql = 'SELECT * FROM monkey_push_driver WHERE is_delete = 0 ORDER BY `ordering` DESC';
        $pushDrivers = Be::getDb()->getObjects($sql);
        return $pushDrivers;
    }

    /**
     * 获取可用的发布器列表
     *
     * @return array
     */
    public function getEnabledPushDrivers(): array
    {
        $sql = 'SELECT * FROM monkey_push_driver WHERE is_enable = 1 AND is_delete = 0 ORDER BY `ordering` DESC';
        $pushDrivers = Be::getDb()->getObjects($sql);
        return $pushDrivers;
    }

    /**
     * 获取发布器
     *
     * @param string $pushDriverId
     * @return object
     */
    public function getPushDriver(string $pushDriverId): object
    {
        $db = Be::getDb();

        $sql = 'SELECT * FROM monkey_push_driver WHERE id=? AND is_delete = 0';
        $pushDriver = $db->getObject($sql, [$pushDriverId]);
        if (!$pushDriver) {
            throw new ServiceException('发布器（# ' . $pushDriverId . '）不存在！');
        }

        $pushDriver->ordering = (int)$pushDriver->ordering;
        $pushDriver->is_enable = (int)$pushDriver->is_enable;
        $pushDriver->is_delete = (int)$pushDriver->is_delete;

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

        if ($pushDriver->options === '') {
            $pushDriver->options = [];
        } else {
            $options = unserialize($pushDriver->options);
            if (is_array($options)) {
                $pushDriver->options = $options;
            } else {
                $pushDriver->options = [];
            }
        }

        return $pushDriver;
    }

    /**
     * 获取发布器键值对
     *
     * @return array
     */
    public function getPushDriverKeyValues(): array
    {
        $sql = 'SELECT id, `name` FROM monkey_push_driver WHERE is_delete = 0 ORDER BY `ordering` DESC';
        return Be::getDb()->getKeyValues($sql);
    }

    /**
     * 获取可用的发布器键值对
     *
     * @return array
     */
    public function getEnabledPushDriverKeyValues(): array
    {
        $sql = 'SELECT id, `name` FROM monkey_push_driver WHERE is_enable = 1 AND is_delete = 0 ORDER BY `ordering` DESC';
        return Be::getDb()->getKeyValues($sql);
    }

    /**
     * 编辑发布器
     *
     * @param array $data 发布器数据
     * @return object
     */
    public function edit(array $data): object
    {
        $db = Be::getDb();

        $isNew = true;
        $pushDriverId = null;
        if (isset($data['id']) && is_string($data['id']) && $data['id'] !== '') {
            $isNew = false;
            $pushDriverId = $data['id'];
        }

        $tupleRule = Be::getTuple('monkey_push_driver');
        if (!$isNew) {
            try {
                $tupleRule->load($pushDriverId);
            } catch (\Throwable $t) {
                throw new ServiceException('发布器（# ' . $pushDriverId . '）不存在！');
            }

            if ($tupleRule->is_delete === 1) {
                throw new ServiceException('发布器（# ' . $pushDriverId . '）不存在！');
            }
        }

        if (!isset($data['name']) || !is_string($data['name'])) {
            throw new ServiceException('发布器名称未填写！');
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
            throw new ServiceException('发布字段缺失！');
        }


        $fields = [];
        $i = 0;
        foreach ($data['fields'] as $field) {
            $i++;
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

            $fields[] = [
                'name' => $field['name'],
                'label' => $field['label'],
                'default' => $field['default'],
            ];
        }
        $data['fields'] = $fields;

        $data['options'] = [];

        $db->startTransaction();
        try {
            $now = date('Y-m-d H:i:s');
            $tupleRule->name = $data['name'];
            $tupleRule->interval = $data['interval'];
            $tupleRule->version = $data['version'];
            $tupleRule->ordering = $data['ordering'];
            $tupleRule->fields = serialize($data['fields']);
            $tupleRule->options = serialize($data['options']);
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

            throw new ServiceException(($isNew ? '新建' : '编辑') . '发布器发生异常！');
        }

        return $tupleRule->toObject();
    }


}
