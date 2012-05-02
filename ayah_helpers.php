<?php

function ayah_make_curl_post($url, $fields, $use_timeout) {
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
	$result = curl_exec($curlsession);		if ($result) {
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