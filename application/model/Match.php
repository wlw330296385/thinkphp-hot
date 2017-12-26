<?php
// 比赛（赛事）model
namespace app\model;
use think\Model;
use traits\model\SoftDelete;

class Match extends Model {
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    // 软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    // 字段类型转换
    protected $type = [
        'match_time' => 'timestamp:Y-m-d H:i',
        'start_time' => 'timestamp:Y-m-d H:i',
        'end_time' => 'timestamp:Y-m-d H:i',
        'reg_start_time' => 'timestamp:Y-m-d H:i',
        'reg_end_time' => 'timestamp:Y-m-d H:i',
    ];

    // event_type（活动类型）获取器
    public function getTypeAttr($value) {
        $event_type = [ 1 => '友谊赛', 2 => '联赛' ];
        return $event_type[$value];
    }

    // is_finished(是否完成)获取器
    public function getIsFinishedAttr($value) {
        $is_finished = [ 0 => '未开始', 1 => '已结束' ];
        return $is_finished[$value];
    }

    // status 获取器
    public function getStatusAttr($value) {
        $status = [1=> '上架', -1 => '下架'];
        return $status[$value];
    }


}