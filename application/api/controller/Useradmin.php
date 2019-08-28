<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use app\admin\model\Admin;
use app\admin\model\AuthGroup;
use app\admin\model\AuthGroupAccess;
use fast\Random;
use think\Validate;
use think\Db;

/**
 * 注册管理员接口
 */
class Useradmin extends Api
{
    // protected $noNeedLogin = ['login', 'mobilelogin', 'register', 'resetpwd', 'changeemail', 'changemobile', 'third'];
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
        header('Access-Control-Allow-Origin:*');  
    }

    /**
     * 注册管理员
     *
     * @param string username 用户名
     * @param string nickname 昵称
     * @param string password 密码
     * @param string status 登录状态 
     * @param string email    邮箱
     * @param string mobile   手机号
     */
    public function register()
    {
        if ($this->request->isPost())
        {
            $adminModel = new Admin();
            $authGroupModel = new AuthGroup();
            $AuthGroupAccess = new AuthGroupAccess();
            $params = $this->request->param();
            if ($params)
            {
                $params['salt'] = Random::alnum();
                $params['password'] = md5(md5($params['password']) . $params['salt']);
                $params['avatar'] = '/assets/img/avatar.png'; //设置新管理员默认头像。
                $params["nickname"] = $params["name"];
                unset($params["name"]);
                $params["status"] = "hidden";
                if ($params["radio"] == "XMYH") {
                    $group = $authGroupModel->where("name","项目用户")->find()["id"];
                } else if ($params["radio"] == "YWDWYH") {
                    $group = $authGroupModel->where("name","业务单位用户")->find()["id"];
                }
                unset($params["radio"]);
                unset($params["passwordAgain"]);
                // unset($params["projectId"]);
                //判断用户是否存在
                $checkInfo = Db::name("admin")->where("username",$params["username"])->find();
                if (!empty($checkInfo)) {
                    $info = ["code" => 10,"msg" => "用户名重复"];
                    return json($info);
                }
                $result = $adminModel->save($params);
                if (!$result)
                {
                    //$adminModel->getError()
                    $info = ["code" => 10,"msg" => "用户名重复"];
                    return json($info);
                }

                $dataset = ['uid' => $adminModel->id, 'group_id' => $group];
                $AuthGroupAccess->save($dataset);
                $info = ["code" => 0,"msg" => "注册成功，请等待后台审核"];
                return json($info);
            }
            $info = ["code" => 11,"msg" => "param error!"];
        }
        $info = ["code" => 12,"msg" =>"request error!"];
        return json($info);
    }

}
