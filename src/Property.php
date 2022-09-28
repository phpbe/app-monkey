<?php

namespace Be\App\Monkey;


class Property extends \Be\App\Property
{

    protected string $label = '猴子采集器';
    protected string $icon = 'bi-cloud-arrow-down';
    protected string $description = '基于浏览器油獅插件的采集系统，只要能看到，就100%能采集';

    public function __construct() {
        parent::__construct(__FILE__);
    }

}
