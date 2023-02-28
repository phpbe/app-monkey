<?php

namespace Be\App\Monkey\Controller;

use Be\Be;

class Content
{

    /**
     * 接收器
     *
     * @BeRoute("/monkey/content/receive")
     */
    public function receive()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $postData = $request->json();

        try {
            Be::getService('App.Monkey.Content')->receive($postData);
        } catch (\Throwable $t) {
            $response->set('success', false);
            $response->set('message', '录入失败：' . $t->getMessage());
            $response->json();
        }

        $response->set('success', true);
        $response->set('message', '录入成功');
        $response->json();
    }

}

