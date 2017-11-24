<?php 
namespace app\service;
use app\model\Apply;
use think\Db;
class ApplyService{
	protected $ApplyModel;
	public function __construct(){
		$this->ApplyModel = new Apply;
	}


	public function getApplyList($map,$page = 1,$paginate = 10){
		$result = Apply::where($map)->page($page,$paginate)->select();
		 if($result){
            return $result->toArray();
        }
        return $result;
    }

    public function getApplyListByPage($map,$paginate = 10){
        $result = Apply::with('member')->where($map)->paginate($paginate);
        if($result){
            return $result->toArray();
        }
        return $result;
    }


    // 统计用户数量
    public function countMembers($map){
    	$result = $this->ApplyModel->where($map)->count();
    	return $result?$result:0;
    }
    
    
    
}