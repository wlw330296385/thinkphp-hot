<?php 
namespace app\index\controller;
use think\Controller;
/**
* 
*/
class Index extends Controller
{
	
	function __construct()
	{
		
	}

    public function index(){
        echo "<p><a href='/frontend/index'><h1>欢迎来到篮球管家</h1></p>";
    }

    public function index1(){
    	$a = "100.00";
        $b = (float)$a;
        dump($a);
        dump($b);
    }

    public function index2(){
        
        $list = db('salary_in')->field('salary_in.id,schedule.lesson_time')->join('schedule','schedule.id = salary_in.schedule_id')->select();
        foreach ($list as $key => $value) {
            db('salary_in')->where(['id'=>$value['id']])->update(['schedule_time'=>$value['lesson_time']]);
        }

    }


}