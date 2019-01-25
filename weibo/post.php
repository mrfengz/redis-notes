<?php
require_once("helper.php");

$content = post('content');
if (empty(trim($content))) {
    header('Location: /weibo/home.php' );
    exit();
}
$userId = $_SESSION['id'];
$article = [
    'user_id' => $userId,
    'created_at' => time(),
    'content' => $content
];
var_dump($article);

$redis = connectRedis();
$id = $redis->incr('post:start_id');
if (!$redis->hmset($postsKey = 'post:id:'.$id, $article)) {
    header("Location: /weibo/home.php");
    exit();
}
//某用户已发布的post的有序集合id:id post:userid:1
$userPostIdsKey = 'post:userid:'.$userId ;
$redis->zadd($userPostIdsKey, $id, $id);

//用户发布的所有post队列，超度1000，放到一个列表中，等待定时任务定时持久化到数据库中
$redis->lpush($allUserPostKey = 'user:id:'.$userId. ':postid', $id);
if ($redis->llen($allUserPostKey) > 10) {
    $redis->rpoplpush($allUserPostKey, 'global:postid');
}

if ($redis->zCard($userPostIdsKey) > 20) {
    $redis->zRemRangeByRank($userPostIdsKey, 0,0);
}
$redis->close();
header("Location: /weibo/home.php");