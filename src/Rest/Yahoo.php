<?php

namespace MattThommes\Rest;

use MattThommes\Debug;
use MattThommes\Rest\Http;

class Yahoo
{
	private $auth_consumer_key;
	private $auth_consumer_secret;
	private $auth_callback;
	private $authorize_url;
	private $access_token;

	public $auth_url = "https://api.login.yahoo.com/oauth2/request_auth";
	public $access_token_url = "https://api.login.yahoo.com/oauth2/get_token";

	/**
	 * @param  string  consumer_key     The Yahoo app client ID
	 * @param  string  consumer_secret  The Yahoo app client secret
	 * @param  string  auth_callback    The callback URL or "oob" for "out of band" (meaning a auth code will be presented after authorizing)
	 * @param  string  access_token     Existing access token
	 */
	public function __construct($consumer_key, $consumer_secret, $auth_callback = "oob", $access_token = "")
	{
		$this->auth_consumer_key = $consumer_key;
		$this->auth_consumer_secret = $consumer_secret;
		$this->auth_callback = $auth_callback;
		$this->auth_header = base64_encode(sprintf("%s:%s", $this->auth_consumer_key, $this->auth_consumer_secret));

		$this->access_token = $access_token;

		if (! $this->access_token) {
			// No access token: start auth process
			if (isset($_GET["code"])) {
				// Coming back to this page after authorizing on Yahoo.
				$this->requestAccessToken($_GET["code"]);
			} else {
				// Start a new OAuth request.
				$this->authRequest();
			}
		}
	}

	/**
	 * Get the authorize URL to start the OAuth process
	 */
	public function getAuthorizeUrl()
	{
		return $this->authorize_url;
	}

	/**
	 * Get the access token associated with this class instance
	 */
	public function getAccessToken()
	{
		return $this->access_token;
	}

	/**
	 * Get an authorization URL to authorize access
	 *
	 * @return void
	 */
	private function authRequest()
	{
		$url_params = [
			"client_id" => $this->auth_consumer_key,
			"redirect_uri" => urlencode($this->auth_callback),
			"response_type" => "code",
			"language" => "en-us",
		];
		$this->authorize_url = Http::buildQuery($this->auth_url, $url_params);
	}

	/**
	 * Exchange authorization code for an access token (short-lived: 1 hour),
	 * then get the long-lived access token by calling the same function.
	 *
	 * @param  string  code     The code returned from Yahoo after authorizing the app, or the refresh token
	 * @param  boolean refresh  Whether or not a token refresh is happening
	 * @return mixed
	 */
	private function requestAccessToken($code, $refresh = false)
	{
		$url_params = [
			"grant_type" => "authorization_code",
			"redirect_uri" => $this->auth_callback,
		];

		if ($refresh) {
			$url_params["grant_type"] = "refresh_token";
			$url_params["refresh_token"] = $code;
		} else {
			$url_params["code"] = $code;
		}

		$headers = [
			"Authorization: Basic " . $this->auth_header,
			"Content-Type: application/x-www-form-urlencoded",
		];

		$req = new Http;
		$res = $req->curl($this->access_token_url, $url_params, "POST", [], $headers);

		// Exchange refresh token for new access token.
		// This gives us a longer-lived access token.
		
		if (! $this->access_token) {
			$res = json_decode($res);

			// Set the short-lived (one hour) access token
			$this->access_token = $res->access_token;

			$res = $this->requestAccessToken($res->refresh_token, true);
			$res = json_decode($res);

			// Set the long-lived access token
			$this->access_token = $res->access_token;

			// We're done with access tokens: just return
			return;
		}

		// We only get here when requesting the long-lived (refreshed) access token (2nd request)
		return $res;
	}

}

?>