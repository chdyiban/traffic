<?php

namespace app\admin\controller\traffic;

use app\common\controller\Backend;
use think\Db;

/**
 * 标准立项申请管理---管理员审核模块
 *
 * @icon fa fa-circle-o
 */
class Audit extends Backend
{
    
    /**
     * Audit模型对象
     * @var \app\admin\model\traffic\Audit
     */
    protected $model = null;
    protected $relationSearch = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\traffic\Audit;
        $this->view->assign("stepList", $this->model->getStepList());
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
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            //如果是业务单位用户只看自己提交的内容
            $map = [];
            if ($this->adminGroupId == $this->businessAdminId) {
                $map["admin_id"] = $this->adminId;
            }
            $total = $this->model
                ->where($where)
                ->where($map)
                ->with("getadminname")
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->where($map)
                ->with("getadminname")
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
        $now_admin_id = $this->auth->id;
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
                    
                    //如果是业务单位进行修改则判断是否是响应审核
                    if ($this->adminGroupId == $this->businessAdminId) {
                    if ($params["step"] == 0) {
                        $params["adminId"] = $this->adminId;
                        $params["ID"]      = $ids;
                        $this->model->submitUser($params,"");
                    }
                    }
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 处理用户申请，若判断为管理员账号则进行审核，若为用户则上传文件
     */
    public function detail($ids)
    {
        $row = Db::name("traffic_apply_list")->where('ID',$ids)->find();
        if (!$row)
			$this->error(__('No Results were found'));

		$row["now_time"] = date("Y-m-d H:i",time());
		$stepList = $this->model->getStepList();
		$row["stepText"] = $stepList[$row["step"]];
        $row["groupName"] = $this->adminGroupInfoArray[0]["name"];
        $historyHandle = [];
        $historyHandle = Db::name("traffic_apply_detail")
                        ->where("apply_id",$ids)
                        ->order("handle_time asc")
                        ->select();
        $adminList = Db::name("admin")->field("id,nickname")->select();
        foreach ($adminList as $key => $value) {
            $mapAdmin[$value["id"]] = $value["nickname"];
        }
        $tagsAdmin = $row["status_admin"] == 1 ? true :false;
        $tagsUser  = $row["status_user"]  == 1 ? true :false;
        //$tagsAdmin为true，管理员可以审核，
        //$tagsUser为true，用户可以上传文件,
        if (!empty($historyHandle)) {
            //判断用户是否上传以及判断申请是否被审核
            foreach ($historyHandle as $key => &$value) {
                $value["handle_name"]   = $mapAdmin[$value["handle_admin"]];
                $value["response_name"] = !empty($value["response_user"]) ? $mapAdmin[$value["response_user"]] : "";
                $value["stepText"] = $stepList[$value["new_step"]];
                $value["file"] = json_decode($value["response_content"],true);
            }
        }
        // dump($historyHandle);
        $row["tagsAdmin"] = $tagsAdmin;
        $row["tagsUser"]  = $tagsUser;

        if ($this->adminGroupId == $this->businessAdminId) {

			return $this->view->fetch("detail_user",["row" => $row,"historyHandle" => $historyHandle]);
			
        } elseif ($this->adminGroupId == $this->backAdminId || $this->adminId == 1) {
        
			return $this->view->fetch("detail_admin",["row" => $row,"historyHandle" => $historyHandle]);
			
        } else {
			$this->error("error!");
		}
	}
	
	/**
	 * 提交方法
	 */
	public function submit()
	{
		if ($this->request->isAjax()) {
			$data = $this->request->param();
			$data["adminId"] = $this->adminId;
			if ($data["user"] == "admin") {
				$result = $this->model->submitAdmin($data);
				return $result;
			} elseif ($data["user"] == "user") {
                $url = $this->request->domain().$this->request->root();
                $result = $this->model->submitUser($data,$url);
				return $result;
			}
		} else {
			$this->error("request error!");
		}
	}


}
