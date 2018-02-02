<?php

namespace app\service;

use app\model\Article;
use think\Db;
use app\common\validate\ArticleVal;
class ArticleService {
    private $ArticleModel;
    public function __construct(){
        $this->ArticleModel = new Article;
    }


    // 获取所有银行卡
    public function getArticleList($map=[],$page = 1,$order='',$paginate = 10) {
        $result = Article::where($map)->order($order)->page($page,$paginate)->select();

        return $result;
    }

    // 分页获取银行卡
    public function getArticleListByPage($map=[], $order='',$paginate=10){
        $result = Article::where($map)->order($order)->paginate($paginate);
        
        return $result;
    }

    // 软删除
    public function SoftDeleteArticle($id) {
        $result = Article::destroy($id);
        if (!$result) {
            return [ 'msg' => __lang('MSG_400'), 'code' => 100 ];
        } else {
            return ['msg' => __lang('MSG_200'), 'code' => 200, 'data' => $result];
        }
    }

    // 获取一个银行卡
    public function getArticleInfo($map) {
        $result = Article::where($map)->find();
        return $result;
    }




    // 编辑银行卡
    public function updateArticle($data,$map){
        
        $validate = validate('ArticleVal');
        if(!$validate->scene('edit')->check($data)){
            return ['msg' => $validate->getError(), 'code' => 100];
        }
        
        $result = $this->ArticleModel->allowField(true)->save($data,$map);
        if($result){
            return ['msg' => '操作成功', 'code' => 200, 'data' => $map];
        }else{
            return ['msg'=>'操作失败', 'code' => 100];
        }
    }

    // 新增银行卡
    public function createArticle($data){
        
        
        $validate = validate('ArticleVal');
        if(!$validate->scene('add')->check($data)){
            return ['msg' => $validate->getError(), 'code' => 100];
        }
        $result = $this->ArticleModel->allowField(true)->save($data);
        if($result){
            return ['msg' => '操作成功', 'code' => 200, 'data' => $this->ArticleModel->id];
        }else{
            return ['msg'=>'操作失败', 'code' => 100];
        }
    }





}
