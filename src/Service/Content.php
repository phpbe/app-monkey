<?php

namespace Be\App\Monkey\Service;

use Be\App\ServiceException;
use Be\Be;

class Content
{

    /**
     * 接收数据
     *
     * @param array $data
     */
    public function receive(array $data)
    {
        if (!isset($data['pull_task_id']) || !is_string($data['pull_task_id'])) {
            throw new ServiceException('参数（pull_task_id）参数缺失！');
        }
        $data['pull_task_id'] = trim($data['pull_task_id']);
        if ($data['pull_task_id'] === '') {
            throw new ServiceException('参数（pull_task_id）不能为空！');
        }

        $db = Be::getDb();
        $sql = 'SELECT * FROM monkey_pull_task WHERE id=? AND is_delete = 0';
        $pullTask = $db->getObject($sql, [$data['pull_task_id']]);
        if (!$pullTask) {
            throw new ServiceException('采集任务（# ' . $data['pull_task_id'] . '）不存在！');
        }

        $fields = unserialize($pullTask->fields);

        if (!isset($data['url']) || !is_string($data['url'])) {
            throw new ServiceException('参数（url）缺失！');
        }

        $data['url'] = trim($data['url']);
        if ($data['url'] === '') {
            throw new ServiceException('参数（url）不能为空！');
        }

        if (!isset($data['fields']) || !is_array($data['fields']) || count($data['fields']) === 0) {
            throw new ServiceException('参数（fields）缺失！');
        }

        $i = 1;
        foreach ($data['fields'] as &$field) {
            if (!isset($field['name']) || !is_string($field['name'])) {
                throw new ServiceException('参数（fields）第 ' . $i . ' 项的子参数（name）缺失！');
            }
            $field['name'] = trim($field['name']);
            if ($field['name'] === '') {
                throw new ServiceException('参数（fields）第 ' . $i . ' 项的子参数（name）不能为空！');
            }

            $found = false;
            foreach ($fields as $f) {
                if ($field['name'] === $f['name']) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                throw new ServiceException('参数（fields）第' . $i . '项的子参数（name：' . $field['name'] . '）无法识别！');
            }

            if (!isset($field['content']) || !is_string($field['content'])) {
                throw new ServiceException('参数（fields）第 ' . $i . ' 项的子参数（content）缺失！');
            }
            $field['content'] = trim($field['content']);

            $i++;
        }
        unset($field);

        $title = '';
        foreach ($fields as $f) {
            $found = false;
            foreach ($data['fields'] as $field) {
                if ($field['name'] === $f['name']) {
                    $found = true;

                    if ($f['is_title'] === 1) {
                        $title = $field['content'];
                    }
                    break;
                }
            }

            if (!$found) {
                throw new ServiceException('采集字段 ' . $f->name . ' 缺失！');
            }
        }

        if (mb_strlen($data['url']) > 120) {
            $data['url'] = mb_substr($data['url'], 0 ,120);
        }

        if (mb_strlen($title) > 200) {
            $title = mb_substr($title, 0 ,200);
        }

        $db->startTransaction();
        try {

            $tupleContent = Be::getTuple('monkey_content');
            try {
                $tupleContent->loadBy([
                    'pull_task_id' => $data['pull_task_id'],
                    'url' => $data['url'],
                ]);
            } catch (\Throwable $t) {
            }

            $isNew = !$tupleContent->isLoaded();

            $now = date('Y-m-d H:i:s');
            $tupleContent->pull_task_id = $data['pull_task_id'];
            $tupleContent->url = $data['url'];
            $tupleContent->title = $title;
            $tupleContent->fields = serialize($data['fields']);
            $tupleContent->update_time = $now;
            if ($isNew) {
                $tupleContent->create_time = $now;
                $tupleContent->insert();
            } else {
                $tupleContent->update();
            }

            $db->commit();

        } catch (\Throwable $t) {
            $db->rollback();
            Be::getLog()->error($t);

            throw new ServiceException(($isNew ? '新增' : '更新') . '采集肉容发生异常！');
        }

        return $tupleContent->toObject();
    }


}
