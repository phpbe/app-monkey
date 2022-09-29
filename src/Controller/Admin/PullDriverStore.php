<?php

namespace Be\App\Monkey\Controller\Admin;

use Be\AdminPlugin\Detail\Item\DetailItemCode;
use Be\AdminPlugin\Detail\Item\DetailItemHtml;
use Be\AdminPlugin\Detail\Item\DetailItemToggleIcon;
use Be\AdminPlugin\Table\Item\TableItemLink;
use Be\AdminPlugin\Form\Item\FormItemSelect;
use Be\AdminPlugin\Table\Item\TableItemSelection;
use Be\AdminPlugin\Table\Item\TableItemSwitch;
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
    public function storeDrivers()
    {
        Be::getAdminPlugin('Curd')->setting([

            'label' => '采集器商城',
            'table' => 'monkey_pull_driver',

            'grid' => [
                'title' => '采集器商城',

                'orderBy' => 'ordering',
                'orderByDir' => 'DESC',

                'form' => [
                    'items' => [
                        [
                            'name' => 'name',
                            'label' => '名称',
                        ],
                    ],
                ],

                'table' => [

                    // 未指定时取表的所有字段
                    'items' => [
                        [
                            'driver' => TableItemSelection::class,
                            'width' => '50',
                        ],
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
                            'value' => function ($row) {
                                $sql = 'SELECT COUNT(*) FROM monkey_pull_driver_field WHERE pull_driver_id = ?';
                                $count = Be::getDb()->getValue($sql, [$row['id']]);
                                return $count;
                            },
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
            ],

            'detail' => [
                'title' => '采集器采集器详情',
                'theme' => 'Blank',
                'form' => [
                    'items' => [
                        [
                            'name' => 'id',
                            'label' => 'ID',
                        ],
                        [
                            'name' => 'name',
                            'label' => '名称',
                        ],
                        [
                            'name' => 'description',
                            'label' => '描述',
                            'driver' => DetailItemHtml::class,
                        ],
                        [
                            'name' => 'version',
                            'label' => '版本号',
                        ],
                        [
                            'name' => 'match_1',
                            'label' => '匹配网址1',
                        ],
                        [
                            'name' => 'match_2',
                            'label' => '匹配网址2',
                        ],
                        [
                            'name' => 'match_3',
                            'label' => '匹配网址3',
                        ],
                        [
                            'name' => 'start_page',
                            'label' => '起始页',
                        ],
                        [
                            'name' => 'get_next_page_script',
                            'label' => '获取下一页脚本',
                            'driver' => DetailItemCode::class,
                            'language' => 'javascript',
                        ],
                        [
                            'name' => 'get_links_script',
                            'label' => '获取页面链接脚本',
                            'driver' => DetailItemCode::class,
                            'language' => 'javascript',
                        ],
                        [
                            'name' => 'interval',
                            'label' => '间隔时间（毫秒）',
                        ],
                        [
                            'name' => 'ordering',
                            'label' => '排序',
                        ],
                        [
                            'name' => 'is_enable',
                            'label' => '启用/禁用',
                            'driver' => DetailItemToggleIcon::class,
                        ],
                        [
                            'name' => 'create_time',
                            'label' => '创建时间',
                        ],
                        [
                            'name' => 'update_time',
                            'label' => '更新时间',
                        ],
                    ]
                ],
            ],

        ])->execute();
    }

    /**
     * 新建采集器
     *
     * @BePermission("新建", ordering="1.21")
     */
    public function create()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        if ($request->isAjax()) {
            try {
                Be::getService('App.Monkey.Admin.PullDriver')->edit($request->json('formData'));
                $response->set('success', true);
                $response->set('message', '新建采集器成功！');
                $response->json();
            } catch (\Throwable $t) {
                $response->set('success', false);
                $response->set('message', $t->getMessage());
                $response->json();
            }
        } else {
            $response->set('pullDriver', false);

            $response->set('title', '新建采集器');

            //$response->display();
            $response->display('App.Monkey.Admin.PullDriver.edit');
        }
    }

    /**
     * 编辑
     *
     * @BePermission("编辑", ordering="1.22")
     */
    public function edit()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        if ($request->isAjax()) {
            try {
                Be::getService('App.Monkey.Admin.PullDriver')->edit($request->json('formData'));
                $response->set('success', true);
                $response->set('message', '编辑采集器成功！');
                $response->json();
            } catch (\Throwable $t) {
                $response->set('success', false);
                $response->set('message', $t->getMessage());
                $response->json();
            }
        } elseif ($request->isPost()) {
            $postData = $request->post('data', '', '');
            if ($postData) {
                $postData = json_decode($postData, true);
                if (isset($postData['row']['id']) && $postData['row']['id']) {
                    $response->redirect(beAdminUrl('Monkey.PullDriver.edit', ['id' => $postData['row']['id']]));
                }
            }
        } else {
            $pullDriverId = $request->get('id', '');
            $pullDriver = Be::getService('App.Monkey.Admin.PullDriver')->getPullDriver($pullDriverId);
            $response->set('pullDriver', $pullDriver);

            $response->set('title', '编辑采集器');

            $response->display();
        }
    }

    /**
     * 查看采集任务
     *
     * @BePermission("*")
     */
    public function showPullTasks()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $postData = $request->post('data', '', '');
        if ($postData) {
            $postData = json_decode($postData, true);
            if (isset($postData['row']['id']) && $postData['row']['id']) {
                $response->redirect(beAdminUrl('Monkey.PullTask.tasks', ['pull_driver_id' => $postData['row']['id']]));
            }
        }
    }

    /**
     * 创建采集任务
     *
     * @BePermission("*")
     */
    public function createPullTask()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $postData = $request->post('data', '', '');
        if ($postData) {
            $postData = json_decode($postData, true);
            if (isset($postData['row']['id']) && $postData['row']['id']) {
                $response->redirect(beAdminUrl('Monkey.PullTask.create', ['pull_driver_id' => $postData['row']['id']]));
            }
        }
    }

}
