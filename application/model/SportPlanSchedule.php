<?php 
namespace app\model;
use think\Model;
use traits\model\SoftDelete;
class SportPlanSchedule extends Model {
    use SoftDelete;
    protected $deleteTime = 'delete_time';
	protected $autoWriteTimestamp = true;
    // protected $readonly = [
    //                         'create_time',
    //                         'status',
    //                         'type',
    //                         ];

	public function getSportTimeAttr($timeStr){
        return date('Y-m-d', $timeStr);
    }

}