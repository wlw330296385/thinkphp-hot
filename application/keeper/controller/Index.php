<?php
namespace app\keeper\controller;
use app\service\MemberService;
use app\service\WechatService;

class Index extends Base
{
    // 管家版首页
    public function index() {
        $bannerList = db('banner')->where(['organization_id'=>0,'organization_type'=>0,'status'=>1])->order('ord asc')->limit(3)->select();

        // 热门文章
        $ArticleService= new \app\service\ArticleService;
        $ArticleList = $ArticleService->getArticleList([],1,'hot DESC',4);

        $this->assign('bannerList',$bannerList);
        $this->assign('ArticleList',$ArticleList);
        return view('Index/index');
    }


    // 微信授权回调
    public function wxindex() {
        $WechatS = new WechatService;
        $memberS = new MemberService();
        $userinfo = $WechatS->oauthUserinfo();
        if ($userinfo) {
            cache('userinfo_'.$userinfo['openid'], $userinfo);
            $avatar = str_replace("http://", "https://", $userinfo['headimgurl']);
            //$avatar = $memberS->downwxavatar($userinfo);

            $dbMember = db('member');
            $isMember = $dbMember->where(['openid' => $userinfo['openid']])->find();
            if ($isMember) {
                unset($isMember['password']);
                cookie('mid', $isMember['id']);
                cookie('openid', $isMember['openid']);
                cookie('member', md5($isMember['id'].$isMember['member'].config('salekey')));
                session('memberInfo', $isMember, 'think');
                $this->redirect('keeper/Index/index');
            } else {
                $member = [
                    'id' => 0,
                    'openid' => $userinfo['openid'],
                    'member' => $userinfo['nickname'],
                    'nickname' => $userinfo['nickname'],
                    'avatar' => $avatar,
                    'hp' => 0,
                    'level' => 0,
                    'telephone' =>'',
                    'email' =>'',
                    'realname'  =>'',
                    'province'  =>'',
                    'city'  =>'',
                    'area'  =>'',
                    'location'  =>'',
                    'sex'   =>0,
                    'height'    =>0,
                    'weight'    =>0,
                    'charater'  =>'',
                    'shoe_code' =>0,
                    'birthday'  =>'0000-00-00',
                    'create_time'=>0,
                    'pid'   =>0,
                    'hp'    =>0,
                    'cert_id'   =>0,
                    'score' =>0,
                    'flow'  =>0,
                    'balance'   =>0,
                    'remarks'   =>0,
                    'hot_id'=>00000000,
                ];
                cookie('mid', 0);
                cookie('openid', $userinfo['openid']);
                cookie('member', md5($member['id'].$member['member'].config('salekey')) );
                session('memberInfo', $member, 'think');
                $this->redirect('keeper/Index/index');
            }
        } else {
            $this->redirect('keeper/index/index');
        }
    }
}