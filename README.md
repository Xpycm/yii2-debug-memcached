Yii2 debug memcached panel
==========================
The memcached panel for Yii2 debug module

## Features

- Show memcached statistic per server
- Automatic get configuration from cache component
- Configurable cache usage levels for warnings
- Use Formatter or internal converter to show statistic


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist salopot/yii2-debug-memcached "dev-master"
```

or add

```
"salopot/yii2-debug-memcached": "dev-master"
```

to the require section of your `composer.json` file.

Update config: add panel to your debug module configuration:
```php
'components'=>[
    ...
    'cache' => [
        'class' => 'yii\caching\MemCache',
         'servers' => [
            [
                'host' => 'server1',
                'port' => 11211,
                'weight' => 100,
            ],
            [
                'host' => 'server2',
                'port' => 11211,
                'weight' => 50,
            ],
            ***
         ],

    ],
    ...

],
...
'modules'=>[
    ...
    'debug'=>[
        ...
        'panels'=>[
            ...
            'memcached'=>[
                'class' => '\salopot\debug\memcached\panels\MemcachedPanel',
                //'useFormatter' => false,
            ]
        ]
    ]
    ...
]
```


Usage
-----

You will see a debugger toolbar showing at the bottom of every page of your application.
You can click on the "Memcached" toolbar panel to see more detailed debug information.