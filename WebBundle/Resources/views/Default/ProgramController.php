<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\FileToolkit;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;


class ProgramController extends BaseController
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
        $tj = $data;
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
			$data = $model->searchPrograms( $where,[0,100] );
		else
			$data = $model->searchPrograms( 'p.comid>0',[0,100] );

		$result = [];

		if( $data )
		{
			foreach( $data as $k => $v )
			{
				$v = (array)$v;
				$result[$v["comid"]][] = $v;
				$result[$v["comid"]]['info']['comid']       = $v['comid'];
				$result[$v["comid"]]['info']['schoolname'] 	= $v['university'];
				$result[$v["comid"]]['info']['ranklocal'] 	= $v['rank_local'];
				$result[$v["comid"]]['info']['rankworld'] 	= $v['rank_world'];
				$result[$v["comid"]]['info']['logo'] 		= $v['logo_sqr'];
			}
            //去除空项
            array_filter($result);

            foreach( $result as $k => $v )
            {
                $result[$k]['info']['count'] = count($v)-1;
            }
		}

        // 侧边栏显示
        $aside[] = $model->findProgramsBySchoolId(1,[0,10]);
        $aside[] = $model->findProgramsBySchoolId(2,[0,10]);
        $aside[] = $model->findProgramsBySchoolId(3,[0,10]);
        $aside[] = $model->findProgramsBySchoolId(4,[0,10]);
        $aside[] = $model->findProgramsBySchoolId(6,[0,10]);
        $aside[] = $model->findProgramsBySchoolId(7,[0,10]);
        $aside[] = $model->findProgramsBySchoolId(8,[0,10]);
        $aside[] = $model->findProgramsBySchoolId(9,[0,10]);

		return $this->render('TopxiaWebBundle:Default:program.html.twig',[
								'tags'		=>	$tags,
								'edus'		=>	$edus,
								'lists'		=>	$result,
                                'schoolcount'     =>  count($result),
                                'programcount'     =>  count($data),
                                'aside'     =>  $aside,
                                'tj'        =>  $tj,
			]);
    }

    public function listAction(Request $request,$id)
    {
        header("Content-Type:text/html;Charset=utf-8");
        if( $id == (int)$id )
        {
            // 获取学校信息
            $school = $this->getConsultantService()->searchSchoolByid($id);
            //获取文章信息
            $article = $this->getArticleService()->getArticlesForIndex();
            // 获取专业信息
            $programs = $this->getProgramService()->findProgramsBySchoolId($id,[0,100]);
            $programs['count'] = count($programs);
            // echo '<pre>';  print_r($programs);exit;

            return $this->render('TopxiaWebBundle:Default:program_list.html.twig',[
                                                                'school'       =>   $school,
                                                                'program'      =>   $programs,
                                                                'article'      =>   $article,
                                                            ]);
        }
        else
        {
            return $this->jump('页面走丢咯，请稍后再试',3);
        }
    }

    public function detailAction(Request $request,$id)
    {
        header("Content-Type:text/html;Charset=utf-8");
        if( $id == (int)$id )
        {
            $data = $this->getProgramService()->searchPrograms("p.id={$id}",[0,1]);
            echo '<pre>';  print_r($data);exit;

            return $this->render('TopxiaWebBundle:Default:program_detail.html.twig',$data);
        }
        else
        {
            return $this->jump('页面走丢咯，请稍后再试',3);

        }
    }


    protected function getProgramService()
    {
        return $this->getServiceKernel()->createService('Topxia:Program.ProgramService');
    }

    protected function getConsultantService()
    {
        return $this->getServiceKernel()->createService('Consultant:Consultant.ConsultantService');
    }

    protected function getArticleService()
    {
        return $this->getServiceKernel()->createService('Topxia:Article.ArticleService');
    }
}
