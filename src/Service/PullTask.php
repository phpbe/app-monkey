<?php

namespace Be\App\Monkey\Service;

use Be\App\ServiceException;
use Be\Be;

class PullTask
{

    /**
     * 获取任务
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
            throw new ServiceException('任务（# ' . $pullTaskId . '）不存在！');
        }

        $pullTask->ordering = (int)$pullTask->ordering;
        $pullTask->is_enable = (int)$pullTask->is_enable;
        $pullTask->is_delete = (int)$pullTask->is_delete;

        if ($pullTask->is_enable !== 1) {
            throw new ServiceException('任务（# ' . $pullTaskId . '）未启用！');
        }

        $pullTask->fields = unserialize($pullTask->fields);

        return $pullTask;
    }


    /**
     * 获取任务安装网址
     *
     * @param array $params
     * @return string
     */
    public function getPullTaskInstallUrl(array $params = []): string
    {
        $pullTask = $this->getPullTask($params['id']);
        return '/monkey/pull-task/' . $pullTask->id.'.user.js';
    }



}
