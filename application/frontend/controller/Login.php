<?php 
namespace app\frontend\controller;
use app\frontend\controller\Base;
use app\service\WechatService;

class Login extends base{
	
    public function index() {

        return view('Login/index');
    }

    public function login(){
        
    	return view('Login/login');
    }
    public function registerSuccess(){

    	return view('Login/registerSuccess');
    }
    public function loginSuccess(){
    	return view('Login/loginSuccess');
    }


    public function register(){
        $referer = input('param.referer');
        $pid = input('param.pid');
        $this->assign('pid',$pid);
        $this->assign('referer',$referer);
        return view('Login/register');
    }

    // 微信用户授权
    public function wxlogin() {
        $WechatS = new WechatService;
        $userinfo = $WechatS->oauthUserinfo();
        if ($userinfo) {
            // 查询是否已注册
            $memberInfo = db('member')->where(['openid'=>$userinfo['openid']])->find();
            if($memberInfo){
                session('memberInfo',$memberInfo,'think');
                $url = cookie('url');
                $this->redirect($url);
            }else{
                $data = ['id'=>0,'member'=>'游客','nickname'=>$userinfo['nickname'],'hp'=>0,'level'=>0,'avatar'=>$userinfo['headimgurl'],'openid'=>$userinfo['openid']];
                session('memberInfo',$data,'think');
                // $this->redirect('Login/fastRegister');
                $url = cookie('url');
                $this->redirect($url);
            }
            
        }else{
            $this->redirect('frontend/Index/index');
        }
        
    }

    public function fastRegister(){

        return view('Login/fastRegister');
    }
}