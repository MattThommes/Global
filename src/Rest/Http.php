<?php

namespace MattThommes\Rest;

Class Http {

	function __construct() {
	
	}

	function curl($url, $params = array(), $verb = "GET", $opts = array(), $headers = array()) {
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

		// "opt_userpwd" should be "username:password"
		if (isset($opts["opt_userpwd"]) && $opts["opt_userpwd"]) {
			curl_setopt($c, CURLOPT_USERPWD, $opts["opt_userpwd"]);
		}

		if ($verb == "PUT") {
			curl_setopt($c, CURLOPT_CUSTOMREQUEST, "PUT");
		} elseif ($verb == "DELETE") {
			curl_setopt($c, CURLOPT_CUSTOMREQUEST, "DELETE");
		}

		if (($verb == "POST" || $verb == "PUT" || $verb == "DELETE") && $params) {
			curl_setopt($c, CURLOPT_POST, 1);
			// $post_fields SHOULD BE AN ARRAY IN THIS FORMAT: array(0 => "Hello=World", 1 => "Foo=Bar", 2 => "Baz=Wombat")
			// WHEN implode IS APPLIED, IT TURNS INTO WHAT cURL UNDERSTANDS: "Hello=World&Foo=Bar&Baz=Wombat"
			curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query($params));
		}

		$headers[] = "Expect:";
		if (isset($opts["opt_httpheader"]) && $opts["opt_httpheader"]) {
			foreach ($opts["opt_httpheader"] as $h) {
				$headers[] = $h;
			}
		}

		curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);

		$output = curl_exec($c);
		$http_code = curl_getinfo($c, CURLINFO_HTTP_CODE);
		curl_close($c);

		return $output;
	}

}

?>