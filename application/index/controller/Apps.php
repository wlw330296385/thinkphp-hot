<?php 
namespace app\index\controller;
use app\frontend\controller\Base;
/**
* 
*/
class Apps extends Base
{
	
	public function _initialize(){
        parent::_initialize();
    }

    public function appsForm(){
        $member_id = session('memberInfo.id');
        $event_id = input('param.event_id');
        $memberInfo = db('member')->where(['id'=>$member_id])->find();
        $event_member = db('event_member')->where(['event_id'=>$event_id,'member_id'=>$this->memberInfo['id']])->find();
        if ($member_id > 0) {
            $cert = db('cert')->where(['member_id'=>$member_id,'cert_type'=>1])->find();

        } else {
            $cert = [
                'cert_no' => '',
                'photo_positive' => '',
                'cert_type' => '',
                'photo_back' => ''
            ];
        }

        $eventInfo = db('event')->where(['id'=>$event_id])->find();

        $this->assign('memberInfo',$memberInfo);
        $this->assign('cert',$cert);
        $this->assign('event_member',$event_member);
        $this->assign('eventInfo',$eventInfo);
        return view('apps/appsForm');
    }

}