<?php

namespace Be\App\Monkey\Controller\Admin;

use Be\AdminPlugin\Detail\Item\DetailItemCode;
use Be\AdminPlugin\Detail\Item\DetailItemHtml;
use Be\AdminPlugin\Detail\Item\DetailItemToggleIcon;
use Be\AdminPlugin\Table\Item\TableItemImage;
use Be\AdminPlugin\Table\Item\TableItemLink;
use Be\AdminPlugin\Form\Item\FormItemSelect;
use Be\AdminPlugin\Table\Item\TableItemProgress;
use Be\AdminPlugin\Table\Item\TableItemSelection;
use Be\AdminPlugin\Table\Item\TableItemSwitch;
use Be\AdminPlugin\Table\Item\TableItemToggleTag;
use Be\App\System\Controller\Admin\Auth;
use Be\Be;

/**
 * @BePermissionGroup("发布器")
 */
class PushDriver extends Auth
{

    /**
     * 发布器
     *
     * @BeMenu("发布器", icon="bi-cloud-upload", ordering="3.2")
     * @BePermission("发布器", ordering="3.2")
     */
    public function pushDrivers()
    {
        Be::getAdminPlugin('Curd')->setting([

            'label' => '发布器',
            'table' => 'monkey_push_driver',

            'grid' => [
                'title' => '发布器',

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
                                    case 'create':
                                        return '新建';
                                    case 'pending':
                                        return '即将运行';
                                    case 'running':
                                        return '运行中';
                                    case 'finish':
                                        return '执行完成';
                                    case 'error':
                                        return '执行出错';
                                    default:
                                        return '-';
                                }
                            },
                        ],
                        [
                            'name' => 'total',
                            'label' => '总计',
                            'width' => '90',
                            'value' =>  function ($row) {
                                $sql = 'SELECT COUNT(*) FROM monkey_content WHERE pull_driver_id = ?';
                                $count = (int)Be::getDb()->getValue($sql, [$row['pull_driver_id']]);
                                return $count;
                            },
                        ],
                        [
                            'name' => 'pushed',
                            'label' => '已发布',
                            'driver' => TableItemLink::class,
                            'action' => 'goPushDriverLog',
                            'target' => 'blank',
                            'width' => '90',
                            'value' =>  function ($row) {
                                $sql = 'SELECT COUNT(DISTINCT content_id) FROM monkey_push_driver_log WHERE push_driver_id = ?';
                                $count = (int)Be::getDb()->getValue($sql, [$row['id']]);
                                return $count;
                            },
                        ],
                        [
                            'name' => 'process',
                            'label' => '进度',
                            'width' => '120',
                            'driver' => TableItemProgress::class,
                            'value' =>  function ($row) {
                                if ($row['total'] === 0) {
                                    return 0;
                                } else {
                                    return round($row['pushed'] * 100 / $row['total'], 1);
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
                                    ':disabled' => 'scope.row.is_enable !== \'1\' || scope.row.status === \'pending\' || scope.row.status === \'running\'',
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
                                'task' => 'fieldEdit',
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
                'title' => '发布器详情',
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
                                    case 'create':
                                        return '新建';
                                    case 'pending':
                                        return '即将运行';
                                    case 'running':
                                        return '运行中';
                                    case 'finish':
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
     * 新建发布器
     *
     * @BePermission("新建", ordering="3.21")
     */
    public function create()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $pullDriverId = $request->get('pull_driver_id', '');

        if ($request->isAjax()) {
            try {
                Be::getService('App.Monkey.Admin.PushDriver')->edit($request->json('formData'));
                $response->set('success', true);
                $response->set('message', '新建发布器成功！');
                $response->json();
            } catch (\Throwable $t) {
                $response->set('success', false);
                $response->set('message', $t->getMessage());
                $response->json();
            }
        } else {
            if ($pullDriverId === '') {
                $pullDrivers = Be::getService('App.Monkey.Admin.PullDriver')->getEnabledPullDrivers();
                $serviceContent = Be::getService('App.Monkey.Admin.Content');
                foreach ($pullDrivers as $pullDriver) {
                    $pullDriver->content_count = $serviceContent->getPullDriverContentCount($pullDriver->id);
                }
                $response->set('pullDrivers', $pullDrivers);
                $response->set('pullDriverId', $pullDriverId);

                $response->set('title', '新建发布器');
                $response->display();
            } else {
                $pullDriver = Be::getService('App.Monkey.Admin.PullDriver')->getPullDriver($pullDriverId);
                $response->set('pullDriver', $pullDriver);
                $response->set('pullDriverId', $pullDriverId);

                $response->set('pushDriver', false);

                $response->set('title', '新建发布器');
                $response->display('App.Monkey.Admin.PushDriver.edit');
            }
        }
    }

    /**
     * 编辑
     *
     * @BePermission("编辑", ordering="3.22")
     */
    public function edit()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        if ($request->isAjax()) {
            try {
                Be::getService('App.Monkey.Admin.PushDriver')->edit($request->json('formData'));
                $response->set('success', true);
                $response->set('message', '编辑发布器成功！');
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
                    $response->redirect(beAdminUrl('Monkey.PushDriver.edit', ['id' => $postData['row']['id']]));
                }
            }
        } else {
            $pushDriverId = $request->get('id', '');
            $pushDriver = Be::getService('App.Monkey.Admin.PushDriver')->getPushDriver($pushDriverId);
            $response->set('pushDriver', $pushDriver);

            $pullDriver = Be::getService('App.Monkey.Admin.PullDriver')->getPullDriver($pushDriver->pull_driver_id);
            $response->set('pullDriver', $pullDriver);

            $response->set('title', '编辑发布器');

            $response->display();
        }
    }



    /**
     * 发布器 - 启动
     *
     * @BePermission("发布器 - 启动", ordering="3.33")
     */
    public function run()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $pushDriverId = $request->json('row.id');
        if ($pushDriverId) {
            Be::getService('App.Monkey.Admin.PushDriver')->run($pushDriverId);
            $response->success('发布器已启动');
        }
    }


    /**
     * 发布器 - 日志
     *
     * @BePermission("发布器 - 日志", ordering="3.34")
     */
    public function goPushDriverLog()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $postData = $request->post('data', '', '');
        if ($postData) {
            $postData = json_decode($postData, true);
            if (isset($postData['row']['id']) && $postData['row']['id']) {
                $response->redirect(beAdminUrl('Monkey.PushDriver.pushDriverLogs', ['push_driver_id' => $postData['row']['id']]));
            }
        }
    }

    /**
     * 发布器 - 日志
     *
     * @BePermission("发布器 - 日志", ordering="3.34")
     */
    public function pushDriverLogs()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $pushDriverId = $request->get('push_driver_id', '');

        Be::getAdminPlugin('Curd')->setting([

            'label' => '发布器日志',
            'table' => 'monkey_push_driver_log',

            'grid' => [
                'title' => '发布器日志',

                'filter' => [
                    ['push_driver_id', '=', $pushDriverId],
                ],

                'orderBy' => 'create_time',
                'orderByDir' => 'DESC',

                'tableToolbar' => [
                    'items' => [
                        [
                            'label' => '批量删除',
                            'task' => 'delete',
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
                            'name' => 'content_title',
                            'label' => '文章标题',
                            'align' => 'left',
                            'value' => function($row) {
                                $sql = 'SELECT title FROM monkey_content WHERE id=?';
                                $contentTitle = Be::getDb()->getValue($sql, [$row['content_id']]);
                                if ($contentTitle) {
                                    return $contentTitle;
                                }
                                return '';
                            },
                        ],
                        [
                            'name' => 'success',
                            'label' => '是否成功',
                            'width' => '90',
                            'driver' => TableItemToggleTag::class,
                        ],
                        [
                            'name' => 'create_time',
                            'label' => '时间',
                            'width' => '180',
                            'sortable' => true,
                        ],
                    ],
                    'operation' => [
                        'label' => '操作',
                        'width' => '120',
                        'items' => [
                            [
                                'label' => '',
                                'tooltip' => '查看',
                                'task' => 'detail',
                                'drawer' => [
                                    'title' => '日志详情',
                                    'width' => '80%'
                                ],
                                'ui' => [
                                    'type' => 'primary',
                                    ':underline' => 'false',
                                    'style' => 'font-size: 20px;',
                                ],
                                'icon' => 'el-icon-search',
                            ],
                            [
                                'label' => '',
                                'tooltip' => '删除',
                                'task' => 'delete',
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
                'title' => '发布器日志详情',
                'theme' => 'Blank',
                'form' => [
                    'items' => [
                        [
                            'name' => 'id',
                            'label' => 'ID',
                        ],
                        [
                            'name' => 'content_title',
                            'label' => '文章标题',
                            'value' => function($row) {
                                $sql = 'SELECT title FROM monkey_content WHERE id=?';
                                $contentTitle = Be::getDb()->getValue($sql, [$row['content_id']]);
                                if ($contentTitle) {
                                    return $contentTitle;
                                }
                                return '';
                            },
                        ],
                        [
                            'name' => 'request',
                            'label' => '请求数据',
                            'driver' => DetailItemCode::class,
                            'language' => 'json',
                            'value' => function($row) {
                                return json_encode(unserialize($row['request']), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                            }
                        ],
                        [
                            'name' => 'response',
                            'label' => '响应数据',
                            'driver' => DetailItemCode::class,
                            'language' => 'auto',
                            'value' => function($row) {
                                return $row['response'];
                            }
                        ],
                        [
                            'name' => 'success',
                            'label' => '是否成功',
                            'driver' => DetailItemToggleIcon::class,
                        ],
                        [
                            'name' => 'message',
                            'label' => '消息',
                        ],
                        [
                            'name' => 'create_time',
                            'label' => '创建时间',
                        ],
                    ]
                ],
            ],

        ])->execute();
    }


}
