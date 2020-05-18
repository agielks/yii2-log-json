<?php
/**
 * @author Agiel K. Saputra <agielkurniawans@gmail.com>
 * @copyright Copyright (c) Agiel K. Saputra
 */

namespace agielks\yii2\jsonlog;

use Exception;
use yii\log\Dispatcher;
use yii\log\Target;

/**
 * LogstashTarget stores the log to Logstash as single line JSON.
 */
class LogstashTarget extends Target
{
    use TraitJsonTarget;

    /**
     * @var string Connection configuration to Logstash.
     */
    public $dsn = 'tcp://localhost:5000';

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        try {
            $messages = implode("\n", array_map([$this, 'formatMessage'], $this->messages)) . "\n";

            if ($socket = stream_socket_client($this->dsn, $errorNumber, $error, 30)) {
                fwrite($socket, $messages);
                fclose($socket);
            } else {
                throw new Exception('Failed to connect');
            }
        } catch (Exception $e) {
            new Dispatcher([
                'targets' => [
                    new FileTarget([
                        'categories' => $this->categories,
                        'except' => $this->except,
                        'logVars' => $this->logVars,
                        'maskVars' => $this->maskVars,
                        'levels' => $this->levels,
                        'prefix' => $this->prefix,
                        'exportInterval' => $this->exportInterval,
                        'messages' => $this->messages,
                        'microtime' => $this->microtime,
                    ]),
                ],
            ]);
        }
    }
}
