<?php

namespace MattThommes\Rest;

use MattThommes\Debug;
use MattThommes\Rest\Http;

class Flickr {

	private $d;
	private $auth_consumer_key;
	private $auth_consumer_secret;
	public $auth_callback;
	public $authorize_url;

	function __construct($consumer_key, $consumer_secret, $auth_callback) {
		$this->d = new Debug;
		$this->auth_consumer_key = $consumer_key;
		$this->auth_consumer_secret = $consumer_secret;
		$this->auth_callback = $auth_callback;
		$this->authRequest();
	}

	private function authRequest() {
		$request_url = "https://www.flickr.com/services/oauth/request_token";
		//$mt = microtime();
		$nonce = mt_rand();
		$timestamp = time();
		$signature_method = "HMAC-SHA1";
		$version = "1.0";
		$base = array(
			"GET",
			$request_url,
			"oauth_callback=" . $this->auth_callback,
			"&oauth_consumer_key=" . $this->auth_consumer_key,
			"&oauth_nonce=" . $nonce,
			"&oauth_signature_method=" . $signature_method,
			"&oauth_timestamp=" . $timestamp,
			"&oauth_version=" . $version,
		);
		$base = array_map("urlencode", $base);
		$base_str = "{$base[0]}&{$base[1]}&{$base[2]}{$base[3]}{$base[4]}{$base[5]}{$base[6]}{$base[7]}";
//$this->d->dbg($base_str);
		$signature = hash_hmac("sha1", $base_str, $this->auth_consumer_secret);
		$url = "{$request_url}";
		$url .= "?oauth_nonce={$nonce}";
		$url .= "&oauth_timestamp={$timestamp}";
		$url .= "&oauth_consumer_key={$this->auth_consumer_key}";
		$url .= "&oauth_signature_method={$signature_method}";
		$url .= "&oauth_version={$version}";
		$url .= "&oauth_signature={$signature}";
		$url .= "&oauth_callback=" . urlencode($this->auth_callback);
		$this->authorize_url = $url;
		return true;
	}

}

?>