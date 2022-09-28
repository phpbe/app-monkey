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


}
