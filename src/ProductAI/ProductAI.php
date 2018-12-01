<?php
/**
 * @author hanfengshan
 * @abstract The main purpose of this program is to define the productAI API
 * @version v1.0 2017-5-25
 * @copyright Copyright (c) 2016-2017 ProductAI Center (http://www.productai.com)
 * @category API - ProductAI
 */

namespace PhalApi\ProductAI\ProductAI;

use PhalApi\ProductAI\ProductAI\Curl as Curl;
use PhalApi\ProductAI\ProductAI\MultiCurl as MultiCurl;
use PhalApi\ProductAI\ProductAI\CaseInsensitiveArray as CaseInsensitiveArray;
use use PhalApi\ProductAI\ProductAI\ArrayUtil as ArrayUtil;

class ProductAI {
	
	# @string apiKey
	protected $apiKey;

	# @string apiSecret
	protected $apiSecret;

	# @string version
	public $version=1;

	# @integer timeout
	public $timeout=100;

	# @object curl
	public $curl;

	# @object multiCurl
	public $multiCurl;

	# @integer errorcode
	public $errorcode=000001;

	# @integer concurrency
	public $concurrency=20;

	# @string method
	public $method="POST";

	# @object tmpfile
	protected $tmpfile;

	# @string API
	public $API="https://api.productai.cn/";
//	public $API="https://api.productai.com";


	/**
	 * [__construct]
	 * @param string $apiKey    
	 * @param string $apiSecret 
	 */
	public function __construct($apiKey,$apiSecret)
	{
		$this->apiKey=$apiKey;
		$this->apiSecret=$apiSecret;
		$this->_curl=new Curl();
		$this->_curl->setOpt(CURLOPT_SSL_VERIFYPEER,false);
		$this->_curl->setOpt(CURLOPT_CAINFO,__DIR__.'/ca.pem');


		$this->_multiCurl=new MultiCurl();
		$this->_multiCurl->setOpt(CURLOPT_RETURNTRANSFER,true);
		$this->_multiCurl->setOpt(CURLOPT_SSL_VERIFYPEER,false);
		$this->_multiCurl->setOpt(CURLOPT_CAINFO,__DIR__.'/ca.pem');
		$this->_multiCurl->setUserAgent('Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36');

		$this->_arrayUtil=new ArrayUtil();
	}


	/**
	 * [signRequests]
	 * @param  array  $headers 
	 * @param  array  $body    
	 * @return string          
	 */
	public function signRequests($headers=array(),$body=array())
	{
		unset($body['search']);
		
		$requests=array_merge($headers,$body);
		
		ksort($requests);
		

		return base64_encode(hash_hmac('sha1', urldecode(http_build_query($requests)), $this->apiSecret, true));
	}

	/**
	 * [_setHeaders]
	 * @param array $parameters
	 */
	public function _setHeaders($lang="zh_Hans") {
		$headers = array();
		$headers['x-ca-version'] = 1.0;
		$headers['x-ca-accesskeyid'] = $this->apiKey;
//		$headers['x-ca-timestamp']=time();
//		$headers['x-ca-signaturenonce']=$this->generateNonce(16);
//		$headers['requestmethod']=$this->method;
//
//		$headers['x-ca-signature']=$this->signRequests($headers,$body);
		$headers['accept-language']='zh-Hans';
		
		$this->_curl->setHeaders($headers);
	}


	/**
	 * [sendRequest description]
	 * @param  string $url        
	 * @param  array  $parameters 
	 * @param  string $method     
	 * @return array             
	 */
	public function sendRequest($url, $parameters = array (), $method = "POST") {
		
		$this->_setHeaders ();

		$data = array ();

		switch ($method) {
			case 'GET' :
				$data = $this->_curl->get($url,$parameters);
				break;
			case 'POST' :
				$data = $this->_curl->post($url,$parameters);
				break;
		}
		if (isset ( $data->failures )) {
			$this->errorCode = $data->failures[0]->code; 
		}
		return $data;
	}


	/**
	 * [setConcurrency]
	 * @param $concurrency 
	 */
	public function setConcurrency($concurrency)
	{
		$this->concurrency=$concurrency;
	}

	/**
	 * [close]
	 * @return none
	 */
	public function close()
	{
		$this->_curl->close();

		$this->_multiCurl->close();
	}



	/**
	 * [generateNonce]
	 * @param  integer $len   
	 * @param  string $chars 
	 * @return [type]        
	 */
	public function generateNonce($len, $chars = '')
    {
        if (!$chars) {
            $chars = array_merge(range('a', 'z'), range('A', 'Z'), range(0, 9));
        } elseif (!is_array($chars)) {
            $chars = str_split($chars);
        }

        $index = count($chars) - 1;

        $nonce = '';
        for ($i = 0; $i < $len; ++$i) {
            $nonce .= $chars[mt_rand(0, $index)];
        }

        return $nonce;
    }


    /**
     * [iamgeIdentify]
     * @param  file or url $image 
     * @return string or file        
     */
    public function imageIdentify($image)
    {
    	$prefix=substr($image,0,1);

		switch ($prefix) {
			case '#':
			case '@':
				$image=substr($image,1);

				if ($prefix=='#') {
					if (!isset($_FILES[$image])) {
						throw new OutOfBoundsException("name $image is not found in forms", 1);
						
					}

					$image=$_FILES[$image]['tmp_name'];

					if (!is_uploaded_file($image)) {
						throw new UnexpectedValueException("possible file upload attack :$image", 1);
						
					}
				}

				return 'search';
				break;
			
			default:
				if (substr($image,0,4)=='http' || substr($image,0,3)=='ftp' || substr($image,0,5)=='https') {

					return 'url';
				} else {
					
					$this->tmpfile=tmpfile();
					fwrite($this->tmpfile,$image);

					return $this->tmpfile;	
				}

				break;
		}
    }

    /**
     * [imageAnalyze]
     * @param  string $portID 
     * @param  file or url $image  
     * @param  string $loc    
     * @return json         
     */
    public function imageAnalyze($portID,$image,$debug=NUll,$granularity=NULL,$return_type=NULL,$loc=NULL)
    {
    	$parameters=array();

    	$imageIdentify=$this->imageIdentify($image);

    	if (is_string($image)) {
    		$parameters[$imageIdentify]=$image;

    	} elseif (is_file($image)) {
    		$parameters['search']=new CURLFile(stream_get_meta_data($imageIdentify)['uri']);
    	}

		if (!empty($loc)) {
            $parameters['loc']=is_array($loc)?implode('-',$loc):$loc;
		}

		if (!empty($granularity)) {
			$parameters['granularity']=$granularity;
		}

		if (!empty($return_type)) {
			$parameters['return_type']=$return_type;
		}
		
		if (!empty($debug)) {
    	    $parameters['debug']=1;
        }

		return $this->sendRequest($this->API.$portID,$parameters);
    }

    /**
	 * [searchImage]
	 * @param  string  $serviceId 
	 * @param  url or file  $image     
	 * @param  array  $loc       
	 * @param  array   $tags      
	 * @param  integer $count     
	 * @return json             
	 */
	public function searchImage($serviceId,$image,$loc=NULL,$count=20,$tags=[],$imageTags=1,$relativeLoc=NULL,$mostCommonTags=NULL)
	{
		$parameters=array();

		$imageIdentify=$this->imageIdentify($image);

    	if (is_string($imageIdentify)) {
    		$parameters[$imageIdentify]=$image;
    	} elseif (is_file($imageIdentify)) {
    		$parameters['search']=new CURLFile(stream_get_meta_data($imageIdentify)['uri']);
    	}

		if (!empty($loc)) {
			
			$parameters['loc']=is_array($loc)?implode('-',$loc):$loc;
		} else {
			$parameters['loc']='0-0-1-1';
		}

		$parameters['count']=$count;

		if (!empty($tags)) {
			if (is_array($tags)) {
				if ($this->_arrayUtil->is_array_multidim($tags)) {
					$parameters['tags']=json_encode($tags);
				} else {
					$parameters['tags']=implode('|',$tags);
				}
			} else {
				$parameters['tags']=$tags;
			}
		}

		if (!empty($imageTags)) {
			$parameters['ret_img_tags']=$imageTags;
		}

		if (!empty($relativeLoc)) {
			$parameters['ret_relative_loc']=$relativeLoc;
		}

		if (!empty($mostCommonTags)) {
			$parameters['n_most_common_tags']=$mostCommonTags;
		}

		return $this->sendRequest($this->API."/search/".$serviceId,$parameters);
	}


    /**
     * @param $serviceId
     * @return array
     */
    public function getService($serviceId) {
	    if (empty($serviceId)) {
	        echo $serviceId." is empty! please enter it.";
	        die;
        }

        return $this->sendRequest($this->API."customer_services/_0000172/".$serviceId,$para=array(),"GET");
    }



    /**
     * [readAllFiles
     * @param  string $dir
     * @return array
     */
    public function readAllFiles($dir='')
    {
        $result = array();
        $dir=rtrim($dir,"/");
        $handle = opendir($dir);
        if ( $handle )
        {
            while ( ( $file = readdir ( $handle ) ) !== false )
            {
                if ( $file != '.' && $file != '..')
                {
                    $cur_path = $dir . DIRECTORY_SEPARATOR . $file;
                    if ( is_dir ( $cur_path ) )
                    {
                        $result['dir'][$cur_path] = readAllFiles( $cur_path );
                    }
                    else
                    {
                        $result['file'][] = str_replace("\\","/",$cur_path);
                    }
                }
            }
            closedir($handle);
        }
        return $result;
    }

}






?>