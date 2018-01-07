<?php 
namespace app\frontend\controller;
use app\frontend\controller\Base;
use app\service\ItemCouponService;
class ItemCoupon extends Base{
	protected $ItemCouponService;
	public function _initialize(){
		parent::_initialize();
		$this->ItemCouponService = new ItemCouponService;
	}

    public function index() {

        return view('Coupon/index');
    }


    public function itemCouponInfo(){
    	$itemCoupon_id = input('param.item_coupon_id');
        $itemCouponInfo = $this->ItemCouponService->getItemCouponInfo(['id'=>$itemCoupon_id]);
        $itemCouponMemberInfo = $this->ItemCouponService->getItemCouponMemberInfo(['item_coupon_id'=>$itemCoupon_id,'member_id'=>$this->memberInfo['id']]);
        // dump($itemCouponMemberInfo);die;
        $this->assign('itemCouponMemberInfo',$itemCouponMemberInfo);
        $this->assign('itemCouponInfo',$itemCouponInfo);
    	return view('Coupon/itemCouponInfo');
    }

    public function itemCouponList(){
        $member_id = input('param.member_id')?input('param.member_id'):$this->memberInfo['id'];
        $itemCouponList = $this->ItemCouponService->getItemCouponMemberListByPage(['member_id'=>$member_id]);
        // dump($itemCouponList->toArray());die;
        $this->assign('itemCouponList',$itemCouponList);
        return view('Coupon/itemCouponList');
    }

    public function updateItemCoupon(){   	
    	$itemCoupon_id = input('param.itemCoupon_id');
        $ItemCouponInfo = $this->ItemCouponService->getItemCouponInfo(['id'=>$itemCoupon_id]);
        $CampService = new \app\service\CampService;
        $power = $CampService->isPower($ItemCouponInfo['camp_id'],$this->memberInfo['id']);
        if($power<2){
            $this->error('请先加入一个训练营并成为管理员或者创建训练营');
        }
		$this->assign('ItemCouponInfo',$ItemCouponInfo);
    	return view('Coupon/updateItemCoupon');
    }

    public function createItemCoupon(){
        $camp_id = input('param.camp_id');
        $CampService = new \app\service\CampService;
        $power = $CampService->isPower($camp_id,$this->memberInfo['id']);

        if($power<2){
            $this->error('请先加入一个训练营并成为管理员或者创建训练营');
        }

        $campInfo = $CampService->getCampInfo(['id'=>$camp_id]);
        $this->assign('campInfo',$campInfo);
        return view('Coupon/createItemCoupon');
    }
    // 分页获取数据
    public function itemCouponListApi(){
    	$camp_id = input('param.camp_id');
        $condition = input('post.');
        $where = ['status'=>['or',[1,$camp_id]]];
        $map = array_merge($condition,$where);
    	$itemCouponList = $this->ItemCouponService->getItemCouponPage($map,10);
    	return json($result);
    }

    public function searchItemCouponList(){
        $camp_id = input('param.camp_id');
        $this->assign('camp_id',$camp_id);
        return view('Coupon/searchItemCouponList');
    }
}