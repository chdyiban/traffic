<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;
use app\api\model\Bztx as BztxModel;
/**
 * 标准体系
 */
class Bztx extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];


    public function query()
    {
        header('Access-Control-Allow-Origin:*');  
        $type = $this->request->get("type");
        $BztxModel = new BztxModel();
        $param = $this->request->get();
        $result = array();
        switch ($type) {
            case 'xtd':
                $result = $BztxModel -> getXTD($param);
                break;
            case 'sx':
                $result = $BztxModel -> getSX($param);
                break;
            case 'jtb':
                $result = $BztxModel -> getJTB($param);
                break;
            case 'gb':
                $result = $BztxModel -> getGBW($param);
                break;
        }
        return json($result);
    }
}