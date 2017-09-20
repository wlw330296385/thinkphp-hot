<?php 
namespace app\api\controller;
use app\api\controller\Base;
use app\service\CampService
class CampMember extends Base{
        protected $CampService;
	public function _initialize(){
		parent::_initialize();
        $this->CampService = new CampService;
	}

    

    // 申请成为训练营的某个身份
    public function applyApi(){
        try{
            $type = input('param.type');
            $camp_id = input('param.camp_id');
            $remarks = input('param.remarks');
            $campInfo = $this->CampService->getCampInfo($camp_id);
            if(!$campInfo){
                return json(['code'=>200,'msg'=>'不存在此训练营']);
            }
            if(!$type || ($type>5 || $type = 4 || $type <1)){
                return json(['code'=>200,'msg'=>'不存在这个身份']);
            }

            $result = db('camp_member')->insert(['camp_id'=>$campInfo['id'],'camp'=>$campInfo['camp'],'member_id'=>$this->memberInfo['id'],'member'=>$memberInfo['member'],'type'=>$type,'status'=>0]);
            if($result){
                return json(['code'=>100,'msg'=>'申请成功']);
            }else{
                return json(['code'=>200,'msg'=>'申请失败']);
            }
        }catch(Exception $e){
            return json(['code'=>200,'msg'=>$e->getMessage()]);
        }  
    }

    // 训练营人员审核
    public function ApproveApplyApi(){
        try{
            $id = input('param.id');
            $status = input('param.status');
            if(!$id || !$status || ($status!=1|| $status!=-1)){
                return json(['code'=>200,'msg'=>'请正确传参']);
            }
            $campMemberInfo = db('camp_member')->where(['id'=>$id,'status'=>0])->find();
            if(!$campMemberInfo){
                return json(['code'=>200,'msg'=>'不存在该申请']);
            }
            $isPower = $this->CampService->isPower($campMemberInfo['camp_id'],$this->memberInfo['id']);

            if($isPower<3 && $type>2){
                return json(['code'=>200,'msg'=>'您没有这个权限']);
            }

            $result = db('camp_member')->where(['id'=>$id])->update(['status'=>1]);
            if($result){
                return json(['code'=>100,'msg'=>'操作成功']);
            }else{
                return json(['code'=>200,'msg'=>'操作失败']);
            }
        }catch(Exception $e){
            return json(['code'=>200,'msg'=>$e->getMessage()]);
        }  
    }



    //训练营人员变更
    public function modifyApi(){
        try{
            $id = input('param.id');
            $type = input('param.type');
            if(!$id || !$type || ($type!=2|| $type!=5||$type!=3)){
                return json(['code'=>200,'msg'=>'请正确传参']);
            }

            $campMemberInfo = db('camp_member')->where(['id'=>$id,'status'=>1])->find();
            if(!$campMemberInfo){
                return json(['code'=>200,'msg'=>'不存在该人员']);
            }
            $isPower = $this->CampService->isPower($campMemberInfo['camp_id'],$this->memberInfo['id']);

            if($isPower<4){
                return json(['code'=>200,'msg'=>'您没有这个权限']);
            }

            $result = db('camp_member')->where(['id'=>$id])->update(['type'=>$type]);
            if($result){
                return json(['code'=>100,'msg'=>'操作成功']);
            }else{
                return json(['code'=>200,'msg'=>'操作失败']);
            }
        }catch(Exception $e){
            return json(['code'=>200,'msg'=>$e->getMessage()]);
        }  
    }
   
}
