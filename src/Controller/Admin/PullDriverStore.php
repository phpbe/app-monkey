<?php

namespace Be\App\Monkey\Controller\Admin;

use Be\AdminPlugin\Detail\Item\DetailItemCode;
use Be\AdminPlugin\Detail\Item\DetailItemHtml;
use Be\AdminPlugin\Detail\Item\DetailItemToggleIcon;
use Be\AdminPlugin\Table\Item\TableItemIcon;
use Be\AdminPlugin\Table\Item\TableItemLink;
use Be\AdminPlugin\Form\Item\FormItemSelect;
use Be\AdminPlugin\Table\Item\TableItemSelection;
use Be\AdminPlugin\Table\Item\TableItemSwitch;
use Be\AdminPlugin\Table\Item\TableItemTag;
use Be\AdminPlugin\Toolbar\Item\ToolbarItemLink;
use Be\App\System\Controller\Admin\Auth;
use Be\Be;

/**
 * @BeMenuGroup("采集", icon="bi-cloud-arrow-down", ordering="1")
 * @BePermissionGroup("采集")
 */
class PullDriverStore extends Auth
{

    /**
     * 采集器商店
     *
     * @BeMenu("采集器商店", icon="bi-cloud-download-fill", ordering="1.1")
     * @BePermission("采集器商店", ordering="1.1")
     */
    public function pullDrivers()
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
                'title' => '采集器商店',
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
                            'name' => 'start_page',
                            'label' => '起始网址',
                            'driver' => TableItemLink::class,
                            'align' => 'left',
                            'target' => 'blank',
                            'ui' => [
                                'rel' => 'noreferrer',
                            ],
                        ],
                        [
                            'name' => 'filed_count',
                            'label' => '字段数',
                            'align' => 'center',
                            'width' => '80',
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
