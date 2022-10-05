<?php

namespace Be\App\Monkey\Controller\Admin;

use Be\App\System\Controller\Admin\Auth;
use Be\Be;

/**
 * @BeMenuGroup("控制台", icon="el-icon-monitor", ordering="4")
 * @BePermissionGroup("控制台",  ordering="4")
 */
class Task extends Auth
{
    /**
     * @BeMenu("计划任务", icon="el-icon-timer", ordering="4.1")
     * @BePermission("计划任务", ordering="4.1")
     */
    public function dashboard()
    {
        Be::getAdminPlugin('Task')->setting(['appName' => 'Monkey'])->execute();
    }

}
