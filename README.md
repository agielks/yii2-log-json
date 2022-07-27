Yii2 Log Json Target
====================
Convert your yii2 application logs as json and save it to file, redis, or logstash

[![Latest Stable Version](http://poser.pugx.org/agielks/yii2-log-json/v)](https://packagist.org/packages/agielks/yii2-log-json) 
[![Total Downloads](http://poser.pugx.org/agielks/yii2-log-json/downloads)](https://packagist.org/packages/agielks/yii2-log-json) 
[![Latest Unstable Version](http://poser.pugx.org/agielks/yii2-log-json/v/unstable)](https://packagist.org/packages/agielks/yii2-log-json) 
[![License](http://poser.pugx.org/agielks/yii2-log-json/license)](https://packagist.org/packages/agielks/yii2-log-json) 
[![PHP Version Require](http://poser.pugx.org/agielks/yii2-log-json/require/php)](https://packagist.org/packages/agielks/yii2-log-json)

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist agielks/yii2-log-json "~1.0"
```

or add

```
"agielks/yii2-log-json": "~1.0"
```

to the require section of your `composer.json` file.

## Basic usage

### File Target
```php
'components' => [
    // ...
    'log' => [
        'traceLevel' => YII_DEBUG ? 3 : 0,
        'targets' => [
            [
                'class' => 'agielks\yii2\log\json\FileTarget',
                'levels' => ['error', 'warning'],
                'except' => [
                    'yii\web\HttpException:*',
                ],
            ],
        ],
    ],
    // ...
],
```

### Logstash Target Configuration
```php
'components' => [
    // ...
    'log' => [
        'traceLevel' => YII_DEBUG ? 3 : 0,
        'targets' => [
            [
                'class' => 'agielks\yii2\log\json\LogstashTarget',
                'dsn' => '127.0.0.1:5000',
                'index' => 'my-index',
                'type' => 'log',
                'levels' => ['error', 'warning'],
                'except' => [
                    'yii\web\HttpException:*',
                ],
            ],
        ],
    ],
    // ...
],
```

### Redis Target Configuration
```php
'components' => [
    // ...

    // Redis connection
    'redis' => [
        'class' => 'yii\redis\Connection',
        'hostname' => '127.0.0.1',
        'port' => 6379,
        'database' => 0,
    ],

    // Redis log configuration
    'log' => [
        'traceLevel' => YII_DEBUG ? 3 : 0,
        'targets' => [
            [
                'class' => 'agielks\yii2\log\json\RedisTarget',
                'db' => 'redis',
                'levels' => ['error', 'warning'],
                'except' => [
                    'yii\web\HttpException:*',
                ],
            ],
        ],
    ],
    // ...
],
```

### More Usage
- [https://www.yiiframework.com/doc/api/2.0/yii-log-target](https://www.yiiframework.com/doc/api/2.0/yii-log-target)
- [https://www.yiiframework.com/doc/guide/2.0/en/runtime-logging](https://www.yiiframework.com/doc/guide/2.0/en/runtime-logging)
