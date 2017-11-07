<?php

namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;

class NotificationController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        // 执行分页程序
        $paginator = new Paginator(
            $request,
            $this->getNotificationService()->getUserNotificationCount($user->id),
            5
        );

        

        // 获取个人消息通知
        $notifications = $this->getNotificationService()->findUserNotifications(
            $user->id,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        // 执行分页程序
        $paginators = new Paginator(
            $request,
            count($nots = $this->getNotification()),
            5
        );

        // 获取群发消息通知
        $nots = $this->getNotification($paginators->getOffsetCount(), $paginators->getPerPageCount());

        // 修改
        for ($i=0; $i < count($nots); $i++) { 
            $nots[$i]['createdTime']  = date('Y-m-d H:i',$nots[$i]['createdTime']); 
        }



        $this->getNotificationService()->clearUserNewNotificationCounter($user->id);
        $user->clearNotifacationNum();

        return $this->render('TopxiaWebBundle:Notification:index.html.twig', array(
            'notifications' => $notifications,
            'paginator' => $paginator,
            'paginators' => $paginators,
            'nots' => $nots
        ));
    }

    public function showAction(Request $request, $id)
    {
        $batchnotification = $this->getBatchNotificationService()->getBatchNotification($id);
        return $this->render('TopxiaWebBundle:Notification:batch-notification-show.html.twig', array(
            'batchnotification' => $batchnotification
        ));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    protected function getBatchNotificationService()
    {
        return $this->getServiceKernel()->createService('User.BatchNotificationService');
    }

    /**
    * 获取指定条数的群发消息，默认10条
    */
    protected function getNotification($start = 0, $limit = 10)
    {
        // 连接数据库，获取PDO对象
        $pdo = new \PDO('mysql:host=127.0.0.1;dbname=wangxiao', 'root', 'root');
        // 准备SQL
        $sql = "select * from batch_notification ORDER BY `createdTime` DESC limit {$start},{$limit}";
        $stmt = $pdo->prepare($sql);
        // 执行操作
        $stmt->execute();
        $notifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        // 释放资源
        $pdo = null;

        return $notifications;

    }
}



