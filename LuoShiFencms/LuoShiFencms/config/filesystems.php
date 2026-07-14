<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3", "rackspace"
    |
    */

    'disks' => [
        // 热数据（近6个月）—— 高性能SSD
        'hot' => [
            'driver'   => 'oss',
            'bucket'   => 'luoshifen-hot',
            'endpoint' => 'oss-cn-guangzhou.aliyuncs.com',
        ],

        // 冷数据（6个月~3年）—— 低频存储
        'cold' => [
            'driver'   => 'oss',
            'bucket'   => 'luoshifen-cold',
            'endpoint' => 'oss-cn-guangzhou.aliyuncs.com',
        ],

        // 归档数据（3年以上）—— 归档存储
        'archive' => [
            'driver'   => 'oss',
            'bucket'   => 'luoshifen-archive',
            'endpoint' => 'oss-cn-guangzhou.aliyuncs.com',
        ],
         'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],
    ],

];
