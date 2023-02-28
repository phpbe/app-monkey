<?php

namespace Be\App\Monkey\Service\Admin;

use Be\App\ServiceException;
use Be\Be;

class Content
{

    /**
     * 获取内容
     *
     * @param string $contentId
     * @return object
     */
    public function getContent(string $contentId): object
    {
        $db = Be::getDb();

        $sql = 'SELECT * FROM monkey_content WHERE id=?';
        $content = $db->getObject($sql, [$contentId]);
        if (!$content) {
            throw new ServiceException('"内容（# ' . $contentId . '）不存在！');
        }
        $content->fields = unserialize($content->fields);

        return $content;
    }

    /**
     * 获取采集器采集到的文章数
     * @param string $pullDriverId
     * @return false|object
     * @throws \Be\Db\DbException
     * @throws \Be\Runtime\RuntimeException
     */
    public function getPullDriverContentCount(string $pullDriverId): int
    {
        $db = Be::getDb();
        $sql = 'SELECT COUNT(*) FROM monkey_content WHERE pull_driver_id=?';
        return (int)$db->getValue($sql, [$pullDriverId]);
    }


}
