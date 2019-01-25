<?php
require_once('helper.php');

$userId = $_SESSION['id'];

$id = get('id');
$status = get('status');

$redis = connectRedis();

if ($status == 'focus') {
    $redis->lpush('user:id:'.$userId.':following', $id); //我关注的人多了
    $redis->lpush('user:id:'.$id.':followers', $userId); //被关注的人，多了
} else {
    $redis->lrem('user:id:'.$userId.':following', $id); //我关注的人 少了
    $redis->lrem('user:id:'.$id.':followers', $userId); //被我关注的人，也少了
}
$redis && $redis->close();
header("Location: timeline.php");