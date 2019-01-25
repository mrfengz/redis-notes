<?php
session_start();
include_once('helper.php');
$username = post('username');
$password = post('password');

$redis = connectRedis();
if (($userId = $redis->get('user:username:'.$username.':id')) && $redis->hget("user:id:{$userId}", "password") == $password)  {
	$_SESSION['isLogin'] = true;
	$_SESSION['username'] = $username;
	$_SESSION['id'] = $userId;
	redirect('home.php');
} else {
    redirect('index.html');
}
