<?php

namespace Be\App\Monkey\Service;

use Be\App\ServiceException;
use Be\Be;

class PullDriver
{

    /**
     * 获取采集器
     *
     * @param string $pullDriverId
     * @return object
     */
    public function getPullDriver(string $pullDriverId): object
    {
        $db = Be::getDb();

        $sql = 'SELECT * FROM monkey_pull_driver WHERE id=? AND is_delete = 0';
        $pullDriver = $db->getObject($sql, [$pullDriverId]);
        if (!$pullDriver) {
            throw new ServiceException('采集器（# ' . $pullDriverId . '）不存在！');
        }

        $pullDriver->ordering = (int)$pullDriver->ordering;
        $pullDriver->is_enable = (int)$pullDriver->is_enable;
        $pullDriver->is_delete = (int)$pullDriver->is_delete;

        if ($pullDriver->is_enable !== 1) {
            throw new ServiceException('采集器（# ' . $pullDriverId . '）未启用！');
        }

        $pullDriver->fields = unserialize($pullDriver->fields);

        return $pullDriver;
    }


    /**
     * 获取采集器安装网址
     *
     * @param array $params
     * @return string
     */
    public function getPullDriverInstallUrl(array $params = []): string
    {
        $pullDriver = $this->getPullDriver($params['id']);
        return '/monkey/pull-driver/' . $pullDriver->id . '-v' . $pullDriver->version . '.user.js';
    }


}
