<?php 
namespace app\admin\controller;
use app\admin\controller\base\Backend;
class Withdraw extends Backend{
	public function _initialize(){
		parent::_initialize();
	}

    public function index() {

        return view('CampWithdraw/index');
    }


   
    // 提现列表
    public function campWithdrawList(){
        $keyword = input('param.keyword');
        $status = input('param.status');
        $camp_type = input('param.camp_type');
        $map = [];
        if($status){
            $map['status'] = $status;
        }
        if($camp_type){
            $map['camp_type'] = $camp_type;
        }

        $CampWithdraw = new \app\model\CampWithdraw;
        if($keyword){
            $hasWhere['camp|goods|student'] = ['like',"%$keyword%"];
            $campWithdrawList = $CampWithdraw->where($map)->select();
        }else{
            $campWithdrawList = $CampWithdraw->where($map)->select();
        }
        

        if($campWithdrawList){
            $campWithdrawList = $campWithdrawList->toArray();
        }else{
            $campWithdrawList = [];
        }  

        $this->assign('campWithdrawList',$campWithdrawList);
        return $this->fetch('Withdraw/campWithdrawList');
    }

    // 提现处理
    public function campWithdrawDeal(){
        $campWithdraw_id = input('param.campWithdraw_id');
        if(request()->isPost()){
            $remarks = input('param.remarks');
            $action = input('param.action');//2=同意,3=已打款,4=同意并已打款,-1=拒绝;
            
            $CampWithdraw = new \app\model\CampWithdraw;
            $campWithdrawInfo = $CampWithdraw->where(['id'=>$campWithdraw_id])->find();
            if(!$campWithdrawInfo){
                $this->error('传参错误,找不到退款信息');
            }
            $campInfo = db('camp')->where(['id'=>$campWithdrawInfo['camp_id']])->find();
            
            if($action == 3){
                if($campWithdrawInfo['camp_type'] == 2){
                    $camp_withdraw_fee = $campWithdrawInfo['withdraw'] * $campInfo['schedule_rebate'];
                    $buffer = $camp_withdraw_fee + $campWithdrawInfo['withdraw'];
                    db('output')->insert([
                        'output'        => $camp_withdraw_fee,
                        'camp_id'       => $campWithdrawInfo['camp_id'],
                        'camp'          => $campWithdrawInfo['camp'],
                        'member_id'     => $campWithdrawInfo['member_id'],
                        'member'        => $campWithdrawInfo['member'],
                        'type'          => 4,
                        'e_balance'     =>($campInfo['balance'] - $camp_withdraw_fee),
                        's_balance'     =>$campInfo['balance'],
                        'f_id'          =>$campWithdrawInfo['id'],
                        'system_remarks'=>$remarks,
                        'create_time'   => time(),
                        'update_time'   => time(),
                    ]);
                }else{
                    $buffer = $campWithdrawInfo['withdraw'];
                }
                $output  = $campWithdrawInfo['withdraw'];
                db('output')->insert([
                    'output'        => $output,
                    'camp_id'       => $campWithdrawInfo['camp_id'],
                    'camp'          => $campWithdrawInfo['camp'],
                    'member_id'     => $campWithdrawInfo['member_id'],
                    'member'        => $campWithdrawInfo['member'],
                    'type'          => -1,
                    'e_balance'     =>($campInfo['balance'] - $output - $camp_withdraw_fee),
                    's_balance'     =>($campInfo['balance'] - $camp_withdraw_fee),
                    'f_id'          =>$campWithdrawInfo['id'],
                    'system_remarks'=>$remarks,
                    'create_time'   => time(),
                    'update_time'   => time(),
                ]);
                $CampWithdraw->save(['status'=>3,'buffer'=>0],['id'=>$campWithdraw_id]);
            }elseif ($action == -1) {
                if($campWithdrawInfo['camp_type'] == 2){
                    $buffer = $campWithdrawInfo['withdraw'];
                
                    // 解冻资金
                    $CampWithdraw->save(['status'=>-1,'buffer'=>0],['id'=>$campWithdraw_id]);
                    db('camp')->where(['id'=>$campWithdrawInfo['camp_id']])->inc('balance',$buffer)->update();
                }
            
                $this->success('操作成功'); 
            }   
        }else{
            $CampWithdraw = new \app\model\CampWithdraw;
            $campWithdrawInfo = $CampWithdraw
                        ->with('bank')
                        ->where(['id'=>$campWithdraw_id])
                        ->find();    
            $this->assign('campWithdrawInfo',$campWithdrawInfo);
            return $this->fetch('Withdraw/campWithdrawDeal');
        }
        
    }


}