<?php

namespace Be\App\Monkey\Service;

use Be\App\ServiceException;
use Be\Be;

class Task
{

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

        if ($task->is_enable !== 1) {
            throw new ServiceException('任务（# ' . $taskId . '）未启用！');
        }

        $sql = 'SELECT * FROM monkey_task_field WHERE task_id=? ORDER BY `ordering` DESC';
        $fields = $db->getObjects($sql, [$taskId]);
        $task->fields = $fields;

        return $task;
    }


    /**
     * 获取任务安装网址
     *
     * @param array $params
     * @return string
     */
    public function getTaskInstallUrl(array $params = []): string
    {
        $task = $this->getTask($params['id']);
        return '/task/' . $task->id.'.user.js';
    }



}
