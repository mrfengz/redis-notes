<?php
//session_start();  //helper也开启了，会报错，A session has been already started
/*
if (session_id() == '') {
	session_start();
}
*/

require_once('helper.php');
$username = post('username');
$password = post('password');
$password2 = post('password2');

if (empty(trim($username)) || empty(trim($password)) || empty(trim($password2))) {
	directLogin();
}

$redis = connectRedis();
if ($redis->get('user:username:'.$username.':id')) {
	directLogin();
} else {
	if ($password != $password2) {
		directLogin();
	}
	$user = [
		'username' => $username,
		'password' => $password,
		'created_at' => time(),
	];
	$id = $redis->incr('user:start_id');
	$redis->hmset('user:id:'.$id, $user);
	$redis->set('user:username:'.$username . ':id', $id);
	$redis->lpush('user:ids', $id);
	$_SESSION['isLogin'] = true;
	$_SESSION['username'] = $username;
	$_SESSION['id'] = $id;
	$redis->close();
	header("Location: /weibo/home.php");
}
?>
