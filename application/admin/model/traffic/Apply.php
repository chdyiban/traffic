<?php

namespace app\admin\model\traffic;

use think\Model;
use think\Db;

class Apply extends Model
{

    

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'traffic_apply_list';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'step_text'
    ];
    
    public function getStepList()
    {
        return ['0' => __('Step 0'), '1' => __('Step 1'), '2' => __('Step 2'), '3' => __('Step 3'), '4' => __('Step 4'),'5' => __('Step 5')];
    }


    public function getStepTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['step']) ? $data['step'] : '');
        $list = $this->getStepList();
        return isset($list[$value]) ? $list[$value] : '';
    }

       //获取录入用户名称
	public function getadminname(){
		return $this->belongsTo('app\admin\model\Admin', 'admin_id')->seteagerlytype(0);
	}

	/**
	 * 提交管理员审核意见
	 */
	public function submitAdmin($data)
	{
		$ID = $data["ID"];
		$opinion = $data["opinion"];
		$step    = $data["step"];
		$adminId = $data["adminId"];
		$advise  = $data["advise"];
		$reason  = $data["reason"];
		$insertData = [
			"apply_id"      => $ID,
			"old_step"      => $step,
			"handle_time"   => time(),
			"handle_admin"  => $adminId,
			"handle_extra"  => empty($advise) ? $reason : $advise,
		];
		switch ($opinion) {
			//同意则状态进行至下一阶段
			case 0:
				if ($step != 4) {
					$updateData = ["status_admin" => 0,"status_user" => 1,"step" => $step+1];
					$res = $this->where("ID",$ID)->update($updateData);
					$insertData["new_step"] = $step+1;
					$insertData["handle_content"] = "通过";
					$response = Db::name("traffic_apply_detail")->insert($insertData);
					return $res&&$response;
				} else {
					$updateData = ["status_admin" => 0,"status_user" => 0];
					$res = $this->where("ID",$ID)->update($updateData);
					$insertData["new_step"] = $step;
					$insertData["handle_content"] = "通过";
					$response = Db::name("traffic_apply_detail")->insert($insertData);
					return $res&&$response;
				}
				break;
			//不通过
			case 1:
				$updateData = ["status_admin" => 0,"status_user" => 0,"step" => 5];
				$res = $this->where("ID",$ID)->update($updateData);
				$insertData["new_step"] = 5;
				$insertData["handle_content"] = "不通过";
				$response = Db::name("traffic_apply_detail")->insert($insertData);
				return $res&&$response;
				break;
			//修改后再提交则状态不变
			case 2:
				$updateData = ["status_admin" => 0,"status_user" => 1];
				$res = $this->where("ID",$ID)->update($updateData);
				$insertData["new_step"] = $step;
				$insertData["handle_content"] = "修改后再次提交";
				$response = Db::name("traffic_apply_detail")->insert($insertData);
				return $res&&$response;
				break;
		}
	}
	
	/**
	 * 提交业务单位用户
	 */
	public function submitUser($data,$url)
	{
		$apply_id  = $data["ID"];
		$step      = $data["step"];
		$userID    = $data["adminId"];
		switch ($step) {
			//项目初始不同意需要修改
			case 0:
				$data = "";
				break;
			//立项提交草案，项目申报书
			case 1:
				$XMSBS = $url.$data["XMSBS"];
				$CA    = $url.$data["CA"];
				$data = json_encode(["XMSBS" => $XMSBS,"CA" => $CA]);
				break;	
				//征求意见提交征求意见稿,编制说明
			case 2:
				$ZQYJG = $url.$data["ZQYJG"];
				$BZSM    = $url.$data["BZSM"];
				$data = json_encode(["ZQYJG" => $ZQYJG,"BZSM" => $BZSM]);
				break;	
				//送审阶段提交送审稿,编制说明
			case 3:
				$SSG     = $url.$data["SSG"];
				$BZSM    = $url.$data["BZSM"];
				$data = json_encode(["SSG" => $SSG,"BZSM" => $BZSM]);	
				break;	
			//报批阶段提交报批稿，编制说明
			case 4:
				$BPG     = $url.$data["BPG"];
				$BZSM    = $url.$data["BZSM"];
				$data = json_encode(["BPG" => $BPG,"BZSM" => $BZSM]);
				break;	
		}
		//插入数据库
		$updateData = [
			"response_time"  => time(),
			"response_user" => $userID,
			"response_content" => $data,
		];
		$res = Db::name("traffic_apply_list")
				-> where("ID",$apply_id)
				-> update([
					"status_admin" => 1,
					"status_user"  => 0.
				]);
		$res = Db::name("traffic_apply_detail")
				->where("apply_id",$apply_id)
				->order("handle_time desc")
				->limit(1)
				->field("ID")
				->find()["ID"];
		$response = Db::name("traffic_apply_detail")
				->where("ID",$res)
				->update($updateData);
		return $res&&$response;
	}
}
