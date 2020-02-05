<?php
defined('BASEPATH') OR exit('No direct script access allowed');

define('SECRET_KEY', '6TNsqsYvHpTAjFpqZet3Yh4k');

class Api extends CI_Controller {
	### index - route ###
	public function index() {
		echo 'Rest Api';
	}

	### login - route ###
	public function auth() {
		if ($this->input->method() == 'get') {
			$response = array(
				"success" => false,
				"message" => "Request method not allowed!"
			);
			
			$this->jsonResponse($response);
		}
		$this->load->database();

		$rawData = json_decode($this->input->raw_input_stream, true);

		$credentials = array(
			"login" => isset($rawData["login"]) ? $rawData["login"] : null,
			"password" => isset($rawData["password"]) ? $rawData["password"] : null
		);

		// Check credentials
		if ($credentials["login"] === null || $credentials["password"] === null) {
			$response = array(
				"success" => false,
				"message" => "Credentials not valid."
			);

			$this->jsonResponse($response);
		}

		// Check user		
		$this->db->select('username, email, password');
		$query = $this->db->get_where('users', array('login' => $credentials["login"]));
		$hash = $query->row()->password;

		// Generate Token
		if (password_verify($credentials["password"], $hash)) {
			$payload = array(
				"user" => $query->row()->username,
				"mail" => $query->row()->email,
				"iat" => date("Y-m-d")
			);
	
			$token = $this->generateToken($payload);
	
			$response = array(
				"success" => true,
				"token" => $token
			);
	
			$this->jsonResponse($response);
		}
	}

	### data - route ###
	public function data() {
		$token = str_replace(
			"Bearer ",
			"",
			$this->input->get_request_header("Authorization", true)
		);
		
		$tokenParts = explode(".", $token);

		// Check token expiration
		$payload = json_decode(base64_decode($tokenParts[1]));

		$iat = new DateTime($payload->iat);
		$now = date("Y-m-d");

		if ($now > $iat) {
			$response = array(
				"success" => false,
				"message" => "Token expired."
			);

			$this->jsonResponse($response, 401);
		}
		// Check token signature & return data
		$signature = hash_hmac("sha256", "$tokenParts[0].$tokenParts[1]", SECRET_KEY);

		if ($signature === $tokenParts[2]) {
			$response = array(
				"success" => true,
				"data" => array()
			);

			$this->jsonResponse($response);
		}
	}

	private function jsonResponse($data, $statusHeader = 200) {
		$this->output
        	->set_status_header($statusHeader)
        	->set_content_type('application/json', 'utf-8')
        	->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
			->_display();
		exit;
	}

	private function generateToken($payload) {
		$header = base64_encode(
			json_encode(
				array(
					"alg" => "HS256",
					"typ" => "JWT"
				)
			)
		);

		$payload = base64_encode(
			json_encode($payload)
		);

		$signature = hash_hmac("sha256", "$header.$payload", SECRET_KEY);

		return "$header.$payload.$signature";

	}
}
