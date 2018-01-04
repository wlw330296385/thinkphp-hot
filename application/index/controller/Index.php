<?php
namespace app\index\controller;
use think\Controller;
use think\Cookie;
use think\Db;
use app\service\WechatService;
class Index extends Controller{

    public function counttest(){
        db('log_wxpay')->count('*');
        echo db('log_wxpay')->getlastsql();
    }
    public function spu(){
        $a = [
            ['name'=>'包车','price'=>5200],
            ['name'=>'不包车','price'=>5000]
        ];
        
        echo json_encode($a);
    }

    public function test(){
        $StudentModel = new \app\model\Student;
        $StudentModel->where(['member_id'=>8])->setInc(['parent_id','total_lesson'],5);
    }

    public function totalSchedule(){
        $giftRecord = db('schedule_giftrecord')->select();
        $list = [];
        foreach ($giftRecord as $key => &$value) {
            $studentList = json_decode($value['student_str'],true);
            foreach ($studentList as $k => $val) {
                
                $list[] = [
                    'student'=> $val['student'],
                    'student_id'=>$val['student_id'],
                    'member'=>$value['member'],
                    'member_id'=>$value['member_id'],
                    'camp'=>$value['camp'],
                    'camp_id'=>$value['camp_id'],
                    'lesson_id'=>$value['lesson_id'],
                    'lesson'=>$value['lesson'],
                    'grade'=>$value['grade'],
                    'grade_id'=>$value['grade_id'],
                    'gift_schedule'=>$value['gift_schedule'],
                    'remarks'=>$value['remarks'],
                    'status'=>$value['status'],
                    'system_remarks'=>'',
                    'create_time'=>time(),
                    'update_time'=>time()
                ];
   
                
            }

        }
        dump($list);
        foreach ($list as $key => $value) {
            db('schedule_gift_student')->insert($value);
        }
        
    }



    public function totalLessonSchedule(){
        // 赠送课时的数量
        $scheduleGiftList = db('schedule_gift_student')
        ->field("student_id,lesson_id,lesson,student,sum(gift_schedule) total ")
        ->group('student_id,lesson_id')
        ->select();
        // dump($scheduleGiftList);
        foreach ($scheduleGiftList as $key => $value) {
            db('lesson_member')->where(['lesson_id'=>$value['lesson_id'],'student_id'=>$value['student_id'],'status'=>1,'type'=>1])->setInc('total_schedule',$value['total']);
        }
        $scheduleBuyList = db('bill')
        ->field("student_id,goods_id as lesson_id,goods as lesson,student,sum(total) total ")
        ->where(['status'=>1,'is_pay'=>1,'goods_type'=>1])
        ->group('student_id,goods_id')
        ->select();
        foreach ($scheduleBuyList as $key => $value) {
            db('lesson_member')->where(['lesson_id'=>$value['lesson_id'],'student_id'=>$value['student_id'],'status'=>1,'type'=>1])->setInc('total_schedule',$value['total']);
        }
        echo '<br /><br /><br />--------------------------------------------------------------------------<br />成功<br /><br /><br />--------------------------------------------------------------------------<br />';
        dump($scheduleBuyList);
    }
    

    public function index(){
        $timestr = strtotime('2017-11-16');
        // 生成微信参数
        $shareurl = request()->url(true);
        $WechatService = new WechatService();
        $jsApi = $WechatService->jsapi($shareurl);
        // echo $timestr-time();
        $this->assign('timestr',time());
        $this->assign('jsApi',$jsApi);
        return view('Index/index');
    }

    public function adminAuth(){
        $res = db('admin_menu')
                ->where(['pid'=>4])
                ->whereOr('pid =5')
                ->whereOr('pid = 3')
                ->column('id');
        echo json_encode($res);
    }


    public function gdMap(){



        return view('Index/gdMap');

    }

    public function wxMap(){
        // 生成微信参数
        $shareurl = request()->url(true);
        $WechatService = new WechatService;
        $jsApi = $WechatService->jsapi($shareurl); 
        $this->assign('jsApi',$jsApi);   
        return view('Index/wxMap');
    }
    

    public function exerciseSort(){
        header("Content-Type:text/html;charset=utf-8");
        $str = file_get_contents(ROOT_PATH.'/data/exercise.txt');
        dump($str);
        $arrr = ['a'=>['a'=>2,'b'=>2],'b'=>['a'=>2,'b'=>3]];
        $strr = json_encode($arrr);


        dump(json_decode($strr,true));
        $arr = json_decode($str,true);
        dump($arr);
    }


    public function getStudentAddress(){
        $students = db('lesson_member')->field('lesson_member.student_id,lesson_member.lesson_id,lesson.area')->join('lesson','lesson.id=lesson_member.lesson_id')->distinct('student_id')->order('lesson_member.id')->select();


        foreach ($students as $key => $value) {

            $update = ['student_province'=>'广东省','student_city'=>'深圳市','student_area'=>$value['area']];
            db('student')->where(['id'=>$value['student_id']])->update($update);
        }
    }

    public function getPost(){
        $model = new \app\model\Exercise;
        $postData = input('post.');
        $arr = [];
        foreach ($postData as $key => $value) {
            // $save = ['exercise_setion'=>$value['exercise_setion'],'exercise'=>$value['exercise_setion'],'camp_id'=>0,'pid'=>0,'id'=>$key+1,'create_time'=>time(),'member'=>'平台'];
            // db('exercise')->insert($save);
            foreach ($value['sub'] as $k => $v) {
                $save = ['exercise_setion'=>$value['exercise_setion'],'exercise'=>$v['exercise'],'camp_id'=>0,'pid'=>$v['pid'],'create_time'=>time(),'member'=>'平台','exercise_detail'=>$v['exercise_detail']];
                db('exercise')->insert($save);
            }
        }
       
    }

    public function xmltest(){
       $xml = '<xml><appid><![CDATA[wx19f60be0f2f24c31]]></appid>
                <bank_type><![CDATA[CFT]]></bank_type>
                <cash_fee><![CDATA[120000]]></cash_fee>
                <fee_type><![CDATA[CNY]]></fee_type>
                <is_subscribe><![CDATA[N]]></is_subscribe>
                <mch_id><![CDATA[1488926612]]></mch_id>
                <nonce_str><![CDATA[alw7b10lcwiorixu0deh3ul4ooycn6rb]]></nonce_str>
                <openid><![CDATA[o83291NM7kHtVyTmKG-ao5-Pxwzo]]></openid>
                <out_trade_no><![CDATA[1201711031610202726]]></out_trade_no>
                <result_code><![CDATA[SUCCESS]]></result_code>
                <return_code><![CDATA[SUCCESS]]></return_code>
                <sign><![CDATA[430A51B37742ABF6D5F6CBECF84E09C5]]></sign>
                <time_end><![CDATA[20171103160702]]></time_end>
                <total_fee>120000</total_fee>
                <trade_type><![CDATA[JSAPI]]></trade_type>
                <transaction_id><![CDATA[4200000027201711032186864528]]></transaction_id>
                </xml>';
                $obj=simplexml_load_string($xml,'SimpleXMLElement',LIBXML_NOCDATA);
                $jsonObj = json_encode($obj);
                $data = json_decode($jsonObj,true);
        dump($data);die; 
    }
    public function grade(){
        $action = input('param.action');
        if($action!= 'woo'){

            return '???';die;
        }
        // $GradeService = new \app\service\GradeService;
        // $result = $GradeService->getGradeListByPage([]);
        $result = db('grade')->select();
        dump($result);die;
        foreach($result as $key =>&$value){
            if($value['student_str'] == 0){
                $value['student_str'] = '';
            }
            if($value['status'] == 0){
                $value['status'] = 1;
            }
        }
        $GradeModel = new \app\model\Grade;
        $GradeModel->saveAll($result); 
    }
        
    public function getGradeStudentStr(){
        $action = input('param.action');
        if($action!= 'woo'){

            return '???';die;
        }
        $GradeService = new \app\service\GradeService;
        $result = $GradeService->getGradeListByPage([]);
        // $list = $result->toArray();
        $list = [];
        dump($result);die;
        foreach ($result['data'] as $key => &$value) {
            // dump($value['grade_member']);
            $value['grade_member'] = $value['grade_member']->toArray();
        }
        $list = $result['data'];
        foreach ($list as $key => &$value) {
            if($value['student_str'] == 0){
                $value['student_str'] ='';
            }
            foreach ($value['grade_member'] as $ky => $val) {
                // dump($val);
                $value['student_str'] .= $val['student'].',';
            }
            $value['student_str'] = substr($value['student_str'],0,strlen($value['student_str'])-1);
            unset($value['grade_member']);
            unset($value['status']);
        }
        dump($list);
        $GradeModel = new \app\model\Grade;
        $GradeModel->saveAll($list);
    }

    public function wxbind() {
        $WeixinService = new Weixin();
        $WeixinService->mpbind();
    }
        
    




    
    public function sendMsg(){
        $action = input('param.action');
        if($action!= 'woo'){

            return '???';die;
        }
        
        $MessageService = new \app\service\MessageService;
        $MessageCampData = [
                        "touser" => '',
                        "template_id" => config('wxTemplateID.successBill'),
                        "url" => url('frontend/bill/billInfoOfCamp',['bill_order'=>'1201712190929205361'],'',true),
                        "topcolor"=>"#FF0000",
                        "data" => [
                            'first' => ['value' => '强亦宸购买课程订单支付成功补发通知'],
                            'keyword1' => ['value' => '强亦宸'],
                            'keyword2' => ['value' => '1201712190929205361'],
                            'keyword3' => ['value' => '1500元'],
                            'keyword4' => ['value' => '强亦宸购买课程'],
                            'remark' => ['value' => '大热篮球']
                        ]
                    ];
        $MessageCampSaveData = [
                                'title'=>"购买课程-大热常规班",
                                'content'=>"订单号: 1201712190929205361<br/>支付金额: 1200元<br/>购买学生:强亦宸<br/>购买理由: sys",
                                'member_id'=>244,
                                'url'=>url('frontend/bill/billInfoOfCamp',['bill_order'=>'1201712190929205361'],'',true)
                            ];

        // 发送个人消息
        $MessageData = [
            "touser" => '',
            "template_id" => config('wxTemplateID.successBill'),
            "url" => url('frontend/bill/billInfo',['bill_order'=>'1201712190929205361'],'',true),
            "topcolor"=>"#FF0000",
            "data" => [
                'first' => ['value' => '订单支付成功通知'],
                'keyword1' => ['value' => '强亦宸'],
                'keyword2' => ['value' => '1201712190929205361'],
                'keyword3' => ['value' => '1500元'],
                'keyword4' => ['value' => '强亦宸购买课程'],
                'remark' => ['value' => '大热篮球']
            ]
        ];
        $saveData = [
                        'title'=>"订单支付成功-大热常规班",
                        'content'=>"订单号: 1201712190929205361<br/>支付金额: 1200元<br/>支付学生信息:强亦宸",
                        'url'=>url('frontend/bill/billInfo',['bill_order'=>'1201712190929205361']),
                        'member_id'=>244
                    ];
        $MessageService->sendMessageMember(244,$MessageData,$saveData);            
        $MessageService->sendCampMessage(9,$MessageCampData,$MessageCampSaveData);
    }



    public function insertLessonMember(){
        $action = input('param.action');
        if($action!= 'woo'){

            return '???';die;
        }
        $grade_member = db('grade_member')->select();
        foreach ($grade_member as $key => &$value) {
            unset($value['id']);
        }

        // dump($grade_member);die;
        $LessonMmeber = new \app\model\LessonMember;
        $LessonMmeber->saveAll($grade_member);

    }

    public function repairCourt(){
        $result = db('court_camp')
                ->field("camp_id,count('court_id') camp_base,camp")
                ->where('delete_time','null')
                ->group('camp_id')
                ->select();
                echo db('court_camp')->getlastsql();
        dump($result);
        foreach ($result as $key => $value) {
           db('camp')->where(['id'=>$value['camp_id']])->update(['camp_base'=>$value['camp_base']]);
        }
    }

    public function repairBill(){
        $result = db('bill')
                ->where(['balance_pay'=>0])
                ->update(['expire'=>time()+86400]);
        
    }

    public function repairStudentStotalschedule(){
        // $studentList = db('lesson_member')->field('sum(`rest_schedule`) total_schedule,student_id id')->group('student_id')->select();

        $studentList = db('bill')->field('sum(`total`) total_schedule,student_id id')->where(['is_pay'=>1,'status'=>1,'expire'=>0,'goods_type'=>1])->group('student_id')->select();
        dump(db('bill')->getlastsql()) ; 
        // dump($studentList);die;
        $StudentModel = new \app\model\Student;
        $StudentModel->saveAll($studentList);
        echo $StudentModel->getlastsql();
    }


    public function repairStudentStotalslesson(){
        // $studentList = db('lesson_member')->field('sum(`rest_schedule`) total_schedule,student_id id')->group('student_id')->select();

        $studentList = db('lesson_member')->field('count(`lesson_id`) total_lesson,student_id id')->group('student_id')->select();
        dump(db('bill')->getlastsql()) ;
        // dump($studentList);die;
        $StudentModel = new \app\model\Student;
        $StudentModel->saveAll($studentList);
        echo $StudentModel->getlastsql();
    }


    public function sortGC(){
        
        $arr = db('grade_category')->order('sort asc')->select();
        $arr = $this->getGradeCategoryTree($arr);
        $this->assign('arr',$arr);
        return view('Index/sortGC');
        // dump($arr);
    }



    protected function getGradeCategoryTree($arr = [],$pid = 0){
        $list = [];
         foreach ($arr as $key => $value) {
            if($value['pid'] == $pid){
                $value['daughter'] = $this->getGradeCategoryTree($arr,$value['id']);
               $list[] = $value;
            }
        }
        return $list;
    }

    public function sortLesson(){
        $list = Db::view('lesson','*')
                ->view('grade_category','pid','lesson.gradecate_id=grade_category.id')
                ->select();
        
        foreach ($list as $key => $value) {
            $name = db('grade_category')->where(['id'=>$value['pid']])->value('name');    
            $list[$key]['gradecate_setion'] = $name;
            $list[$key]['gradecate_setion_id'] = $value['pid'];

            db('lesson')->where(['id'=>$value['id']])->update(['gradecate_setion_id'=>$value['pid'],'gradecate_setion'=>$name]);
        }     
       
    }

    public function sortGrade(){
        $list = Db::view('grade','*')
                ->view('lesson','gradecate gradecates, gradecate_id gradecate_ids, gradecate_setion gradecate_setions,gradecate_setion_id gradecate_setion_ids','lesson.id=grade.lesson_id')
                ->select();
        foreach ($list as $key => $value) { 
            db('grade')->where(['id'=>$value['id']])->update(['gradecate'=>$value['gradecates'],'gradecate_id'=>$value['gradecate_ids'],'gradecate_setion'=>$value['gradecate_setions'],'gradecate_setion_id'=>$value['gradecate_setion_ids']]);
        }     
       
    }

    public function gradecate(){
        $arr = db('test')->where(['pid'=>0])->select();
        foreach ($arr as $key => $value) {
            $data1 = ['sort'=>99,'pid'=>$value['id'],'name'=>'花式篮球课程'];
            $data2 = ['sort'=>99,'pid'=>$value['id'],'name'=>'专项训练课程'];
            db('test')->insert($data1);
            db('test')->insert($data2);
        }
        
    }

    public function getLocation(){
        $aee = file_get_contents('https://api.map.baidu.com/location/ip?ak=g5XXhTU6gY4Ka68E5ktVMrGz2uiosuTE&coor=bd09ll');
        dump($aee);die;
    }

    public function getAdmin(){
        $ids = db('admin_menu')->column('id');
        dump(json_encode($ids));
        db('admin_group')->where(['pid'=>1])->update(['menu_auth'=>json_encode($ids)]);
    }
}