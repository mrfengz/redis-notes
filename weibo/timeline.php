<?php
require_once('helper.php');
require_once "header.php";
$redis = connectRedis();
$key = 'user:ids';
$userId = $_SESSION['id'];
$followers = $redis->lrange('user:id:' . $userId .':followers',0, -1); //我的粉丝
$following = $redis->lrange('user:id:' . $userId . ':following', 0, -1); //我关注的人
$following[] = $userId;
//var_dump($following, $followers);die;
$userIds = $redis->lrange($key, 0, 20);

//获取20条用户
//sort 类似与mysql的左连接
//$res = $redis->sort('user:ids', ['sort' => 'desc', 'get' => 'user:id:'.$userId]); //貌似这个key必须为字符串
//var_dump($res);die;
$users = [];
foreach ($userIds as $id) {
    $users[$id] = $redis->hget("user:id:{$id}", 'username');
}
//从我关注的人那里，获取他们发布的微博，已经获取过，就不再获取
$lastPullPostId = (int)$redis->get('last_pull_post_id:userid:'.$userId);
if (!$lastPullPostId) {
    $lastPullPostId = -1; //如果不是-1，则起始位置位0+1，就无法获取关注人发的第一条post
}
//var_dump($lastPullPostId);
$lastestPostsKey = 'latest:userid:'.$userId.':postids'; //我关注的人的post，队列长度控制为100

$pullPostIds = [];
//拉取关注人的最新的post
foreach ($following as $_id) {
    if ($_id != $userId) {
        $pullPostIds = array_merge($pullPostIds, $redis->zRangeByScore("post:userid:{$_id}", $lastPullPostId+1, '+inf'));
    } else {
        $pullPostIds = array_merge($pullPostIds, $redis->lrange('user:id:'.$userId.':postid', 0, -1));
    }
}

if ($pullPostIds) {
    sort($pullPostIds, SORT_NUMERIC);
    $redis->set('last_pull_post_id:userid:'.$userId, reset($pullPostIds));
    foreach ($pullPostIds as $postId) {
        if ($postId > $lastPullPostId) {
            $redis->lpush($lastestPostsKey, $postId);
        }
    }
}
$retainLen = 9;
if ($redis->llen($lastestPostsKey) > $retainLen) {
    $redis->ltrim($lastestPostsKey, 0, $retainLen);
}
$latestPostIds = $redis->lrange($lastestPostsKey,0, -1);
$posts = [];
foreach ($latestPostIds as $postId) {
    $posts[] = $redis->hmget('post:id:'.$postId, ['user_id','content','created_at']);
}
//echo '<pre>';
//var_dump($latestPostIds, $users, $posts);
//die;
?>
<h2>热点</h2>
<i>最新注册用户(redis中的sort用法)</i><br>
<?php foreach($users as $id => $user){if($id == $userId) {continue;}?>
<div>
    <a class="username" href="profile.php?u=<?= $id;?>"><?= $user?></a>
    <?php if(in_array($id, $followers) && in_array($id, $following)){?>
        <button>互相关注</button>
        <a href="focus.php?id=<?= $id?>&status=unfocus">取消关注</a>
    <?php } elseif(in_array($id, $followers)){?>
        <button>粉丝</button>
        <a href="focus.php?id=<?= $id?>&status=focus">关注</a>
    <?php } elseif(in_array($id, $following)){?>
        <button>我关注的</button>
        <a href="focus.php?id=<?= $id?>&status=unfocus">取消关注</a>
    <?php } else {?>
        <a href="focus.php?id=<?= $id?>&status=focus">关注</a>
    <?php }?>
</div>
<?php };?>

<br><i>最新的10条微博!</i><br>
<?php foreach($posts as $post) {?>
<div class="post">
<a class="username" href="profile.php?u=<?= $post['user_id']?>"><?= $redis->hget('user:id:'.$post['user_id'], 'username')?></a>
<?= $post['content']?><br>
<i><?= showTime($post['created_at']);?></i>
</div>
<?php }?>

<?php require_once "footer.php";?>