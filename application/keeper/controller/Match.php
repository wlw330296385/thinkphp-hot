<?php
// 比赛 赛事
namespace app\keeper\controller;

use app\model\MatchRefereeApply;
use app\service\CertService;
use app\service\LeagueService;
use app\service\MatchService;
use app\service\TeamService;
use app\service\RefereeService;

class Match extends Base {
    protected $league_id;
    protected $leagueInfo;
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $league_id = input('league_id', 0, 'intval');
        if ($league_id) {
            $matchS = new MatchService();
            $leagueInfo = $matchS->getMatch(['id' => $league_id]);
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
        $view = 'Match/createOrganization'.$step;

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
        return view('Match/createOrgSuccess', [
            'org_id' => $orgId
        ]);
    }

    // 联赛组织编辑
    public function organizationSetting() {
        // 视图页
        $step = input('step', 1, 'intval');
        $view = 'Match/organizationSetting'.$step;

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
        return view('Match/leagueInfo');
    }

    // 联赛章程
    public function leagueregulation() {
        return view('Match/leagueRegulation');
    }

    // 联赛赛程
    public function leagueSchedule() {
        return view('Match/leagueSchedule');
    }

    // 联赛战绩
    public function leagueRecord() {
        return view('Match/leagueRecord');
    }

    // 联赛数据
    public function leagueData() {
        return view('Match/leagueData');
    }

    // 联赛比赛动态列表
    public function leagueDynamicList() {
        return view('Match/leagueDynamicList');
    }

    /*=======以上是外部展示页，以下是管理操作页=======*/

    // 联赛球队
    public function teamListOfLeague() {
        return view('Match/teamListOfLeague');
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

        return view('Match/teamInfoOfLeague', [
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

        return view('Match/teamInfoSignupLeague', [
            'matchApplyInfo' => $matchApply,
            'teamInfo' => $teamInfo
        ]);
    }

    // 联赛比赛
    public function matchListOfLeague() {
        return view('Match/matchListOfLeague');
    }

    // 联赛创建比赛
    public function createMatchOfLeague() {
        return view('Match/createMatchOfLeague');
    }

    // 联赛比赛详情
    public function matchInfoOfLeague() {
        return view('Match/matchInfoOfLeague');
    }

    // 联赛编辑比赛
    public function matchEditOfLeague() {
        return view('Match/matchEditOfLeague');
    }

    // 联赛章程编辑
    public function regulationofleague() {
        return view('Match/regulationOfLeague');
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

    // 组织管理员
    public function adminListOfOrganization() {
        return view('Match/adminListOfOrganization');
    }

    // 联赛工作人员
    public function workListOfLeague() {
        return view('Match/workListOfLeague');
    }

    // 联赛消息
    public function messageListOfLeague() {
        return view('Match/messageListOfLeague');
    }
    
    // 联赛战绩管理
    public function recordListOfLeague() {
        return view('Match/recordListOfLeague');
    }

    // 联赛数据管理
    public function dataListOfLeague() {
        return view('Match/dataListOfLeague');
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
        return view('Match/groupsListOfLeague', [
            'btnEditAction' => $btnEditAction
        ]);
    }
    
    // 联赛创建分组
    public function createGroups() {
        // 联赛正式球队数
        $leagueS = new LeagueService();
        $teamCount = $leagueS->getMatchTeamCount(['match_id' => $this->league_id]);

        return view('Match/createGroups', [
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

        return view('Match/editGroups', [
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

        return view('Match/completePlayerByTeam', [
            'matchApplyInfo' => $matchApplyInfo,
            'matchTeamInfo' => $matchTeamInfo,
            'teamInfo' => $teamInfo
        ]);
    }
}