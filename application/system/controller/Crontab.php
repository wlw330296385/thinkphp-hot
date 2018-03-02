<?php
namespace app\system\controller;
use app\model\Coach;
use app\model\Rebate;
use app\service\CoachService;
use app\service\ScheduleService;
use app\service\SystemService;
use app\service\MemberService;
use app\model\SalaryIn;
use app\model\CampFinance;
use app\model\Income;
use think\Controller;
use think\Db;
use think\Exception;
use think\helper\Time;

class Crontab extends Controller {
    public $setting;


    public function _initialize() {
        $SystemS = new SystemService();
        $this->setting = $SystemS::getSite();
    }

    // 结算可结算已申课时工资收入&扣减课时学员课时数
    public function schedulesalaryin() {
        try {
            // 获取可结算课时数据列表
            // 赠课记录，有赠课记录先抵扣
            // 91分 9进入运算 1平台收取
            // 结算主教+助教收入，剩余给营主
            // 上级会员收入提成(90%*5%,90%*3%)
            //list($start, $end) = Time::yesterday();
            //$map['update_time'] = ['between', [$start, $end]];
            $map['status'] = 1;
            $map['is_settle'] = 0;
            // 当前时间日期
            $nowDate = date('Ymd', time());
            $map['can_settle_date'] = $nowDate;
            $map['rebate_type'] = 1;
            //$map['questions'] = 0;
            Db::name('schedule')->where($map)->whereNull('delete_time')->chunk(50, function ($schedules) {
                foreach ($schedules as $schedule) {
                    // 扣减课时学员课时数 start
                    // 课时相关学员剩余课时-1
                    $scheduleS = new ScheduleService();
                    $students = unserialize($schedule['student_str']);
                    $decStudentRestscheduleResult = $scheduleS->decStudentRestschedule($students, $schedule);
                    // 扣减课时学员课时数 end

                    // 课时工资收入结算 start
                    // 课时正式学员人数
                    $numScheduleStudent = count(unserialize($schedule['student_str']));
                    $lesson = $lesson = Db::name('lesson')->where('id', $schedule['lesson_id'])->find();
                    // 抵扣赠送课时数
                    $numGiftSchedule = 0;
                    $systemRemarks = '';
                    if ($lesson['unbalanced_giftschedule'] > 0) {
                        // 课程有未结算赠课数,  抵扣赠课课时：上课正式学员人数/2取整
                        $numGiftSchedule = ceil($numScheduleStudent / 2);
                        if ($lesson['unbalanced_giftschedule'] > $numGiftSchedule) {
                            Db::name('lesson')->where('id', $schedule['lesson_id'])->setDec('unbalanced_giftschedule', $numGiftSchedule);
                            $systemRemarks = $lesson['lesson'] . '抵扣赠课数：' . $numGiftSchedule;
                        } else {
                            $numGiftSchedule = 0;
                        }
                    }
                    // 课时总收入
                    $incomeSchedule = ($lesson['cost'] * ($numScheduleStudent - $numGiftSchedule));
                    //$incomeScheulde1 = $incomeSchedule * (1-$this->setting['sysrebate']);
                    // 课时工资提成
                    $pushSalary = $schedule['salary_base'] * $numScheduleStudent;
                    $coachMember = $this->getCoachMember($schedule['coach_id']);

                    // 主教练薪资
                    $incomeCoach = [
                        'salary' => $schedule['coach_salary'],
                        'push_salary' => $pushSalary,
                        'member_id' => $coachMember['member']['id'],
                        'member' => $coachMember['member']['member'],
                        'realname' => $coachMember['coach'],
                        'member_type' => 4,
                        'pid' => $coachMember['member']['pid'],
                        'level' => $coachMember['member']['level'],
                        'schedule_id' => $schedule['id'],
                        'lesson_id' => $schedule['lesson_id'],
                        'lesson' => $schedule['lesson'],
                        'grade_id' => $schedule['grade_id'],
                        'grade' => $schedule['grade'],
                        'camp_id' => $schedule['camp_id'],
                        'camp' => $schedule['camp'],
                        'schedule_time' => $schedule['lesson_time'],
                        'status' => 1,
                        'type' => 1,
                    ];
                    $this->insertSalaryIn($incomeCoach);
                    
                    // 助教薪资
                    $incomeAssistant = [];
                    if (!empty($schedule['assistant_id']) && $schedule['assistant_salary']) {
                        $assistantMember = $this->getAssistantMember($schedule['assistant_id']);
                        foreach ($assistantMember as $k => $val) {
                            $incomeAssistant[$k] = [
                                'salary' => $schedule['assistant_salary'],
                                'push_salary' => $pushSalary,
                                'member_id' => $val['member']['id'],
                                'member' => $val['member']['member'],
                                'realname' => $val['coach'],
                                'member_type' => 3,
                                'pid' => $val['member']['pid'],
                                'level' => $val['member']['level'],
                                'schedule_id' => $schedule['id'],
                                'lesson_id' => $schedule['lesson_id'],
                                'lesson' => $schedule['lesson'],
                                'grade_id' => $schedule['grade_id'],
                                'grade' => $schedule['grade'],
                                'camp_id' => $schedule['camp_id'],
                                'camp' => $schedule['camp'],
                                'schedule_time' => $schedule['lesson_time'],
                                'status' => 1,
                                'type' => 1,
                            ];
                        }
                        //dump($incomeAssistant);
                        $this->insertSalaryIn($incomeAssistant, 1);
                    }

                    // 剩余为训练营所得 课时收入*抽取比例-主教底薪-助教底薪-课时工资提成*教练人数。教练人数 = 助教人数+1（1代表主教人数）
                    // 抽取比例：训练营有特定抽取比例以(1-特定抽取比例)计算|否则以(1-平台抽取比例)计算
                    $campScheduleRebate = db('camp')->where('id', $schedule['camp_id'])->value('schedule_rebate');
                    if (!empty($campScheduleRebate)) {
                        $scheduleRebate = (1 - $campScheduleRebate);
                    } else {
                        $scheduleRebate = (1 - $this->setting['sysrebate']);
                    }

                    $incomeCampSalary = $incomeSchedule * $scheduleRebate - $schedule['coach_salary'] - $schedule['assistant_salary'] - ($pushSalary * (count($incomeAssistant) + 1));
                    $incomeCamp = [
                        'income' => $incomeCampSalary,
                        'schedule_id'=>$schedule['id'],
                        'schedule_id' => $schedule['id'],
                        'lesson_id' => $schedule['lesson_id'],
                        'lesson' => $schedule['lesson'],
                        'camp_id' => $schedule['camp_id'],
                        'camp' => $schedule['camp'],
                        'schedule_time' => $schedule['lesson_time'],
                        'status' => 1,
                        'type' => 3,
                        'system_rebate'=>(1-$scheduleRebate),
                        'system_remarks' => $systemRemarks
                    ];
                    $this->insertIncome($incomeCamp);

                    // 保存训练营财务支出信息
                    $dataCampFinance = [
                        'camp_id' => $schedule['camp_id'],
                        'camp' => $schedule['camp'],
                        'finance_type' => 2,
                        'schedule_salary' => $incomeSchedule,
                        'schedule_id' => $schedule['id'],
                        'date' => date('Ymd', $schedule['lesson_time']),
                        'datetime' => $schedule['lesson_time']
                    ];
                    $this->insertcampfinance($dataCampFinance);

                    // 更新课时数据
                    Db::name('schedule')->where(['id' => $schedule['id']])->update(['is_settle' => 1, 'schedule_income' => $incomeSchedule, 'finish_settle_time' => time()]);
                    db('schedule_member')->where(['schedule_id' => $schedule['id']])->update(['status' => 1, 'update_time' => time()]);
                    // 课时工资收入结算 end
                }
            });
        } catch (Exception $e) {
            // 记录日志：错误信息
            trace($e->getMessage(), 'error');
        }
    }

    // 结算上一个月收入 会员分成
    public function salaryinrebate(){
        try {
            list($start, $end) = Time::lastMonth();
            $map['status'] = 1;
            $map['has_rebate'] = 0;
            $map['create_time'] = ['between', [$start, $end]];
            $salaryins = DB::name('salary_in')->field(['member_id', 'sum(salary)+sum(push_salary)'=>'month_salary'])->where($map)->group('member_id')->where('delete_time', null)->select();
            $datemonth = date('Ym', $end);
            foreach ($salaryins as $salaryin) {
                //dump($salaryin);
                if ($salaryin['month_salary'] >0 ){
                    $res = $this->insertRebate($salaryin['member_id'], $salaryin['month_salary'], $datemonth);
                    if (!$res) { continue; }
                }
            }
            DB::name('salary_in')->where($map)->update(['has_rebate' => 1]);
        }catch (Exception $e) {
            // 记录日志：错误信息
            trace($e->getMessage(), 'error');
        }
    }

    // 获取教练会员
    private function getCoachMember($coach_id) {
        $coachM = new Coach();
        $member = $coachM->with('member')->where(['id' => $coach_id])->find();
        if ($member) {
            return $member->toArray();
        }
    }

    // 获取营主会员
    private function getCampMember($camp_id) {
        $member = Db::view('member')
            ->view('camp', '*','camp.member_id=member.id')
            ->where(['camp.id' => $camp_id])
            ->order('camp.id desc')
            ->find();
        return $member;
    }

    // 获取助教会员
    private function getAssistantMember($assistant_id) {
        $assistants = unserialize($assistant_id);
        $member = [];
        $coachM = new Coach();
        foreach( $assistants as $k => $assistant ) {
            $member[$k] = $coachM->with('member')->where(['id' => $assistant])->find()->toArray();
        }
        return $member;
    }

    // 保存收入记录
    private function insertSalaryIn($data, $saveAll=0) {
        $model = new \app\model\SalaryIn();
        if ($saveAll == 1) {
            $execute = $model->allowField(true)->saveAll($data);
        } else {
            $execute = $model->allowField(true)->save($data);
        }
        if ($execute) {
            $memberDb = db('member');
            if ($saveAll ==1) {
                foreach ($data as $val) {
                    $memberDb->where('id', $val['member_id'])->setInc('balance', $val['salary']+$val['push_salary']);
                }
            } else {
                $memberDb->where('id', $data['member_id'])->setInc('balance', $data['salary']+$data['push_salary']);
            }
            file_put_contents(ROOT_PATH.'data/salaryin/'.date('Y-m-d',time()).'.txt', json_encode(['time'=>date('Y-m-d H:i:s',time()), 'success'=>$data], JSON_UNESCAPED_UNICODE).PHP_EOL, FILE_APPEND );
            return true;
        } else {
            file_put_contents(ROOT_PATH.'data/salaryin/'.date('Y-m-d',time()).'.txt',json_encode(['time'=>date('Y-m-d H:i:s',time()), 'error'=>$data], JSON_UNESCAPED_UNICODE).PHP_EOL, FILE_APPEND  );
            return false;
        }
    }
     // 保存课时收入记录
    private function insertIncome($data, $saveAll=0) {
        $model = new \app\model\Income();
        if ($saveAll == 1) {
            $execute = $model->allowField(true)->saveAll($data);
        } else {
            $execute = $model->allowField(true)->save($data);
        }
        if ($execute) {
            $campDb = db('camp');
            if ($saveAll ==1) {
                foreach ($data as $val) {
                    $campDb->where('id', $val['camp_id'])->inc('balance_true', $val['income'])->update();
                }
            } else {
                $campDb->where('id', $data['camp_id'])->inc('balance_true', $data['income'])->update();
            }
            file_put_contents(ROOT_PATH.'data/income/'.date('Y-m-d',time()).'.txt', json_encode(['time'=>date('Y-m-d H:i:s',time()), 'success'=>$data], JSON_UNESCAPED_UNICODE).PHP_EOL, FILE_APPEND );
            return true;
        } else {
            file_put_contents(ROOT_PATH.'data/income/'.date('Y-m-d',time()).'.txt',json_encode(['time'=>date('Y-m-d H:i:s',time()), 'error'=>$data], JSON_UNESCAPED_UNICODE).PHP_EOL, FILE_APPEND  );
            return false;
        }
    }

    // 保存会员分成记录
    private function insertRebate($member_id, $salary, $datemonth) {
        $memberS = new MemberService();
        $model = new Rebate();
        $memberPiers = $memberS->getMemberPier($member_id);
        if (!empty($memberPiers)) {
            foreach ($memberPiers as $k => $memberPier) {
                if ($memberPier['tier']==1) {
                    $memberPiers[$k]['salary'] = $salary*$this->setting['rebate'];
                } elseif ($memberPier['tier']==2){
                    $memberPiers[$k]['salary'] = $salary*$this->setting['rebate2'];
                }
                $memberPiers[$k]['datemonth'] = $datemonth;
            }
            //dump($memberPiers);
            $execute = $model->allowField(true)->saveAll($memberPiers);
            if ($execute) {
                $memberDb = db('member');
                foreach ($memberPiers as $member) {
                    $memberDb->where('id', $member['member_id'])->setInc('balance', $member['salary']);
                }
                file_put_contents(ROOT_PATH.'data/rebate/'.date('Y-m-d',time()).'.txt',json_encode(['time'=>date('Y-m-d H:i:s',time()), 'success'=>$memberPiers], JSON_UNESCAPED_UNICODE).PHP_EOL, FILE_APPEND  );
                return true;
            } else {
                file_put_contents(ROOT_PATH.'data/rebate/'.date('Y-m-d',time()).'.txt',json_encode(['time'=>date('Y-m-d H:i:s',time()), 'error'=>$memberPiers], JSON_UNESCAPED_UNICODE).PHP_EOL, FILE_APPEND );
                return false;
            }
        }
    }

    // 保存训练营财务记录
    private function insertcampfinance($data, $saveAll=0) {
        $model = new CampFinance();
        if ($saveAll == 1) {
            $model->allowField(true)->saveAll($data);
        } else {
            $model->allowField(true)->save($data);
        }
    }


    // 统计更新教练流量数字段
    public function coachflowcounter() {
        try {
            // 遍历coach数据
            $coachs = db('coach')->select();
            // 教练service
            $coachService = new CoachService();
            //dump($coachs);
            $dataSaveAll = [];
            foreach ($coachs as $key => $coach) {
                // 课程流量
                $lessonFlow = $coachService->lessoncount($coach['id']);
                // 班级流量
                $gradeList = $coachService->ingradelist($coach['id']);
                //dump($gradeList);
                $gradeFlow = count($gradeList);
                // 学员流量
                $studentFlow = $coachService->teachstudents($coach['id']);
                // 课时流量
                $scheduleFlow = $coachService->schedulecount($coach['id']);
                $dataSaveAll[$key]['id'] = $coach['id'];
                $dataSaveAll[$key]['lesson_flow'] = $lessonFlow;
                $dataSaveAll[$key]['grade_flow'] = $gradeFlow;
                $dataSaveAll[$key]['student_flow'] = $studentFlow+$coach['student_flow_init'];
                $dataSaveAll[$key]['schedule_flow'] = $scheduleFlow+$coach['schedule_flow_init'];

                // 顺手整理introduction值为图文内容格式
                $newIntroduction = '<div class="operationDiv"><p>'. $coach['introduction'] .'</p></div>';
                //$dataSaveAll[$key]['introduction'] = $newIntroduction;
            }
            //dump($dataSaveAll);
            // 批量更新
            $modelCoach = new Coach();
            $res = $modelCoach->saveAll($dataSaveAll);
            return json($res);
        } catch (Exception $e) {
            dump($e->getMessage());
        }
    }
}