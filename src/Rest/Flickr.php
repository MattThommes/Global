<?php

namespace MattThommes\Rest;

use MattThommes\Debug;
use MattThommes\Rest\Http;

class Flickr {

	private $d;
	private $auth_consumer_key;
	private $auth_consumer_secret;
	private $nonce;
	private $timestamp;
	private $signature_method;
	private $version;
	public $request_url = "https://www.flickr.com/services/oauth/request_token";
	public $authorize_url = "https://www.flickr.com/services/oauth/authorize";
	public $access_token_url = "https://www.flickr.com/services/oauth/access_token";
	public $auth_callback;

	function __construct($consumer_key, $consumer_secret, $auth_callback) {
		$this->d = new Debug;
		$this->auth_consumer_key = $consumer_key;
		$this->auth_consumer_secret = $consumer_secret;
		$this->nonce = mt_rand();
		$this->timestamp = time();
		$this->signature_method = "HMAC-SHA1";
		$this->version = "1.0";
		$this->auth_callback = $auth_callback;
		if (isset($_GET["oauth_token"]) && isset($_GET["oauth_verifier"])) {
			// Coming back to this page after authorizing on Flickr.
			$this->verifyRequest($_GET["oauth_token"], $_GET["oauth_verifier"]);
		} else {
			// Start a new OAuth request.
			$this->authRequest();
		}
	}

	private function generateSignature() {
		$base = array(
			"oauth_callback" => $this->auth_callback,
			"oauth_consumer_key" => $this->auth_consumer_key,
			"oauth_nonce" => $this->nonce,
			"oauth_signature_method" => $this->signature_method,
			"oauth_timestamp" => $this->timestamp,
			"oauth_version" => $this->version,
		);
		$base_keys = array_keys($base);
		sort($base_keys, SORT_STRING);
		$base_key_values = array();
		foreach ($base_keys as $k) {
			$base_key_values[] = rawurlencode($k) . "=" . rawurlencode($base[$k]);
		}
		$base_str = implode("&", $base_key_values);
		$base_str = sprintf("%s&%s&%s", rawurlencode("GET"), rawurlencode($this->request_url), rawurlencode($base_str));
		$key = sprintf("%s&", $this->auth_consumer_secret);
		$signature = base64_encode(hash_hmac("sha1", $base_str, $key, true));
		return $signature;
	}

	/**
	 * Signing Requests, and Getting a Request Token. 
	 */
	private function authRequest() {
		$signature = $this->generateSignature();
		$url = "{$this->request_url}";
		$url .= "?oauth_nonce={$this->nonce}";
		$url .= "&oauth_timestamp={$this->timestamp}";
		$url .= "&oauth_consumer_key={$this->auth_consumer_key}";
		$url .= "&oauth_signature_method={$this->signature_method}";
		$url .= "&oauth_version={$this->version}";
		$url .= "&oauth_signature={$signature}";
		$url .= "&oauth_callback=" . rawurlencode($this->auth_callback);
		$req = new Http;
		$response = $req->curl($url);
		$response_vars = explode("&", $response);
		list(,$oauth_token) = explode("=", $response_vars[1]);
		$authorize_url = "{$this->authorize_url}?oauth_token={$oauth_token}";
		$this->authorize_url = $authorize_url;
		return true;
	}

	/**
	 * Exchanging the Request Token for an Access Token.
	 *
	 * @param string oauth_token    The token returned from Flickr after authorizing the app.
	 * @param string oauth_verifier The verifier returned from Flickr after authorizing the app.
	 */
	private function verifyRequest($oauth_token, $oauth_verifier) {
		$base = array(
			"oauth_nonce" => $this->nonce,
			"oauth_timestamp" => $this->timestamp,
			"oauth_verifier" => $oauth_verifier,
			"oauth_consumer_key" => $this->auth_consumer_key,
			"oauth_signature_method" => $this->signature_method,
			"oauth_version" => $this->version,
			"oauth_token" => $oauth_token,
			"oauth_signature" => "", //// <--- NEED TO GENERATE THIS AGAIN.
		);
		///// NEED CURL CALL HERE TO GET ACCESS TOKEN!
	}

}

?>