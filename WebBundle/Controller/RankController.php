<?php 
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Service\Common\ServiceKernel;
class RankController extends BaseController
{
	public function indexAction(Request $request, $id, $p=1)
	{
		$pagesize = 8;
		$kernel = $this->getServiceKernel();
		$model = $kernel->createService('Topxia:Rank.RankService');
		//取出分类
		$cates = $model->getRankCategories();
		//判断当前页数
		$nowid = $id ? $id : 1;
		//当前分类下 排行
		$start = ($p-1)*$pagesize;
		$ranks = $model->getSchoolRanksByCateId( $nowid, $start, $pagesize );

		//获取数量
		$counts = $model->getRandValidCount($nowid);
		//页数
		$pages = (int)$counts['counts']/(int)$pagesize;
		//进一取整
		$pages = ceil($pages);
		
		//分类排行
		$cateRank = $model->getCatesByCount(8);

		return $this->render( 'TopxiaWebBundle:Default:rank.html.twig', array(
												'cates'		=>	$cates,
												'ranks'		=>	$ranks,
												'id'		=>	$nowid,
												'cateRank'	=>	$cateRank,
												'counts'	=>	$counts,
												'nowpage'	=>	$p,
												'page'		=>	$pages,
											) );
	}

	public function ranklistAction(Request $request, $id, $p=1) 
	{
		//定义每页显示量
		$pagesize = 8;

		//接受rank_id 处理
		$id = $id ? $id : 1;

		//服务操作
		$kernel = $this->getServiceKernel();
		$model = $kernel->createService('Topxia:Rank.RankService');

		//分页参数
		$start = ($p-1)*$pagesize;

		//获取数据
		$schools = $model->GetSchoolsByRankId( $id, $start, $pagesize );
		$rank = $model->GetRank($id);

		// echo '<pre>';exit(print_r($schools));
		
		return $this->render( 'TopxiaWebBundle:Default:ranklist.html.twig', array(
												'schools'	=>	$schools,
												'rank'		=>	$rank,
										) );
	}
}
