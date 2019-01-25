<?php
require_once('helper.php');
require_once('header.php');
isLogin();
$redis = connectRedis();
$userId = $_SESSION['id'];
?>
<div id="postform">
    <form method="POST" action="post.php">
        <?php echo $_SESSION['username']; ?>, 有啥感想?
        <br>
        <table>
            <tr>
                <td><textarea cols="70" rows="3" name="content"></textarea></td>
            </tr>
            <tr>
                <td align="right"><input type="submit" name="doit" value="Update"></td>
            </tr>
        </table>
    </form>
    <div id="homeinfobox">
        <?= (int)$redis->llen('user:id:' . $_SESSION['id'] . ':followers') ?> 粉丝<br>
        <?= (int)$redis->llen('user:id:' . $_SESSION['id'] . ':followering') ?> 关注<br>
    </div>
</div>



<?php
$postIds = $redis->lrange("user:id:{$userId}:postid", 0, 4);
$posts = [];
foreach ($postIds as $postId) {
    $posts[] = $redis->hmget("post:id:{$postId}", ['content', 'created_at']);
}
?>
<?php foreach ($posts as $v) { ?>
    <div class="post">
        <a class="username" href="profile.php?u=test"><?= $_SESSION['username'] ?></a> <?= $v['content']; ?><br>
        <i><?= showTime($v['created_at']);?> 通过 web发布</i>
    </div>
<?php } ?>


<?php
require_once('footer.php');
?>
