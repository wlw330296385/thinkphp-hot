<?php 
namespace app\management\controller;
use app\management\controller\Base;

/**
* 基本层
*/
class Backend extends Base
{
	public $power;
	function _initialize()
	{
		parent::_initialize();
		$power = cache("power_{$this->memberInfo['id']}");
		if($power){
			$this->power = $power;
		}else{
			$this->error('权限不足','Login/login');
		}

		
	}
}