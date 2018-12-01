<?php
namespace PhalApi\ProductAI;

use PhalApi\ProductAI\search as PhalApi_search;
use PhalApi\ProductAI\searchCompare as PhalApi_searchCompare;
use PhalApi\ProductAI\tagging as PhalApi_tagging;
use PhalApi\ProductAI\detect as PhalApi_detect;
use PhalApi\ProductAI\fashion as PhalApi_fashion;
use PhalApi\ProductAI\OCR as PhalApi_OCR;
// use PhalApi\ProductAI\ProductAI as PhalApi_ProductAI;

/**
 * 
 */
class Lite
{
	// 秘钥
	public $key;
	// 密文
	public $secret;

	public $resultCount;
	
	/**
	 * [__construct description]
	 * @param string $key         key
	 * @param string $secret      secret
	 * @param string $resultCount resultCount
	 */
	public function __construct($key,$secret,$resultCount)
	{
		$this->key=$key;
		$this->secret=$secret;
		$this->resultCount=$resultCount;

	}

	/**
	 * search
	 * @param  string $serviceId serviceID
	 * @param  file $file      file
	 * @param  string $url       url
	 * @return string            url
	 */
	public function search($serviceId,$file,$url)
	{
		$search=new PhalApi_search($serviceId,$file,$url,$this->key,$this->secret,$this->resultCount);

		return $search;
	}


	/**
	 * search Compare
	 * @param  string $serviceId  serviceID
	 * @param  string $serviceId2 serviceID2
	 * @param  file $file       file
	 * @param  string $url        url
	 * @return string             url
	 */
	public function searchCompare($serviceId,$serviceId2,$file,$url)
	{
		$searchCompare=new PhalApi_searchCompare($serviceId,$file,$url,$this->key,$this->secret,$this->resultCount);

		return $searchCompare;
	}


	/**
	 * tagging
	 * @param  string $portId portID
	 * @param  file $file      file
	 * @param  string $url       url
	 * @return string            url
	 */
	public function tagging($portId,$file,$url)
	{
		$tagging=new PhalApi_tagging($portId,$file,$url,$this->key,$this->secret,$this->resultCount);

		return $tagging;
	}


	/**
	 * detect
	 * @param  string $portId portid
	 * @param  file $file   file
	 * @param  string $url    url
	 * @return string         url
	 */
	public function detect($portId,$file,$url)
	{
		$detect=new PhalApi_detect($portId,$file,$url,$this->key,$this->secret,$this->resultCount);

		return $detect;
	}


	/**
	 * fashion
	 * @param  string $portId portid
	 * @param  file $file   file
	 * @param  string $url    url
	 * @return string         url
	 */
	public function fashion($portId,$file,$url)
	{
		$fashion=new PhalApi_fashion($portId,$file,$url,$this->key,$this->secret,$this->resultCount);

		return $fashion;
	}


	/**
	 * [OCR description]
	 * @param [type] $file [description]
	 * @param [type] $url  [description]
	 */
	public function OCR($file,$url)
	{
		$ocr=new PhalApi_detect($file,$url,$this->key,$this->secret,$this->resultCount);

		return $ocr;
	}
}


