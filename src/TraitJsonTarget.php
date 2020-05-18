<?php
/**
 * @author Agiel K. Saputra <agielkurniawans@gmail.com>
 * @copyright Copyright (c) Agiel K. Saputra
 */

namespace agielks\yii2\jsonlog;

use Yii;
use yii\base\Application;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\VarDumper;
use yii\log\Logger;
use yii\web\Request;
use yii\web\Session;
use yii\web\User;

trait TraitJsonTarget
{
    /**
     * @var string Elasticsearch index name.
     */
    public $index = 'yii';

    /**
     * @var string Elasticsearch type name.
     */
    public $type = 'log';

    /**
     * @var boolean If true, context will be included in every message.
     * This is convenient if you log application errors and analyze them with tools like Kibana.
     */
    public $includeContext = false;

    /**
     * @var boolean If true, context message will cached once it's been created. Makes sense to use with [[includeContext]].
     */
    public $cacheContext = false;

    /**
     * @var string Context message cache (can be used multiple times if context is appended to every message)
     */
    protected $_contextMessage = null;

    /**
     * Set this to true when the message in json format instead of object/array when logging.
     * e.g.
     * \Yii::info(\yii\helpers\Json::encode(['class' => $class,
     * 'attributes' => $attributes]),$this->category);
     *
     * @var bool
     */
    public $decodeMessage = true;

    /**
     * @param mixed $log
     * @return string The formatted message
     */
    public function formatMessage($log): string
    {
        list($message, $level, $category, $timestamp) = $log;
        $traces = self::formatTracesIfExists($log);

        $text = $this->parseMessage($message);
        $basicInfo = [
            'timestamp' => $this->getTime($timestamp),
            'index' => $this->index,
            'type' => $this->type,
            'message' => $text,
            'level' => Logger::getLevelName($level),
            'category' => $category,
            'traces' => $traces,
        ];
        $appInfo = self::getAppInfo($log);
        $formatted = array_merge($basicInfo, $appInfo);

        if ($this->includeContext) {
            $formatted = array_merge($formatted, [
                'context' => ArrayHelper::getValue($log, 'context'),
            ]);
        }
        return Json::encode($formatted);
    }

    /**
     * @param mixed $message
     * @return array|mixed|string
     */
    protected function parseMessage($message)
    {
        if (is_array($message)) {
            return $message;
        }

        if ($message instanceof \Exception) {
            $message = (string)$message->getMessage();
        }

        if (!is_string($message)) {
            return VarDumper::export($message);
        }

        if (!$this->decodeMessage) {
            return $message;
        }

        try {
            return Json::decode($message, true);
        } catch (InvalidArgumentException $e) {
            return $message;
        }
    }

    /**
     * @param $log
     * @return array
     */
    protected static function formatTracesIfExists($log): array
    {
        $traces = ArrayHelper::getValue($log, 4, []);
        $formattedTraces = array_map(function ($trace) {
            return "in {$trace['file']}:{$trace['line']}";
        }, $traces);

        $message = ArrayHelper::getValue($log, 0);
        if ($message instanceof \Exception) {
            $tracesFromException = explode("\n", $message->getTraceAsString());
            $formattedTraces = array_merge($formattedTraces, $tracesFromException);
        }
        return $formattedTraces;
    }

    /**
     * @param $message
     * @return array
     */
    protected function getAppInfo($message): array
    {
        if ($this->prefix !== null) {
            return call_user_func($this->prefix, $message);
        }

        $app = Yii::$app;
        if ($app === null) {
            return [];
        }

        $ip = self::getUserIP($app);
        $sessionId = self::getSessionId($app);
        $userId = self::getUserId($app);

        return [
            'ip' => $ip,
            'userId' => $userId,
            'sessionId' => $sessionId,
        ];
    }

    /**
     * @param Application $app
     * @return string
     */
    private static function getUserIP(Application $app): string
    {
        $request = $app->getRequest();
        if ($request instanceof Request) {
            return $request->getUserIP();
        }
        return '-';
    }

    /**
     * @param Application $app
     * @return string
     */
    private static function getSessionId(Application $app): string
    {
        try {
            /** @var Session $session */
            $session = $app->get('session', false);
        } catch (InvalidConfigException $ex) {
            return '-';
        }
        if ($session === null) {
            return '-';
        }

        if (!$session->getIsActive()) {
            return '-';
        }
        return $session->getId();
    }

    /**
     * @param Application $app
     * @return string
     */
    private static function getUserId(Application $app): string
    {
        try {
            /** @var User $user */
            $user = $app->get('user', false);
        } catch (InvalidConfigException $ex) {
            return '-';
        }

        if ($user === null || !$user instanceof User) {
            return '-';
        }
        try {
            $identity = $user->getIdentity(false);
        } catch (\Throwable $ex) {
            return '-';
        }
        if ($identity === null) {
            return '-';
        }
        return $identity->getId();
    }

    /**
     * {@inheritdoc}
     */
    protected function getContextMessage()
    {
        if (null === $this->_contextMessage || !$this->cacheContext) {
            $this->_contextMessage = ArrayHelper::filter($GLOBALS, $this->logVars);
        }

        return $this->_contextMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function collect($messages, $final)
    {
        $this->messages = array_merge($this->messages, $this->filterMessages($messages, $this->getLevels(), $this->categories, $this->except));
        $count = count($this->messages);
        if ($count > 0 && ($final || $this->exportInterval > 0 && $count >= $this->exportInterval)) {
            if ($this->includeContext) {
                $context = $this->getContextMessage();
                if (!empty($context)) {
                    foreach ($this->messages as &$message) {
                        $message['context'] = $context;
                    }
                }
            }

            // set exportInterval to 0 to avoid triggering export again while exporting
            $oldExportInterval = $this->exportInterval;
            $this->exportInterval = 0;
            $this->export();
            $this->exportInterval = $oldExportInterval;
            $this->messages = [];
        }
    }
}