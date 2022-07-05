Yii2 Json Log Target
====================
Convert your yii2 application logs as json and save it to file, redis, or logstash

## Installation

1. Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist agielks/yii2-json-log "*"
```

or add

```
"agielks/yii2-json-log": "*"
```

to the require section of your `composer.json` file.

## Basic usage

### File Target
Update your configuration file `common/main.php`

```php
'components' => [
    // ...
    's3' => [
        'class' => 'agielks\yii2\aws\s3\Service',
        'endpoint' => 'my-endpoint',
        'usePathStyleEndpoint' => true,
        'credentials' => [ // Aws\Credentials\CredentialsInterface|array|callable
            'key' => 'my-key',
            'secret' => 'my-secret',
        ],
        'region' => 'my-region',
        'defaultBucket' => 'my-bucket',
        'defaultAcl' => 'public-read',
    ],
    // ...
],
```

### Redis Target
Update your configuration file `common/main.php`

```php

```

### Logstash Target
Update your configuration file `common/main.php`

```php

```


## License

Yii2 AWS S3 is licensed under the MIT License.

See the [LICENSE](LICENSE) file for more information.
