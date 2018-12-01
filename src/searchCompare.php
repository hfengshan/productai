<?php
/**
 * Created by PhpStorm.
 * User: hfengshan
 * Date: 2018/8/31
 * Time: 16:19
 */

namespace PhalApi\ProductAI;

use PhalApi\ProductAI\ProductAI\ProductAI as PhalApi_ProductAI;

/**
 * 
 */
class searchCompare
{
    public $serviceId;
    public $serviceId2;
    public $file;
    public $productai;
    public $count;
    public $url;
    
    public function __construct($serviceId,$serviceId2,$file,$url,$key,$secret,$resultCount)
    {
        $this->productai=new PhalApi_ProductAI($key,$secret);

        $this->serviceId=$serviceId;
        $this->serviceId2=$serviceId2;
        $this->file=$file;
        $this->resultCount=$resultCount;
        $this->url=basename($url);
        $this->count=count(file($this->file));

        return $this->searchCompare();
    }

    public function searchCompare()
    {
        $inHandle=fopen($this->file,'r');

        $content='';

        while ($data=fgetcsv($inHandle)) {
            $url=$data[0];

            $resFirst=$this->productai->searchImage($this->serviceId,$url,NULL,$this->resultCount);
//    if (array_key_exists('results',$resFirst) and !empty($resFirst['results'])) {
//        $resFirst=$productAI->searchImage(SERVICEID_FIRST,$url,NULL,RESULTCOUNT,$resFirst['results'][0]['tags']);
//    }

            $resSecond=$this->productai->searchImage($this->serviceId2,$url,null,$this->resultCount);
//    if (array_key_exists('results',$resSecond) and !empty($resSecond['results'])) {
//        $resSecond=$productAI->searchImage(SERVICEID_SECOND,$url,null,RESULTCOUNT,$resSecond['results'][0]['tags']);
//    }

            $content.='
        <div class="pure-g">
            <div class="box-1 pure-u-1-3 pure-u-md-1-5 pure-u-lg-1-5 is-center">
                <h3 class="content-subhead">
                    <i class="fa fa-mobile-phone"></i>
                    搜索图片：
                </h3>
                <a href="'.$url.'" target="_blank">
                    <img src="'.$url.'" class="pure-img" alt="query iamge">
                </a>
            </div>
            <div class="box-1 pure-u-1-3 pure-u-md-2-5 pure-u-lg-2-5">
                <h3 class="content-subhead">
                    <i class="fa fa-rocket"></i>
                    '.$this->serviceId.' 
                </h3>
                <div class="masonry">';

        if (array_key_exists('results',$resFirst)) {
            if (!empty($resFirst['results'])) {
                foreach ($resFirst['results'] as $item) {
                    $imageUrl=$item['url'];
                    $content.='
                <div class="item">
                        <div class="__content">
                            <a href="'.$imageUrl.'" target="_blank">
                                <img src="'.$imageUrl.'" class="pure-img" alt="result image">
                            </a>
                        </div>
                    </div>';
            }
        }
    }

    $content.='
                </div>
            </div>
            <div class="box-1 pure-u-1-3 pure-u-md-2-5 pure-u-lg-2-5">
                <h3 class="content-subhead">
                    <i class="fa fa-rocket"></i>
                    '.$this->serviceId2.' 
                </h3>
                <div class="masonry">';

    if (array_key_exists('results',$resSecond)) {
        if (!empty($resSecond['results'])) {
            foreach ($resSecond['results'] as $item) {
                $imageUrl=$item['url'];
                $content.='
                <div class="item">
                        <div class="__content">
                            <a href="'.$imageUrl.'" target="_blank">
                                <img src="'.$imageUrl.'" class="pure-img" alt="result image">
                            </a>
                        </div>
                    </div>';
            }
        }
    }

    $content.='
            </div>
        </div>
    </div>';

    }

    fclose($inHandle);

    //利用模版，形成最终文件中的内容
    $searchFp=fopen(__DIR__.'/ProductAI/template.html','r');
    $str=fread($searchFp,filesize(__DIR__.'/ProductAI/template.html'));
    $str=str_replace('{content}',$content,$str);
    fclose($searchFp);

    $searchHandle=fopen(__DIR__.'/testReport/'.$this->url,'w');
    fwrite($searchHandle,$str);
    fclose($searchHandle);

    $this->productai->close();

    return __DIR__.'/testReport/'.$this->url;

    }
}

