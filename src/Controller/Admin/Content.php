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
 * @BeMenuGroup("采集的内容", icon="el-icon-document-copy", ordering="2")
 * @BePermissionGroup("采集的内容")
 */
class Content extends Auth
{

    /**
     * 内容列表
     *
     * @BeMenu("内容列表", icon="el-icon-document-copy", ordering="2.1")
     * @BePermission("内容列表", ordering="1.2")
     */
    public function contents()
    {
        Be::getAdminPlugin('Curd')->setting([

            'label' => '采集的内容',
            'table' => 'monkey_content',

            'grid' => [
                'title' => '采集的内容',

                'orderBy' => 'update_time',
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
                            'name' => 'task_name',
                            'label' => '任务名称',
                            'width' => '250',
                            'align' => 'left',
                            'value' => function ($row) {
                                $sql = 'SELECT `name` FROM monkey_task WHERE id = ?';
                                $taskName = Be::getDb()->getValue($sql, [$row['task_id']]);
                                if ($taskName) {
                                    return $taskName;
                                } else {
                                    return '-';
                                }
                            },
                        ],
                        [
                            'name' => 'title',
                            'label' => '标题',
                            'width' => '250',
                            'align' => 'left',
                            'driver' => TableItemLink::class,
                            'action' => 'detail',
                            'target' => 'drawer',
                            'drawer' => [
                                'width' => '75%',
                            ]
                        ],
                        [
                            'name' => 'url',
                            'label' => '采集网址',
                            'align' => 'left',
                            'driver' => TableItemLink::class,
                            'target' => 'blank',
                            'ui' => [
                                'rel' => 'noreferrer',
                            ],
                        ],
                        [
                            'name' => 'update_time',
                            'label' => '更新时间',
                            'width' => '180',
                        ],
                    ],
                    'operation' => [
                        'label' => '操作',
                        'width' => '200',
                        'items' => [
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
        ])->execute();
    }


    /**
     * 查看任务
     *
     * @BePermission("内容详情")
     */
    public function detail()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $postData = $request->post('data', '', '');
        if ($postData) {
            $postData = json_decode($postData, true);
            if (isset($postData['row']['id']) && $postData['row']['id']) {
                $content = Be::getService('App.Monkey.Admin.Content')->getContent($postData['row']['id']);
                $response->set('content', $content);
                $response->set('title', $content->title);
                $response->display(null, 'Blank');
            }
        }
    }


}
