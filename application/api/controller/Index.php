<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;

/**
 * 首页接口
 */
class Index extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function index()
    {
        $this->success('请求成功');
        // $list = Db::view("employees_info")
        //             -> view("employees_marry","YGID,POXM,PONL,POZY","employees_info.ID = employees_marry.YGID","LEFT")
        //             -> view("employees_school","YGID,XL,ZY,BYSJ,XX,WYQK","employees_info.ID = employees_school.YGID","LEFT")
        //             -> view("employees_position","ZWMC","employees_info.ZC = employees_position.ID")
        //             -> view("employees_department","BMMC","employees_info.GZBM = employees_department.ID")
        //             -> select();
        // dump(Db::getLastSql());
        // dump($list);
    }
}
