<?php

namespace Be\App\Monkey\Controller\Admin;

use Be\AdminPlugin\Table\Item\TableItemLink;
use Be\App\System\Controller\Admin\Auth;
use Be\Be;

/**
 * @BeMenuGroup("发布", icon="bi-cloud-arrow-up", ordering="3")
 * @BePermissionGroup("发布")
 */
class PushDriverStore extends Auth
{

    /**
     * 发布器
     *
     * @BeMenu("发布器商店", icon="bi-cloud-upload-fill", ordering="3.1")
     * @BePermission("发布器商店", ordering="3.1")
     */
    public function pushDrivers()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();
        if ($request->isAjax()) {
            $gridData = [];
            $response->set('success', true);
            $response->set('data', [
                'total' => 0,
                'gridData' => $gridData,
            ]);
            $response->json();
        } else {
            Be::getAdminPlugin('Grid')->setting([
                'title' => '发布器商店',
                'pageSize' => 10,

                'table' => [
                    'items' => [
                        [
                            'name' => 'name',
                            'label' => '名称',
                            'driver' => TableItemLink::class,
                            'align' => 'left',
                            'task' => 'detail',
                            'drawer' => [
                                'width' => '80%'
                            ],
                        ],
                        [
                            'name' => 'version',
                            'label' => '版本号',
                            'width' => '80',
                        ],
                    ],
                    'operation' => [
                        'label' => '操作',
                        'width' => '120',
                        'items' => [
                            [
                                'label' => '',
                                'tooltip' => '拉取到本地',
                                'action' => 'pull',
                                'target' => 'self',
                                'ui' => [
                                    'type' => 'success',
                                    ':underline' => 'false',
                                    'style' => 'font-size: 20px;',
                                ],
                                'icon' => 'el-icon-download',
                            ],
                        ]
                    ],
                ],
            ])->execute();
        }
    }


}
