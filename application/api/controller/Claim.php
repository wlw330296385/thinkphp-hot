<?php
// 联赛api
namespace app\api\controller;

use app\model\Team;
use app\model\Member;
use app\model\MemberClaimApply;
use app\model\TeamMember;
use app\model\TeamMemberRole;
use app\model\MatchTeamMember;
use app\model\MatchStatistics;
use app\model\MatchRecordMember;
use app\service\MessageService;
use think\Db;
use think\Exception;
use think\Validate;

class Claim extends Base
{
    public function teamMember() {

        $team_member_id = input("post.team_member_id");
        if (empty($this->memberInfo['id'])) {
            return json(['code' => 100, 'msg' => __lang('MSG_001')]);
        }

        if (empty($this->memberInfo['telephone'])) {
            return json(['code' => 100, 'msg' => "请先填写手机号"]);
        }

        $memberInfo = $this->memberInfo;
        $teamMember = TeamMember::get(["id" => $team_member_id, "member_id" => -1, "telephone" => $memberInfo['telephone']]);
        if (empty($teamMember)) {
            return json(['code' => 100, 'msg' => "认领的数据与您不匹配"]);
        }
        $teamMemberIdStr = $teamMember['id'];

        // 如果是队长则给球队创始人
        $is_leader = 0;
        $teamMemberRoleList = TeamMemberRole::all(["member_id" => -1, "name" => $teamMember["name"]]);
        $teamMemberRoleIdStr = '';
        if (!empty($teamMemberRoleList)) {
            $teamMemberRoleIdArray = [];
            foreach($teamMemberRoleList as $row) {
                array_push($teamMemberRoleIdArray, $row['id']);
                if ($row['type'] == 3 || $row['type'] == 6) {
                    $is_leader = 1;
                }
            }
            $teamMemberRoleIdStr = implode($teamMemberRoleIdArray, ',');
        }

        $teamIdStr = '';
        if ($is_leader) {
            $team = Team::get(["member_id" => -1, "id" => $teamMember["team_id"]]);
            if (empty($team)) {
                $is_leader = 0;
            } else {
                $teamIdStr = $team['id'];
            }
        }

        $matchTeamMemberList = MatchTeamMember::all(["team_member_id" => $teamMember["id"]]);
        $matchTeamMemberIdStr = '';
        if (!empty($matchTeamMemberList)) {
            $matchTeamMemberIdArray = [];
            foreach($matchTeamMemberList as $row) {
                array_push($matchTeamMemberIdArray, $row['id']);
            }
            $matchTeamMemberIdStr = implode($matchTeamMemberIdArray, ',');
        }

        $matchStatisticsList = MatchStatistics::all(["team_member_id" => $teamMember["id"]]);
        $matchStatisticsIdStr = '';
        if (!empty($matchStatisticsList)) {
            $matchStatisticsIdArray = [];
            foreach($matchStatisticsList as $row) {
                array_push($matchStatisticsIdArray, $row['id']);
            }
            $matchStatisticsIdStr = implode($matchStatisticsIdArray, ',');
        }

        $matchRecordMemberList = MatchRecordMember::all(["team_member_id" => $teamMember["id"]]);
        $matchRecordMemberIdStr = '';
        if (!empty($matchTeamMemberList)) {
            $matchRecordMemberIdArray = [];
            foreach($matchRecordMemberList as $row) {
                array_push($matchRecordMemberIdArray, $row['id']);
            }
            $matchRecordMemberIdStr = implode($matchRecordMemberIdArray, ',');
        }

        $now = time();
        Db::startTrans();
        try {
            Db::table('team_member')->where('id', 'in', $teamMemberIdStr)->update([
                'member_id' => $memberInfo['id'],
                'member' => $memberInfo['member'],
                'update_time' => $now
            ]);

            if (!empty($matchRecordMemberList)) {
                Db::table('team_member_role')->where('id', 'in', $teamMemberRoleIdStr)->update([
                    'member_id' => $memberInfo['id'],
                    'member' => $memberInfo['member'],
                    'update_time' => $now
                ]);
            }

            if ($is_leader) {
                Db::table('team')->where('id', 'in', $teamIdStr)->update([
                    'member_id' => $memberInfo['id'],
                    'member' => $memberInfo['member'],
                    'update_time' => $now
                ]);
            }
            if (!empty($matchTeamMemberList)) {
                Db::table('match_team_member')->where('id', 'in', $matchTeamMemberIdStr)->update([
                    'member_id' => $memberInfo['id'],
                    'member' => $memberInfo['member'],
                    'avatar' => $memberInfo['avatar'],
                    'update_time' => $now
                ]);
            }

            if (!empty($matchStatisticsList)) {
                Db::table('match_statistics')->where('id', 'in', $matchStatisticsIdStr)->update([
                    'member_id' => $memberInfo['id'],
                    'member' => $memberInfo['member'],
                    'update_time' => $now
                ]);
            }

            if (!empty($matchRecordMemberList)) {
                Db::table('match_record_member')->where('id', 'in', $matchRecordMemberIdStr)->update([
                    'member_id' => $memberInfo['id'],
                    'member' => $memberInfo['member'],
                    'avatar' => $memberInfo['avatar'],
                    'update_time' => $now
                ]);
            }

            $changes = [
                "team_member" => $teamMemberIdStr,
                "team_member_role" => $teamMemberRoleIdStr,
                "team" => $teamIdStr,
                "match_team_member" => $matchTeamMemberIdStr,
                "match_statistics" => $matchStatisticsIdStr,
                "match_record_member" => $matchRecordMemberIdStr
            ];
            $logData = [
                'member_id' => $memberInfo['id'],
                'changes' => json_encode($changes),
                'create_time' => $now
            ];

            Db::table('log_claim')->insert($logData);

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            trace('error:' . $e->getMessage(), 'error');
            return json(['code' => 100, 'msg' => $e->getMessage()]);
        }

        return json(['code' => 200, 'msg' => __lang('MSG_200')]);
        
    }

    public function otherTeamMember() {
        $member_id = input("post.member_id");
        $team_member_id = input("post.team_member_id");
        if (!$member_id || !$team_member_id) {
            return json(['code' => 100, 'msg' => __lang('MSG_402')]);
        }
        if (empty($this->memberInfo['id'])) {
            return json(['code' => 100, 'msg' => __lang('MSG_001')]);
        }

        // 1.判断操作人是不是 队长/ 领队
        $teamMemberRole = TeamMemberRole::where(["member_id" => $this->memberInfo['id'], "type" => ['in', '3,6']])->order('type desc')->find();
        if (!$teamMemberRole) {
            return json(['code' => 100, 'msg' => __lang('MSG_403')." 你不是领队或队长，无法操作"]);
        }

        $teamMember = TeamMember::get(['team_id' => $teamMemberRole['team_id'], 'id'=>$team_member_id]);
        if (!$teamMember) {
            return json(['code' => 100, 'msg' => __lang('MSG_404')." 找不到该队员数据"]);
        }
        $teamMemberIdStr = $teamMember['id'];

        $matchTeamMemberList = MatchTeamMember::all(["team_member_id" => $teamMember["id"]]);
        $matchTeamMemberIdStr = '';
        if (!empty($matchTeamMemberList)) {
            $matchTeamMemberIdArray = [];
            foreach($matchTeamMemberList as $row) {
                array_push($matchTeamMemberIdArray, $row['id']);
            }
            $matchTeamMemberIdStr = implode($matchTeamMemberIdArray, ',');
        }

        $matchStatisticsList = MatchStatistics::all(["team_member_id" => $teamMember["id"]]);
        $matchStatisticsIdStr = '';
        if (!empty($matchStatisticsList)) {
            $matchStatisticsIdArray = [];
            foreach($matchStatisticsList as $row) {
                array_push($matchStatisticsIdArray, $row['id']);
            }
            $matchStatisticsIdStr = implode($matchStatisticsIdArray, ',');
        }

        $matchRecordMemberList = MatchRecordMember::all(["team_member_id" => $teamMember["id"]]);
        $matchRecordMemberIdStr = '';
        if (!empty($matchTeamMemberList)) {
            $matchRecordMemberIdArray = [];
            foreach($matchRecordMemberList as $row) {
                array_push($matchRecordMemberIdArray, $row['id']);
            }
            $matchRecordMemberIdStr = implode($matchRecordMemberIdArray, ',');
        }

        $now = time();
        Db::startTrans();
        try {
            Db::table('team_member')->where('id', 'in', $teamMemberIdStr)->update([
                'member_id' => $memberInfo['id'],
                'member' => $memberInfo['member'],
                'update_time' => $now
            ]);

            if (!empty($matchRecordMemberList)) {
                Db::table('team_member_role')->where('id', 'in', $teamMemberRoleIdStr)->update([
                    'member_id' => $memberInfo['id'],
                    'member' => $memberInfo['member'],
                    'update_time' => $now
                ]);
            }

            if (!empty($matchTeamMemberList)) {
                Db::table('match_team_member')->where('id', 'in', $matchTeamMemberIdStr)->update([
                    'member_id' => $memberInfo['id'],
                    'member' => $memberInfo['member'],
                    'avatar' => $memberInfo['avatar'],
                    'update_time' => $now
                ]);
            }

            if (!empty($matchStatisticsList)) {
                Db::table('match_statistics')->where('id', 'in', $matchStatisticsIdStr)->update([
                    'member_id' => $memberInfo['id'],
                    'member' => $memberInfo['member'],
                    'update_time' => $now
                ]);
            }

            if (!empty($matchRecordMemberList)) {
                Db::table('match_record_member')->where('id', 'in', $matchRecordMemberIdStr)->update([
                    'member_id' => $memberInfo['id'],
                    'member' => $memberInfo['member'],
                    'avatar' => $memberInfo['avatar'],
                    'update_time' => $now
                ]);
            }

            $changes = [
                "team_member" => $teamMemberIdStr,
                "team_member_role" => $teamMemberRoleIdStr,
                "match_team_member" => $matchTeamMemberIdStr,
                "match_statistics" => $matchStatisticsIdStr,
                "match_record_member" => $matchRecordMemberIdStr
            ];
            $logData = [
                'member_id' => $memberInfo['id'],
                'changes' => json_encode($changes),
                'create_time' => $now
            ];

            Db::table('log_claim')->insert($logData);

            Db::table('member_claim_apply')->where(["member_id" => $member_id, "team_member_id" => $team_member_id])->update(['status' => 1]);

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            trace('error:' . $e->getMessage(), 'error');
            return json(['code' => 100, 'msg' => $e->getMessage()]);
        }

        return json(['code' => 200, 'msg' => __lang('MSG_200')]);

    }

    public function apply() {
        $team_member_id = input("post.team_member_id");
        $remarks = input("post.remarks");
        if (!$team_member_id) {
            return json(['code' => 100, 'msg' => __lang('MSG_402')]);
        }
        if (empty($this->memberInfo['id'])) {
            return json(['code' => 100, 'msg' => __lang('MSG_001')]);
        }

        $teamMember = TeamMember::get($team_member_id);
        if (!$teamMember || $teamMember['name'] != $this->memberInfo['realname'] ) {
            return json(['code' => 100, 'msg' => "队员名字与您不匹配"]);
        }

        $model = new MemberClaimApply();

        $memberClaimApply = $model->get([
            "member_id" => $this->memberInfo['id'],
            "team_member_id" => $team_member_id
        ]);
        if ($memberClaimApply) {
            return json(['code' => 100, 'msg' => "请勿重复申请"]);
        }

        $data = [
            "member_id" => $this->memberInfo['id'],
            "team_member_id" => $team_member_id,
            "remarks" => $remarks,
            "status" => 0
        ];
        $result = $model->allowField(true)->isUpdate(false)->save($data);
        if (!$result) {
            return json(['code' => 100, 'msg' => "申请失败"]);
        }
        // 发送模版消息给领队和队长
        $messageS = new MessageService();
        // 消息内容组合
        $message = [
            'title' => '申请认领比赛数据并加入球队通知',
            'content' => $teamMember['name'] . ' 申请认领在 ' . $teamMember['team'] . ' 的比赛数据，备注：' . $remarks,
            'url' => url('keeper/team/matchMemberClaimApplyinfo', ['id' => $model->id], '', true),
            'keyword1' => '球员数据认领',
            'keyword2' => $teamMember['name'],
            'keyword3' => date('Y-m-d H:i', time()),
            'remark' => '点击进入查看更多',
            'steward_type' => 2
        ];

        $teamMemberRole = TeamMemberRole::where(["team_id" => $teamMember['team_id'], "type" => ['in', '3,6']])->order('type desc')->find();
        // 发送消息
        $messageS->sendMessageToMember($teamMemberRole['member_id'], $message, config('wxTemplateID.checkPend'));

        return json(['code' => 200, 'msg' => __lang('MSG_200')]);
    }
    
}