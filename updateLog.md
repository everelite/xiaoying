# 1115

1.src/topxia/service/course/dao/lmpl/coursedaolmpl.php

    添加createExcel方法

2.src/topxia/service/course/dao/coursedao.php

    添加createExcel接口

3.src/topxia/service/course/lmplcourseservicelmpl.php

    添加createExcel方法

4.src/topxia/service/course/courseservice.php

    添加createExcel接口

5.src/topxia/adminbundle/controller/coursecontroller.php

    在index方法中添加判断，如果是生成表格动作，则创建excel


# 1114

1.src/topxia/adminbundle/resources/views/course/index.html.twig

    39行(button的下一行)中添加一个a标签，用于导出表格


# 1113

1./public_html/src/topxia/webbundle/controller/courseManageController.php

    修改baseAction，增加判断，如果是授课老师，则跳转到授课老师的专用模板(学员管理)
    授课老师可查看内容只有当前课程的学员列表 和 退出当前课程的学员列表。

2./public_html/src/topxia/webbundle/resources/views/coursestudentmanage/givestudent.html.twig

    添加授课老师专用模板

3./public_html/src/topxia/webbundle/resources/views/coursestudentmanage/giveindex.html.twig

    添加授课老师专用模板

4./public_html/src/topxia/webbundle/resources/views/coursestudentmanage/givetr.html.twig

    添加授课老师专用模板

5./public_html/src/topxia/webbundle/resources/views/coursestudentmanage/givelayout.html.twig

    添加授课老师专用模板

6./public_html/src/topxia/webbundle/controller/UserController.php

    添加_giveAction方法，当用户角色是give时，跳转到此方法显示获取对应信息并显示相应模板

7./public_html/src/topxia/webbundle/resources/views/user/layout.html.twig

    修改判断条件，使授课老师有权限访问当前课程中学员的信息

8./public_html/src/topxia/webbundle/controller/courseStudentManageController.php

    修改refundRecordActionAction，添加判断是授课老师则使用专用模板

9./public_html/src/topxia/webbundle/resources/views/coursestudentmanage/givequit-record.html.twig

    添加授课老师专用模板

10./public_html/src/topxia/webbundle/resources/views/coursestudentmanage/giveindex.html.twig

    添加授课老师专用模板

11./public_html/src/topxia/webbundle/resources/views/coursestudentmanage/givequit-record-tr.html.twig

    添加授课老师专用模板

12./public_html/src/topxia/adminbundle/resources/views/user/create-by-mobile-modal.html.twig

    优化，添加三个checkbox，分别是授课老师、留学顾问和普通学员的选项。


13./public_html/src/topxia/adminbundle/controller/usercontroller.php

    修改createAction，添加用户时角色改为可选则的(从静态改为动态)

PS：角色方面需要添加一个ROLE_GIVE的角色，并复制教师的data给ROLE_GIVE。

**Time: 2017-11-13 15:00**


***


# 1108

1.src\Topxia\Service\User\Impl\BatchNotificationServiceImpl.php

    // 修改用户newNotificationNum字段 显示为有新消息的状态
    $this->createDao('User.UserDao')->getConnection()->update('user', ['newNotificationNum'=>1], array('newNotificationNum' => 0));

2.src\Topxia\WebBundle\Controller\CourseManageController.php

    在 publishAction 添加了新课程发布的组信息推送 和 发布。

3.src\Topxia\WebBundle\Controller\NotificationController.php

    在 indexAction 添加组信息的查询，并拼接两种信息，删除分页功能

4.src\Topxia\WebBundle\Resources\views\Notification\index.html.twig

    删除分页功能 {{ web_macro.paginator(paginator) }}

**Time: 2017-11-8 15:00**