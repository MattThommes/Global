<?php

namespace MattThommes\Rest;

use Aws\Common\Aws as AwsMain;
use Aws\Common\Credentials\Credentials;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

class Aws {

	private $key;
	private $secret;
	private $s3;

	function __construct($key, $secret, $client) {
		$auth = new Credentials($key, $secret);
		if ($client == "s3") {
			$this->s3_auth($auth);
		}
	}

	function s3_auth($auth) {
		$this->s3 = S3Client::factory();
		$this->s3->setCredentials($auth);
	}

	function s3_upload($bucket, $key, $body, $acl) {
		try {
			$data = array(
				"Bucket" => $bucket,
				"Key" => $key,
				"Body" => $body,
				"ACL" => $acl,
			);
			$this->s3->putObject($data);
		} catch (S3Exception $e) {
			return "S3 upload error: " . $e->getMessage();
		}
		return true;
	}

}

?>