<?php

namespace Be\App\Monkey\Controller\Admin;


use Be\AdminPlugin\Table\Item\TableItemCustom;
use Be\AdminPlugin\Table\Item\TableItemLink;
use Be\AdminPlugin\Form\Item\FormItemSelect;
use Be\AdminPlugin\Table\Item\TableItemSelection;
use Be\App\System\Controller\Admin\Auth;
use Be\Be;

/**
 * @BeMenuGroup("内容", icon="el-icon-document-copy", ordering="2")
 * @BePermissionGroup("内容")
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
        $pullTaskKeyValues = Be::getService('App.Monkey.Admin.PullTask')->getPullTaskKeyValues();

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
                            'name' => 'title',
                            'label' => '标题',
                        ],
                        [
                            'name' => 'pull_task_id',
                            'label' => '采集任务',
                            'driver' => FormItemSelect::class,
                            'keyValues' => $pullTaskKeyValues,
                            'value' => Be::getRequest()->get('pull_task_id'),
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
                            'label' => '采集任务名称',
                            'width' => '250',
                            'align' => 'left',
                            'value' => function ($row) {
                                $sql = 'SELECT `name` FROM monkey_pull_task WHERE id = ?';
                                $pullTaskName = Be::getDb()->getValue($sql, [$row['pull_task_id']]);
                                if ($pullTaskName) {
                                    return $pullTaskName;
                                } else {
                                    return '-';
                                }
                            },
                        ],
                        [
                            'name' => 'title',
                            'label' => '标题',
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
                            'label' => '来源网址',
                            'width' => '120',
                            'align' => 'center',
                            'driver' => TableItemCustom::class,
                            'value' => function($row) {
                                return '<a class="el-link el-link--primary is-underline" href="' .$row['url']. '" title="' .$row['url']. '" rel="noreferrer" target="_blank"><i class="el-icon-link"></i></a>';
                            },
                        ],
                        [
                            'name' => 'update_time',
                            'label' => '更新时间',
                            'width' => '180',
                        ],
                    ],
                    'operation' => [
                        'label' => '操作',
                        'width' => '80',
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
     * 查看采集任务
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
