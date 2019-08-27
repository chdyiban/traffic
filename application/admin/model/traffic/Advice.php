<?php

namespace app\admin\model\traffic;

use think\Model;

class Advice extends Model
{

    

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'traffic_advice_list';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
		'status_text'
	];
	
	public function getStatusList()
    {
        // return ['0' => __('status 0'), '1' => __('status 1'), '2' => __('status 2'), '3' => __('status 3')];
       return ['0' => __('修改'), '1' => __('提议'), '2' => __('新增'), '3' => __('废止')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    //获取录入用户名称
	public function getadminname(){
		return $this->belongsTo('app\admin\model\Admin', 'admin_id')->seteagerlytype(0);
  	}
	
	//获取录入用户组别
	public function getgroupname(){
		return $this->belongsTo('app\admin\model\AuthGroup', 'group_id')->seteagerlytype(0);
  }








}
