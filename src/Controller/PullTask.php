<?php

namespace Be\App\Monkey\Controller;

use Be\Be;

class PullTask
{

    /**
     * 安装没猴脚本
     *
     * @BeRoute("\Be\Be::getService('App.Monkey.PullTask')->getPullTaskInstallUrl($params)")
     */
    public function install()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $pullTaskId = $request->get('id', '');
        $pullTask = Be::getService('App.Monkey.PullTask')->getPullTask($pullTaskId);
        $response->set('pullTask', $pullTask);

        $response->header('Content-Type', 'application/javascript');
        $response->display();
    }

}

