<?php
namespace app\service;
use app\model\Bill;
use app\model\CampFinance;
use app\common\validate\BillVal;
use think\Db;
use app\model\LessonMember;
use app\model\CampMember;
class BillService {

    public $Bill;
    public function __construct()
    {
        $this->Bill = new Bill;
    }

    // 关联lesson获取订单详情
    public function getBill($map){
        $result = $this->Bill->where($map)->find();
        if($result){
            $res = $result->toArray();
            return $res;
        }else{
            return $result;
        }
    }


    // 获取订单列表
    public function getBillList($map,$p = 1){
        $result = $this->Bill->where($map)->page($p,10)->select();
        
        if($result){
            $res = $result->toArray();
            return $res;
        }else{
            return $result;
        }
    }

    // 获取订单列表
    public function getBillListByPage($map,$paginate = 10){
        $result = $this->Bill->where($map)->paginate($paginate);
        // dump($result);die;
        if($result){
            $res = $result->toArray();
            return $res;
        }else{
            return $result;
        }
    }

    public function billCount($map){
        $result = $this->Bill->where($map)->count();
        return $result?$result:0;
    }

    //生成一笔订单
    public function createBill($data){
        $validate = validate('BillVal');
        if(!$validate->check($data)){
            return ['msg' => $validate->getError(), 'code' => 100];
        }
        $data['expire'] = time()+86400;
        $result = $this->Bill->save($data);
        if($result){
            return ['code'=>200,'msg'=>'新建成功','data'=>$result];
        }else{
            return ['code'=>100,'msg'=>$this->Bill->getError()];
        }
    }
    
    // 订单支付
    public function pay($data,$map){
        $data['pay_time'] = time();
        $data['expire'] = 0;
        $result = $this->Bill->save($data,$map);      
        if($result){
            $billInfo = $this->Bill->where($map)->find();
            $billData = $billInfo->toArray();
            // 课程支付后操作
            $res = $this->finishBill($billData);
            return $res;
        }else{
            return false;
        }
    }


    // 订单支付(不发送通知)
    public function payNoNotice($data,$map){
        $data['pay_time'] = time();
        $data['expire'] = 0;
        $result = $this->Bill->save($data,$map);      
        if($result){
            $billInfo = $this->Bill->where($map)->find();
            $billData = $billInfo->toArray();
            $res = $this->finishBillNoNotic($billData);
            return $res;
        }else{
            return false;
        }
    }



    /**
    * 订单支付完成操作
    * @param $data 一条订单记录
    **/
    private function finishBill($data){
        $MessageService = new \app\service\MessageService;
        //开始课程操作,包括(模板消息发送,camp\camp_mamber和lesson的数据更新)
        if($data['goods_type'] == '课程'){
            //购买人数+1;
            db('lesson')->where(['id'=>$data['goods_id']])->setInc('students');
            // 训练营的余额和历史会员增加
            $setting = db('setting')->find();
            $campBlance = ($data['balance_pay']*(1-$setting['sysrebate']));
            $ress = db('camp')->where(['id'=>$data['camp_id']])->inc('balance',$campBlance)->inc('total_member',1)->update();

            if($ress){
                db('income')->insert(['lesson_id'=>$data['goods_id'],'lesson'=>$data['goods'],'camp_id'=>$data['camp_id'],'camp'=>$data['camp_id'],'income'=>$data['balance_pay']*(1-$setting['sysrebate']),'system_rebate'=>$setting['sysrebate'],'member_id'=>$data['member_id'],'member'=>$data['member'],'create_time'=>time()]);
            }
            //学生表的总课程和总课量+n;   
            db('student')->where(['id'=>$data['student_id']])->inc('total_lesson',1)->inc('total_schedule',$data['total'])->update();
            // 发送个人消息           
            $MessageData = [
                "touser" => '',
                "template_id" => config('wxTemplateID.successBill'),
                "url" => url('frontend/bill/billInfo',['bill_order'=>$data['bill_order']],'',true),
                "topcolor"=>"#FF0000",
                "data" => [
                    'first' => ['value' => '订单支付成功通知'],
                    'keyword1' => ['value' => $data['student']],
                    'keyword2' => ['value' => $data['bill_order']],
                    'keyword3' => ['value' => $data['balance_pay'].'元'],
                    'keyword4' => ['value' => $data['goods_des']],
                    'remark' => ['value' => '篮球管家']
                ]
            ];
            $saveData = [
                            'title'=>"订单支付成功-{$data['goods']}",
                            'content'=>"订单号: {$data['bill_order']}<br/>支付金额: {$data['balance_pay']}元<br/>支付学生信息:{$data['student']}",
                            'url'=>url('frontend/bill/billInfo',['bill_order'=>$data['bill_order']],'',true),
                            'member_id'=>$data['member_id']
                        ];
            //给训练营营主发送消息
            $MessageCampData = [
                "touser" => '',
                "template_id" => config('wxTemplateID.successBill'),
                "url" => url('frontend/bill/billInfoOfCamp',['bill_order'=>$data['bill_order']],'',true),
                "topcolor"=>"#FF0000",
                "data" => [
                    'first' => ['value' => '订单支付成功通知'],
                    'keyword1' => ['value' => $data['student']],
                    'keyword2' => ['value' => $data['bill_order']],
                    'keyword3' => ['value' => $data['balance_pay'].'元'],
                    'keyword4' => ['value' => $data['goods_des']],
                    'remark' => ['value' => '篮球管家']
                ]
            ];
            $MessageCampSaveData = [
                'title'=>"购买-{$data['goods']}",
                'content'=>"订单号: {$data['bill_order']}<br/>支付金额: {$data['balance_pay']}元<br/>购买人:{$data['member']}<br/>购买理由: {$data['remarks']}",
                'member_id'=>$data['member_id'],
                'url'=>url('frontend/bill/billInfoOfCamp',['bill_order'=>$data['bill_order']],'',true)
            ];
            // camp_member操作
            $CampMember = new CampMember;
            $is_campMember = $CampMember->where(['camp_id'=>$data['camp_id'],'member_id'=>$data['member_id']])->find();
            if($is_campMember){
                // 强制更新
                 $CampMember->save(['type'=>1,'status'=>1],['camp_id'=>$data['camp_id'],'member_id'=>$data['member_id']]);
            }else{
                $CampMember->save(['type'=>1,'camp_id'=>$data['camp_id'],'member_id'=>$data['member_id'],'camp'=>$data['camp'],'member'=>$data['member'],'status'=>1]);
            }
            // lesson_member操作
            $LessonMember = new LessonMember;
            $is_student2 = $LessonMember->where(['camp_id'=>$data['camp_id'],'lesson_id'=>$data['goods_id'],'student_id'=>$data['student_id']])->find();
            
            //添加一条学生数据
            if(!$is_student2){
                if($data['balance_pay']>0){
                    $re = $LessonMember->save(['camp_id'=>$data['camp_id'],'camp'=>$data['camp'],'member_id'=>$data['member_id'],'member'=>$data['member'],'status'=>1,'student_id'=>$data['student_id'],'student'=>$data['student'],'lesson_id'=>$data['goods_id'],'lesson'=>$data['goods'],'rest_schedule'=>$data['total'],'total_schedule'=>$data['total'],'type'=>1]);
                    if(!$re){
                        db('log_lesson_member')->insert(['member_id'=>$data['member_id'],'member'=>$data['member'],'data'=>json_encode($data)]);
                    }else{
                        db('log_lesson_member')->insert(['member_id'=>$data['member_id'],'member'=>$data['member'],'data'=>json_encode($data),'status'=>1]);
                    }
                }
            }else{
                //$re = db('lesson_member')->where(['camp_id'=>$data['camp_id'],'lesson_id'=>$data['goods_id'],'student_id'=>$data['student_id'],'status'=>1])->inc('rest_schedule',$data['total'])->inc('total_schedule',$data['total'])->update();
                $re = db('lesson_member')->where(['camp_id'=>$data['camp_id'],'lesson_id'=>$data['goods_id'],'student_id'=>$data['student_id']])->whereNull('delete_time')->inc('rest_schedule',$data['total'])->inc('total_schedule',$data['total'])->update(['status' => 1,'type' => 1]);
                $ress = db('student')->where(['id'=>$data['student_id']])->inc('total_lesson',1)->update();
                if(!$re){
                    db('log_lesson_member')->insert(['member_id'=>$data['member_id'],'member'=>$data['member'],'data'=>json_encode($data)]);
                }else{
                    db('log_lesson_member')->insert(['member_id'=>$data['member_id'],'member'=>$data['member'],'data'=>json_encode($data),'status'=>1]);
                }
                
            }
        //结束增加学生数据
        // 发送模板消息
        $MessageService->sendMessageMember($data['member_id'],$MessageData,$saveData);
        $MessageService->sendCampMessage($data['camp_id'],$MessageCampData,$MessageCampSaveData);
        //结束课程操作
        // 记录训练营课程营业额
        $daytime = $data['pay_time'];
        $dataCampFinance = [
            'camp_id' => $data['camp_id'],
            'camp' => $data['camp'],
            'finance_type' => 1,
            'lesson_turnover' => $data['balance_pay'],
            'bill_id' => $data['id'],
            'date' => date('Ymd', $daytime),
            'datetime' => $daytime
        ];
        CampFinance::create($dataCampFinance);
        }elseif ($data['goods_type'] == '活动') {
            // camp_member操作
            $CampMember = new CampMember;
            $is_campMember = $CampMember->where(['camp_id'=>$data['camp_id'],'member_id'=>$data['member_id']])->find();
            if(!$is_campMember){
                //成为粉丝
                $CampMember->save(['type'=>-1,'camp_id'=>$data['camp_id'],'member_id'=>$data['member_id'],'camp'=>$data['camp'],'member'=>$data['member'],'status'=>1]);
            }
        }   
                
        return 1;
    }



    /**
    * 订单支付完成操作(没有用户通知)
    * @param $data 一条订单记录
    **/
    private function finishBillNoNotic($data){
        $MessageService = new \app\service\MessageService;
        //开始课程操作,包括(模板消息发送,camp\camp_mamber和lesson的数据更新)
        if($data['goods_type'] == '课程'){
            //购买人数+1;
            db('lesson')->where(['id'=>$data['goods_id']])->setInc('students');
            // 训练营的余额和历史会员增加
            $setting = db('setting')->find();
            $campBlance = ($data['balance_pay']*(1-$setting['sysrebate']));
            $ress = db('camp')->where(['id'=>$data['camp_id']])->inc('balance',$campBlance)->inc('total_member',1)->update();

            if($ress){
                db('income')->insert(['lesson_id'=>$data['goods_id'],'lesson'=>$data['goods'],'camp_id'=>$data['camp_id'],'camp'=>$data['camp'],'income'=>$data['balance_pay']*(1-$setting['sysrebate']),'member_id'=>$data['member_id'],'member'=>$data['member'],'create_time'=>time()]);
            }
            //学生表的总课程和总课量+n;   
            db('student')->where(['id'=>$data['student_id']])->inc('total_lesson',1)->inc('total_schedule',$data['total'])->update();
            
            //给训练营营主发送消息
            $MessageCampData = [
                "touser" => '',
                "template_id" => config('wxTemplateID.successBill'),
                "url" => url('frontend/bill/billInfoOfCamp',['bill_order'=>$data['bill_order']],'',true),
                "topcolor"=>"#FF0000",
                "data" => [
                    'first' => ['value' => '订单支付成功通知'],
                    'keyword1' => ['value' => $data['student']],
                    'keyword2' => ['value' => $data['bill_order']],
                    'keyword3' => ['value' => $data['balance_pay'].'元'],
                    'keyword4' => ['value' => $data['goods_des']],
                    'remark' => ['value' => '篮球管家']
                ]
            ];
            $MessageCampSaveData = [
                'title'=>"购买-{$data['goods']}",
                'content'=>"订单号: {$data['bill_order']}<br/>支付金额: {$data['balance_pay']}元<br/>购买人:{$data['member']}<br/>购买理由: {$data['remarks']}",
                'member_id'=>$data['member_id'],
                'url'=>url('frontend/bill/billInfoOfCamp',['bill_order'=>$data['bill_order']],'',true)
            ];
            // camp_member操作
            $CampMember = new CampMember;
            $is_campMember = $CampMember->where(['camp_id'=>$data['camp_id'],'member_id'=>$data['member_id']])->find();
            if($is_campMember){
                // 强制更新
                 $CampMember->save(['type'=>1],['camp_id'=>$data['camp_id'],'member_id'=>$data['member_id']]);
            }else{
                $CampMember->save(['type'=>1,'camp_id'=>$data['camp_id'],'member_id'=>$data['member_id'],'camp'=>$data['camp'],'member'=>$data['member'],'status'=>1]);
            }
            // lesson_member操作
            $LessonMember = new LessonMember;
            $is_student2 = $LessonMember->where(['camp_id'=>$data['camp_id'],'lesson_id'=>$data['goods_id'],'student_id'=>$data['student_id']])->find();
            
            //添加一条学生数据
            if(!$is_student2){
                    $re = $LessonMember->save(['camp_id'=>$data['camp_id'],'camp'=>$data['camp'],'member_id'=>$data['member_id'],'member'=>$data['member'],'status'=>1,'student_id'=>$data['student_id'],'student'=>$data['student'],'lesson_id'=>$data['goods_id'],'lesson'=>$data['goods'],'rest_schedule'=>$data['total'],'total_schedule'=>$data['total'],'type'=>1]);
                    if(!$re){
                        db('log_lesson_member')->insert(['member_id'=>$data['member_id'],'member'=>$data['member'],'data'=>json_encode($data)]);
                    }else{
                        db('log_lesson_member')->insert(['member_id'=>$data['member_id'],'member'=>$data['member'],'data'=>json_encode($data),'status'=>1]);
                    }
            }else{
                // 课量增加
                $re = db('lesson_member')->where(['lesson_id'=>$data['goods_id'],'student_id'=>$data['student_id']])->inc('rest_schedule',$data['total'])->inc('total_schedule',$data['total'])->update(['status'=>1]);
                $ress = db('student')->where(['id'=>$data['student_id']])->inc('total_lesson',1)->inc('total_schedule',$data['total'])->update();
                if(!$re){
                    db('log_lesson_member')->insert(['member_id'=>$data['member_id'],'member'=>$data['member'],'data'=>json_encode($data)]);
                }else{
                    db('log_lesson_member')->insert(['member_id'=>$data['member_id'],'member'=>$data['member'],'data'=>json_encode($data),'status'=>1]);
                }
                
            }
        //结束增加学生数据
        // 发送模板消息
        $MessageService->sendCampMessage($data['camp_id'],$MessageCampData,$MessageCampSaveData);
        //结束课程操作
            // 记录训练营课程营业额
            $daytime = $data['pay_time'];
            $dataCampFinance = [
                'camp_id' => $data['camp_id'],
                'camp' => $data['camp'],
                'finance_type' => 1,
                'lesson_turnover' => $data['balance_pay'],
                'bill_id' => $data['id'],
                'date' => date('Ymd', $daytime),
                'datetime' => $daytime
            ];
            CampFinance::create($dataCampFinance);
        }elseif ($data['goods_type'] == '活动') {
            // camp_member操作
            $CampMember = new CampMember;
            $is_campMember = $CampMember->where(['camp_id'=>$data['camp_id'],'member_id'=>$data['member_id']])->find();
            if(!$is_campMember){
                //成为粉丝
                $CampMember->save(['type'=>-1,'camp_id'=>$data['camp_id'],'member_id'=>$data['member_id'],'camp'=>$data['camp'],'member'=>$data['member'],'status'=>1]);
            }
        }   
                
        return 1;
    }


    //判断订单付款金额
    public function isPay($map){
        $result = $this->Bill->where($map)->find();
        if($result){
            return $result['is_pay'];
        }else{
            return false;
        }
        
    }

    // 用户编辑订单
    public function updateBill($data,$map){
        $billInfo = $this->getBill($map);   
        $MessageService = new \app\service\MessageService;            
        switch ($data['action']) {
        // 退款操作
            case '1':
                if($billInfo['member_id']!= session('memberInfo.id','','think')){
                    return ['code'=>100,'msg'=>'不是您的订单不能申请退款,谢谢'];
                }

                if($billInfo['status'] != 1){
                    return ['code'=>100,'msg'=>'该订单状态不支持退款申请'];
                }else{
                    if($billInfo['goods_type'] == 1){
                        // 查询剩余课时
                        $lesson_member = db('lesson_member')->where(['lesson_id'=>$billInfo['goods_id'],'member_id'=>$billInfo['member_id'],'status'=>1])->find();
                        if($lesson_member['rest_schedule'] < 1){
                            return ['code'=>100,'msg'=>'您已上完课,不允许退款了'];
                        }
                    }
                    $updateData = ['status'=>-1,'remarks'=>$data['remarks']];
                    $result = $this->Bill->save($updateData,$map);
                    if($result){
                        // 发送消息给训练营管理员和营主
                        $MessageCampData = [
                            "touser" => '',
                            "template_id" => config('wxTemplateID.successRefund'),
                            "url" => url('frontend/bill/billInfoOfCamp',['bill_id'=>$map['id']],'',true),
                            "topcolor"=>"#FF0000",
                            "data" => [
                                'first' => ['value' => '['.$billInfo['goods'].']收到一笔申请退款'],
                                'keyword1' => ['value' => $billInfo['bill_order']],
                                'keyword2' => ['value' => $billInfo['balance_pay'].'元'],
                                'keyword3' => ['value' => $billInfo['remarks']],
                                'remark' => ['value' => '大热篮球']
                            ]
                        ];
                        $saveData = [
                                'title'=>"退款申请-{$billInfo['goods']}",
                                'content'=>"订单号: {$billInfo['bill_order']}<br/>退款金额: {$billInfo['balance_pay']}元<br/>退款理由:{$billInfo['remarks']}",
                                'url'=>url('frontend/bill/billInfoOfCamp',['bill_id'=>$map['id']])
                            ];
                        $MessageService->sendCampMessage($billInfo['camp_id'],$MessageCampData,$saveData);
                    }

                }
                break;
            // 修改价格和数量
                case '2':
                    if($billInfo['status'] != 0){
                        return ['code'=>100,'msg'=>'只有未付款订单才可以修改价格和数量'];
                    }else{
                        $updateData = ['remarks'=>$data['remarks'],'total'=>$data['total'],'price'=>$data['price']];
                        $result = $this->Bill->save($updateData,$map);
                        if($result){
                            // 发送消息给用户

                        }
                    }
                    break;
            //同意退款 
                case '3':
                        if($billInfo['status']!= -1){
                            return ['code'=>100,'msg'=>'该订单状态不支持该操作'];
                        }   
                        $isPower = $this->isPower($billInfo['camp_id'],session('memberInfo.id'));
                        if($isPower<3){
                            return ['code'=>100,'msg'=>'您没有这个权限'];
                        }
                        if($billInfo['goods_type'] == '课程'){
                            // 查询剩余课时
                            $lesson_member = db('lesson_member')->where(['lesson_id'=>$billInfo['goods_id'],'member_id'=>$billInfo['member_id'],'status'=>1,'type'=>1])->find();
                            if(!$lesson_member || $lesson_member['rest_schedule']<1){
                                return ['code'=>100,'msg'=>'该学生已上完课,不允许退款'];
                            }
                            $refundTotal = ($lesson_member['rest_schedule']<$billInfo['total'])?$lesson_member['rest_schedule']:$billInfo['total'];


                            $updateData = [
                                'refundamount'=>($refundTotal*$billInfo['price']),
                                'status'=>-2,
                                'remarks' => "您的剩余课时为{$lesson_member['rest_schedule']}, 您的订单总数量为{$billInfo['total']},因此退您{$refundTotal}节课的钱"
                            ]; 
                            $result = $this->Bill->save($updateData,$map);
                            if($result){
                                // 剩余课时的变化
                                $rest_schedule = $lesson_member['rest_schedule']-$refundTotal;
                                if($rest_schedule == 0){
                                    db('lesson_member')->where(['lesson_id'=>$billInfo['goods_id'],'member_id'=>$billInfo['member_id'],'status'=>1,'type'=>1])->update(['rest_schedule'=>$rest_schedule,'status'=>2]);
                                }else{
                                    db('lesson_member')->where(['lesson_id'=>$billInfo['goods_id'],'member_id'=>$billInfo['member_id'],'status'=>1,'type'=>1])->update(['rest_schedule'=>$rest_schedule]);
                                }

                                //训练营营业额对冲
                                db('income')->insert([
                                    'income'        => -($refundTotal*$billInfo['price']),
                                    'lesson_id'     => $billInfo['goods_id'],
                                    'lesson'        => $billInfo['goods'],
                                    'camp_id'       => $billInfo['camp_id'],
                                    'camp'          => $billInfo['camp'],
                                    'system_rebate' => 0,
                                    'member_id'     => $billInfo['member_id'],
                                    'member'        => $billInfo['member'],
                                    'create_time'   => time(),
                                    'update_time'   => time(),
                                ]);
                                // 减少训练营营业额
                                db('camp')->where(['id'=>$billInfo['camp_id']])->setDec('balance',($refundTotal*$billInfo['price']));
                            }
                        }else{
                            // 其他订单
                            
                        }
                       
                        
                        if($result){
                            
                            //发送信息给用户
                            $MessageData = [
                                "touser" => session('memberInfo.openid'),
                                "template_id" => config('wxTemplateID.successCheck'),
                                "url" => url('frontend/bill/billInfo',['bill_id'=>$billInfo['id']],'',true),
                                "topcolor"=>"#FF0000",
                                "data" => [
                                    'first' => ['value' => "{$billInfo['goods']}退款申请已被同意"],
                                    'keyword1' => ['value' => '您的退款申请已被同意'],
                                    'keyword2' => ['value' => date('Y-m-d H:i:s',time())],
                                    'remark' => ['value' => '退款完成需要2-3个工作日到账,如有疑问,请联系客服']
                                ]
                            ];
                            $saveData = [
                                            'title'=>"{$billInfo['goods']}退款申请已被同意",
                                            'content'=>"订单号: {$billInfo['bill_order']}<br/>支付金额: {$billInfo['balance_pay']}元<br/>支付信息:{$billInfo['student']}",
                                            'url'=>url('frontend/bill/billInfo',['bill_id'=>$billInfo['id']],'',true),
                                            'member_id'=>$data['member_id']
                                        ];

                            $MessageService->sendMessageMember($billInfo['member_id'],$MessageData,$saveData); 


                        }
                    break;
            //拒绝退款
                case '4': 
                    
                    break;
            //其他  
                default:
                    return ['code'=>100,'msg'=>'非法操作'];
                    break;
            }

            if($result){
                return ['code'=>200,'msg'=>'操作成功'];
            }else{
                return ['code'=>100,'msg'=>'操作失败,请检查网络或者刷新页面重新尝试'];
            }

    }


    /**
     * 返回权限
     */
    public function isPower($camp_id,$member_id){
        $is_power = db('camp_member')
                    ->where(['member_id'=>$member_id,'camp_id'=>$camp_id,'status'=>1])
                    ->value('type');
                    // echo db('camp_member')->getlastsql();die;
        return $is_power?$is_power:0;
    }



    

}