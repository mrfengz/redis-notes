<?php
/**
 * Created by PhpStorm.
 * User: my
 * Date: 2019/1/19
 * Time: 0:23
 */
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

function start_session()
{
	if (session_id() == '') {
		session_start();
	}
}

/**
 * 判断用户是否登录
 */
function isLogin()
{
	$login =  $_SESSION['isLogin'] ?? false;
	if(!$login) {
		directLogin();
	}
}

function directLogin()
{
    global $redis;
    if ($redis) {
        $redis->close();
    }
	header("Location: /weibo/index.html");
    exit();
}

/**
 *退出登录
 */
function logout()
{
	$_SESSION=null;
	session_destroy();
	directLogin();
}

function p($var)
{
    echo '<pre>';
    var_dump($var);
    die;
}

function showTime($time)
{
    $interval = time() - $time;
    if ($interval < 60) {
        $ret = $interval . '秒前';
    }elseif ($interval < 3600) {
        $ret = floor($interval / 60) . '分钟前';
    } elseif($interval < 86400) {
        $ret = floor($interval / 3600) . '小时前';
    } else {
        $ret = floor($interval / 86400) . '天前';
    }
    return $ret;
}

function get($key = null) {
    return $key ? ($_GET[$key] ?? null) : $_GET;
}

function post($key = null) {
    return $key ? ($_POST[$key] ?? null) : $_POST;
}

function connectRedis()
{
    $redis = new Redis();
    $redis->connect('127.0.0.1');
    return $redis;
}

function redirect($url)
{
    global $redis;
    if ($redis) {
        $redis->close();
    }
    header("Location:{$url}");
}
