<?php

namespace app\service;

use app\model\Message;
use app\model\MessageMember;
use app\common\validate\MessageVal;
use think\Db;

class MessageService
{
    private $MessageModel;
    private $MessageMemberModel;

    public function __construct()
    {
        $this->MessageModel = new Message;
        $this->MessageMemberModel = new MessageMember;
    }

    // 获取Message
    public function getMessageInfo($map)
    {
        $result = $this->MessageModel->where($map)->find();
        if ($result) {
            $res = $result->toArray();
            return $res;
        } else {
            return $result;
        }


    }

    //个人消息
    public function getMessageMemberInfo($map)
    {
        $result = $this->MessageMemberModel->where($map)->find();
        if ($result) {
            $res = $result->toArray();
            return $res;
        } else {
            return $result;
        }


    }


    //获取个人消息列表
    public function getMessageMemberList($map = [], $page = 1, $paginate = 10)
    {
        $result = $this->MessageMemberModel
            ->where($map)
            ->page($page, $paginate)
            ->select();
        if ($result) {
            $res = $result->toArray();
            return $res;
        } else {
            return $result;
        }
    }


    // 发送个人消息
    public function sendMessageMember($member_id, $messageData, $saveData)
    {

        $res = $this->MessageMemberModel->save($saveData);
        if ($res) {
            $WechatService = new \app\service\WechatService();
            $result = $WechatService->sendTemplate($messageData);
            if ($result) {
                $logData = ['wxopenid' => $messageData['touser'], 'member_id' => $saveData['member_id'], 'status' => 1, 'content' => serialize($messageData)];
                $this->insertLog($logData);
            } else {
                $logData = ['wxopenid' => $messageData['touser'], 'member_id' => $saveData['member_id'], 'status' => 0, 'content' => serialize($messageData)];
                $this->insertLog($logData);
            }
            return true;
        }
        return false;
    }

    // 给训练营的营主|管理员发送消息
    public function sendCampMessage($camp_id, $messageData, $saveData)
    {
        $saveallData = [];
        // 获取训练营的营主openid
        $memberIDs = db('camp_member')->where(['camp_id' => $camp_id, 'status' => 1])->where('type', 'egt', 3)->column('member_id');
        $memberList = db('member')->where('id', 'in', $memberIDs)->select();
        // 发送模板消息
        foreach ($memberList as $key => $value) {
            if ($value['openid']) {
                $messageData['touser'] = $value['openid'];
                $WechatService = new \app\service\WechatService();
                $result = $WechatService->sendTemplate($messageData);
                if ($result) {
                    $logData = ['wxopenid' => $value['openid'], 'member_id' => $value['id'], 'status' => 1, 'content' => serialize($messageData)];
                    $this->insertLog($logData);
                } else {
                    $logData = ['wxopenid' => $value['openid'], 'member_id' => $value['id'], 'status' => 0, 'content' => serialize($messageData)];
                    $this->insertLog($logData);
                }
            }
            $saveallData[$key] = $saveData;
            $saveallData[$key]['member_id'] = $value['id'];
        }
        $res = $this->MessageMemberModel->saveAll($saveallData);
        if ($res) {
            return true;
        }
        return false;
    }

    // 获取系统消息列表
    public function getMessageList($map = [], $page)
    {
//		$result = $this->MessageModel
//				->where($map)
//				->whereOr(['is_system'=>1])
//				->paginate($paginate);
//		if($result){
//			$res = $result->toArray();
//			return $res;
//		}else{
//			return $result;
//		}
        $result = Db::view('message', ['id' => 'messageid', 'title', 'content', 'url', 'create_time'])
            ->view('message_read', ['id', 'member_id', 'isread'], 'message_read.message_id=message.id','LEFT')
            ->where($map)
            //->fetchSql(true)
            ->page($page, 10)
            ->select();
//        dump($result);
        if ($result) {
            foreach ($result as $key => $val) {
                $result[$key]['create_time'] = date('Y-m-d H:i:s', $val['create_time']);
            }
            return $result;
        } else {
            return $result;
        }
    }

    // 获取个人消息列表
    public function getMessageMemberListByPage($map = [], $paginate = 10)
    {
        $result = $this->MessageMemberModel
            ->where($map)
            ->paginate($paginate);
        if ($result) {
            $res = $result->toArray();
            return $res;
        } else {
            return $result;
        }
    }

    //修改系统Message资料
    public function updateMessageInfo($data, $id)
    {
        $result = $this->MessageModel->save($data, ['id' => $id]);

        if ($result === false) {
            return ['msg' => $this->MessageModel->getError(), 'code' => 100];
        } else {
            return ['msg' => __lang('MSG_200'), 'code' => 200, 'data' => $result];
        }
    }

    //新建系统Message
    public function saveMessageInfo($data, $templateData)
    {
        $validate = validate('MessageVal');
        if (!$validate->check($data)) {
            return ['msg' => $validate->getError(), 'code' => 100];
        }
        $result = $this->MessageModel->data($data)->save();
        // 循环发送模板消息

        if ($result === false) {
            return ['msg' => $this->MessageModel->getError(), 'code' => 100];
        } else {
            return ['msg' => __lang('MSG_200'), 'code' => 200, 'data' => $result];
        }
    }


    // 删除消息
    public function removeMessageMember($map)
    {
        $result = $this->MessageMemberModel->delete($map);
        return $result;
    }

    // 消息记录封装
    private function insertLog($data)
    {
        $LogSendtemplatemsg = new \app\model\LogSendtemplatemsg;
        $LogSendtemplatemsg->save($data);
    }

    // 发送消息给管理员/营主-申请加入训练营审核
    public function campJoinAudit($data, $camp_id)
    {
        if (!$camp_id) {
            return ['code' => 100, 'msg' => __lang('MSG_402')];
        }
        $receivers = db('camp_member')->where(['camp_id' => $camp_id, 'status' => 1, 'type' => ['egt', 3]])->select();
        $wechatS = new WechatService();
        foreach ($receivers as $receiver) {
            $memberopenid = getMemberOpenid($receiver['member_id']);
            $sendTemplateData = [
                'touser' => $memberopenid,
                'template_id' => 'aOTMBdZbOKo8fFEKS5HWNaw9Gu-2c8ASTOcXlL6129Q',
                'url' => url($data['baseurl'], ['camp_id' => $camp_id, 'status' => 0, 'openid' => $memberopenid], '', true),
                'data' => [
                    'first' => ['value' => $data['content']],
                    'keyword1' => ['value' => $data['member']],
                    'keyword2' => ['value' => $data['jointime']],
                    'remark' => ['value' => '点击进入操作']
                ]
            ];
            $sendTemplateResult = $wechatS->sendTemplate($sendTemplateData);
            $log_sendTemplateData = [
                'wxopenid' => $sendTemplateData['touser'],
                'member_id' => $receiver['member_id'],
                'url' => $sendTemplateData['url'],
                'content' => serialize($sendTemplateData),
                'create_time' => time()
            ];
            if ($sendTemplateResult) {
                $log_sendTemplateData['status'] = 1;
            } else {
                $log_sendTemplateData['status'] = 0;
            }
            db('log_sendtemplatemsg')->insert($log_sendTemplateData);

            db('message_member')->insert([
                'title' => $data['title'],
                'content' => $data['content'],
                'url' => $sendTemplateData['url'],
                'member_id' => $receiver['member_id'],
                'create_time' => time(),
                'status' => 1
            ]);
        }
    }

    // 发送消息给申请人-申请加入训练营审核结果
    public function campJoinAuditResult($data, $member_id)
    {
        if (!$member_id) {
            return ['code' => 100, 'msg' => __lang('MSG_402')];
        }
        $wechatS = new WechatService();
        $memberopenid = getMemberOpenid($member_id);
        $sendTemplateData = [
            'touser' => $memberopenid,
            'template_id' => 'xohb4WrWcaDosmQWQL27-l-zNgnMc03hpPORPjVjS88',
            'url' => $data['url'],
            'data' => [
                'first' => ['value' => $data['content']],
                'keyword1' => ['value' => $data['checkstr']],
                'keyword2' => ['value' => $data['audittime']],
                'remark' => ['value' => '点击进入操作']
            ]
        ];
        $sendTemplateResult = $wechatS->sendTemplate($sendTemplateData);
        $log_sendTemplateData = [
            'wxopenid' => $sendTemplateData['touser'],
            'member_id' => $member_id,
            'url' => $sendTemplateData['url'],
            'content' => serialize($sendTemplateData),
            'create_time' => time()
        ];
        if ($sendTemplateResult) {
            $log_sendTemplateData['status'] = 1;
        } else {
            $log_sendTemplateData['status'] = 0;
        }
        db('log_sendtemplatemsg')->insert($log_sendTemplateData);

        db('message_member')->insert([
            'title' => $data['title'],
            'content' => $data['content'],
            'url' => $sendTemplateData['url'],
            'member_id' => $member_id,
            'create_time' => time(),
            'status' => 1
        ]);
    }
}