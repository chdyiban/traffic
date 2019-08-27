<?php

namespace app\admin\model\traffic;

use think\Model;


class Project extends Model
{

    

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'traffic_project_list';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

	//获取业务单位用户名称
	public function getywdwyhname(){
		return $this->belongsTo('app\admin\model\Admin', 'YWDWYH')->field("nickname,id")->seteagerlytype(0);
    }
	
	//获取项目单位用户名称
	public function getxmyhname(){
		return $this->belongsTo('app\admin\model\Admin', 'XMYH')->field("nickname,id")->seteagerlytype(0);
    }

	//上传文件
	public function upload($data)
	{
		$ID       = $data["ID"];
		$SJZD     = $data["SJZD"];
		$database = $data["database"];
		$SJZDfile = explode(",",$SJZD);
		$databasefile = explode(",",$database);
		$url      = $data["url"];
		foreach ($SJZDfile as $key => &$value) {
			$value = $url.$value;
		}
		foreach ($databasefile as $key => &$value) {
			$value = $url.$value;
		}
		$updateData = [
			"SJZD"     => $SJZDfile,
			"database" => $databasefile,
		];
		$result = $this->where("ID",$ID)->update(["file" => json_encode($updateData)]);
		return $result;
	}




}
