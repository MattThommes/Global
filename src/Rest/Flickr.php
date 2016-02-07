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
			"oauth_callback" => $this->auth_callback,
			"oauth_consumer_key" => $this->auth_consumer_key,
			"oauth_nonce" => $nonce,
			"oauth_signature_method" => $signature_method,
			"oauth_timestamp" => $timestamp,
			"oauth_version" => $version,
		);
		$base_keys = array_keys($base);
		sort($base_keys, SORT_STRING);
		$base_key_values = array();
		foreach ($base_keys as $k) {
			$base_key_values[] = rawurlencode($k) . "=" . rawurlencode($base[$k]);
		}
		$base_str = implode("&", $base_key_values);
		$base_str = sprintf("%s&%s&%s", rawurlencode("GET"), rawurlencode($request_url), rawurlencode($base_str));
		$key = sprintf("%s&", $this->auth_consumer_secret);
		$signature = base64_encode(hash_hmac("sha1", $base_str, $key, true));
		$url = "{$request_url}";
		$url .= "?oauth_nonce={$nonce}";
		$url .= "&oauth_timestamp={$timestamp}";
		$url .= "&oauth_consumer_key={$this->auth_consumer_key}";
		$url .= "&oauth_signature_method={$signature_method}";
		$url .= "&oauth_version={$version}";
		$url .= "&oauth_signature={$signature}";
		$url .= "&oauth_callback=" . urlencode($this->auth_callback);
		$req = new Http;
		$response = $req->curl($url);
		$response_vars = explode("&", $response);
		list(,$oauth_token) = explode("=", $response_vars[1]);
		$authorize_url = "https://www.flickr.com/services/oauth/authorize?oauth_token={$oauth_token}";
		$this->authorize_url = $authorize_url;
		return true;
	}

}

?>