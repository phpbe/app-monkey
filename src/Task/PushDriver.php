<?php

namespace Be\App\Monkey\Task;

use Be\Be;
use Be\Task\Task;
use Be\Util\Net\Curl;

/**
 * 发布器
 *
 * @BeTask("发布器")
 */
class PushDriver extends Task
{

    protected $parallel = true;

    protected $timeout = 3600;

    public function execute()
    {

        $db = Be::getDb();
        $sql = 'SELECT * FROM monkey_push_driver WHERE is_enable = 1 AND is_delete = 0 AND status = \'pending\'';
        $pushDriver = $db->getObject($sql);
        if ($pushDriver) {

            $this->taskLog->data = ['push_driver_id' => $pushDriver->id];
            $this->updateTaskLog();

            $sql = 'UPDATE monkey_push_driver SET status=\'running\', update_time = \'' . date('Y-m-d H:i:s') . '\' WHERE id=\'' . $pushDriver->id . '\'';
            $db->query($sql);

            try {

                $pushDriver->headers = unserialize($pushDriver->headers);
                $pushDriver->fields = unserialize($pushDriver->fields);

                $sql = 'SELECT * FROM monkey_content WHERE pull_driver_id = \''.$pushDriver->pull_driver_id.'\'';
                $contents = $db->getYieldObjects($sql);
                foreach ($contents as $content) {

                    $content->fields = unserialize($content->fields);

                    $headers = [];
                    if (is_array($pushDriver->headers) && count($pushDriver->headers) > 0) {
                        foreach($pushDriver->headers as $pushDriverHeader) {
                            $headers[$pushDriverHeader['name']] = $pushDriverHeader['value'];
                        }
                    }

                    $postData = [];
                    foreach($pushDriver->fields as $pushDriverField) {
                        $value = '';
                        switch ($pushDriverField['value']) {
                            case 'use':
                                foreach ($content->fields as $contentField) {
                                    if ($contentField['name'] === $pushDriverField['value_use']) {
                                        $value = $contentField['content'];
                                        break;
                                    }
                                }
                                break;
                            case 'custom':
                                $value = $pushDriverField['value_custom'];
                                break;
                        }

                        $postData[$pushDriverField['name']] = $value;
                    }

                    $pushDriverLog = [];
                    $pushDriverLog['id'] = $db->uuid();
                    $pushDriverLog['push_driver_id'] = $pushDriver->id;
                    $pushDriverLog['content_id'] = $content->id;
                    $pushDriverLog['request'] = serialize([
                        'url' => $pushDriver->url,
                        'headers' => $headers,
                        'format' => $pushDriver->format,
                        'postData' => $postData,
                    ]);

                    $pushDriverLog['response'] = '';
                    $pushDriverLog['message'] = '';
                    try {
                        if ($pushDriver->format === 'form') {
                            $response = Curl::post($pushDriver->url, $postData, $headers);
                        } else {
                            $response = Curl::postJson($pushDriver->url, $postData, $headers);
                        }
                        $pushDriverLog['response'] = $response;
                        $pushDriverLog['success'] = 1;
                    } catch (\Throwable $t) {
                        $pushDriverLog['success'] = 0;

                        $message = $t->getMessage();
                        if (mb_strlen($message) > 500) {
                            $message = mb_substr($message, 0, 500);
                        }
                        $pushDriverLog['message'] = $message;
                    }

                    $pushDriverLog['create_time'] = date('Y-m-d H:i:s');

                    $db->insert('monkey_push_driver_log', $pushDriverLog);

                    if ($pushDriver->interval > 0) {
                        if (Be::getRuntime()->isSwooleMode()) {
                            \Swoole\Coroutine::sleep($pushDriver->interval / 1000);
                        } else {
                            usleep($pushDriver->interval);
                        }
                    }
                }

                $sql = 'UPDATE monkey_push_driver SET status=\'finish\', message=\'\', update_time = \'' . date('Y-m-d H:i:s') . '\' WHERE id=\'' . $pushDriver->id . '\'';
                $db->query($sql);

            } catch (\Throwable $t) {
                $message = $t->getMessage();
                if (mb_strlen($message) > 500) {
                    $message = mb_substr($message, 0, 500);
                }

                $sql = 'UPDATE monkey_push_driver SET status=\'error\', message=' . $db->quoteValue($message) . ', update_time = \'' . date('Y-m-d H:i:s') . '\' WHERE id=\'' . $pushDriver->id . '\'';
                $db->query($sql);
            }
        }

    }

}
