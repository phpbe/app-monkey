<?php

namespace Be\App\Monkey\Controller\Admin;

use Be\AdminPlugin\Detail\Item\DetailItemCode;
use Be\AdminPlugin\Detail\Item\DetailItemHtml;
use Be\AdminPlugin\Detail\Item\DetailItemToggleIcon;
use Be\AdminPlugin\Table\Item\TableItemImage;
use Be\AdminPlugin\Table\Item\TableItemLink;
use Be\AdminPlugin\Form\Item\FormItemSelect;
use Be\AdminPlugin\Table\Item\TableItemSelection;
use Be\AdminPlugin\Table\Item\TableItemSwitch;
use Be\App\System\Controller\Admin\Auth;
use Be\Be;

/**
 * @BeMenuGroup("发布")
 * @BePermissionGroup("发布")
 */
class PushTask extends Auth
{

    /**
     * 发布器
     *
     * @BeMenu("发布任务", icon="bi-arrow-up-square", ordering="3.3")
     * @BePermission("发布任务", ordering="3.3")
     */
    public function pushTasks()
    {
        Be::getAdminPlugin('Curd')->setting([

            'label' => '发布任务',
            'table' => 'monkey_push_task',

            'grid' => [
                'title' => '发布任务',

                'filter' => [
                    ['is_delete', '=', '0'],
                ],

                'orderBy' => 'ordering',
                'orderByDir' => 'DESC',

                'form' => [
                    'items' => [
                        [
                            'name' => 'name',
                            'label' => '名称',
                        ],
                        [
                            'name' => 'is_enable',
                            'label' => '启用状态',
                            'driver' => FormItemSelect::class,
                            'keyValues' => [
                                '1' => '启用',
                                '0' => '禁用',
                            ],
                        ],
                    ],
                ],

                'titleRightToolbar' => [
                    'items' => [
                        [
                            'label' => '新建发布器',
                            'action' => 'create',
                            'target' => 'self', // 'ajax - ajax请求 / dialog - 对话框窗口 / drawer - 抽屉 / self - 当前页面 / blank - 新页面'
                            'ui' => [
                                'icon' => 'el-icon-plus',
                                'type' => 'primary',
                            ]
                        ],
                    ]
                ],

                'tableToolbar' => [
                    'items' => [
                        [
                            'label' => '批量启用',
                            'task' => 'fieldEdit',
                            'postData' => [
                                'field' => 'is_enable',
                                'value' => '1',
                            ],
                            'target' => 'ajax',
                            'confirm' => '确认要启用吗？',
                            'ui' => [
                                'icon' => 'el-icon-check',
                                'type' => 'success',
                            ]
                        ],
                        [
                            'label' => '批量禁用',
                            'task' => 'fieldEdit',
                            'postData' => [
                                'field' => 'is_enable',
                                'value' => '0',
                            ],
                            'target' => 'ajax',
                            'confirm' => '确认要禁用吗？',
                            'ui' => [
                                'icon' => 'el-icon-close',
                                'type' => 'warning',
                            ]
                        ],
                        [
                            'label' => '批量删除',
                            'task' => 'fieldEdit',
                            'postData' => [
                                'field' => 'is_delete',
                                'value' => '1',
                            ],
                            'target' => 'ajax',
                            'confirm' => '确认要删除吗？',
                            'ui' => [
                                'icon' => 'el-icon-delete',
                                'type' => 'danger'
                            ]
                        ],
                    ]
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
                            'name' => 'status_desc',
                            'label' => '状态',
                            'align' => 'center',
                            'width' => '120',
                            'value' =>  function ($row) {
                                switch ($row['status']) {
                                    case 'created':
                                        return '新建';
                                    case 'prepared':
                                        return '即将运行';
                                    case 'running':
                                        return '运行中';
                                    case 'completed':
                                        return '执行完成';
                                    case 'error':
                                        return '执行出错';
                                    default:
                                        return '-';
                                }
                            },
                        ],
                        [
                            'name' => 'ordering',
                            'label' => '排序',
                            'width' => '80',
                            'sortable' => true,
                        ],
                        [
                            'name' => 'is_enable',
                            'label' => '启用/禁用',
                            'driver' => TableItemSwitch::class,
                            'target' => 'ajax',
                            'task' => 'fieldEdit',
                            'width' => '90',
                            'exportValue' => function ($row) {
                                return $row['is_enable'] ? '启用' : '禁用';
                            },
                        ],
                    ],
                    'operation' => [
                        'label' => '操作',
                        'width' => '200',
                        'items' => [
                            [
                                'label' => '',
                                'tooltip' => '启动',
                                'action' => 'run',
                                'target' => 'ajax',
                                'confirm' => '确认要启动么？',
                                'ui' => [
                                    'type' => 'warning',
                                    ':underline' => 'false',
                                    'style' => 'font-size: 20px;',
                                    ':disabled' => 'scope.row.is_enable !== \'1\' || scope.row.status === \'prepared\' || scope.row.status === \'running\'',
                                ],
                                'icon' => 'bi-caret-right-square',
                            ],
                            [
                                'label' => '',
                                'tooltip' => '编辑',
                                'action' => 'edit',
                                'target' => 'self',
                                'ui' => [
                                    ':underline' => 'false',
                                    'style' => 'font-size: 20px;',
                                ],
                                'icon' => 'el-icon-edit',
                            ],
                            [
                                'label' => '',
                                'tooltip' => '删除',
                                'postData' => [
                                    'field' => 'is_delete',
                                    'value' => '1',
                                ],
                                'confirm' => '确认要删除么？',
                                'target' => 'ajax',
                                'ui' => [
                                    'type' => 'danger',
                                    ':underline' => 'false',
                                    'style' => 'font-size: 20px;',
                                ],
                                'icon' => 'el-icon-delete',
                            ],
                        ]
                    ],
                ],
            ],

            'detail' => [
                'title' => '发布任务发布任务详情',
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
                            'name' => 'url',
                            'label' => '发布网址',
                        ],
                        [
                            'name' => 'headers',
                            'label' => '请求头',
                            'driver' => DetailItemCode::class,
                            'language' => 'json',
                            'value' => function($row) {
                                return json_encode(unserialize($row['headers']), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                            }
                        ],
                        [
                            'name' => 'fields',
                            'label' => '字段',
                            'driver' => DetailItemCode::class,
                            'language' => 'json',
                            'value' => function($row) {
                                return json_encode(unserialize($row['fields']), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                            }
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
                            'name' => 'status',
                            'label' => '状态',
                        ],
                        [
                            'name' => 'status_desc',
                            'label' => '状态描述',
                            'value' => function($row) {
                                switch ($row['status']) {
                                    case 'created':
                                        return '新建';
                                    case 'prepared':
                                        return '即将运行';
                                    case 'running':
                                        return '运行中';
                                    case 'completed':
                                        return '执行完成';
                                    case 'error':
                                        return '执行出错';
                                    default:
                                        return '-';
                                }
                            }
                        ],
                        [
                            'name' => 'message',
                            'label' => '消息',
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
     * 发布任务 - 新建
     *
     * @BePermission("发布任务 - 新建", ordering="3.31")
     */
    public function create()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $pushDriverId = $request->get('push_driver_id', '');
        $pullTaskId = $request->get('pull_task_id', '');

        if ($request->isAjax()) {
            try {
                Be::getService('App.Monkey.Admin.PushTask')->edit($request->json('formData'));
                $response->set('success', true);
                $response->set('message', '新建发布任务成功！');
                $response->json();
            } catch (\Throwable $t) {
                $response->set('success', false);
                $response->set('message', $t->getMessage());
                $response->json();
            }
        } else {
            if ($pushDriverId === '' || $pullTaskId === '') {
                $pushDrivers = Be::getService('App.Monkey.Admin.PushDriver')->getEnabledPushDrivers();
                $response->set('pushDrivers', $pushDrivers);
                $response->set('pushDriverId', $pushDriverId);

                $pullTasks = Be::getService('App.Monkey.Admin.PullTask')->getEnabledPullTasks();
                $serviceContent = Be::getService('App.Monkey.Admin.Content');
                foreach ($pullTasks as $pullTask) {
                    $pullTask->content_count = $serviceContent->getPullTaskContentCount($pullTask->id);
                }
                $response->set('pullTasks', $pullTasks);
                $response->set('pullTaskId', $pullTaskId);

                $response->set('title', '新建发布任务');
                $response->display();
            } else {
                $pushDriver = Be::getService('App.Monkey.Admin.PushDriver')->getPushDriver($pushDriverId);
                $response->set('pushDriver', $pushDriver);
                $response->set('pushDriverId', $pushDriverId);

                $pullTask = Be::getService('App.Monkey.Admin.PullTask')->getPullTask($pullTaskId);
                $response->set('pullTask', $pullTask);
                $response->set('pullTaskId', $pullTaskId);

                $response->set('pushTask', false);

                $response->set('title', '新建发布任务');
                $response->display('App.Monkey.Admin.PushTask.edit');
            }
        }
    }

    /**
     * 发布任务 - 编辑
     *
     * @BePermission("发布任务 - 编辑", ordering="3.32")
     */
    public function edit()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        if ($request->isAjax()) {
            try {
                Be::getService('App.Monkey.Admin.PushTask')->edit($request->json('formData'));
                $response->set('success', true);
                $response->set('message', '编辑发布任务成功！');
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
                    $response->redirect(beAdminUrl('Monkey.PushTask.edit', ['id' => $postData['row']['id']]));
                }
            }
        } else {
            $pushTaskId = $request->get('id', '');
            $pushTask = Be::getService('App.Monkey.Admin.PushTask')->getPushTask($pushTaskId);
            $response->set('pushTask', $pushTask);

            $pushDriver = Be::getService('App.Monkey.Admin.PushDriver')->getPushDriver($pushTask->push_driver_id);
            $response->set('pushDriver', $pushDriver);

            $pullTask = Be::getService('App.Monkey.Admin.PullTask')->getPullTask($pushTask->pull_task_id);
            $response->set('pullTask', $pullTask);

            $response->set('title', '编辑发布任务');

            $response->display();
        }
    }


    /**
     * 发布任务 - 启动
     *
     * @BePermission("发布任务 - 启动", ordering="3.33")
     */
    public function run()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $pushTaskId = $request->json('row.id');
        if ($pushTaskId) {
            Be::getService('App.Monkey.Admin.PushTask')->run($pushTaskId);
            $response->success('发布任务已启动');
        }
    }

}
