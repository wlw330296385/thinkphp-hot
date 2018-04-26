<?php
namespace app\admin\controller;
use app\admin\controller\base\Backend;
use app\model\Student as StudentModel;
use think\Db;

class Student extends Backend {
    public function _initialize(){
        parent::_initialize();
    }
    // 学员列表
    public function index() {
        // 搜索筛选
        $map = [];
        $camp_id = input('camp_id');
        if ($camp_id) {
            $map['grade_member.camp_id']=$camp_id;
        }
        $camp = input('camp');
        if ($camp) {
            $map['grade_member.camp'] = ['like', '%'. $camp .'%'];
        }
        $name = input('name');
        if ($name) {
            $map['student.student'] = ['like', '%'. $name .'%'];
        }
        $tel = input('tel');
        if ($tel) {
            $map['member.telephone'] = $tel;
        }
        // 视图查询 grade_member - student
        $studentList = Db::view('student','student,member_id,id')
            ->view('member', 'member,hot_id,telephone', 'member.id=student.member_id', 'left')
            ->view('grade_member', 'camp,camp_id,grade,grade_id,status', 'grade_member.student_id=student.id', 'LEFT')
            ->where($map)
            ->where('grade_member.delete_time', null)
            ->order('student.member_id desc')
            ->paginate(15, false, ['query' => request()->param()]);
//        dump($list->toArray());die;
        $this->assign('studentList', $studentList);
        $this->assign('camp_id', $camp_id);
        return $this->fetch();
    }

    // 学员档案
    public function show() {
        $id = input('id');
        $studentInfo = StudentModel::with('member')->where(['id' => $id])->find()->toArray();
        $studentInfo['_incamp'] = Db::view('student', 'id, student,member_id')
            ->view('grade_member', 'grade,camp,type,status', 'grade_member.student_id=student.id')
            ->where(['student_id' => $studentInfo['id']])->select();
        $this->assign('studentInfo', $studentInfo);    
        return view();
    }


    // 创建|修改学生
    public function updateStudent(){
        $student_id = input('param.student_id');
        $StudentService = new \app\service\StudentService;
        $member_id = input('param.member_id');
        $memberInfo = db('member')->where(['id'=>$member_id])->find();
            
            
        if($student_id){
            if(request()->isPost()){
                $data = input('post.');
                $result = $StudentService->updateStudent($data,$student_id);
                if($result){
                    echo  "<script>alert('".$result['msg']."');</script>";
                }
            }
            // 编辑学生
            $memberInfo = db('member')->where(['id'=>$member_id])->find();
            $this->assign('memberInfo',$memberInfo);
            return view('student/updateStudent');
        }else{
            if(request()->isPost()){
                $data = input('post.');
                $data['member'] = $memberInfo['member'];
                $result = $StudentService->createStudent($data);
                if($result){
                    echo  "<script>alert('".$result['msg']."');</script>";
                }
            }
            // 创建学生
            $memberInfo = db('member')->where(['id'=>$member_id])->find();
            $this->assign('memberInfo',$memberInfo);
            return view('student/createStudent');
        }
        
    }
}