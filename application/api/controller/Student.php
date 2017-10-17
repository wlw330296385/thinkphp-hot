<?php 
namespace app\api\controller;
use app\service\StudentService;
use app\api\controller\Base;

/**
* 学生控制器
*/
class Student extends Base
{
	protected $studentService;
	function _initialize()
	{
		parent::_initialize();
		$this->studentService = new StudentService;
	}

	public function index(){
	}

	public function getStudentListByPageApi(){
		try{
			$map = input('post.');
            $keyword = input('param.keyword');
            $province = input('param.province');
            $city = input('param.city');
            $area = input('param.area');
            $map['province']=$province;
            $map['city']=$city;
            $map['area']=$area;
            foreach ($map as $key => $value) {
                if($value == ''|| empty($value) || $value==' '){
                    unset($map[$key]);
                }
            }
            if(!empty($keyword)&&$keyword != ' '&&$keyword != '' && $keyword!=NULL){
                $map['court'] = ['LIKE','%'.$keyword.'%'];
            }
            if( isset($map['keyword']) ){
                unset($map['keyword']);
            }
            if( isset($map['page']) ){
                unset($map['page']);
            }
			$result = $this->studentService->getStudentListByPage($map);
			if($result){
				return json(['data'=>$result,'code'=>200,'msg'=>'ok']);
			}else{
				return json(['code'=>200,'msg'=>'没数据了']);
			}
		}catch(Exception $e){
			return json(['code'=>100,'msg'=>$e->getMessage()]);
		}
	}


	public function createStudentApi(){
		try{
			$data = input('post.');
			$data['member'] = $this->memberInfo['member'];
			$data['member_id'] = $this->memberInfo['id'];
			$data['emergency_telephone'] = $this->memberInfo['telephone'];
			$result = $this->studentService->createStudent($data);
			return json($result);
		}catch (Exception $e){
			return json(['code'=>100,'msg'=>$e->getMessage()]);
		}
		
	}


	
	public function updateStudentApi(){
		try{
			$data = input('post.');
			$student_id = input('param.student_id');
			$result = $this->studentService->updateStudent($data,$student_id);
			return json($result);
		}catch (Exception $e){
			return json(['code'=>100,'msg'=>$e->getMessage()]);
		}
		
	}

	public function getStudentListApi(){
		try{
			$member_id = input('param.member_id')?input('param.member_id'):$this->memberInfo['id'];
			$result = $this->studentService->getStudentList(['member_id'=>$member_id]);
			if($result){
				return json(['data'=>$result,'code'=>200,'msg'=>'ok']);
			}else{
				return json(['code'=>200,'msg'=>'没数据了']);
			}
		}catch(Exception $e){
			return json(['code'=>100,'msg'=>$e->getMessage()]);
		}
	}	

	//从关系表学生列表
	public function getStudentListByPageApi(){
		try{
			$map = input('post.');
            $keyword = input('param.keyword');
            $province = input('param.province');
            $city = input('param.city');
            $area = input('param.area');
            $map['province']=$province;
            $map['city']=$city;
            $map['area']=$area;
            foreach ($map as $key => $value) {
                if($value == ''|| empty($value) || $value==' '){
                    unset($map[$key]);
                }
            }
            if(!empty($keyword)&&$keyword != ' '&&$keyword != '' && $keyword!=NULL){
                $map['court'] = ['LIKE','%'.$keyword.'%'];
            }
            if( isset($map['keyword']) ){
                unset($map['keyword']);
            }
            if( isset($map['page']) ){
                unset($map['page']);
            }
			$result = $this->studentService->getStudentListByPage($map);
			if($result){
				return json(['data'=>$result,'code'=>200,'msg'=>'ok']);
			}else{
				return json(['code'=>200,'msg'=>'没数据了']);
			}
		}catch(Exception $e){
			return json(['code'=>100,'msg'=>$e->getMessage()]);
		}
	}

}