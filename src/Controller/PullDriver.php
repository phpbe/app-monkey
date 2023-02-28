<?php

namespace Be\App\Monkey\Controller;

use Be\Be;

class PullDriver
{

    /**
     * 安装没猴脚本
     *
     * @BeRoute("\Be\Be::getService('App.Monkey.PullDriver')->getPullDriverInstallUrl($params)")
     */
    public function install()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $pullDriverId = $request->get('id', '');
        $pullDriver = Be::getService('App.Monkey.PullDriver')->getPullDriver($pullDriverId);
        $response->set('pullDriver', $pullDriver);

        $response->header('Content-Type', 'application/javascript');
        $response->display();
    }

}

