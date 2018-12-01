<?php
/**
 * Created by PhpStorm.
 * User: hfengshan
 * Date: 2018/7/4
 * Time: 10:13
 */

namespace PhalApi\ProductAI;

use PhalApi\ProductAI\ProductAI\ProductAI as PhalApi_ProductAI;

/**
 * 
 */
class search
{
    public $serviceId;
    public $file;
    public $productai;
    public $count;
    public $url;

    public function __construct($serviceId,$file,$url,$key,$secret,$resultCount)
    {
        $this->productai=new PhalApi_ProductAI($key,$secret);

        $this->serviceId=$serviceId;
        $this->file=$file;
        $this->resultCount=$resultCount;
        $this->url=basename($url);
        $this->count=count(file($this->file));

        return $this->search();
    }

    // search
    public function search()
    {
        $inHandle=fopen($this->file,'r');

        $content='';

        while ($data=fgetcsv($inHandle)) {
            $url=$data[0];

            $response=$this->productai->searchImage($this->serviceId,$url,null,$this->resultCount);

            if (array_key_exists('results',$response) && !empty($response['results'])) {
                $response=$this->productai->searchImage($this->serviceId,$url,null,$this->resultCount,$response['results'][0]['tags']);
            }

            $content.='
            <div class="pure-g">
                <div class="box-1 pure-u-1 pure-u-md-1-2 pure-u-lg-1-4 is-center">
                    <h3 class="content-subhead">
                        <i class="fa fa-mobile-phone"></i>
                        搜索图片：
                    </h3>
                    <a href="'.$url.'" target="_blank">
                        <img src="'.$url.'" class="pure-img" alt="query iamge">
                    </a>
                </div>
                <div class="box-1 pure-u-3-4 pure-u-md-1-2 pure-u-lg-3-4">
                    <h3 class="content-subhead">
                        <i class="fa fa-rocket"></i>
                        搜索结果图片：
                    </h3>
                    <div class="masonry">';

                if (array_key_exists('results',$response) && !empty($response['results'])) 
                {
                    foreach ($response['results'] as $result) {
                        $imageUrl = $result['url'];
                        $content .= '
                                <div class="item">
                                    <div class="__content">
                                        <a href="' . $imageUrl . '" target="_blank">
                                            <img src="' . $imageUrl . '" class="pure-img" alt="result image">
                                        </a>
                                    </div>
                                </div>
                    ';
                    }
                }

                $content.=' 
                        </div>
                    </div>
                </div>
                ';
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
