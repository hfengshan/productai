<?php

namespace PhalApi\ProductAI;

use PhalApi\ProductAI\ProductAI\ProductAI as PhalApi_ProductAI;

/**
 * 		
 */
class OCR
{
	
	public $file;
    public $productai;
    public $count;
    public $url;

	function __construct($file,$url,$key,$secret,$resultCount)
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
		return 'haha';
	}
}