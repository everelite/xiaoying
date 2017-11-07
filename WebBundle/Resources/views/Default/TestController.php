<?php 
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Service\Common\BaseDao;
use Topxia\Service\Common\ServiceKernel;
class TestController extends BaseController
{
	public function indexAction(Request $request)
	{
		//实例化服务了类
		$model = $this->getProgramService();
		// 标签数据
		$tags = $model->findTagsAll();
		// 学历数据
		$edus = $model->findEdusAll();


		$data = $request->query->all();

		$where = '';

		if( isset($data['program']) && !empty($data['program']) )
			$where = "p.name LIKE '%{$data['program']}%'";

		if( isset($data['school']) && !empty($data['school']) )
			$where = "p.university LIKE '%{$data['school']}%'";

		if( isset($data['tag_id']) && !empty($data['tag_id']) )
			$where = "p.cid = {$data['tag_id']}";

		if( isset($data['edu_id']) && !empty($data['edu_id']) )
			$where = "p.eid = {$data['edu_id']}";

		if( $where )
			$data = $model->searchPrograms( $where,[0,3] );
		else
			$data = [];

		$result = [];

		if( $data )
		{
			foreach( $data as $k => $v )
			{
				$v = (array)$v;
				$result[$v["comid"]][] = $v;
				$result[$v["comid"]]['info']['schoolname'] 	= $v['university'];
				$result[$v["comid"]]['info']['ranklocal'] 	= $v['rank_local'];
				$result[$v["comid"]]['info']['rankworld'] 	= $v['rank_world'];
				$result[$v["comid"]]['info']['logo'] 		= $v['logo_sqr'];
				$result[$v["comid"]]['info']['count'] 		= count($v);
			}
		}


		// echo '<pre>';
		// print_r($data);
		// print_r($result);
		// exit;

		return $this->render('TopxiaWebBundle:Default:laozhou.html.twig',[
								'tags'		=>	$tags,
								'edus'		=>	$edus,
								'lists'		=>	$result,
			]);
	}

	//获取 articleService 
    private function getProgramService()
    {
        return $this->getServiceKernel()->createService('Topxia:Program.ProgramService');
    }

    private function getSchoolService()
    {
        return $this->getServiceKernel()->createService('Topxia:School.SchoolService');
    }
}