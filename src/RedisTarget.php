<?php
/**
 * @author Agiel K. Saputra <agielkurniawans@gmail.com>
 * @copyright Copyright (c) Agiel K. Saputra
 */

namespace agielks\yii2\jsonlog;

use Exception;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\log\Dispatcher;
use yii\log\Target;
use yii\redis\Connection;

class RedisTarget extends Target
{
    use TraitJsonTarget;

    /**
     * @var Connection|array|string the redis connection object or the application component ID
     * of the redis connection.
     */
    public $db = 'redis';

    /**
     * This method will initialize the [[redis]] property to make sure it refers to a valid Redis connection.
     * @throws InvalidConfigException if [[redis]] is invalid.
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::class);
    }

    /**
     * @inheritdoc
     */
    public function export()
    {
        try {
            $messages = implode("\n", array_map([$this, 'formatMessage'], $this->messages)) . "\n";
            $timestamp = ArrayHelper::getValue(Json::decode($messages), 'timestamp');
            $this->db->lpush($this->index . '-' . $this->type . ':' . $timestamp, $messages);
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
