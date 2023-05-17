<?php

namespace Be\App\Monkey\Task;

use Be\Be;
use Be\Task\Task;
use Be\Util\Net\Curl;

/**
 * 发布任务
 *
 * @BeTask("发布任务")
 */
class PushTask extends Task
{

    protected $parallel = true;

    protected $timeout = 3600;

    public function execute()
    {

        $db = Be::getDb();
        $sql = 'SELECT * FROM monkey_push_task WHERE is_enable = 1 AND is_delete = 0 AND status = \'pending\'';
        $pushTask = $db->getObject($sql);
        if ($pushTask) {

            $this->taskLog->data = ['push_task_id' => $pushTask->id];
            $this->updateTaskLog();

            $sql = 'UPDATE monkey_push_task SET status=\'running\', update_time = \'' . date('Y-m-d H:i:s') . '\' WHERE id=\'' . $pushTask->id . '\'';
            $db->query($sql);

            try {

                $pushTask->fields = unserialize($pushTask->fields);

                $sql = 'SELECT * FROM monkey_content WHERE pull_driver_id = \''.$pushTask->pull_driver_id.'\'';
                $contents = $db->getYieldObjects($sql);
                foreach ($contents as $content) {

                    $content->fields = unserialize($content->fields);

                    $headers = [];
                    if (is_array($pushTask->headers) && count($pushTask->headers) > 0) {
                        foreach($pushTask->headers as $pushTaskHeader) {
                            $headers[$pushTaskHeader['name']] = $pushTaskHeader['value'];
                        }
                    }

                    $postData = [];
                    foreach($pushTask->fields as $pushTaskField) {
                        if ($pushTaskField['is_enable'] === 1) {
                            $value = '';
                            switch ($pushTaskField['value_type']) {
                                case 'pull_driver_field':
                                    foreach ($content->fields as $contentField) {
                                        if ($contentField['name'] === $pushTaskField['value_pull_driver_field']) {
                                            $value = $contentField['content'];
                                            break;
                                        }
                                    }
                                    break;
                                case 'default':
                                    $value = $pushTaskField['value_default'];
                                    break;
                                case 'custom':
                                    $value = $pushTaskField['value_custom'];
                                    break;
                            }

                            $postData[$pushTaskField['name']] = $value;
                        }
                    }

                    $pushTaskLog = [];
                    $pushTaskLog['id'] = $db->uuid();
                    $pushTaskLog['push_task_id'] = $pushTask->id;
                    $pushTaskLog['content_id'] = $content->id;
                    $pushTaskLog['request'] = serialize([
                        'url' => $pushTask->url,
                        'postData' => $postData,
                        'headers' => $headers,
                    ]);

                    $pushTaskLog['response'] = '';
                    $pushTaskLog['message'] = '';
                    try {
                        $pushTaskLog['response'] = Curl::post($pushTask->url, $postData, $headers);
                        $pushTaskLog['success'] = 1;
                    } catch (\Throwable $t) {
                        $pushTaskLog['success'] = 0;

                        $message = $t->getMessage();
                        if (mb_strlen($message) > 500) {
                            $message = mb_substr($message, 0, 500);
                        }
                        $pushTaskLog['message'] = $message;
                    }

                    $pushTaskLog['create_time'] = date('Y-m-d H:i:s');

                    $db->insert('monkey_push_task_log', $pushTaskLog);

                    if ($pushTask->interval > 0) {
                        if (Be::getRuntime()->isSwooleMode()) {
                            \Swoole\Coroutine::sleep($pushTask->interval / 1000);
                        } else {
                            usleep($pushTask->interval);
                        }
                    }
                }

                $sql = 'UPDATE monkey_push_task SET status=\'finish\', message=\'\', update_time = \'' . date('Y-m-d H:i:s') . '\' WHERE id=\'' . $pushTask->id . '\'';
                $db->query($sql);

            } catch (\Throwable $t) {
                $message = $t->getMessage();
                if (mb_strlen($message) > 500) {
                    $message = mb_substr($message, 0, 500);
                }

                $sql = 'UPDATE monkey_push_task SET status=\'error\', message=' . $db->quoteValue($message) . ', update_time = \'' . date('Y-m-d H:i:s') . '\' WHERE id=\'' . $pushTask->id . '\'';
                $db->query($sql);
            }
        }

    }

}
