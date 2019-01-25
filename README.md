# redis-notes
learn redis 

## weibo
	这个是根据视频项目讲解练习的，非常基础。
	使用了string,list,hash等数据结构。

	主要有登录、注册、关注、发短文功能，还有一个不太完善的数据持久化操作。

	持久化post脚本 cron_save.php
		需要先建立一个数据库，字段为id,content,user_id,created_at
		然后，修改数据库连接信息，运行 php cron_save.php