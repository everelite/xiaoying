# 修改内容:

1. 文件：test\src\Topxia\Service\User\Impl\BatchNotificationServiceImpl.php
添加以下内容：

    // 修改用户newNotificationNum字段 显示为有新消息的状态
    $this->createDao('User.UserDao')->getConnection()->update('user', ['newNotificationNum'=>1], array('newNotificationNum' => 0));

2. 文件：test\src\Topxia\WebBundle\Controller\CourseManageController.php
修改以下内容：

    在 publishAction 添加了新课程发布的组信息推送 和 发布。

3. 文件：test\src\Topxia\WebBundle\Controller\NotificationController.php
修改以下内容：

    在 indexAction 添加组信息的查询，并拼接两种信息，删除分页功能

4. 文件：test\src\Topxia\WebBundle\Resources\views\Notification\index.html.twig
删除以下内容：

     {{ web_macro.paginator(paginator) }}

**Time: 2017-11-8 15:00**