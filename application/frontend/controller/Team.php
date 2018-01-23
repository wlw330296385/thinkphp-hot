<?php
// 球队模块
namespace app\frontend\controller;


use app\service\MatchService;
use app\service\TeamService;

class Team extends Base {
    public $team_id;
    public $teamInfo;
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        // 获取球队详细信息 模块下全局赋值
        $team_id = input('team_id');
        $teamS = new TeamService();
        $teamInfo = $teamS->getTeam(['id' => $team_id]);
        if ($team_id && !$teamInfo) {
            $this->error('没有球队信息');
        }
        $this->team_id = $team_id;
        $this->teamInfo = $teamInfo;
        $this->assign('team_id', $team_id);
        $this->assign('teamInfo', $teamInfo);
    }

    // 球队列表(平台展示)
    public function teamlist() {
        return view('Team/teamList');
    }

    // 创建球队
    public function createteam() {
        return view('Team/createTeam');
    }

    // 球队管理
    public function teammanage() {
        // 获取会员在球队角色身份
        $teamS = new TeamService();
        $teamrole = $teamS->checkMemberTeamRole($this->team_id, $this->memberInfo['id']);
        //dump($teamrole);
        $this->assign('teamrole', $teamrole);
        return view('Team/teamManage');
    }

    // 编辑球队
    public function teamedit() {
        // 获取球队有角色身份的会员列表
        $teamS = new TeamService();
        $rolemembers = $teamS->getTeamRoleMembers($this->team_id, 'team_member.member_id asc');
        // 教练、队委名单集合组合
        $roleslist = [
            'coach_ids' => '',
            'committee_ids' => '',
            'coach_names' => [],
            'committee_names' => []
        ];
        foreach ($rolemembers as $rolemember) {
            if ($rolemember['type'] == 2) {
                $roleslist['coach_ids'] .= $rolemember['member_id'].',';
                array_push($roleslist['coach_names'], [
                    'id' => $rolemember['id'],
                    'member_id' => $rolemember['member_id'],
                    'member' => $rolemember['member']
                ]);
            }
            if ($rolemember['type'] == 1 ) {
                $roleslist['committee_ids'] .= $rolemember['member_id'].',';
                array_push($roleslist['committee_names'], [
                    'id' => $rolemember['id'],
                    'member_id' => $rolemember['member_id'],
                    'member' => $rolemember['member']
                ]);
            }
        }
        // 去掉结尾最后一个逗号
        $roleslist['coach_ids'] = rtrim($roleslist['coach_ids'], ',');
        $roleslist['committee_ids'] = rtrim($roleslist['committee_ids'], ',');

        $this->assign('rolemembers', $rolemembers);
        $this->assign('roleslist', $roleslist);
        return view('Team/teamEdit');
    }

    // 球队首页
    public function teaminfo() {
        // 变量标识$isMemberInTeam：判断当前会员有无在球队正式成员
        $teamS = new TeamService();
        $teamMemberInfo = $teamS->getTeamMemberInfo([
            'team_id' => $this->team_id,
            'member_id' => $this->memberInfo['id'],
            'status' => 1
        ]);
        $isMemberInTeam = ($teamMemberInfo) ? 1 : 0;
        
        $this->assign('isMemberInTeam', $isMemberInTeam);
        return view('Team/teamInfo');
    }

    // 我的球队列表（会员所在球队列表）
    public function myteam() {
        $teamS = new TeamService();
        $myTeamList = $teamS->myTeamWithRole($this->memberInfo['id']);
        $this->assign('myTeamList', $myTeamList);
        return view('Team/myteam');
    }

    // 队员列表
    public function teammember() {
        return view('Team/teamMember');
    }

    // 队员档案
    public function teammemberinfo() {
        // 接收参数
        $team_id = input('team_id', 0);
        $member_id = input('member_id', 0);
        $teamS = new TeamService();
        // 获取队员在当前球队的数据信息
        $map = ['team_id' => $team_id, 'member_id' => $member_id];
        $teamMemberInfo = $teamS->getTeamMemberInfo($map);
        if (!$teamMemberInfo) {
            $this->error('无此队员信息');
        }

        // 该队员的其他球队列表
        $memberOtherTeamMap = [ 'member_id' => $member_id, 'team_id' => ['neq', $team_id]];
        $memberOtherTeam = $teamS->getTeamMemberList($memberOtherTeamMap);

        // 领队可移除除自己外的球队成员，成员自己申请退队 按钮显示
        $delbtnDisplay = 0;
        if ($this->memberInfo['id'] == $this->teamInfo['leader_id']) {
            if ($teamMemberInfo['member_id'] == $this->teamInfo['leader_id']) {
                $delbtnDisplay = 0;
            } else {
                $delbtnDisplay = 2;
            }
        } else {
            if ($this->memberInfo['id'] == $teamMemberInfo['member_id']) {
                $delbtnDisplay = 1;
            }
        }

        // 编辑成员资料入口显示：队委以上成员可编辑所有成员、成员自己操作自己
        $editbtnDisplay = 0;
        $teamRole = $teamS->checkMemberTeamRole($team_id, $this->memberInfo['id']);
        if ($teamRole) {
            $editbtnDisplay = 1;
        } else if ($this->memberInfo['id'] == $teamMemberInfo['member_id']) {
            $editbtnDisplay = 1;
        }

        $this->assign('teamMemberInfo', $teamMemberInfo);
        $this->assign('memberOtherTeam', $memberOtherTeam);
        $this->assign('delbtnDisplay', $delbtnDisplay);
        $this->assign('editbtnDisplay', $editbtnDisplay);
        return view('Team/teamMemberInfo');
    }

    // 队员编辑
    public function teammemberedit() {
        // 接收参数
        $team_id = input('team_id', 0);
        $member_id = input('member_id', 0);
        $teamS = new TeamService();
        // 获取队员在当前球队的数据信息
        $map = ['team_id' => $team_id, 'member_id' => $member_id];
        $teamMemberInfo = $teamS->getTeamMemberInfo($map);
        // 可访问页面人员判断：队员自己、球队队委及以上角色成员
        $teamrole = $teamS->checkMemberTeamRole($this->team_id, $this->memberInfo['id']);
        if (!$teamrole && ($teamMemberInfo['member_id'] != $this->memberInfo['id'])) {
            $this->error('您只能编辑自己的球队成员信息');
        }

        $this->assign('teamMemberInfo', $teamMemberInfo);
        return view('Team/teamMemberEdit');
    }

    // 申请加入列表
    public function teamapplylist() {
        return view('Team/teamApplyList');
    }

    // 申请加入详情
    public function teamapplyinfo() {
        $applyId = input('id');
        $teamS = new TeamService();
        $apply = $teamS->getApplyInfo(['id' => $applyId, 'organization_id' => $this->team_id]);

        $this->assign('applyInfo', $apply);
        return view('Team/teamApplyInfo');
    }

    // 粉丝列表
    public function fans() {
        return view('Team/fans');
    }

    // 消息列表
    public function messagelist() {
        return view('Team/messagelist');
    }

    // 消息详情
    public function messageinfo() {
        return view('Team/messageInfo');
    }

    // 发布球队消息（公告）
    public function createmessage() {
        return view('Team/createMessage');
    }

    // 相册列表
    public function album() {
        return view('Team/album');
    }

    // 添加活动
    public function createevent() {
        return view('Team/createEvent');
    }

    // 编辑活动&活动录入
    public function eventedit() {
        $event_id = input('event_id', 0);
        // $directentry 1为新增活动并录入活动
        $directentry = 0;
        // 如果有event_id参数即修改活动，没有就新增活动并录入活动（事后录活动）
        if ($event_id === 0) {
            $eventInfo = [
                'id' => 0,
                'send_message' => 0
            ];
            $directentry = 1;
            $memberlist = [];
        } else {
            $teamS = new TeamService();
            $eventInfo = $teamS->getTeamEventInfo(['id' => $event_id]);
            $memberlist = $teamS->teamEventMembers(['event_id' => $event_id]);
            if (!empty($eventInfo['album'])) {
                $eventInfo['album'] = json_decode($eventInfo['album'], true);
            }
        }

        $this->assign('event_id', $event_id);
        $this->assign('eventInfo', $eventInfo);
        $this->assign('directentry', $directentry);
        $this->assign('memberList', $memberlist);
        return view('Team/EventEdit');
    }

    // 活动列表管理
    public function eventlistofteam(){
        return view('Team/eventListOfTeam');
    }

    // 活动列表
    public function eventlist() {
        return view('Team/eventList');
    }

    // 活动详情
    public function eventinfo() {
        // 活动详情数据
        $event_id = input('param.event_id');
        $teamS = new TeamService();
        $eventInfo = $teamS->getTeamEventInfo(['id' => $event_id]);
        if (!empty($eventInfo['album'])) {
            $eventInfo['album'] = json_decode($eventInfo['album'], true);
        }
        // 获取会员在球队角色身份
        $teamrole = $teamS->checkMemberTeamRole($eventInfo['team_id'], $this->memberInfo['id']);
        $memberlist = $teamS->teamEventMembers(['event_id' => $event_id]);

        $this->assign('teamrole', $teamrole);
        $this->assign('eventInfo', $eventInfo);
        $this->assign('memberList', $memberlist);
        return view('Team/eventInfo');
    }

    // 活动报名人员名单
    public function eventsignuplist() {
        // 活动详情数据
        $event_id = input('param.event_id');
        $teamS = new TeamService();
        $eventInfo = $teamS->getTeamEventInfo(['id' => $event_id]);
        $this->assign('event_id', $event_id);
        $this->assign('eventInfo', $eventInfo);
        return view('Team/eventSignupList');
    }

    // 赛事列表（平台展示）
    public function matchlist() {
        return view('Team/matchList');
    }

    // 赛事详情
    public function matchinfo() {
        $match_id = input('match_id', 0);
        $matchS = new MatchService();
        $teamS = new TeamService();
        // 比赛详情
        $matchInfo = $matchS->getMatch(['id' => $match_id]);
        if ($matchInfo['type_num'] == 1) {
            // 友谊赛 输出比赛战绩数据
            $matchRecordInfo = $matchS->getMatchRecord(['match_id' => $matchInfo['id']]);
            if ($matchRecordInfo) {
                if (!empty($matchRecordInfo['album'])) {
                    $matchRecordInfo['album'] = json_decode($matchRecordInfo['album'], true);
                }
                $matchInfo['record'] = $matchRecordInfo;
            }
        }
        // 进入编辑录入入口判断 获取会员在球队角色身份
        $teamrole = $teamS->checkMemberTeamRole($matchInfo['team_id'], $this->memberInfo['id']);

        $this->assign('teamrole', $teamrole);
        $this->assign('matchInfo', $matchInfo);
        return view('Team/matchInfo');
    }

    // 创建比赛
    public function creatematch() {
        return view('Team/createMatch');
    }

    // 编辑比赛
    public function matchedit() {
        $match_id = input('match_id', 0);
        $matchS = new MatchService();

        // $directentry 1为新增并录入比赛
        $directentry = 0;
        // 如果有event_id参数即修改活动，没有就新增活动并录入活动（事后录活动）
        if ($match_id === 0) {
            $matchInfo = [
                'id' => 0,
                'is_finished_num' => 0,
                'is_finished' => '未完成',
                'match_time' => 0
            ];
            $directentry = 1;
            $memberlist = [];
        } else {
            $matchInfo = $matchS->getMatch(['id' => $match_id]);
            if ($matchInfo['type_num'] == 1) {
                $matchRecordInfo = $matchS->getMatchRecord(['match_id' => $matchInfo['id']]);
                if ($matchRecordInfo) {
                    if (!empty($matchRecordInfo['album'])) {
                        $matchRecordInfo['album'] = json_decode($matchRecordInfo['album'], true);
                    }
                    $matchInfo['record'] = $matchRecordInfo;
                }
            }

            $memberlist = [];
        }
        $this->assign('match_id', $match_id);
        $this->assign('matchInfo', $matchInfo);
        $this->assign('directentry', $directentry);
        $this->assign('memberList', $memberlist);
        return view('Team/matchEdit');
    }

    // 比赛管理列表
    public function matchlistofteam() {
        return view('Team/matchlistofteam');
    }

    // 比赛报名/出席人员名单
    public function matchsignuplist() {
        // 活动详情数据
        $match_id = input('param.match_id');
        $matchS = new MatchService();
        $matchInfo = $matchS->getMatch(['id' => $match_id]);
        $this->assign('match_id', $match_id);
        $this->assign('matchInfo', $matchInfo);
        return view('Team/matchSignupList');
    }
    
    // 申请参加比赛的球队列表
    public function matchapplylist() {
        return view('Team/matchApplyList');
    }

    
    // 申请参加比赛的球队详情
    public function matchapplyinfo() {
        $id = input('apply_id');
        $matchS = new MatchService();
        $teamS = new TeamService();
        // 获取match_apply内容
        $applyInfo = $matchS->getMatchApply(['id' => $id]);
        // 获取申请的球队信息
        $applyTeam = $teamS->getTeam(['id' => $applyInfo['team_id']]);
        $applyInfo['team'] = $applyTeam;

        $this->assign('applyInfo', $applyInfo);
        return view('Team/matchApplyInfo');
    }
}