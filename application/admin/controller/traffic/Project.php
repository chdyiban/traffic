<?php

namespace app\admin\controller\traffic;

use app\common\controller\Backend;
use think\Db;
/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Project extends Backend
{
    
    /**
     * Project模型对象
     * @var \app\admin\model\traffic\Project
     */
    protected $model = null;
    protected $relationSearch = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\traffic\Project;

        //当前管理员ID
        $this->adminId = $this->auth->id;
        //当前管理员的分组信息
        $this->adminGroupInfoArray = $this->auth->getGroups();
        //当前管理员分组ID
        $this->adminGroupId = $this->auth->getGroupIds()[0];
        //获取后台管理组id

        $idList = Db::name("auth_group")->select();
        foreach ($idList as $key => $value) {
            if ($value["name"] == "后台管理组") {
                $this->backAdminId = $value["id"];
            } elseif ($value["name"]  == "业务单位用户") {
                $this->businessAdminId = $value["id"];
            } elseif ($value["name"] == "项目用户") {
                $this->projectAdminId  = $value["id"];
            }
        }
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    
     /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            $map = [];
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            //如果是项目用户则只能看到自己绑定的项目
            if ($this->adminGroupId == $this->projectAdminId) {
                $map["XMYH"] = $this->adminId;
            } 
            $total = $this->model
                ->where($where)
                ->where($map)
                ->with("getywdwyhname,getxmyhname")
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->where($map)
                ->with("getywdwyhname,getxmyhname")
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

      /**
     * 添加
     */
    public function add()
    {
        $now_admin_id = $this->adminId;
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);

                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validateFailException(true)->validate($validate);
                    }
                    $result = $this->model->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign(["adminId" => $now_admin_id]);
        return $this->view->fetch();
    }


    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $adminName = Db::name("admin")->where("id",$row["YWDWYH"])->field("nickname")->find()["nickname"];
        
        $this->view->assign("adminName", $adminName);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 获取项目用户人员供分配
     * 
     */
    public function getXmyhJson()
    {
        $projectIdList = Db::view("auth_group_access")
                        ->view("admin","nickname,id","auth_group_access.uid = admin.id")
                        ->where("group_id",$this->projectAdminId)
                        ->select();
        $return = array();

        if (empty($projectIdList)) {
            $return[] = [
                "value" => 0,
                "name"  => "尚未存在项目用户",
            ];
        } else {
            foreach ($projectIdList as $key => $value) {  
                $temp = [
                    "value"    => $value["id"],
                    "name" => $value["nickname"],
                ];
                $return[] = $temp;
            }
        }
        $this->success('', null, $return);
    }

    /**
	 * 获取项目用户名单供搜索
	 */
	public function getXmyhNicknameJson()
	{
        $adminIdList = Db::view("auth_group_access")
                        ->view("admin","nickname,id","auth_group_access.uid = admin.id")
                        ->where("group_id",$this->projectAdminId)
                        ->select();
        $return = [];
        foreach ($adminIdList as $key => $value) {
            $result[$value["nickname"]] = $value["nickname"];
        }
		return json($result);
    }
    

    /**
	 * 获取业务单位用户名单供搜索
	 */
	public function getYwdwyhNicknameJson()
	{
        $adminIdList = Db::view("auth_group_access")
                        ->view("admin","nickname,id","auth_group_access.uid = admin.id")
                        ->where("group_id",$this->businessAdminId)
                        ->select();
        $return = [];
        foreach ($adminIdList as $key => $value) {
            $result[$value["nickname"]] = $value["nickname"];
        }
		return json($result);
    }
    
    //项目用户上传资料
    //目前项目用户上传的资料会进行覆盖
    public function upload($ids)
    {
       
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            $data["projectId"] = $this->adminId;
            $data["url"] = $this->request->domain().$this->request->root();
            $result = $this->model->upload($data);
            return $result;
        }
        $fileUrl = Db::name("traffic_project_list")
                -> where("ID",$ids)
                -> field("file")
                -> find()["file"];
        $fileArray = json_decode($fileUrl,true);
        if (!empty($fileArray)) {
            $this->view->assign([
                "SJZD"    => $fileArray["SJZD"],
                "database"=> $fileArray["database"],
            ]);
            
        }
        $this->view->assign(["ID" => $ids]);
        return $this->view->fetch();
    }

}
