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
    //用来查看体系内的文章
    const baseUrl   = "http://jtst.mot.gov.cn/gb/search/gbDetailed?id="; 

    const getJtbUrl = "http://jtst.mot.gov.cn/gb/search/gbAdvancedSearchPage";
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
        $page = (int)$page;
        // dump($page);
        $url = self::getXtdUrl;
        $dataArray = [
            // "mcode"         => (int)$mcode,
            // "bzcode"        => $bzcode,
            "searchText"    => $bzname,
            "pageNumber"    => (int)$page,
            "pageSize"      => (int)$pageSize,
            "arch"           => self::arch,
            "level"         => 2,
            "sortOrder"     =>  "asc",
        ];
        // dump($dataArray);
        $postData = http_build_query($dataArray);
        $data  = Http::get($url,$dataArray);

        $resultData = json_decode($data,true);
        dump($resultData);
        if (isset($resultData["error"]) || !isset($resultData["rows"]) ) {
              $return = [
                    "code" => 0,
                    "data" => [
                        "mocode"  => $mcode,
                        "bzcode"  => $bzcode,
                        "bzname"  => $bzname,
                        "rid"     => self::arch,
                        "page"    => 1,
                        "allPage" => 1,
                        "total"   => 1,
                        "list"    => [
                            [
                                "BZTXBH" => "------",
                                "BZBH"   => "------",
                                "CKXQ"   => "------",
                                "BZMC"   => "查询网站关闭，服务暂停使用！",
                                "YDJB"   => "------",
                                "SSRQ"   => "------",
                                "CYGX"   => "------",
                                "DTBZH"  => "------",
                                "BZ"     => "------",
                            ],
                        ],
                    ]
                ];
                return $return;
            
        }

        $allPage = 0;
        if (!empty($resultData["total"])) {
            $allPage = ceil((int)$resultData["total"]/(int)$pageSize);
        }
        $return = [
            "code" => 0,
            "data" => [
                "mocode"  => $mcode,
                "bzcode"  => $bzcode,
                "bzname"  => $bzname,
                "rid"     => self::arch,
                "page"    => $page,
                "allPage" => $allPage,
                "total"   => isset($resultData["total"]) ? $resultData["total"] : 0,
                "list"    => [],
            ]
        ];

        foreach ($resultData["rows"] as $k => $v) {
            $temp = [
                "BZTXBH" => $v["CODE1"].".".$v["CODE2"].".".$v["CODE3"],
                "BZBH"   => $v["STD_CODE"],
                "CKXQ"   => "",
                "BZMC"   => $v["STD_NAME"],
                "YDJB"   => "",
                "SSRQ"   => $v["ACT_DATE"],
                "CYGX"   => "",
                "DTBZH"  => "",
                "BZ"     => self::baseUrl.$v["DETAIL_ID"],
            ];
            $return["data"]["list"][] = $temp;
        }
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
        
        $bzname = isset($param["bzname"]) ? $param["bzname"] : "";
        $page = isset($param["page"]) ? $param["page"]: 1;
        $page = (int)$page;
        // $is_new = $page == 1 ? 1 : "";
        $url = self::getJtbUrl;
        $dataArray = [
            "tid" => 5,
            "std_p1"=> "",
            "std_p2"=> "",
            "std_p3"=> "", 
            "std_p34"=> "", 
            "std_p4"=> "", 
            "std_p5"=> "", 
            "std_p22"=> "", 
            "std_p23"=> "", 
            "std_p35"=> "", 
            "std_p38"=> "", 
            "std_p39"=> "", 
            "std_p36"=> "", 
            "std_p37"=> "", 
            "std_p32"=> "", 
            "std_p33"=> "", 
            "std_p29"=> "", 
            "std_p30"=> "", 
            "std_p31"=> "", 
            "std_p6_1"=> "", 
            "std_p6_2"=> "", 
            "std_p7"=> "", 
            "std_p16"=> "", 
            "std_p27"=> "", 
            "std_p18"=> "", 
            "std_p28"=> "", 
            "std_p8"=>  $bzname, 
            "std_p9"=> "", 
            "std_p10"=> "", 
            "std_p11"=> "", 
            "std_p12"=> "", 
            "std_p13"=> "", 
            "std_p14"=> "", 
            "std_p15"=> "", 
            "std_p19"=> "", 
            "std_p20"=> "", 
            "std_p21"=> "", 
            "sortOrder"=> "asc", 
            "pageSize"=> "10",
            "pageNumber"=> $page, 
        ];

		$html = Http::get($url,$dataArray);
        $resultData = json_decode($html,true);
        //若查询网站关闭
        if (isset($resultData["error"]) || !isset($resultData["rows"]) ) {
            $return = [
                "code" => 0,
                "data" => [
                    "bzname"  => $bzname,
                    "page"    => 1,
                    "allPage" => 1,
                    "total"   => 1,
                    "list"    => [
                        [
                            "stdID"  => 1,
                            "BZBH"   => "------",
                            "CKXQ"   => "------",
                            "BZMC"   =>"查询网站关闭，服务暂停使用！",
                            "FBRQ"  => "------",
                            "SSRQ"   => "------",
                            "CK"     => "------",
                        ],
                    ],
                ]
            ];
            return $return;
          
      }

        $allPage = 0;
        if (!empty($resultData["total"])) {
            $allPage = ceil((int)$resultData["total"]/(int)$page);
        }
        $return = [
            "code" => 0,
            "data" => [
                // "mocode"  => $mcode,
                // "bzcode"  => $bzcode,
                // "bzcode"  => "",
                "bzname"  => $bzname,
                "page"    => $page,
                "allPage" => (int)$allPage,
                "total"   => (int)$resultData["total"],
                "list"    => [],
            ]
        ];
        $i = 1;
        foreach ($resultData["rows"] as $k => $v) {
            $temp = [
                "stdID"  => $i,
                "BZBH"   => $v["STD_CODE"],
                "CKXQ"   => "",
                "BZMC"   => $v["C_NAME"],
                "FBRQ"  => $v["ISSUE_DATE"],
                "SSRQ"   => $v["ACT_DATE"],
                "CK"     => self::baseUrl.$v['id'],
            ];
            $i++;
            $return["data"]["list"][] = $temp;
        }
       
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
                "stdID"  => $data_list[$i][1],
                "BZBH"   => $data_list[$i][3],
                "CK"     => self::GBW_DETAIL_URL.$data_list[$i][2],
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