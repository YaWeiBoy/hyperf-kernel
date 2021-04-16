<?php
/**
 * @copyright
 * @version 1.0.0
 * @link
 */
return [
    // 默认短信渠道
    'default'           => 'aliCloud',
    // 短信验证码缓存名
    'verify_code_cache' => 'SMSVerifyCode:%s:%s',
    // 发送频率
    'interval'          => 60,
    // 缓存有效期
    'expired'           => 30 * 60,
    // 短信渠道
    'channel'           => [
        // 阿里云短信配置
        'aliCloud' => [
            'driver'       => \Zyw\HyperfKernel\SMS\AliCloudSMS::class,
            'accessKeyId'  => env('aliCloudAccessKeyId', ''),
            'accessSecret' => env('aliCloudAccessSecret', ''),
            'regionId'     => env('aliCloudSMSRegionId', 'cn-hangzhou'),
            'host'         => env('aliCloudSMSHost', 'dysmsapi.aliyuncs.com'),
            'signName'     => env('aliCloudSMSSignName', '')
        ],
        // 聚合短信配置
        'juhe'     => [
            'driver' => \Zyw\HyperfKernel\SMS\JuheSMS::class,
            'key'    => env('juheSMSKey', ''),
        ]
    ]
];