<?php 
namespace app\api\controller;
use SmsApi;
class Login extends Base{
	
    public function index() {

       
    }

    public function registerApi(){
        try{
        	$data = input('post.');
            $pid = input('param.pid');
            if($pid){
                $data['pid'=>$pid];
            }
        	$memberService = new \app\service\MemberService;
        	return $memberService->saveMemberInfo($data);
        }catch (Exception $e){
        	return json(['code'=>100,'msg'=>$e->getMessage()]);
        }
    }

    public function isFieldRegisterApi(){
    	try{
        	$field = input('post.field');
        	$value = input('post.value');
        	$memberService = new \app\service\MemberService;
        	$result = $memberService->isFieldRegister($field,$value);
            return json(['code'=>100,'msg'=>$result]);
        }catch (Exception $e){
        	return json(['code'=>100,'msg'=>$e->getMessage()]);
        }
    }


    public function loginApi(){
    	try{
        	$username = input('post.username');
        	$password = input('post.password');
        	$memberService = new \app\service\MemberService;
        	$result = $memberService->login($username,$password);
            if($result){
            	$res = $this->wxlogin($result);
            	if($res===true){
                    session(null,'token');
            		return json(['code'=>100,'msg'=>'登陆成功']);
            	}else{
            		return json(['code'=>200,'msg'=>'系统错误']);
            	}            	
            }
            return json(['code'=>200,'msg'=>'账号密码错误']);
        }catch (Exception $e){
        	return json(['code'=>200,'msg'=>$e->getMessage()]);
        }
    }


    protected function wxlogin($id){
		$member =new \app\service\MemberService;
    	$memberInfo = $member->getMemberInfo(['id'=>$id]);
    	unset($memberInfo['password']);
    	$cookie = md5($memberInfo['id'].$memberInfo['create_time'].'hot');
    	cookie('member',md5($memberInfo['id'].$memberInfo['create_time'].'hot'));    	
        $result = session('memberInfo',$memberInfo,'think');
        if($cookie){
        	return true;
        }else{
        	return false;
        }
	}



    public function autoLogin(){
        $id = 1;
        $member =new \app\service\MemberService;
        $memberInfo = $member->getMemberInfo(['id'=>$id]);
        unset($memberInfo['password']);
        $this->memberInfo = $memberInfo;
        $cookie = md5($memberInfo['id'].$memberInfo['create_time'].'hot');
        cookie('member',md5($this->memberInfo['id'].$this->memberInfo['create_time'].'hot'));   
        $result = session('memberInfo',$memberInfo,'think');
        return json($result);
    }


    
    // 获取手机验证码
    public function getMobileCodeApi(){
        try{
            $telephone = input('telephone');
            $randstr = str_shuffle('1234567890');
            $smscode = substr($randstr, 0, 6);
            $content = json_encode([ 'code' => $smscode, 'minute' => 5, 'comName' => 'HOT大热篮球' ]);
            $smsApi = new SmsApi();
            $smsApi->paramArr = [
                'mobile' => $telephone,
                'content' => $content,
                'tNum' => 'T150606060601'
            ];
            $sendsmsRes = $smsApi->sendsms();
            if ($sendsmsRes == 0) {
                $data = ['smscode' => $smscode, 'phone' => $telephone, 'content' => $content,'create_time' => time(), 'use' => '会员注册'];
                $savesms = db('smsverify')->insert($data);
                if (!$savesms) {
                    return [ 'code' => 200, 'msg' => '短信验证码记录异常' ];
                }

                return [ 'code' => 100, 'msg' => '验证码已发送,请注意查收' ];
            } else {
                return [ 'code' => 200, 'msg' => '获取验证码失败,请重试' ];
            }

        }catch (Exception $e){
            return json(['code'=>200,'msg'=>$e->getMessage()]);
        }
    }

    // 验证手机验证码
    public function validateSmsCodeApi(){
        try{
            $telephone = input('telephone');
            $smscode = input('smsCode');
            $smsverify = db('smsverify')->where([ 'phone' => $telephone, 'smscode' => $smscode, 'status' => 0 ])->find();
            if (!$smsverify) {
                return [ 'code' => 200, 'msg' => '验证码无效,请重试' ];
            }

            if (time()-$smsverify['create_time'] > 300) {
                return [ 'code' => 200, 'msg' => '验证码已过期,请重新获取' ];
            }

            db('smsverify')->where(['id' => $smsverify['id']])->setField('status', 1);
            return [ 'code' => 100, 'msg' => '验证通过'];

        }catch (Exception $e){
            return json(['code'=>200,'msg'=>$e->getMessage()]);
        }
    }
}