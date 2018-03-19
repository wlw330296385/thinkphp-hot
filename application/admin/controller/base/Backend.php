<?php
// admin模块 控制器基类
namespace app\admin\controller\base;

use app\model\Camp;
use app\service\SystemService;
use app\admin\controller\base\Base;
use app\service\AuthService;
use think\Cookie;
use app\admin\model\AdminMenu as MenuModel;
class Backend extends Base {
    public $cur_camp;
    public $site;
    public $AuthService;
    public $admin;
    public function _initialize() {
        parent::_initialize();
        // 检查控制台登录
        $this->AuthService = new AuthService();
        if ( !$this->AuthService->islogin() ) {
<<<<<<< HEAD
            $this->error('请登录后操作', url('Login/index'));
=======
            //$this->error('请登录后操作', url('Login/index'));
            $this->redirect('Login/index');
>>>>>>> 12f73e9f54aec3c924def7292bf18f1602adfef4
        }else{
            $this->admin = session('admin');
            $this->assign('admin',$this->admin);
        }
        if(config('develop_mode') == 0){
            //存储权限节点
<<<<<<< HEAD
            $this->AuthService->adminGroup();
            // dump($_SESSION);
            // dump(cache('group_id_menu_auth_5'));die;
=======
            // dump($_SESSION);
            $this->AuthService->adminGroup();
            
            // dump(cache('group_id_menu_auth_1'));
>>>>>>> 12f73e9f54aec3c924def7292bf18f1602adfef4
            //检查权限
            if (!$this->AuthService->checkAuth()) $this->error('权限不足！');
            // 获取面包屑导航
            $_location =  MenuModel::getLocation('', true);
            $this->assign('_location',$_location);
<<<<<<< HEAD
=======
            // dump($_location);

>>>>>>> 12f73e9f54aec3c924def7292bf18f1602adfef4
        }else{
            $this->assign('_location',[0=>['title'=>'开发者模式'],1=>['title'=>'不验证权限']]);
        }
        // 获取侧边栏菜单
        $sidebar_menu = MenuModel::getSidebarMenu();
        // dump($sidebar_menu);die;
        $this->assign('_sidebar_menus', $sidebar_menu);
        // dump($sidebar_menu);die;
        
    }

}