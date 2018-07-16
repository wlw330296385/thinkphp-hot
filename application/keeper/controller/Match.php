<?php
// 比赛 赛事
namespace app\keeper\controller;

use app\model\MatchRefereeApply;
use app\service\ArticleService;
use app\service\CertService;
use app\service\LeagueService;
use app\service\MatchService;
use app\service\MemberService;
use app\service\TeamService;
use app\service\RefereeService;
use think\Exception;

class Match extends Base {
    protected $league_id;
    protected $leagueInfo;
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $league_id = input('league_id', 0, 'intval');
        if ($league_id) {
            //$matchS = new MatchService();
            $leagueS = new LeagueService();
            $leagueInfo = $leagueS->getLeaugeInfoWithOrg(['id' => $league_id]);
            $this->assign('league_id', $league_id);
            $this->assign('leagueInfo', $leagueInfo);
            $this->league_id = $league_id;
            $this->leagueInfo = $leagueInfo;
        }
    }

    // 赛事列表（平台展示）
    public function matchlist() {
        return view('Match/matchList');
    }

    // 赛事详情
    public function matchinfo() {
        return view('Match/matchInfo');
    }

    // 创建比赛
    public function creatematch() {
        return view('Match/createMatch');
    }

    // 编辑比赛
    public function matchedit() {
        return view('Match/matchEdit');
    }
    // 比赛管理列表
    public function matchlistofteam() {
        return view('Match/matchlistofteam');
    }

    // 约战比赛列表
    public function friendlylist() {
        return view('Match/friendlylist');
    }

    // 发布约战比赛列表
    public function friendlylistOfTeam() {
        // 获取球队详细信息 模块下全局赋值
        $team_id = input('team_id');
        $teamS = new TeamService();
        $teamInfo = $teamS->getTeam(['id' => $team_id]);
        $this->assign('team_id', $team_id);
        $this->assign('teamInfo', $teamInfo);
        return view('Match/friendlylistOfTeam');
    }

    // 约战列表 (机构版)
    public function friendlylistOfOrganization() {
        return view('Match/friendlylistOfOrganization');
    }

    // 约战比赛详情
    public function friendlyinfo() {
        $matchId = input('match_id');
        $matchS = new MatchService();
        $refereeS = new RefereeService();
        // 比赛详情
        $matchInfo = $matchS->getMatch(['id' => $matchId]);
        $matchRecordInfo = $matchS->getMatchRecord(['match_id' => $matchInfo['id']]);
        if ($matchRecordInfo) {
            if (!empty($matchRecordInfo['album'])) {
                $matchRecordInfo['album'] = json_decode($matchRecordInfo['album'], true);
            }
            if (empty($matchRecordInfo['away_team'])) {
                $matchRecordInfo['away_team_logo'] = config('default_image.team_logo');
            }
            // 裁判字段数据为空
            $emptyRefereeArr = [
                'referee_id' => 0, 'referee' => '', 'referee_cost' => ''
            ];
            if ( empty($matchRecordInfo['referee1']) ) {
                $matchRecordInfo['referee1'] = $emptyRefereeArr;
            }
            if ( empty($matchRecordInfo['referee2']) ) {
                $matchRecordInfo['referee2'] = $emptyRefereeArr;
            }
            if ( empty($matchRecordInfo['referee3']) ) {
                $matchRecordInfo['referee3'] = $emptyRefereeArr;
            }
            $matchInfo['record'] = $matchRecordInfo;
        }

        // 裁判列表： 获取已同意的裁判比赛申请|邀请的裁判名单
        $modelMatchRefereeApply = new MatchRefereeApply();
        $refereeList = $modelMatchRefereeApply->where([
            'match_id' => $matchRecordInfo['match_id'],
            'match_record_id' => $matchRecordInfo['id'],
            'status' => ['neq', 3]
        ])->select();


        // 比赛发布球队信息
        $teamS = new TeamService();
        $teamInfo = $teamS->getTeam(['id' => $matchInfo['team_id']]);

        // 获取会员的已审核裁判员信息
        $memberRefereeInfo = $refereeS->getRefereeInfo(['member_id' => $this->memberInfo['id'], 'status' => 1]);

        $this->assign('match_id', $matchId);
        $this->assign('matchInfo', $matchInfo);
        $this->assign('teamInfo', $teamInfo);
        $this->assign('memberRefereeInfo', $memberRefereeInfo);
        $this->assign('refereeList', $refereeList);
        return view('Match/friendlyinfo');
    }

    // 联赛组织创建
    public function createorganization() {
        // 视图页
        $step = input('step', 1, 'intval');
        $view = 'Match/organization/createOrganization'.$step;

        // 有联赛组织
        $id = input('org_id', 0, 'intval');
        $leagueS = new LeagueService();
        if ($id) {
            $matchOrgInfo = $leagueS->getMatchOrg(['id' => $id]);
            $this->assign('matchOrgInfo', $matchOrgInfo);
        }

        // 认证信息
        // 身份证
        $idCard = db('cert')->where([
            'cert_type' => 1,
            'member_id' => $this->memberInfo['id'],
            'camp_id' => 0,
            'match_org_id' => 0
        ])->find();

        return view($view, [
            'idCard' => $idCard
        ]);
    }

    // 注册联赛组织成功页
    public function createorgsuccess() {
        $orgId = input('org_id', 0, 'intval');
        return view('Match/organization/createOrgSuccess', [
            'org_id' => $orgId
        ]);
    }

    // 联赛组织编辑
    public function organizationSetting() {
        // 视图页
        $step = input('step', 1, 'intval');
        $view = 'Match/organization/organizationSetting'.$step;

        // 有联赛组织
        $orgId = input('org_id', 0, 'intval');
        $leagueS = new LeagueService();
        if (!$orgId) {
           $this->error(__lang('MSG_402'));
        }
        $matchOrgInfo = $leagueS->getMatchOrg(['id' => $orgId]);
        if (!$matchOrgInfo) {
            $this->error(__lang('MSG_404'));
        }

        // 证件信息
        $certS = new CertService();
        $orgCert = $leagueS->getOrgCert($orgId);
        // 创建人身份证
        $idCard = db('cert')->where([
            'cert_type' => 1,
            'member_id' => $matchOrgInfo['creater_member_id'],
            'camp_id' => 0,
            'match_org_id' => 0
        ])->find();

        return view($view, [
            'matchOrgInfo'=> $matchOrgInfo,
            'orgCert' => $orgCert,
            'idCard' => $idCard
        ]);
    }

    // 联赛列表
    public function leagueList() {
        $leagueS = new LeagueService();
        $matchOrgList = $leagueS->getMemberInMatchOrgs($this->memberInfo['id']);
        // 会员有无联赛组织标识
        $hasMatchOrg = ($matchOrgList) ? 1 : 0;
        return view('Match/leagueList', [
            'hasMatchOrg' => $hasMatchOrg
        ]);
    }

    // 创建联赛信息
    public function createleaguematch() {
        $leagueS = new LeagueService();
        // 传入联赛组织id
        $orgId = input('org_id', 0, 'intval');
        if (!$orgId) {
            // 获取会员所在联赛组织，若无组织数据 跳转至创建联赛组织
            $matchOrgList = $leagueS->getMemberInMatchOrgs($this->memberInfo['id']);
            $this->assign('matchOrgList', $matchOrgList);
        } else {
            $matchOrgInfo = $leagueS->getMatchOrg(['id' => $orgId]);
            $this->assign('matchOrgInfo', $matchOrgInfo);
        }

        return view('Match/createLeagueMatch', [
            'orgId' => $orgId
        ]);
    }

    // 修改联赛信息
    public function leaguematchedit() {
        return view('Match/leagueMatchEdit');
    }

    // 联赛管理
    public function leagueManage() {
        // 获取当前会员的联赛人员关系数据
        $leagueS = new LeagueService();
        $matchMemberInfo = $leagueS->getMatchMember([
            'match_id' => $this->league_id,
            'member_id' => $this->memberInfo['id'],
            'status' => 1
        ]);
        if (!$matchMemberInfo) {
            $this->error(__lang('MSG_403'));
        }

        $this->assign('matchMemberInfo', $matchMemberInfo);
        return view('Match/leagueManage');
    }

    // 我的联赛
    public function myLeague() {
        $id = input('org_id', 0, 'intval');
        $leagueS = new LeagueService();
        $matchS = new MatchService();
        // 获取联赛组织详情
        $leagueOrg = $leagueS->getMatchOrg(['id' =>$id]);
        // 获取该联赛组织的联赛列表
        $leagueList = $matchS->matchListAll(['match_org_id' => $id]);

        return view('Match/myLeague', [
            'orgInfo' => $leagueOrg,
            'leagueList' => $leagueList
        ]);
    }

    // 联赛主页
    public function leagueInfo() {
        // 工作人员类型
        $leagueS = new LeagueService();
        $types = $leagueS->getMatchMemberTypes();
        // 申请联赛工作人员按钮显示：查询联赛工作人员无数据就显示
        $btnApplyWorkerShow = 0;
        $matchmember = $leagueS->getMatchMember(['match_id' => $this->league_id, 'member_id' => $this->memberInfo['id']]);
        if (!$matchmember || $matchmember['status'] != 1) {
            $btnApplyWorkerShow = 1;
        }

        $this->assign('types', $types);
        $this->assign('btnApplyWorkerShow', $btnApplyWorkerShow);
        return view('Match/leagueInfo');
    }

    // 联赛章程
    public function leagueregulation() {
        return view('Match/regulation/leagueRegulation');
    }
     // 联赛章程编辑
     public function regulationofleague() {
        return view('Match/regulation/regulationOfLeague');
    }

    // 联赛赛程详情页
    public function leaguescheduleinfo() {
        $id = input('param.id', 0, 'intval');
        $leagueS = new LeagueService();
        $matchScheduleInfo = $leagueS->getMatchSchedule(['id' => $id]);

        $this->assign('matchScheduleInfo', $matchScheduleInfo);
        return view('Match/schedule/leagueScheduleInfo');
    }

    // 联赛赛程
    public function leagueSchedule() {
        $id = input('param.id', 0, 'intval');
        $leagueS = new LeagueService();
        $matchScheduleInfo = $leagueS->getMatchSchedule(['id' => $id]);

        $this->assign('matchScheduleInfo', $matchScheduleInfo);
        return view('Match/schedule/leagueSchedule');
    }

    // 联赛战绩
    public function leagueRecord() {

        return view('Match/record/leagueRecord');
    }

    // 联赛战绩详情
    public function leaguerecordinfo() {
        $id = input('param.id', 0, 'intval');
        $matchS = new MatchService();
        $matchRecordInfo = $matchS->getMatchRecord(['id' => $id]);

        $this->assign('matchRecordInfo', $matchRecordInfo);
        return view('Match/record/recordInfo');
    }

    // 联赛数据
    public function leagueData() {
        return view('Match/data/leagueData');
    }

    /*=======以上是外部展示页，以下是管理操作页=======*/

    // 联赛球队
    public function teamListOfLeague() {
        return view('Match/team/teamListOfLeague');
    }

    // 联赛球队详情
    public function teaminfoofleague() {
        $league_id = input('league_id', 0, 'intval');
        $team_id = input('team_id', 0, 'intval');

        // 获取联赛球队信息
        $leagueService = new LeagueService();
        $matchTeam = $leagueService->getMatchTeamInfo([
            'match_id' => $league_id,
            'team_id' => $team_id
        ]);
        if (!$matchTeam) {
            $this->error(__lang('MSG_404'));
        }

        return view('Match/team/teamInfoOfLeague', [
            'matchTeamInfo' => $matchTeam
        ]);
    }

    // 联赛报名球队详情
    public function teaminfosignupleague() {
        $league_id = input('league_id', 0, 'intval');
        $team_id = input('team_id', 0, 'intval');

        // 获取联赛申请球队信息
        $matchService = new MatchService();
        $matchApply = $matchService->getMatchApply([
            'match_id' => $league_id,
            'team_id' => $team_id
        ]);
        if (!$matchApply) {
            $this->error(__lang('MSG_404'));
        }

        // 获取球队详细信息
        $teamS = new TeamService();
        $teamInfo = $teamS->getTeam(['id' => $team_id]);

        return view('Match/team/teamInfoSignupLeague', [
            'matchApplyInfo' => $matchApply,
            'teamInfo' => $teamInfo
        ]);
    }

    // 联赛比赛
    public function matchListOfLeague() {
        return view('Match/match/matchListOfLeague');
    }

    // 比赛技术数据创建
    public function matchInfoOfLeague() {
        $id = input('id', 0, 'intval');
        $matchS = new MatchService();
        $matchRecordInfo = $matchS->getMatchRecord(['id' => $id]);

        $this->assign('matchRecordInfo',$matchRecordInfo);
        return view('Match/match/matchInfoOfLeague');
    }

    // 比赛技术数据编辑
    public function editMatchOfLeague() {
        $id = input('id', 0, 'intval');
        $matchS = new MatchService();
        $matchRecordInfo = $matchS->getMatchRecord(['id' => $id]);

        $this->assign('matchRecordInfo',$matchRecordInfo);
        return view('Match/match/editMatchOfLeague');
    }

    // 联赛编辑比赛
    public function matchEditOfLeague() {
        return view('Match/match/matchEditOfLeague');
    }

   

    // 报名联赛
    public function signUpLeague() {
        $league_id = input('league_id');
        return view('Match/signUpLeague', [
            'league_id' => $league_id
        ]);
    }

    // 联赛球队报名回复
    public function teamApplyListOfLeague() {
        return view('Match/teamApplyListOfLeague');
    }

    // 组织管理员列表
    public function adminListOfOrganization() {
        $org_id = input('org_id');
        // 查询联赛组织
        $leagueService = new LeagueService();
        $leagueOrgInfo = $leagueService->getMatchOrg(['id' => $org_id]);

        return view('Match/organization/adminListOfOrganization', [
            'org_id' => $org_id,
            'leagueOrgInfo' => $leagueOrgInfo
        ]);
    }

    //  邀请会员-组织管理员
    public function addorgmemberoforg() {
        $org_id = input('org_id');
        // 查询联赛组织
        $leagueService = new LeagueService();
        $leagueOrgInfo = $leagueService->getMatchOrg(['id' => $org_id]);

        return view('Match/organization/addOrgMemberOfOrg', [
            'org_id' => $org_id,
            'leagueOrgInfo' => $leagueOrgInfo
        ]);
    }

    // 会员查看联赛组织邀请详情
    public function orginvitation() {
        // apply表id
        $id = input('id', 0, 'intval');
        // 查询联赛组织邀请信息
        $leagueS = new LeagueService();
        $matchS = new MatchService();
        $applyInfo = $leagueS->getApplyByLeague([
            'id' => $id,
            'organization_type' => 5
        ]);
        if (!$applyInfo) {
            $this->error(__lang('MSG_404'));
        }
        // 查询联赛组织信息、联赛信息
        $orgInfo = $leagueS->getMatchOrg(['id' => $applyInfo['organization_id']]);
        $leagueInfo = $matchS->getMatch(['match_org_id' => $applyInfo['organization_id']]);
        // 更新apply阅读状态为已读
        try {
            if ($this->memberInfo['id'] == $applyInfo['member_id']) {
                $leagueS->saveApplyByLeague([
                    'id' => $applyInfo['id'],
                    'isread' => 1
                ]);
            }
        } catch (Exception $e) {
            trace('error:'.$e->getMessage(), 'error');
            $this->error($e->getMessage());
        }

        return view('Match/orgInvitation', [
            'applyInfo' => $applyInfo,
            'orgInfo' => $orgInfo,
            'leagueInfo' => $leagueInfo
        ]);
    }

    // 联赛工作人员列表
    public function workListOfLeague() {
        return view('Match/work/workListOfLeague');
    }

    // 邀请联赛工作人员
    public function addworkerofleague() {
        // 工作人员类型
        $leagueS = new LeagueService();
        $types = $leagueS->getMatchMemberTypes();
        return view('Match/work/addWorkerOfLeague', [
            'types' => $types
        ]);
    }

    // 会员查看联赛工作人员邀请详情
    public function workerinvitation() {
        // apply表id
        $id = input('id', 0, 'intval');
        // 查询联赛组织邀请信息
        $leagueS = new LeagueService();
        $matchS = new MatchService();
        $applyInfo = $leagueS->getApplyByLeague([
            'id' => $id,
            'organization_type' => 4,
            'apply_type' => 2
        ]);
        if (!$applyInfo) {
            $this->error(__lang('MSG_404'));
        }
        // 查询联赛联赛信息
        $leagueInfo = $leagueS->getLeaugeInfoWithOrg(['id' => $applyInfo['organization_id']]);
        // 更新apply阅读状态为已读
        try {
            if ($this->memberInfo['id'] == $applyInfo['member_id']) {
                $leagueS->saveApplyByLeague([
                    'id' => $applyInfo['id'],
                    'isread' => 1
                ]);
            }
        } catch (Exception $e) {
            trace('error:'.$e->getMessage(), 'error');
            $this->error($e->getMessage());
        }

        return view('Match/work/workerInvitation', [
            'applyInfo' => $applyInfo,
            'leagueInfo' => $leagueInfo
        ]);
    }

    // 联赛工作人员申请列表
    public function workerapplylist() {
        return view('Match/work/workerApplyList');
    }

    // 查看联赛工作人员申请详情
    public function workerapplyinfo() {
        $apply_id = input('apply_id', 0, 'intval');
        // 获取联赛工作人员申请数据
        $leagueS = new LeagueService();
        $applyInfo = $leagueS->getApplyByLeague([
            'id' => $apply_id,
            'organization_type' => 4,
            'apply_type' => 1
        ]);
        if (!$applyInfo) {
            $this->error(__lang('MSG_404'));
        }

        // 申请职位文案
        switch ($applyInfo['type']) {
            case 3:
                $applyInfo['type_text'] = '管理员';
                break;  // 管理员
            case 8:
                $applyInfo['type_text'] = '记分员';
                break;  // 记分员
            case 6:
                $applyInfo['type_text'] = '裁判员';
                break;  // 裁判员
            default:
                $applyInfo['type_text'] = '工作人员';
        }
        // 查询联赛联赛信息
        $leagueInfo = $leagueS->getLeaugeInfoWithOrg(['id' => $applyInfo['organization_id']]);
        // 查询联赛工作人员信息
        $matchMemberInfo = $leagueS->getMatchMember([
            'match_id' => $applyInfo['organization_id'],
            'member_id' => $applyInfo['member_id']
        ]);
        $memberS = new MemberService();
        $applyInfo['member'] = $memberS->getMemberInfo(['id' => $matchMemberInfo['member_id']]);
        // 若是裁判员读取裁判员数据
        $refereeS = new RefereeService();
        if ($applyInfo['type'] == 6) {
            $refereeInfo = $refereeS->getRefereeInfo(['member_id' => $applyInfo['member_id']]);
            $this->assign('refereeInfo', $refereeInfo);
        }

        // 更新apply阅读状态为已读
        try {
            // 联赛工作人员查看更新阅读状态
            $matchOrgMember = $leagueS->getMatchOrgMember([
                'match_org_id' => $leagueInfo['match_org_id'],
                'member_id' => $this->memberInfo['id'],
                'status' => 1
            ]);
            if ($matchOrgMember && $matchOrgMember['status'] == 1) {
                $leagueS->saveApplyByLeague([
                    'id' => $applyInfo['id'],
                    'isread' => 1
                ]);
            }
        } catch (Exception $e) {
            trace('error:'.$e->getMessage(), 'error');
            $this->error($e->getMessage());
        }

        $this->assign('applyInfo', $applyInfo);
        $this->assign('leagueInfo', $leagueInfo);
        $this->assign('matchMemberInfo', $matchMemberInfo);
        return view('Match/work/workerApplyInfo');
    }

    // 联赛消息
    public function messageListOfLeague() {
        return view('Match/message/messageListOfLeague');
    }
    
    // 联赛战绩管理
    public function recordListOfLeague() {
        return view('Match/record/recordListOfLeague');
    }

    // 联赛数据管理
    public function dataListOfLeague() {
        return view('Match/data/dataListOfLeague');
    }

    // 联赛分组列表
    public function groupsListOfLeague() {
        // 获取联赛有无分组数据
        $leagueS = new LeagueService();
        // 创建/编辑分组 控制标识：0创建/1编辑
        $btnEditAction = 0;
        $matchGroups = $leagueS->getMatchGroups(['match_id' => $this->league_id]);
        if ($matchGroups) {
            $btnEditAction = 1;
        }
        return view('Match/group/groupsListOfLeague', [
            'btnEditAction' => $btnEditAction
        ]);
    }
    
    // 联赛创建分组
    public function createGroups() {
        // 联赛正式球队数
        $leagueS = new LeagueService();
        $teamCount = $leagueS->getMatchTeamCount(['match_id' => $this->league_id]);

        return view('Match/group/createGroups', [
            'teamCount' => $teamCount
        ]);
    }

    // 联赛创建分组
    public function createCustomGroups() {
        // 联赛正式球队数
        $leagueS = new LeagueService();
        $teamCount = $leagueS->getMatchTeamCount(['match_id' => $this->league_id]);

        return view('Match/group/createCustomGroups', [
            'teamCount' => $teamCount
        ]);
    }

    // 编辑联赛某个分组
    public function editgroups() {
        // 联赛正式球队数
        $leagueS = new LeagueService();
        // 联赛正式球队数
        $teamCount = $leagueS->getMatchTeamCount(['match_id' => $this->league_id]);
        // 联赛分组数
        $groupCount = $leagueS->getMatchGroupCount(['match_id' => $this->league_id]);

        return view('Match/group/editGroups', [
            'teamCount' => $teamCount,
            'groupCount' => $groupCount
        ]);
    }

    // 球队登记联赛参赛球员
    public function completeplayerbyteam() {
        $team_id = input('team_id', 0, 'intval');
        $apply_id = input('apply_id', 0, 'intval');
        $teamS = new TeamService();
        $leagueS = new LeagueService();
        $matchS = new MatchService();
        // 查询联赛数据（已初始化）
        // 查询球队申请参加联赛数据
        if ($apply_id) {
            $matchApplyInfo = $matchS->getMatchApply(['id' => $apply_id]);
        } else {
            $matchApplyInfo = $matchS->getMatchApply(['match_id' => $this->league_id, 'team_id' => $team_id]);
        }
        if (!$matchApplyInfo) {
            $this->error('无报名联赛数据');
        }
        // 查询球队-联赛关系数据
        $matchTeamInfo = $leagueS->getMatchTeamInfo(['team_id' => $team_id, 'match_id' => $this->league_id]);
        // 获取球队数据
        $teamInfo = $teamS->getTeam(['id' => $team_id]);

        return view('Match/team/completePlayerByTeam', [
            'matchApplyInfo' => $matchApplyInfo,
            'matchTeamInfo' => $matchTeamInfo,
            'teamInfo' => $teamInfo
        ]);
    }

    // 赛程列表管理
    public function schedulelistofleague() {
        return view('Match/schedule/scheduleListOfLeague');
    }

    // 创建赛程
    public function createscheduleofleague() {
        // 获取联赛分组列表
        $leagueS = new LeagueService();
        $groups = $leagueS->getMatchGroups([
            'match_id' => $this->league_id
        ]);
        $matchStageGroupInfo = $leagueS->getMatchStage([
            'match_id' => $this->league_id,
            'type' => 1
        ]);


        $this->assign('groups', $groups);
        $this->assign('matchStageGroupInfo', $matchStageGroupInfo);
        return view('Match/schedule/createScheduleOfLeague');
    }

    // 手动创建赛程
    public function createscheduleofleague1() {
        // 获取联赛分组列表
        $leagueS = new LeagueService();
        $groups = $leagueS->getMatchGroups([
            'match_id' => $this->league_id
        ]);

        $this->assign('groups', $groups);
        return view('Match/schedule/createScheduleOfLeague1');
    }

    // 编辑赛程
    public function editScheduleOfLeague() {
        $id = input('id', 0, 'intval');
        // 获取赛程详情
        $leagueS = new LeagueService();
        $matchscheduleInfo = $leagueS->getMatchSchedule(['id' => $id]);

        $this->assign('matchScheduleInfo', $matchscheduleInfo);
        return view('Match/schedule/editScheduleOfLeague');
    }

    // 比赛阶段创建
    public function createMatchStage() {
        $leagueS = new LeagueService();
        $types = $leagueS->getMatchStageTypes();

        $this->assign('types', $types);
        return view('Match/stage/createMatchStage');
    }

    // 比赛阶段编辑
    public function editmatchstage() {
        $id = input('id', 0, 'intval');
        // 获取比赛阶段详情
        $leagueS = new LeagueService();
        $stageInfo = $leagueS->getMatchStage(['id' => $id]);
        $types = $leagueS->getMatchStageTypes();

        $this->assign('matchStageInfo', $stageInfo);
        $this->assign('types', $types);
        return view('Match/stage/editMatchStage');
    }

    // 比赛阶段管理列表
    public function matchstageListofleague() {
        return view('Match/stage/matchstageListofleague');
    }

      // 联赛比赛阶段出线（晋级）球队预览页
      public function stagePromotionList() {
        return view('Match/stage/promotionList');
    }

    // 球队积分列表
    public function integralList() {
        return view('Match/record/integralList');
    }

    // 球队赛果录入
    public function recordScoreOfLeague() {
        // 获取比赛比分数据，没有则获取赛程数据
        $id = input('match_id', 0, 'intval');
        $leagueId = input('league_id', 0,'intval');
        $leagueS = new LeagueService();
        $matchS = new MatchService();
        $matchScheduleInfo = $leagueS->getMatchSchedule([
            'id' => $id,
            'match_id' => $leagueId
        ]);

        $this->assign('matchScheduleInfo', $matchScheduleInfo);
        return view('Match/record/recordScoreOfLeague');
    }
    // 球队赛果（比赛）详情
    public function recordInfo() {
        // 获取比赛比分数据，没有则获取赛程数据
        $id = input('id', 0, 'intval');
        $leagueId = input('league_id', 0,'intval');
        $leagueS = new LeagueService();
        $matchS = new MatchService();
        $matchRecordInfo = $matchS->getMatchRecord([
            'id' => $id,
            'match_id' => $leagueId
        ]);

        $this->assign('matchRecordInfo', $matchRecordInfo);
        return view('Match/record/recordInfo');
    }

    // 球队赛果编辑
    public function editRecordScoreOfLeague() {
        // 获取比赛比分数据，没有则获取赛程数据
        $id = input('id', 0, 'intval');
        $leagueId = input('league_id', 0,'intval');
        $leagueS = new LeagueService();
        $matchS = new MatchService();
        $matchRecordInfo = $matchS->getMatchRecord([
            'id' => $id,
            'match_id' => $leagueId
        ]);

        $this->assign('matchRecordInfo', $matchRecordInfo);
        return view('Match/record/editRecordScoreOfLeague');
    }
  
    // 联赛动态列表
    public function dynamicListOfLeague() {
        return view('Match/dynamic/listOfLeague');
    }
    // 联赛创建动态
    public function dynamicCreateOfLeague() {
        return view('Match/dynamic/createOfLeague');
    }

    // 联赛动态详情（外部展示）
    public function dynamicinfo() {
        $id = input('param.article_id');
        $articleS = new ArticleService();
        $articleInfo = $articleS->getArticleInfo([
            'id' => $id,
            'organization_type' => 4,
            'category' => 3
        ]);
        if(!$articleInfo){
            $this->error('找不到文章信息');
        }
        //点击率+1;
        $articleS->incArticle([ 'id' => $articleInfo['id'] ],'hit');

        //收藏列表
        $isCollect = $articleS->getCollectInfo([ 'article_id'=> $articleInfo['id'],' member_id'=>$this->memberInfo['id'] ]);
        $isLikes = $articleS->getLikesInfo([ 'article_id' => $articleInfo['id'], 'member_id'=>$this->memberInfo['id'] ]);

        $this->assign('articleInfo', $articleInfo);
        $this->assign('isLikes',$isLikes);
        $this->assign('isCollect',$isCollect);
        return view('Match/dynamic/info');
    }

    // 联赛动态详情
    public function dynamicinfoOfLeague() {
        $id = input('param.article_id');
        $articleS = new ArticleService();
        $articleInfo = $articleS->getArticleInfo([
            'id' => $id,
            'organization_type' => 4,
            'category' => 3
        ]);
        if(!$articleInfo){
            $this->error('找不到文章信息');
        }

        $this->assign('articleInfo', $articleInfo);
        return view('Match/dynamic/infoOfLeague');
    }
    // 联赛编辑动态
    public function dynamicEditOfLeague() {
        $id = input('param.article_id');
        $articleS = new ArticleService();
        $articleInfo = $articleS->getArticleInfo([
            'id' => $id,
            'organization_type' => 4,
            'category' => 3
        ]);
        if(!$articleInfo){
            $this->error('找不到文章信息');
        }

        $this->assign('articleInfo', $articleInfo);
        return view('Match/dynamic/editOfLeague');
    }
    // 联赛展示列表
    public function dynamicList() {
        return view('Match/dynamic/list');
    }
    // 联赛比赛动态列表
    public function leagueDynamicList() {
        return view('Match/dynamic/leagueDynamicList');
    }
   
}