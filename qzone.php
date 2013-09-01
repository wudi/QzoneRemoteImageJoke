<?php
/**
 * Author:EagleWu <eaglewudi@gmail.com>
 * Date: 2013/09/01
 */

$refer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false;
if ($refer == false) {
    die('想干嘛！');
}
preg_match('/\d{5,11}/', $refer, $qq);
if (empty($qq)) {
    die('Can not match qq number');
} else {
    $qq = $qq[0];
}

$big_pic_file = "pic/{$qq}_big.jpg";
if (file_exists($big_pic_file)) {
    header("Content-type: image/jpeg");
    echo file_get_contents($big_pic_file);
    exit();
}

function getRemoteContent($url, $header = false)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, "http://user.qzone.qq.com");
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4)
    AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.57 Safari/537.36");
    curl_setopt($ch, CURLOPT_HEADER, $header);
    //$only_header && curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

$html = getRemoteContent("http://user.qzone.qq.com/{$qq}");

if ($html) {
    $user_name_regexp = '/<p class="user_name">(.*)<\/p>/i';
    preg_match($user_name_regexp, $html, $regexp_result);
    if (empty($regexp_result)) {
        preg_match('/<span class="text ui_mr10">(.*)<\/span><a id="diamon"/i', $html, $regexp_result);
        empty($regexp_result) && die('Username not found');
    }
    $user_name = $regexp_result[1];
} else {
    die('Connect "user.qzone.qq.com" error!');
}

$user_name = preg_replace("/\s+/", '', $user_name);
$_left = (int)strlen($user_name) - 6;
$source = imagecreatefromjpeg("bg.jpg");
$header = getRemoteContent("http://qlogo2.store.qq.com/qzone/{$qq}/{$qq}/100", true);
$header = substr($header, 0, 200);

if (strpos($header, 'image/jpeg') !== false) {
    $func = "imagecreatefromjpeg";
    $ext = '.jpg';
} elseif (strpos($header, 'image/png') !== false) {
    $func = "imagecreatefrompng";
    $ext = '.png';
}else{
    $func = "imagecreatefromgif";
    $ext = '.gif';
}

$pic_file = "pic/{$qq}";
if (file_exists($pic_file . $ext)) {
    $pic_data = file_get_contents($pic_file . $ext);
} else {
    $pic_data = getRemoteContent("http://qlogo2.store.qq.com/qzone/{$qq}/{$qq}/100");
    $res = file_put_contents($pic_file . $ext, $pic_data);
    $res || die('pic write error');
}

$pic_data || die('Pic get error');
header("Content-type: image/jpeg");

$qqpic = $func($pic_file . $ext);
imagecopy($source, $qqpic, 583, 377, 0, 0, 100, 100);
$wilte = imagecolorallocate($source, 255, 255, 255);
imagettftext($source, 35, 0, 250 - $_left * 4, 115, $wilte, "./DroidSansFallback.ttf", $user_name);

imagepng($source, $big_pic_file);
imagepng($source);

imagedestroy($qqpic);
imagedestroy($source);
