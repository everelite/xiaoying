<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\FileToolkit;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Binfen;

class NewdefaultController extends BaseController
{
	public function indexAction(Request $request)
	{
        /* 获取顾问 */
        $constantslists = $this->getConsultantService()->fetchConstantsWithCaseNum(10);

        /* 获取qstlist */
        $qstlists = $this->getQstService()->fetchQstListByCateId(1,['id','desc'],5);

        /* 获取qst answer */
        $qstanswerlists = $this->getQstService()->fetchQstListWidthOneAnswerByCateId(1,['id','desc'],7);

        /* 获取article */// 4:日本留学DIY 47:留考校内考 10:研究生申请 50:SGU/G30申请 14:日语学习/口语提高 7:日本就职 8:日本生活
        $artmodel = $this->getArticleService();
        $index = [4,47,10,50,14,7,8];
        foreach( $index as $k => $v )
        {
            $arr = $artmodel->findArticlesByCategoryId( $v, 0, 18 );
            $artlists[$v] = array_chunk($arr, 9);
            $artlists[$v][1] = isset($artlists[$v][1]) ? $artlists[$v][1] : [];
        }


        /* 获取案例 */
        $caselists = $this->getConsultantService()->getCasesBySchoolId(1,6);

		return $this->render('TopxiaWebBundle:Default:new_index_body.html.twig',
                            array(
                                'constantslists'    =>  $constantslists,
                                'qstlists'          =>  $qstlists,
                                'qstanswerlists'    =>  $qstanswerlists,
                                'caselists'         =>  $caselists,
                                'artlists'          =>  $artlists,
                                )
                            );
	}

	public function newzhAction(Request $request)
	{
		return $this->render('TopxiaWebBundle:Default:newzh.html.twig');
	}

	public function wenhuaAction(Request $request)
	{
		return $this->render('TopxiaWebBundle:Default:wenhua.html.twig');
	}

	public function jiaqiAction(Request $request)
	{
		return $this->render('TopxiaWebBundle:Default:jiaqi.html.twig');
	}

	public function jxjAction(Request $request)
	{
		return $this->render('TopxiaWebBundle:Default:jxj.html.twig');
	}

	public function kcAction(Request $request)
	{
		return $this->render('TopxiaWebBundle:Default:kc.html.twig');
	}

    public function getInfoAction(Request $request)
    {
        if( $request->isXmlHttpRequest() )
        {
            $file = $request->request->all();

            if( ! $this->checkData($file['tel']) )
            {
                $result = ['status'=>false,'remark'=>'请正确填写手机号'];
            }
            elseif( $this->sendMail( $file ) && $this->sendToBinfen( $file ) )
            {
                $result = ['status'=>true];
            }
            else
            {
                $result = ['status'=>false,'remark'=>'系统维护 请稍后再试~'];
            }
        }
        else
        {
            $result = ['status'=>false,'remark'=>'非法操作!'];
        }
        return $this->createJsonResponse($result);
    }

    protected function checkData( $tel )
    {
        if( strlen( $tel ) !== 11 )
            return FALSE;
        //手机号码验证
        $pattern = "/^(1(([35][0-9])|(47)|[8][01236789]))\d{8}$/";
        if( preg_match( $pattern, $tel ) === 1 )
            return true;
        return FALSE;
    }

    protected function sendToBinfen( $data )
    {
        $binfen = new Binfen();
        $binfenconfig = $this->getSettingService()->get('binfen');
        if (empty($binfenconfig['time']) || $binfenconfig['time'] < time())
        {
            $binfenconfig = $binfen::CorpAccessToken();
            $binfenconfig = $binfenconfig["0"];
            $binfenconfig['time'] = time() + $binfenconfig["expiresIn"];
            $this->getSettingService()->set("binfen", $binfenconfig);
        }

		$result = $binfen::adddata($binfenconfig, $binfen::createBfData($data));

        return ( $result[0]['errorCode'] == 0 );
    }

    protected function sendMail( $data )
    {
        $path = $this->get('kernel')->getRootDir().'/../vendor/phpmailer/';
        require_once($path.'class.phpmailer.php');
        require_once($path.'class.smtp.php');

        $data['xl'] = isset($data['xl']) ? $data['xl'] : '';
        switch ( $data['xl'] ) {
            case 1:
                $xl = '本科大一';
                break;
            case 2:
                $xl = '本科大二';
                break;
            case 3:
                $xl = '本科大三';
                break;
            case 4:
                $xl = '本科大四';
                break;
            case 5:
                $xl = '高一';
                break;
            case 6:
                $xl = '高二';
                break;
            case 7:
                $xl = '高三';
                break;
            case 8:
                $xl = '大专大一';
                break;
            case 9:
                $xl = '大专大二';
                break;
            case 10:
                $xl = '大专大三';
                break;

            default:
                $xl = '其他';
                break;
        }
        $subject = "【学员评估】 需求规划 ：{$data['name']}+{$data['tel']}";
        $str = "姓名：{$data['name']}<br /> 电话：{$data['tel']}<br /> 学历：{$xl} <br />  来源：{$data['from']} <br /><br /><br /><br />来自于评估页面，为精准用户，按学科分配给对应的顾问，尽快电话对应。以上内容已录入纷享逍客CRM系统，请顾问随时在CRM反馈跟进进度。";

        $mail = new \PHPMailer();
        /*服务器相关信息*/
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->Host     = 'ssl://smtp.163.com';

        $mail->Username = 'zgs5999';
        $mail->Password = 'laozhou616888';

        /*内容信息*/
        $mail->isHTML(true);
        $mail->Charset  = 'utf-8';
        $mail->From     = 'zgs5999@163.com';
        $mail->FromName = 'Luke';
        $mail->Subject  = $subject;
        $mail->MsgHTML($str);
        //发送到   地址
        // $address = ['zhou.guangsheng@everelite.com'];
        $address = ['chen@everelite.com','zeng@everelite.com','lei.xingting@everelite.com'];
        foreach( $address as $k => $v )
        {
            $mail->addAddress( $v );
        }

        //发送邮件
        if ( $mail->send() )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

	/**
	 * [getConsultantService 顾问，教师，案例，学校 数据]
	 * @return [type] [description]
	 */
    protected function getConsultantService()
    {
        return $this->getServiceKernel()->createService('Consultant:Consultant.ConsultantService');
    }

    /**
     * [getArticleService 文章]
     * @return [type] [description]
     */
    protected function getArticleService()
    {
        return $this->getServiceKernel()->createService('Topxia:Article.ArticleService');
    }

    /**
     * [getQuestionService 问答数据]
     * @return [type] [description]
     */
    protected function getQstService()
    {
    	return $this->getServiceKernel()->createService('Topxia:Qst.QstService');
    }


    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    public function dp( $config )
    {
        header('Content-Type:text/html;Charset=utf-8');
        echo '<pre>';
        var_dump($config);
        exit;
    }
}
