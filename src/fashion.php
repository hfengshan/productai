<?php

namespace PhalApi\ProductAI;

use PhalApi\ProductAI\ProductAI\ProductAI as PhalApi_ProductAI;

/**
 * 
 */
class fashion
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

        return $this->fashion();
	}


	public function fashion($value='')
	{
		$inHandle=fopen($this->file,'r');

		$content='';

		while ($data=fgetcsv($inHandle)) {
		    $url=$data[0];
		    // 发起ProductAI请求
		    $response=$this->productai->imageAnalyze('/dressing/'.$this->portId,$url);

		    $content.='
		        <div class="pure-g">
		            <div class="box-1 pure-u-1 pure-u-md-1-2 pure-u-lg-1-4 is-center">
		                <h3 class="content-subhead">
		                    <i class="fa fa-mobile-phone"></i>
		                    请求图片：
		                </h3>
		                <a target="_blank" href="'.$url.'">
		                    <div class="detect-view-inner">
		                        <img class="pure-img" src="'.$url.'">
		                    ';

		    if (!empty($response['results'])) {
		        foreach ($response['results'] as $key=>$item) {
		            if (!empty($item['box'])) {
		                $content.='
		                        <div class="detect-view-core">
		                            <div class="detect-view-box" style="top: '.($item['box'][1]*100).'%;left: '.($item['box'][0]*100).'%; width: '.($item['box'][2]*100).'%; height: '.($item['box'][3]*100).'%;">
		                            <div class="detect-view-text">
		                                '.($key+1).': '.$item['item'].'
		                                </div>
		                            </div>
		                        </div>';
		            }
		        }
		    }

		    $content.='
		                </div>
		            </a>
		            </div>
		        ';

		//    开启搜索结果部分
		    $content.='
		    <div class="box-1 pure-u-1 pure-u-md-1-2 pure-u-lg-3-4">
		                    <h3 class="content-subhead">
		                        <i class="fa fa-rocket"></i>
		                        分析结果：
		                    </h3>
		                    <span class="content">
		                        风格标签:
		                    </span>
		                    <div class="ribbon">';
		    if (!empty($response['labels'])) {
		        foreach ($response['labels'] as $label) {
		            $content.='
		                    <button class="pure-button-primary pure-button">'.$label.'</button>
		                    ';
		        }
		    }

		    $content.='     </div>
		                        <br>
		                    <span class="content">
		                        单品颜色分析:
		                    </span>
		                    <ul class="color-assembly">
		                   ';

		    if (!empty($response['results'])) {
		        foreach ($response['results'] as $olKey=>$olItem) {
		            $content.='
		                        <li class="color-assembly-item">
		                            <h4 class="color-assembly-title">
		                                '.($olKey+1).': '.$olItem['item'].'
		                            </h4>
		                            <ol class="color-assembly-partial">
		                                ';
		            foreach ($olItem['colors'] as $color) {
		                $content.='
		                                <li class="color-assembly-partial-item" style="width: '.($color['percent']*100).'%;background: rgb('.implode(',',$color['rgb']).')"></li>
		                                ';
		            }

		            $content.='
		                            </ol>
		                            <ul class="color-assembly-detail">
		                                ';

		            foreach ($olItem['colors'] as $color) {
		                $content.='
		                                <li class="color-assembly-detail-item">
		                                    <i class="color-assembly-detail-figure" style="background: rgb('.implode(',',$color['rgb']).')"></i>
		                                <label class="color-assembly-detail-label">'.$color['w3c-cn'].'：'.($color['percent']*100).'%</label>
		                                </li>';
		            }

		            if (!empty($olItem['textures'])) {
		                foreach ( $olItem['textures'] as $texture) {
		                    $content.='
		                                <li class="color-assembly-detail-item">
		                                    <i class="color-assembly-detail-texture"></i>
		                                    <label class="color-assembly-detail-label">花板：'.$texture.'</label>
		                                </li>';
		                }
		            }

		            $content.='
		                            </ul>
		                        </li>';
		        }
		    }

		    $content.='
		            </ul>
		        </div>
		    </div>
		    ';
		}

		fclose($inHandle);

		$clothFp=fopen(__DIR__.'/ProductAI/template.html','r');
		$str=fread($clothFp,filesize(__DIR__.'/ProductAI/template.html'));
		$str=str_replace('{content}',$content,$str);
		fclose($clothFp);


		$clothHandle=fopen(__DIR__.'/testReport/'.$this->url,'w');
		fwrite($clothHandle,$str);
		fclose($clothHandle);

		$this->productai->close();

		return __DIR__.'/testReport/'.$this->url;
	}
}