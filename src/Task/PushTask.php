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
        $sql = 'SELECT * FROM monkey_push_task WHERE is_enable = 1 AND is_delete = 0 AND status = \'prepared\'';
        $pushTask = $db->getObject($sql);
        if ($pushTask) {

            $this->taskLog->data = ['push_task_id' => $pushTask->id];
            $this->updateTaskLog();

            $sql = 'UPDATE monkey_push_task SET status=\'running\', update_time = \'' . date('Y-m-d H:i:s') . '\' WHERE id=\'' . $pushTask->id . '\'';
            $db->query($sql);

            try {

                $pushTask->fields = unserialize($pushTask->fields);

                $sql = 'SELECT * FROM monkey_content WHERE pull_task_id = \''.$pushTask->pull_task_id.'\'';
                $contents = $db->getYieldObjects($sql);
                foreach ($contents as $content) {

                    $content->fields = unserialize($content->fields);

                    $headers = [];
                    if (is_array($pushTask->headers) && count($pushTask->headers) > 0) {
                        foreach($pushTask->headers as $header) {
                            $headers[$header['name']] = $header['value'];
                        }
                    }

                    $postData = [];
                    foreach($pushTask->fields as $field) {
                        if ($field['is_enable'] === 1) {
                            $value = '';
                            switch ($field['value_type']) {
                                case 'pull_task_field':
                                    $contentField = $field['value_pull_task_field'];
                                    if (isset($content->fields[$contentField])) {
                                        $value = $content->fields[$contentField];
                                    }
                                    break;
                                case 'default':
                                    $value = $field['value_default'];
                                    break;
                                case 'custom':
                                    $value = $field['value_custom'];
                                    break;
                            }

                            $postData[$field['name']] = $value;
                        }
                    }

                    Curl::post($pushTask->url, $headers, $postData);

                    if ($pushTask->interval > 0) {
                        if (Be::getRuntime()->isSwooleMode()) {
                            \Swoole\Coroutine::sleep($pushTask->interval / 1000);
                        } else {
                            usleep($pushTask->interval);
                        }
                    }
                }
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
