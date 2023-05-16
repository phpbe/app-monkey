<?php

namespace Be\App\Monkey\Service\Admin;

use Be\App\ServiceException;
use Be\Be;

class PushTask
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

        $tuplePushDriver = Be::getTuple('monkey_push_driver');
        if (!$isNew) {
            try {
                $tuplePushDriver->load($pushDriverId);
            } catch (\Throwable $t) {
                throw new ServiceException('发布器（# ' . $pushDriverId . '）不存在！');
            }

            if ($tuplePushDriver->is_delete === 1) {
                throw new ServiceException('发布器（# ' . $pushDriverId . '）不存在！');
            }
        }

        if (!isset($data['name']) || !is_string($data['name'])) {
            throw new ServiceException('发布器名称未填写！');
        }

        if (!isset($data['url']) || !is_string($data['url'])) {
            $data['url'] = '';
        }

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

                $headers[] = [
                    'name' => $header['name'],
                    'value' => $header['value'],
                ];
            }

            $data['headers'] = $headers;
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

            if (!isset($field['required']) || !is_numeric($field['required'])) {
                $field['required'] = 0;
            }
            $field['required'] = (int)$field['required'];
            if (!in_array($field['required'], [0, 1])) {
                $field['required'] = 0;
            }

            $fields[] = [
                'name' => $field['name'],
                'label' => $field['label'],
                'default' => $field['default'],
                'required' => $field['required'],
            ];
        }
        $data['fields'] = $fields;

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

        $db->startTransaction();
        try {
            $now = date('Y-m-d H:i:s');
            $tuplePushDriver->name = $data['name'];
            $tuplePushDriver->url = $data['url'];
            $tuplePushDriver->headers = serialize($data['headers']);
            $tuplePushDriver->fields = serialize($data['fields']);
            $tuplePushDriver->interval = $data['interval'];
            $tuplePushDriver->version = $data['version'];
            $tuplePushDriver->ordering = $data['ordering'];
            $tuplePushDriver->is_enable = $data['is_enable'];
            $tuplePushDriver->update_time = $now;
            if ($isNew) {
                $tuplePushDriver->is_delete = 0;
                $tuplePushDriver->create_time = $now;
                $tuplePushDriver->insert();
            } else {
                $tuplePushDriver->update();
            }

            $db->commit();

        } catch (\Throwable $t) {
            $db->rollback();
            Be::getLog()->error($t);

            throw new ServiceException(($isNew ? '新建' : '编辑') . '发布器发生异常！');
        }

        return $tuplePushDriver->toObject();
    }


}
