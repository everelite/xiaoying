<?php 
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\Paginator;
class QstController extends BaseController
{
	public function indexAction(Request $resquest,$p=1) 
	{
        //数据操作
		$model = $this->getQstService();
		$count = $model->GetQstCount();

		//定义每页显示量
		$paginator = new Paginator(
            $this->get('request'),
            $count['count(*)'],
            10
        );
		$data = $model->getQstList( $paginator->getOffsetCount(), $paginator->getPerPageCount() );

		

		return $this->render( 'TopxiaWebBundle:Default:qst.html.twig', array(
											'data'		=>	$data,
											'paginator'	=>	$paginator,
										) );
	}

	public function detailAction(Request $resquest,$id=1)
	{
		$model = $this->getQstService();
		//获取当前qst信息
		$qst = $model->GetQstItemById($id);

		//获取当前qst下answer
		$ans = $model->GetAnswerByQstId($id);

		if( $ans )
		{
			// foreach( $ans as $k=>$v )
			// {
			// 	$v->answer_content = htmlspecialchars($v->answer_content);
			// }
		}
		else
		{
			$ans = [];
		}

		return $this->render( 'TopxiaWebBundle:Default:qstdetail.html.twig', array(
											'ans'		=>	$ans,
											'qst'		=>	$qst,
										) );
	}

	public function getQstService()
	{
		return $this->getServiceKernel()->createService( "Topxia:Question.QuestionService" );
	}
}