<?php 
namespace app\api\controller;
use app\api\controller\Base;
use app\service\CoachService;
use app\service\CertService;
use app\service\GradeMemberService;
use app\service\ScheduleService;
class Coach extends Frontend{
	protected $coachService;
	public function _initialize(){
		parent::_initialize();
		$this->coachService = new CoachService;
		$this->gradeMemberService = new GradeMemberService;
        $this->scheduleService = new ScheduleService;

	}

    // 搜索教练
    public function searchCoachListApi(){
        try{
            $map = [];
            $keyword = input('param.keyword');
            $province = input('param.province');
            $page = input('param.page')?input('param.page'):1;
            $city = input('param.city');
            $area = input('param.area');
            $sex = input('param.sex');
            $map = ['province'=>$province,'city'=>$city,'area'=>$area];
            foreach ($map as $key => $value) {
                if($value == ''|| empty($value) || $value!=' '){
                    unset($map[$key]);
                }
            }
            if(!empty($sex)&&$sex!=''){
                $map['sex'] = $sex;
            }
            if(!empty($keyword)&&$keyword != ' '&&$keyword != ''){
                $map['coach'] = ['LIKE','%'.$keyword.'%'];
            }
            $coachList = $this->coachService->getCoachList($map,$page);
            if($coachList){
                return json(['code'=>100,'msg'=>'OK','data'=>$coachList]);
            }else{
                return json(['code'=>100,'msg'=>'OK','data'=>'']);
            }
        }catch (Exception $e){
            return json(['code'=>100,'msg'=>$e->getMessage()]);
        }
    }



    public function createCoachApi(){
        try{
            $data['coach'] = input('post.coach');
            $data['member_id'] = $this->memberInfo['id'];
            return $this->coachService->createCoach($data);
        }catch (Exception $e){
            return json(['code'=>100,'msg'=>$e->getMessage()]);
        }
    }

    public function updateCoachApi(){
        try{
            $coach_id = input('post.coachid') ? input('post.coachid') : input('param.coach_id');
            if(!$coach_id){
                return ['code'=>200,'msg'=>'找不到教练信息'];
            }

            $member_id = input('post.member_id');
            // 教练数据
            $coachdata = [
                'coach' => input('post.coach'),
                'member_id' => $member_id,
                'coach_year' => input('post.coach_year'),
                'experience' => input('post.experience'),
                'introduction' => input('post.introduction'),
                'portraits' => input('post.portraits'),
                'description' => input('post.description')
            ];
            $coachS = new CoachService();
            $coach = $coachS->updateCoach($coachdata, $coach_id);

            // 实名数据
            $realnamedata = [
                'member_id' => $member_id,
                'cert_no' => input('post.idno'),
                'cert_type' => 1,
                'photo_positive' => input('post.photo_positive'),
                'photo_back' => input('post.photo_back'),
            ];
            // 资质证书
            $certdata = [
                'member_id' => $member_id,
                'cert_no' => "",
                'cert_type' => 3,
                'photo_positive' => input('post.cert')
            ];

            $certS = new CertService();
            $cert1 = $certS->saveCert($realnamedata);
            $cert2 = $certS->saveCert($certdata);
            if ($cert1['code'] == 100 || $cert2['code'] == 100) {
                return json([ 'msg' => '证件信息保存出错,请重试', 'code' => 100]);
            }

            return json($coach);
        }catch (Exception $e){
            return json(['code'=>100,'msg'=>$e->getMessage()]);
        }
    }

    public function getCoachListApi(){
        try{
            $map = input('post.');
            $page = input('param.page')?input('param.page'):1;
            $coachList = $this->coachService->getCoachList($map,$page);
            return json(['code'=>100,'msg'=>'OK','data'=>$coachList]);
        }catch (Exception $e){
            return json(['code'=>100,'msg'=>$e->getMessage()]);
        }
    }


    // 获取教练下的训练营
    public function campListOfCaochApi(){
        try{
            $member_id = input('param.member_id')? input('param.member_id'):$this->memberInfo['id'];
            $campList = Db::view('grade_member','camp_id')
                    ->view('camp','camp,act_member,finished_lessons,star,province,city,area,logo,id,total_member,total_lessons','camp.id=grade_member.camp_id')
                    ->where(['grade_member.member_id'=>$member_id,'grade_member.type'=>4,'grade_member.status'=>1])
                    ->select();
            return json(['code'=>100,'msg'=>'OK','data'=>$campList]);        
        }catch (Exception $e){
            return json(['code'=>100,'msg'=>$e->getMessage()]);
        }
    }
}