<?php

namespace Be\App\Monkey\Controller\Admin;

use Be\AdminPlugin\Detail\Item\DetailItemCode;
use Be\AdminPlugin\Detail\Item\DetailItemHtml;
use Be\AdminPlugin\Detail\Item\DetailItemToggleIcon;
use Be\AdminPlugin\Operation\Item\OperationItemLink;
use Be\AdminPlugin\Table\Item\TableItemImage;
use Be\AdminPlugin\Table\Item\TableItemLink;
use Be\AdminPlugin\Form\Item\FormItemSelect;
use Be\AdminPlugin\Table\Item\TableItemSelection;
use Be\AdminPlugin\Table\Item\TableItemSwitch;
use Be\App\System\Controller\Admin\Auth;
use Be\Be;

/**
 * @BeMenuGroup("采集")
 * @BePermissionGroup("采集")
 */
class Task extends Auth
{

    /**
     * 采集采集器
     *
     * @BeMenu("采集任务", icon="el-icon-video-play", ordering="1.2")
     * @BePermission("采集任务", ordering="1.2")
     */
    public function tasks()
    {

        $ruleKeyValues = Be::getService('App.Monkey.Admin.Rule')->getRuleKeyValues();

        Be::getAdminPlugin('Curd')->setting([

            'label' => '采集任务',
            'table' => 'monkey_task',

            'grid' => [
                'title' => '采集任务',

                'filter' => [
                    ['is_delete', '=', '0'],
                ],

                'orderBy' => 'ordering',
                'orderByDir' => 'DESC',

                'form' => [
                    'items' => [
                        [
                            'name' => 'is_enable',
                            'label' => '启用状态',
                            'driver' => FormItemSelect::class,
                            'keyValues' => [
                                '1' => '启用',
                                '0' => '禁用',
                            ],
                        ],
                        [
                            'name' => 'rule_id',
                            'label' => '采集器',
                            'driver' => FormItemSelect::class,
                            'keyValues' => $ruleKeyValues,
                        ],
                    ],
                ],

                'titleRightToolbar' => [
                    'items' => [
                        [
                            'label' => '新建采集任务',
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
                            'label' => '采集任务名称',
                            'driver' => TableItemLink::class,
                            'align' => 'left',
                            'task' => 'detail',
                            'drawer' => [
                                'width' => '80%'
                            ],
                        ],
                        [
                            'name' => 'rule_name',
                            'label' => '采集器名称',
                            'align' => 'left',
                            'value' => function ($row) {
                                $sql = 'SELECT `name` FROM monkey_rule WHERE id = ?';
                                $ruleName = Be::getDb()->getValue($sql, [$row['rule_id']]);
                                if ($ruleName) {
                                    return $ruleName;
                                } else {
                                    return '-';
                                }
                            },
                        ],
                        [
                            'name' => 'content_count',
                            'label' => '采集的内容',
                            'align' => 'center',
                            'width' => '120',
                            'driver' => TableItemLink::class,
                            'value' => function ($row) {
                                $sql = 'SELECT COUNT(*) FROM monkey_content WHERE task_id = ?';
                                $count = Be::getDb()->getValue($sql, [$row['id']]);
                                return $count;
                            },
                            'action' => 'showContents',
                            'target' => 'self',
                        ],
                        [
                            'name' => 'version',
                            'label' => '子版本',
                            'width' => '80',
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
                                'tooltip' => '安装',
                                'action' => 'install',
                                'target' => 'blank',
                                'ui' => [
                                    'type' => 'success',
                                    ':underline' => 'false',
                                    'style' => 'font-size: 20px;',
                                    ':disabled' => 'scope.row.is_enable !== \'1\'',
                                ],
                                'icon' => 'el-icon-plus',
                            ],
                            [
                                'label' => '',
                                'tooltip' => '启动',
                                'action' => 'run',
                                'target' => 'blank',
                                'ui' => [
                                    'type' => 'danger',
                                    ':underline' => 'false',
                                    'style' => 'font-size: 20px;',
                                    ':disabled' => 'scope.row.is_enable !== \'1\'',
                                ],
                                'icon' => 'el-icon-video-play',
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
                'title' => '采集任务详情',
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
                            'name' => 'author',
                            'label' => '作者',
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
                            'name' => '获取下一页脚本',
                            'label' => 'get_next_page_script',
                            'driver' => DetailItemCode::class,
                            'language' => 'javascript',
                        ],
                        [
                            'name' => '获取页面链接脚本',
                            'label' => 'get_links_script',
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
     * 新建采集任务
     *
     * @BePermission("新建", ordering="1.21")
     */
    public function create()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $ruleId = $request->get('rule_id', '');

        if ($request->isAjax()) {
            try {
                Be::getService('App.Monkey.Admin.Task')->edit($request->json('formData'));
                $response->set('success', true);
                $response->set('message', '新建采集任务成功！');
                $response->json();
            } catch (\Throwable $t) {
                $response->set('success', false);
                $response->set('message', $t->getMessage());
                $response->json();
            }
        } else {
            if ($ruleId === '') {
                $rules = Be::getService('App.Monkey.Admin.Rule')->getRules();
                $response->set('rules', $rules);

                $response->set('title', '新建采集任务');
                $response->display('App.Monkey.Admin.Task.selectRule');
            } else {
                $rule = Be::getService('App.Monkey.Admin.Rule')->getRule($ruleId);
                $response->set('rule', $rule);

                $response->set('task', false);

                $response->set('title', '新建采集任务');
                $response->display('App.Monkey.Admin.Task.edit');
            }
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
                Be::getService('App.Monkey.Admin.Task')->edit($request->json('formData'));
                $response->set('success', true);
                $response->set('message', '编辑采集任务成功！');
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
                    $response->redirect(beAdminUrl('Monkey.Task.edit', ['id' => $postData['row']['id']]));
                }
            }
        } else {
            $taskId = $request->get('id', '');
            $task = Be::getService('App.Monkey.Admin.Task')->getTask($taskId);
            $response->set('task', $task);

            $rule = Be::getService('App.Monkey.Admin.Rule')->getRule($task->rule_id);
            $response->set('rule', $rule);

            $response->set('title', '编辑采集任务');

            $response->display();
        }
    }

    /**
     * 安装脚本
     *
     * @BePermission("*")
     */
    public function install()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $postData = $request->post('data', '', '');
        if ($postData) {
            $postData = json_decode($postData, true);
            if (isset($postData['row']['id']) && $postData['row']['id']) {
                //$response->redirect(beUrl('Monkey.Task.install', ['id' => $postData['row']['id']]));

                $response->write('<meta charset="utf-8" />');
                $response->write('如果您已安装油猴插件，将自动弹出安装/更新油猴脚本界面，如果未弹出，请检查浏览器扩展！');
                $response->write('<script>');
                $response->write('window.location.href="' . beUrl('Monkey.Task.install', ['id' => $postData['row']['id']]) . '";');
                $response->write( '</script>');
            }
        }
    }

    /**
     * 开始采集
     *
     * @BePermission("*")
     */
    public function run()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $postData = $request->post('data', '', '');
        if ($postData) {
            $postData = json_decode($postData, true);
            if (isset($postData['row']['id']) && $postData['row']['id']) {
                $task = Be::getService('App.Monkey.Admin.Task')->getTask($postData['row']['id']);
                $response->set('task', $task);
                $response->set('title', '即将开始采集');
                $response->display();
            }
        }
    }

    /**
     * 查看采集的内容
     *
     * @BePermission("*")
     */
    public function showContents()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $postData = $request->post('data', '', '');
        if ($postData) {
            $postData = json_decode($postData, true);
            if (isset($postData['row']['id']) && $postData['row']['id']) {
                $response->redirect(beAdminUrl('Monkey.Content.contents', ['task_id' => $postData['row']['id']]));
            }
        }
    }
}
