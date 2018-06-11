<?php
// 前端控制器基类
namespace app\common\controller;

use think\Controller;
use app\service\SystemService;
use app\service\MemberService;
use think\Cookie;
use think\Request;
use app\service\WechatService;

class Frontend extends Controller
{
    public $systemSetting;
    public $memberInfo;

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $memberS = new MemberService();
        // 从模板消息url进入 带有openid字段 保存会员登录信息
        if (input('?param.openid')) {
            $member = $memberS->getMemberInfo(['openid' => input('param.openid')]);
            if ($member) {
                cookie('mid', $member['id']);
                cookie('member', md5($member['id'] . $member['member'] . config('salekey')));
                session('memberInfo', $member, 'think');
            }
        }

        $url = cookie('url');
        if (!$url) {
            cookie('url', \request()->url(), 1800);
        }
        // 获取推荐人信息
        $pmember = [];
        if (input('?param.pid')) {
            $pid = input('param.pid');
            $pmember = $memberS->getMemberInfo(['id' => $pid]);
            if ($pmember) {
                cookie('pid', $pmember['id']);
            } else {
                cookie('pid', null);
            }
        }
        $this->systemSetting = SystemService::getSite();

        $this->memberInfo = session('memberInfo', '', 'think');
        $this->assign('memberInfo', $this->memberInfo);
        //提示完善信息对话框链接
        $wechatS = new WechatService();
        $fastRegisterInwx = $wechatS->oauthredirect(url('login/fastRegister', '', '', true));
        $fasturl = $this->is_weixin() ? $fastRegisterInwx : url('login/login');

        // 微信分享信息链接
        $shareurl = request()->url(true);
        $jsapi = $wechatS->jsapi($shareurl);

        // 历史访问记录
        $this->visithistory();

        if (Cookie::has('homeurl')) {
            $this->assign('homeurl', Cookie::get('homeurl'));
        } else {
            $this->assign('homeurl', url(request()->module().'/index/index') );
        }

        $this->assign('shareurl', $shareurl);
        $this->assign('jsapi', $jsapi);
        $this->assign('fasturl', $fasturl);
        $this->assign('mid', cookie('mid'));
        $this->assign('systemSetting', $this->systemSetting);
        $this->assign('pmember', $pmember);
    }

    // 历史访问记录
    protected function visithistory()
    {
        // 历史访问记录
        //Cookie::delete('visit_history');
        if (Cookie::has('visit_history')) {
            //读取cookie
            $urls = Cookie::get('visit_history');
            //字符串转回原来的数组
            $arr = unserialize($urls);
            //当前页面url添加到数组中
            $arr[] = input('server.REQUEST_URI');
            //除去重复的
            $arr = array_unique($arr);
            //只保存10条访问记录
            if (count($arr) > 10) {
                array_shift($arr);
            }
            //存储为字符串
            $urls = serialize($arr);
            //保存到cookie当中（半小时）
            Cookie::set('visit_history', $urls, 1800);
        } else {
            //获取当前页面URL
            $url = input('server.REQUEST_URI');
            //将当前URL保存到数组中
            $arr[] = $url;
            //存储为字符串
            $urls = serialize($arr);
            //保存到cookie当中（半小时）
            Cookie::set('visit_history', $urls, 1800);
        }
        // 倒数第2个url 输出用于返回上一页
        $history = array_slice($arr, -2, 1);
        $this->assign('historyurl', $history[0]);
    }

    // 判断是否是微信浏览器
    function is_weixin()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            // 微信
            return true;
        } else {
            return false;
        }
    }

    // 用户openid是否有会员信息
    public function checkopenid()
    {
        if (cookie('openid')) {
            $openid = cookie('openid');
            $memberS = new MemberService();
            $memberInfo = $memberS->getMemberInfo(['openid' => $openid]);
            if ($memberInfo) {
                unset($memberInfo['password']);
                cookie('mid', $memberInfo['id']);
                cookie('openid', $memberInfo['openid']);
                cookie('member', md5($memberInfo['id'] . $memberInfo['member'] . config('salekey')));
                session('memberInfo', $memberInfo, 'think');
                $this->memberInfo = $memberInfo;
                return json(['code' => 200, 'msg' => 1, 'data' => $memberInfo]);
            } else {
                $userinfo = cache('userinfo_' . $openid);
                $member = [
                    'id' => 0,
                    'openid' => $userinfo['openid'],
                    'member' => $userinfo['nickname'],
                    'nickname' => $userinfo['nickname'],
                    'avatar' => str_replace("http://", "https://", $userinfo['headimgurl']),
                    'hp' => 0,
                    'level' => 0,
                    'telephone' => '',
                    'email' => '',
                    'realname' => '',
                    'province' => '',
                    'city' => '',
                    'area' => '',
                    'location' => '',
                    'sex' => 0,
                    'height' => 0,
                    'weight' => 0,
                    'charater' => '',
                    'shoe_code' => 0,
                    'birthday' => '0000-00-00',
                    'create_time' => 0,
                    'pid' => 0,
                    'hp' => 0,
                    'cert_id' => 0,
                    'score' => 0,
                    'flow' => 0,
                    'balance' => 0,
                    'remarks' => 0,
                    'hot_id' => 00000000,
                    'hot_coin'=>0,
                    'age' => 0,
                    'fans' => 0
                ];
//                cookie('mid', 0);
                cookie('openid', $userinfo['openid']);
                cookie('member', md5($member['id'] . $member['member'] . config('salekey')));
                session('memberInfo', $member, 'think');
                $this->memberInfo = $member;
                return json(['code' => 200, 'msg' => -1, 'data' => $member]);
            }
        } else {
            $member = [
                'id' => 0,
                'member' => '游客',
                'nickname' => '游客',
                'avatar' => '/static/default/avatar.png',
                'hp' => 0,
                'level' => 0,
                'telephone' => '',
                'email' => '',
                'realname' => '',
                'province' => '',
                'city' => '',
                'area' => '',
                'location' => '',
                'sex' => 0,
                'height' => 0,
                'weight' => 0,
                'charater' => '',
                'shoe_code' => 0,
                'birthday' => '0000-00-00',
                'create_time' => 0,
                'pid' => 0,
                'hp' => 0,
                'cert_id' => 0,
                'score' => 0,
                'flow' => 0,
                'balance' => 0,
                'remarks' => 0,
                'hot_id' => 00000000,
                'hot_coin'=>0,
                'age' => 0,
                'fans' => 0
            ];
//            cookie('mid', 0);
            cookie('member', md5($member['id'] . $member['member'] . config('salekey')));
            session('memberInfo', $member, 'think');
            $this->memberInfo = $member;
            return json(['code' => 200, 'msg' => 0, 'data' => $member]);
        }
    }
}