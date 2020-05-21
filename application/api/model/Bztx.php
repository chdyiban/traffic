<?php

namespace app\api\model;

use think\Model;
use fast\Http;
use think\Db;

class Bztx extends Model
{
    // 表名
    protected $name = 'bztx_sx';
    const getXtdUrl = "http://jtst.mot.gov.cn/search/archPage";
    const arch    = "17377f1d7f6409d3dc482dabe859d8d9";
    const baseUrl   = "http://jtst.mot.gov.cn/eap"; 

    const getJtbUrl = "http://jtst.mot.gov.cn/eap/BzAction.do";
    const getGbwUrl = "http://openstd.samr.gov.cn/bzgk/gb/std_list";
    const GBW_DETAIL_URL = "http://openstd.samr.gov.cn/bzgk/gb/newGbInfo?hcno=";


    public function getXTD($param)
    {
        $mcode = isset($param["mcode"]) ? $param["mcode"] : "";
        // $mcode = 17;
        $bzcode = isset($param["bzcode"]) ? $param["bzcode"] : "";
        $bzname = isset($param["bzname"]) ? $param["bzname"] : "";
        $page = isset($param["page"]) ? $param["page"]: 1;
        $pageSize = isset($param["pageSize"]) ? $param["pageSize"]: 10;
        $page = (int)$page-1;
        // dump($page);
        $url = self::getXtdUrl;
        $dataArray = [
            // "mcode"         => (int)$mcode,
            // "bzcode"        => $bzcode,
            "searchText"    => $bzname,
            "pageNumber"    => (int)$page,
            "pageSize"      => (int)$pageSize,
            "arch"           => self::arch,
        ];
        // dump($dataArray);
        $postData = http_build_query($dataArray);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Referer : http://jtst.mot.gov.cn/eap/BzTxAction.do?act=listHome',
                'User-Agent : Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Origin: http://jtst.mot.gov.cn',
                "Accept-Encoding: gzip, deflate",
                "Accept-Language: zh-CN,zh;q=0.9",
                "Host: jtst.mot.gov.cn",
            )
        );
        $html = curl_exec($ch); 
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        

        $return = [
            "code" => 0,
            "data" => [
                "mocode"  => $mcode,
                "bzcode"  => $bzcode,
                "bzname"  => $bzname,
                "rid"     => self::arch,
                "page"    => $page+1,
                "allPage" => 1,
                "total"   => 1,
                "list"    => [],
            ]
        ];
        
    
        $temp = [
            "BZTXBH" => "--------",
            "BZBH"   => "--------",
            "CKXQ"   => "--------",
            "BZMC"   => "--------",
            "YDJB"   => "--------",
            "SSRQ"   => "--------",
            "CYGX"   => "--------",
            "DTBZH"  => "--------",
            "BZ"     => "此网站系统关闭，暂停服务。",
        ];
        $return["data"]["list"][] = $temp;
        
        // $return = [
        //     "code" => 0,
        //     "data" => [
        //         "mocode"  => $mcode,
        //         "bzcode"  => $bzcode,
        //         "bzname"  => $bzname,
        //         "rid"     => self::xtdrid,
        //         "page"    => $page+1,
        //         "allPage" => (int)$all_page,
        //         "total"   => (int)$all_count,
        //         "list"    => [],
        //     ]
        // ];
        // $len = count($data_list);
        // if ($len == 0) {
        //     return $return;
        // }
        
        // for ($i = 0; $i < $len; $i++) { 
        //     $temp = [
        //         "BZTXBH" => $data_list[$i][2],
        //         "BZBH"   => $data_list[$i][3],
        //         "CKXQ"   => self::baseUrl.substr($data_list[$i][4],1),
        //         "BZMC"   => $data_list[$i][5],
        //         "YDJB"   => $data_list[$i][6],
        //         "SSRQ"   => $data_list[$i][7],
        //         "CYGX"   => $data_list[$i][8],
        //         "DTBZH"  => $data_list[$i][9],
        //         "BZ"     => $data_list[$i][10],
        //     ];
        //     $return["data"]["list"][] = $temp;
        // }
        return $return;
    }


    public function getSX($param)
    {
        $mcode  = isset($param["mcode"])  ? $param["mcode"] : "";
        $bzcode = isset($param["bzcode"]) ? $param["bzcode"] : "";
        $bzname = isset($param["bzname"]) ? $param["bzname"] : "";
        $page = isset($param["page"]) ? $param["page"]: 1;
        $limit = 20;
        $list = $this->where("TXDM","LIKE","%$mcode%")
                    -> where("BZBH","LIKE","%$bzcode%")
                    -> where("BZMC","LIKE","%$bzname%")
                    -> limit($limit)
                    -> page($page)
                    -> select();
        // dump($list);
        $all_count = $this->where("TXDM","LIKE","%$mcode%")
                    -> where("BZBH","LIKE","%$bzcode%")
                    -> where("BZMC","LIKE","%$bzname%")
                    -> count();
        

        $all_page = $all_count/$limit;
        $all_page = (int)$all_page+1;
        $return = [
            "code" => 0,
            "data" => [
                "total"  => $all_count,
                "allPage"=> $all_page,
                "mcode"  => $mcode,
                "bzcode" => $bzcode,
                "bzname" => $bzname,
                "page"   => $page,
                "list"   => [],
            ]
        ];

        if (empty($list)) {
            return $return;
        } else {
            foreach ($list as $key => &$value) {
               unset($value["ID"]);
               $return["data"]["list"][] = $value->toArray();
            }
            // dump($return);
            return $return;
        }
    }

    public function getJTB($param)
    {
        
        // $mcode = isset($param["mcode"]) ? $param["bzcode"] : "";
        // $bzcode = isset($param["bzcode"]) ? $param["bzcode"] : "";
        $bzname = isset($param["bzname"]) ? $param["bzname"] : "";
        $page = isset($param["page"]) ? $param["page"]: 1;
        $page = (int)$page;
        $is_new = $page == 1 ? 1 : "";
        $url = self::getJtbUrl;
        $dataArray = [
            // "mcode"  => (int)$mcode,
            // "S_BZBH"  => $bzcode,
            "S_BZBH"  => "",
            "S_ZWMC"  => $bzname, 
            "S_FBRQS" => "", 
            "S_FBRQE" => "", 
            "S_SSRQS" => "", 
            "S_SSRQE" => "", 
            "S_GKDW"  => "", 
            "S_YWMC"  => "", 
            "S_QCDW"  => "", 
            "S_QCR"   => "", 
            "S_BZXZ"  => "", 
            "S_BZJB"  => "", 
            "S_ISABO" => 0,
            "S_GRP"   => "", 
            "treeid"  => 0,
            "order"   => 1,
            "order_style" =>"asc",
            "is_new"  => $is_new,
            "act"     => "search",
            "stdID"   => "", 
            "interval" => 20,
            "pageNo"   => $page-1,
            "total"   => "",
            "totalPage"=> "",
            "thisPageCounts"=> "",
        ];

        $postData = http_build_query($dataArray);
		$html = Http::post($url,$postData);

        $return = [
            "code" => 0,
            "data" => [
                // "mocode"  => $mcode,
                // "bzcode"  => $bzcode,
                // "bzcode"  => "",
                "bzname"  => $bzname,
                "page"    => $page,
                "allPage" => (int)1,
                "total"   => (int)1,
                "list"    => [],
            ]
        ];

        $temp = [
            // "BZTXBH" => $data_list[$i][2],
            "stdID"  => "--------",
            "BZBH"   => "--------",
            "CKXQ"   => "--------",
            "BZMC"   => "此网站系统关闭，暂停服务",
            "SFBRQ"  => "--------",
            "SSRQ"   => "--------",
            // "CYGX"   => $data_list[$i][8],
            // "DTBZH"  => $data_list[$i][9],
            // "BZ"     => $data_list[$i][10],
        ];
        $return["data"]["list"][] = $temp;
       
        return $return;
    }


    public function getGBW($param)
    {
        $bzcode = isset($param["bzcode"]) ? $param["bzcode"] : "";
        $bzname = isset($param["bzname"]) ? $param["bzname"] : "";
        $page = isset($param["page"]) ? $param["page"]: 1;
		// $url = self::getGbwUrl."?p.p2=".$bzcode."&page=1";
		// $url = self::getGbwUrl.urlencode("?p.p90=circulation_date&p.p91=desc&page=$page&p.p2=$bzcode&p.p1=0");
		$url = self::getGbwUrl;
        $dataArray = [
			"p.p90"    => "circulation_date",
			"p.p91"    => "desc",
            "page"     =>  $page,
            "pageSize" =>  "10",
            "p.p1"     =>   "0",
			"p.p2"     =>  $bzcode."+".$bzname,
		];
		$html = Http::get($url,$dataArray);
		$preg_list = '/<tbody style=".*?">(.*?)<\/tbody>/s';
		preg_match_all($preg_list, $html, $data_list,PREG_SET_ORDER);
		$return = [
            "code" => 0,
            "data" => [
                // "mocode"  => $mcode,
                "bzcode"  => $bzcode,
                "bzname"  => $bzname,
                "page"    => $page,
                "list"    => [],
            ]
        ];
		if (empty($html[0])) {
			return $return;
		}
		$html = $data_list[0][1];

		$preg_list = '/<tr>.*?<td>(.*?)<\/td>.*?<td style=".*?"><a href=".*?".*?onclick="showInfo\(\'(.*?)\'\);">(.*?)<\/a><\/td>.*?<td>.*?<\/td>.*?<td class=".*?" onmouseover=".*?".*?style=".*?"><a.*?href=".*?" style=".*?".*?onclick=".*?">(.*?)<\/a><\/td>.*?<td>(.*?)<\/td>.*?<td>.*?<span class=".*?" style=".*?">(.*?)<\/span>.*?<\/td>.*?<td>(.*?)<\/td>.*?<td>(.*?)<\/td>/s';	
		preg_match_all($preg_list, $html, $data_list,PREG_SET_ORDER);
		// dump($data_list);
		
		$len = count($data_list);
		if ($len == 0) {
            return $return;
        }
        
        for ($i = 0; $i < $len; $i++) { 
            $temp = [
                "XH" => $data_list[$i][1],
                "BZBH"   => $data_list[$i][3],
                "CK"   => self::GBW_DETAIL_URL.$data_list[$i][2],
                "BZMC"   => $data_list[$i][4],
                // "YDJB"   => $data_list[$i][6],
                "FBRQ"   => substr($data_list[$i][7],0,10),
                "SSRQ"   => substr($data_list[$i][8],0,10),
                // "CYGX"   => $data_list[$i][8],
                // "DTBZH"  => $data_list[$i][9],
                // "BZ"     => $data_list[$i][10],
            ];
            $return["data"]["list"][] = $temp;
		}
        return $return;
    }


}