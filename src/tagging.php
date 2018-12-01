<?php

namespace PhalApi\ProductAI;

use PhalApi\ProductAI\ProductAI\ProductAI as PhalApi_ProductAI;

/**
 * 
 */
class tagging
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

        return $this->tagging();
	}

	public function tagging()
	{
		$inputHandle=fopen($this->file,'r');

		$content='';

		while ($data=fgetcsv($inputHandle)) {
    		$url=$data[0];
			// 发起标签请求
		    $tags=$this->productai->imageAnalyze('/classify/'.$this->portId,$url);

		    $content.='<div class="pure-g">
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
		                    <i class="fa fa-bug"></i>
		                    标签结果：
		                </h3>
		                ';
		    if (!empty($tags['results'])) {
		        foreach ($tags['results'] as $tagCategory) {
		            $tag=$tagCategory['category'];
		            $content .= '<button class="pure-button pure-button-primary">' . $tag . '</button>
		                   ';
		        }
		    }

		    $content.='</div>
		             </div>
		            ';
		}

		fclose($inputHandle);

		$tagFp=fopen(__DIR__.'/ProductAI/template.html','r');
		$str=fread($tagFp,filesize(__DIR__.'/ProductAI/template.html'));
		$str=str_replace('{content}',$content,$str);
		fclose($tagFp);


		$tagHandle=fopen(__DIR__.'/testReport/'.$this->url,'w');
		fwrite($tagHandle,$str);
		fclose($tagHandle);

		$this->productai->close();

		return __DIR__.'/testReport/'.$this->url;
	}
}








