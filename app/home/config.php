<?php

return [
	//模板参数替换
    'view_replace_str'       => array(
        '__CSS__'    => '/static/home/css',
        '__JS__'     => '/static/home/js',
        '__IMG__' => '/static/home/images',
        '__MCSS__'    => '/static/mobile/css',
        '__MJS__'     => '/static/mobile/js',
        '__MIMG__' => '/static/mobile/images',
        '__UPLOAD__' => 'http://'.$_SERVER['SERVER_NAME'].'/data/upload',
        '__DOMAIN__' => 'http://'.$_SERVER['SERVER_NAME'],
    ),

    'UPLOAD' => 'http://'.$_SERVER['SERVER_NAME'].'/data/upload/',
    'DOMAIN' => 'http://'.$_SERVER['SERVER_NAME'],

    "AUTHCODE" => 'SkIiOusv7pyrhKZbQA',

    'MORE_PAGESIZE' => 10, //文章加载数
    'SPECIAL_PAGE' => 5,
    'HOTLIST_PAGESIZE'=>5, //加载数

    'ENCTRY_KEY' => 'cmsbzzs',
    'ENCTRY_LI' => '20170927',

    //微信分享接口
    'nonceStr' => 'biaozhunpaiming',//jsapi接口字符串
    'WX_APP_ID' => 'wx0c6c26670bc08ecd',//服务号的appid
    'WX_APP_SECRET' => 'd0dce95f1fa92c3a6792bcfbec81307e',//服务号的appsercret

    'think_sdk_qq'  =>[
        'app_key'      => '101430595', 
        'app_secret'   => '39fb1ead5cbed640eca1883aa3755d19',
        'callback'     => 'http://'.$_SERVER['SERVER_NAME'].'/home/openauth/callback/type/qq',
    ],
    'think_sdk_sina'   =>[
        'app_key'      => '2507669473', 
        'app_secret'   => '88090973e26ab87aa2184392ead814ec',
        'callback'     => 'http://'.$_SERVER['SERVER_NAME'].'/home/openauth/callback?type=sina',
    ],

];