<?php

namespace PhalApi\ProductAI;

use PhalApi\ProductAI\ProductAI\ProductAI as PhalApi_ProductAI;

/**
 * 
 */
class detect
{
	public $portId;
    public $file;
    public $productai;
    public $count;
    public $url;
	
	public function __construct($portId,$file,$url,$key,$secret,$resultCount)
	{
		$this->productai=new PhalApi_ProductAI($key,$secret);

        $this->portId=$portId;
        $this->file=$file;
        $this->resultCount=$resultCount;
        $this->url=basename($url);
        $this->count=count(file($this->file))

        return $this->detect();
	}


	public function detect()
	{
		$inputHandle=fopen($this->file,'r');

		$content='
            <div class="pure-g">';


        while ($data=fgetcsv($inHandle)) {
		    $url=$data[0];

		    $content.='
		                <div class="box-1 pure-u-1 pure-u-md-1-2 pure-u-lg-1-4 is-center">
		                    <h3 class="content-subhead">
		                        <i class="fa fa-desktop"></i>
		                        测试图片：
		                    </h3>
		                    <a target="_blank" href="'.$url.'">
		                        <div class="detect-view-inner">
		                            <img src="'.$url.'" class="pure-img">';

		    $response=$this->productai->imageAnalyze('/detect/'.$this->portId,$url);

		    if (array_key_exists('boxes_detected',$response)) {
		        if (!empty($response['boxes_detected'])) {
		            foreach ($response['boxes_detected'] as $item) {
		                if ($item['puid']!='non-fashion') {
		                    if (!empty($item['box'])) {
		                        $content.='
		                        <div class="detect-view-core">
		                            <div class="detect-view-box" style="top: '.($item['box'][1]*100).'%;left: '.($item['box'][0]*100).'%; width: '.($item['box'][2]*100).'%; height: '.($item['box'][3]*100).'%;">
		                            <div class="detect-view-text">
		                                '.$item['type'].'
		                                </div>
		                            </div>
		                        </div>';
		                    }
		                }
		            }
		        }
		    }

		    $content.='
		            </div>
		        </a>
		    </div>';

		    // 判断是否为4行
		    if (floor($iter/4)==$iter/4) {
		        $content.='
		        </div>
		        <div class="pure-g">';
		    }
		}

		fclose($inHandle);

		$searchFp=fopen(__DIR__.'/ProductAI/template.html','r');
		$str=fread($searchFp,filesize(__DIR__.'/ProductAI/template.html'));
		$str=str_replace('{content}',$content,$str);
		fclose($searchFp);


		$searchHandle=fopen(__DIR__.'/testReport/'.$this->url,'w');
		fwrite($searchHandle,$str);
		fclose($searchHandle);

		return __DIR__.'/testReport/'.$this->url;
	}
}