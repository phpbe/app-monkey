<?php

namespace Be\App\Monkey\Controller;

use Be\Be;

class Task
{

    /**
     * 安装没猴脚本
     *
     * @BeRoute("\Be\Be::getService('App.Monkey.Task')->getTaskInstallUrl($params)")
     */
    public function install()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $taskId = $request->get('id', '');
        $task = Be::getService('App.Monkey.Task')->getTask($taskId);
        $response->set('task', $task);

        $response->header('Content-Type', 'application/javascript');
        $response->display();
    }

}

