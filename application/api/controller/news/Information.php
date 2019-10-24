<?php

namespace app\api\controller\news;

use addons\cms\model\Archives as ArchivesModel;
use addons\cms\model\Tags as TagsModel;
use app\api\model\News as NewsModel;
use addons\cms\model\Channel;
use addons\cms\model\Comment;
use addons\cms\model\Modelx;
use app\common\controller\Api;
use think\Db;
use app\api\model\Wxuser as WxuserModel;
/**
 * 资讯栏目控制器
 */
class Information extends Api
{

    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    function _initialize(){
        header('Access-Control-Allow-Origin:*');
    }
    //
    public function index()
    {
        // $page = (int) $this->request->get('page');
        // $model = (int) $this->request->request('model');
        // $channel = (int) $this->request->request('channel');
        // //判断是通过标签搜索返回结果

        // if ($model) {
        //     $params['model'] = $model;
        // }
        // if ($channel) {
        //     $params['channel'] = $channel;
        // }
        // $page = max(1, $page);
        // $params['limit'] = ($page - 1) * 10 . ',10';
        // $params['orderby'] = 'id';

        $result = [
            "code" => 0,
            "data" => [],
        ];
        $widthArray = [
            "标准发布"  =>  16,
            "通知公告"  =>  8,
            "评价公告"  =>  16,
            "政策法规"  =>  8
        ];
        $all = collection(Channel::order("weigh desc,id desc")->select())->toArray();
        foreach ($all as $k => $v) {
            if ($v["parent_id"] == 1) {
                $result["data"][] = [ 
                    'category'       => $v["id"],
                    'category_name'  => $v['name'],
                    'category_url'   => $v['fullurl'],
                    "list"           => [],
                    "width"          => !empty($widthArray[$v["name"]]) ? $widthArray[$v["name"]] : "",
                ];
            }
        }
        //$list = ArchivesModel::getWeAppArchivesList($params);
        // $i = 1;
        foreach ($result["data"] as $key => &$value) {
            // $params["type"]    =  "son";
            $params["channel"] =  $value["category"];
            $list = ArchivesModel::getArchivesList($params);
            $list = array_reverse($list,true);
            foreach ($list as $k => $v) {
                if ($k < 5) {
                    $value["list"][] = [
                        "id"     =>    $v["id"],
                        "title"  =>    $v["title"],
                        "date"   =>    date("Y-m-d",$v["createtime"]),
                        "url"    =>    "/content/".$v["id"],
                        "image"  =>    $v["image"],
                    ];
                }
            }
            // $value["category"] = $i;
            // $i++;
        }
        // dump($list);
        
        return json($result);
    }

    //列表页
    public function newslist()
    {
        $page = (int) $this->request->get('page');
        $model = (int) $this->request->request('model');
        // $channel = (int) $this->request->request('channel');
        $channel = (int) $this->request->request('category_id');
        //判断是通过标签搜索返回结果

        if ($model) {
            $params['model'] = $model;
        }
        if ($channel) {
            $params['channel'] = $channel;
        }
        $page = max(1, $page);
        $params['limit'] = ($page - 1) * 10 . ',10';
        $params['orderby'] = 'id';

        $result = [
            "code" => 0,
            "data" => [
                "total"   => 0,
                "current" => $page,
                "list"    => [],
            ],
        ];

        $list = ArchivesModel::getArchivesList($params);
        foreach ($list as $k => $v) {
            $result["data"]["list"][] = [
                "id"     =>    $v["id"],
                "title"  =>    $v["title"],
                "date"   =>    date("Y-m-d",$v["createtime"]),
                "url"    =>    $v["fullurl"],
                "image"  =>    $v["image"],
            ];
        }
        //目前返回的是总条数，是否需要改为总页数
        $result["data"]["total"] = count($list);
        // dump($list);
        
        return json($result);
    }


    //新闻页导航
    public function nav()
    {
        $result = [
            "code" => 0,
            "data" => [],
        ];
        $all = collection(Channel::order("weigh desc,id desc")->select())->toArray();
        // $i = 1;
        foreach ($all as $k => $v) {
            if ($v["id"] == 1 && $v["type"] == "channel") {
                $result["data"]["id"] = $v["id"];
                $result["data"]["module"] = $v["name"];
            }
            if ($v["parent_id"] == 1) {
                $result["data"]["list"][] = [ 
                    'category'       => $v["id"],
                    'category_name'  => $v['name'],
                    'category_url'   => $v['fullurl'],
                ];
                // $i = $i + 1;
            }
        }
        // dump($result);
        return json($result);
    }


    //内容详情页
    public function content()
    {
        $article_id = $this->request->param('id');
        $diyname = $this->request->param('diyname');
        if ($diyname && !is_numeric($diyname)) {
            $archives = ArchivesModel::getByDiyname($diyname);
        } else {
            $id = $diyname ? $diyname : $this->request->request('id', '');
            $archives = ArchivesModel::get($id);
        }
        if (!$archives || $archives['status'] == 'hidden' || $archives['deletetime']) {
            $this->error(__('No specified article found'));
        }
        $channel = Channel::get($archives['channel_id']);
        if (!$channel) {
            $this->error(__('No specified channel found'));
        }
        $model = Modelx::get($channel['model_id']);
        if (!$model) {
            $this->error(__('No specified model found'));
        }
        $archives->setInc("views", 1);
        $addon = db($model['table'])->where('id', $archives['id'])->find();
        if ($addon) {
            $archives = array_merge($archives->toArray(), $addon);
        }

        $commentList = Comment::getCommentList(['aid' => $archives['id']]);
        // unset($channel['channeltpl'], $channel['listtpl'], $channel['showtpl'], $channel['status'], $channel['weigh'], $channel['parent_id']);
        // $this->success('', ['archivesInfo' => $archives, 'channelInfo' => $channel, 'commentList' => $commentList]);
        $result = [
            "code" => 0,
            "data" => [
                "title"   => $archives["title"],
                "content" => $archives["content"],
                "date"    => date("Y-m-d H:i:s",$archives["createtime"]),
                "author" => $archives["author"],
                "source" => "",
            ],
        ];

        return json($result);
    }




}