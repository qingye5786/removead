<?php
/**
 * Created by PhpStorm.
 * User: chenyanphp@qq.com
 * Date: 2016/10/6
 * Time: 13:08
 */
error_reporting(0);
require_once 'function.php';
if (isset($_GET['url']) and !empty($_GET['url'])) {
    // 根据url获取页面内容
    $url = trim($_GET['url']);
    $content = file_get_contents("compress.zlib://".$url);

    // 防止中文乱码
    $name_type = mb_detect_encoding($content, array('ASCII','GB2312','GBK','UTF-8'));
    $name_type = strtolower($name_type);
    if ($name_type == 'euc-cn') {
        echo mb_convert_encoding($content, "utf-8", "euc-cn");
    }
    if ($name_type == 'cp936') {
        echo mb_convert_encoding($content, "utf-8", "cp936");
    }

    // 域名
    $link_content = parse_url($url);
    $host_url = $link_content['scheme'] . '://' . $link_content['host'];
    $no_file_path = $link_content['scheme'] . '://' . $link_content['host'] . str_replace(strrchr($link_content['path'], '/'), '', $link_content['path']) . '/';

    // 移除JS内容（广告）
    $remove_content = "/<script[\s\S]*?<\/script>/i";
    $new_content = preg_replace($remove_content, '', $content);

    // 修复资源前缀丢失
    $find_url = "/<(link|img)(.*?)href=\"(.*?)\"(.*?)>/";
    preg_match_all($find_url, $new_content, $ar);
    foreach ($ar[3] as $k => $v) {
        if (strpos($v, 'http') === false and strpos($v, 'https') === false) {
            $new_content = str_replace($v, $host_url . $v, $new_content);
        }
    }

    // 重置页面超链接地址
    $a_url = "/<a(.*?)href=[\'\"](.*?)[\'\"](.*?)>/";
    preg_match_all($a_url, $new_content, $ac);
    foreach ($ac[2] as $k => $v) {
        $old_str = "\"" . $v . "\"";
        if (!strpos($v, 'http') and !strpos($v, 'https')) {
            if (substr($v, 0, 1) == '/') {
                $new_content = str_replace($old_str, '"index.php?url=' . $host_url . $v . '"', $new_content);
            } else {
                $new_content = str_replace($old_str, '"index.php?url=' . $no_file_path . $v . '"', $new_content);
            }
        } else if (strpos($v, $host_url)) {
            $new_content = str_replace($old_str, '"index.php?url=' . $v_left . '"', $new_content);
        }
    }

    echo $new_content;
    die;
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport">
    <meta content="yes" name="apple-mobile-web-app-capable">
    <meta content="black" name="apple-mobile-web-app-status-bar-style">
    <meta content="telephone=no" name="format-detection">
    <title>去广告浏览</title>
    <link rel="stylesheet" href="static/lib/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="static/css/style.css">
</head>
<body>
    <div id="search">
        <h4>去广告浏览</h4>
        <form action="" method="get" class="form-inline">
            <div class="form-group col-xs-8">
                <div class="input-group col-xs-12">
                    <input type="text" class="form-control" placeholder="请输入网址" name="url">
                </div>
            </div>
            <input type="submit" class="btn btn-success" value="点击浏览">
        </form>
    </div>
</body>
</html>
