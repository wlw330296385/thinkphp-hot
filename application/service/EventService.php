<?php

namespace app\service;

use app\model\Event;
use think\Db;

use app\common\validate\EventVal;
class EventService {
    private $EventModel;
    public function __construct(){
        $this->EventModel = new Event;
    }


    // 获取所有活动
    public function getEventList($map=[],$page = 1,$order='',$paginate = 10) {
        $result = Event::where($map)->order($order)->page($page,$paginate)->select();

        if($result){
            $res = $result->toArray();
            return $res;
        }else{
            return $result;
        }
    }

    // 分页获取活动
    public function getEventListByPage($map=[], $order='',$paginate=10){
        $res = Event::where($map)->order($order)->paginate($paginate);
        if($res){
            return $res->toArray();
        }else{
            return $res;
        }
    }

    // 软删除
    public function SoftDeleteEvent($id) {
        $result = Event::destroy($id);
        if (!$result) {
            return [ 'msg' => __lang('MSG_400'), 'code' => 100 ];
        } else {
            return ['msg' => __lang('MSG_200'), 'code' => 200, 'data' => $result];
        }
    }

    // 获取一个活动
    public function getEventInfo($map) {
        $result = Event::where($map)->find();
        if ($result){
            $res = $result->toArray();
            if($res['dom']){
                $res['doms'] = unserialize($res['dom']);
            }else{
                $res['doms'] = [];
            }
            if($res['assistant']){
                $pieces = unserialize($res['assistant']);
                $res['assistants'] = implode(',', $pieces);
            }else{
                $res['assistants'] = '';
            }

            if($res['assistant_id']){
                $pieces = unserialize($res['assistant_id']);
                $res['assistant_ids'] = implode(',', $pieces);
            }else{
                $res['assistant_ids'] = '';
            }
            $res['status_num'] = $result->getData('status');
            return $res;
        }else{
            return $result;
        }
    }




    // 编辑活动
    public function updateEvent($data,$id){
        $is_power = $this->isPower($data['camp_id'],$data['member_id']);
        if($is_power<2){
            return ['code'=>100,'msg'=> __lang('MSG_403')];
        }
        
        if($data['doms']){
                $doms = explode(',', $data['doms']);
                $seria = serialize($doms);
                $data['dom'] = $seria;
            }else{
                $data['dom'] = '';
            }
        if($data['assistants']){
            $doms = explode(',', $data['assistants']);
            $seria = serialize($doms);
            $data['assistant'] = $seria;
        }else{
            $data['assistant'] = '';
        }
        if($data['assistant_ids']){
            $doms = explode(',', $data['assistant_ids']);
            $seria = serialize($doms);
            $data['assistant_id'] = $seria;
        }else{
            $data['assistant_id'] = '';
        }
        $validate = validate('EventVal');
        if(!$validate->check($data)){
            return ['msg' => $validate->getError(), 'code' => 100];
        }
        $result = $this->EventModel->save($data,['id'=>$id]);
        if($result){
            // return ['msg' => __lang('MSG_200'), 'code' => 200, 'data' => $this->EventModel->id];
            return ['msg' => __lang('MSG_200'), 'code' => 200, 'data' => $id];
        }else{
            return ['msg'=>__lang('MSG_400'), 'code' => 100];
        }
    }

    // 新增活动
    public function createEvent($data){
        // 查询是否有权限
        $is_power = $this->isPower($data['camp_id'],$data['member_id']);
        if($is_power<2){
            return ['code'=>100,'msg'=>__lang('MSG_403')];
        }
        if($data['doms']){
                $doms = explode(',', $data['doms']);
                $seria = serialize($doms);
                $data['dom'] = $seria;
            }else{
                $data['dom'] = '';
            }
        if($data['assistants']){
            $doms = explode(',', $data['assistants']);
            $seria = serialize($doms);
            $data['assistant'] = $seria;
        }else{
            $data['assistant'] = '';
        }
        if($data['assistant_ids']){
            $doms = explode(',', $data['assistant_ids']);
            $seria = serialize($doms);
            $data['assistant_id'] = $seria;
        }else{
            $data['assistant_id'] = '';
        }
        $validate = validate('EventVal');
        if(!$validate->check($data)){
            return ['msg' => $validate->getError(), 'code' => 100];
        }
       
        $result = $this->EventModel->save($data);
        if($result){
            db('camp')->where(['id'=>$data['camp_id']])->setInc('total_Events');
            return ['msg' => __lang('MSG_200'), 'code' => 200, 'data' => $this->EventModel->id];
        }else{
            return ['msg'=>__lang('MSG_400'), 'code' => 100];
        }
    }

    // 活动权限
    public function isPower($camp_id,$member_id){
        $is_power = db('camp_member')
                    ->where([
                        'camp_id'   =>$camp_id,
                        'status'    =>1,
                        'member_id'  =>$member_id,
                        ])
                    ->value('type');

        return $is_power?$is_power:0;
    }

    // 修改活动上架/下架状态 2017/09/28
    public function updateEventStatus($Eventid, $status) {
        $model = new Event();
        $res = $model->update(['id' => $Eventid, 'status' => $status]);
        if (!$res) {
            return [ 'code' => 100, 'msg' => __lang('MSG_400'), 'data' => $model->getError() ];
        } else {
            return [ 'code' => 200, 'msg' => __lang('MSG_200'), 'data' => $res ];
        }
    }
}

