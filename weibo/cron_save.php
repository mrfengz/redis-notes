<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once("helper.php");
$config = [
    'username' => 'username',
    'password' => 'password',
];
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=weibo_redis;port=3306",
        $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "insert into `post` (`id`,`user_id`, `content`, `created_at`) values";
} catch (\Exception $e) {
    echo ("could not connect to the database, please check database configuration");
    die;
}
$redis = connectRedis();
$key = 'global:postid';
$data = [];
while(1) {
    while ($redis->llen($key)) {
        list(, $postId) = $redis->brpop($key, 0);
        $post = $redis->hmget('post:id:'.$postId, ['user_id', 'content', 'created_at']);
        $data[] = [$postId, $post['user_id'], $post['content'], $post['created_at']];
        if (count($data) >= 2) {
            $_data = array_splice($data, 0,100);
//        print_r($data);  //[]
//        print_r($_data); //$data
            //die;
            $_sql = $sql;
            foreach ($_data as $v) {
                $_sql .=  "($v[0], $v[1], '{$v[2]}', $v[3]),";
            }
            $_sql = rtrim($_sql, ',');
        echo "time: " .time() . "保存数据" .  $_sql."\n";
            $pdo->exec($_sql);
        }
    }
    sleep(1);
}

