<?php
/*
 * Are You A Human
 * PHP Integration Library
 * 
 * @version 0.0.9
 * 
 *    - Documentation and latest version
 *          http://portal.areyouahuman.com/help
 *    - Get an AYAH Publisher Key
 *          https://portal.areyouahuman.com
 *    - Discussion group
 *          http://support.areyouahuman.com
 *
 * Copyright (c) 2011 AYAH LLC -- http://www.areyouahuman.com
 *
 * BY USING THIS SOFTWARE YOU AGREE TO THE TERMS AND CONDITIONS
 * FOUND AT http://portal.areyouahuman.com/termsAndCondition
 */

class AYAH {
	protected $publisher_key;
	protected $scoring_key;
	protected $webservice_host;
	protected $timeout;
	protected $session_secret;
	
	/**
	 * Returns the markup for the PlayThru
	 *
	 * @return string
	 */
	public function getPublisherHTML() {
		
		$url = 'https://' . $this->webservice_host . "/ws/getIframe/";	
		$response = $this->doCall($url, array('publisher_key' => $this->publisher_key), $this->timeout);

		return $response->html;
	}
	
	/**
	 * Check whether the user is a human
	 * Wrapper for the scoreGame API call
	 *
	 * @return boolean
	 */
	public function scoreResult() {
		$result = false;
		if ($this->session_secret) {
			$url = 'https://' . $this->webservice_host . "/ws/scoreGame";
			$fields = array(
				'session_secret' => urlencode($this->session_secret),
				'scoring_key' => $this->scoring_key
			);
			$resp = $this->doCall($url, $fields, false);

			if ($resp) {
				$result = ($resp->status_code == 1);
			}
			
			if($resp->status_code != 1){
				error_log('$resp: '.print_r($resp,true));
			}
		}

		return $result;
	}
	
	/**
	 * Records a conversion
	 * Called on the goal page that A and B redirect to
	 * A/B Testing Specific Function
	 *
	 * @return boolean
	 */
	public function recordConversion(){
		if( isset( $this->session_secret ) ){
			return '<iframe style="border: none;" height="0" width="0" src="https://' . 
				$this->ayah_web_service_host . '/ws/recordConversion/'.
				$this->session_secret . '"></iframe>';
		} else {
			error_log('AYAH::recordConversion - AYAH Conversion Error: No Session Secret');
			return FALSE;
		}
	}


	public function __construct($params = array()) {
		
		//Get the sessionsecret
		if(array_key_exists("session_secret", $_REQUEST)){
			$this->session_secret = $_REQUEST["session_secret"];
		}
		
		$config = parse_ini_file('ayah.config.php');
		
//		echo '<pre>';
//		echo '$config: '.print_r($config,true).'<br>';
//		echo '$params: '.print_r($params,true).'<br>';
		
		foreach($config as $key => $value){
			if(array_key_exists($key, $params)){
				$this->{$key} = $params[$key];
			} else {
				$this->{$key} = $value;
			}
		}
		
//		echo print_r($this,true);
		
		//exit();
	}

	// Internal:
	// Makes a call with CURL
	protected function doCall($url, $fields, $use_timeout) {
		$fields_string = "";
		foreach($fields as $key=>$value) { 
			if (is_array($value)) {
				foreach ($value as $v) {
					$fields_string .= $key . '[]=' . $v . '&';
				}
			} else {
				$fields_string .= $key.'='.$value.'&'; 
			}
		}
		rtrim($fields_string,'&');
		$curlsession = curl_init();		
		curl_setopt($curlsession,CURLOPT_URL,$url);
		curl_setopt($curlsession,CURLOPT_POST,count($fields));
		curl_setopt($curlsession,CURLOPT_POSTFIELDS,$fields_string);
		curl_setopt($curlsession,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($curlsession,CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($curlsession,CURLOPT_SSL_VERIFYPEER,false);
		if ($use_timeout) {
			curl_setopt($curlsession,CURLOPT_TIMEOUT, $this->timeout);
		}
		$result = curl_exec($curlsession);
		if ($result) {
			try {
				$m = json_decode( $result);
			} catch (Exception $e) { 
				error_log("AYAH::doCall() - Exception when calling json_decode: " . $e->getMessage());
				$m = null;
			}
		} else {
error_log(curl_error($curlsession));
			$m = null;
		}
		curl_close($curlsession);	
		return $m;
	}
}