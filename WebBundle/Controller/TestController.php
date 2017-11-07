<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Service\Common\BaseDao;
use Topxia\Service\Common\ServiceKernel;
use Topxia\WebBundle\Controller\BaseController;
use Binfen;
class TestController extends BaseController
{
	public function indexAction(Request $request)
	{
		return $this->jump('哈哈');
	}

	protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}
