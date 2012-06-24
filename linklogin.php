<?php
 
class LinkLogin extends CI_Controller {
 
	function __construct(){
 
		parent::__construct();
 		$this->load->helper('url'); 
	}
 
	function index(){
 
 
 
 
	}
 
 
	function initiate($id){
		$this->data['appKey'] = "";
		$this->data['appSecret'] = "";
		$this->data['callbackUrl'] = "http://50.56.97.92/ci/linklogin/data/" . $id ;
		$this->load->library('linkedin', $this->data);
		$this->linkedin->setResponseFormat(LINKEDIN::_RESPONSE_JSON);
		$token = $this->linkedin->retrieveTokenRequest();
		$this->load->model('add_model');
		$array = array ('oauth_request_token' => $token['linkedin']['oauth_token'], 'oauth_request_token_secret' => $token['linkedin']['oauth_token_secret'], 'user_id' => $this->session->userdata("user_id"), 'encrypt_key' => $this->unique_id);
		$this->add_model->insert_linkedin_token($array);
 		$link = "https://api.linkedin.com/uas/oauth/authorize?oauth_token=". $token['linkedin']['oauth_token'];  
		redirect($link);
	}
	function data($id, $first_login=0){
		$this->data['appKey'] = "";
		$this->data['appSecret'] = "";
		$this->data['callbackUrl'] = "http://50.56.97.92/ci/linklogin/data/" . $id ;
		$this->load->library('linkedin', $this->data);
		$this->linkedin->setResponseFormat(LINKEDIN::_RESPONSE_JSON);
		$this->load->model('add_model');
		$get_token = array('user_id' => $this->session->userdata("user_id"), 'encrypt_key' => $id );
		$tokens = $this->add_model->get_linkedin_token($get_token);
		$oauth_token = $tokens->oauth_request_token;
		$oauth_token_secret = $tokens->oauth_request_token_secret;
		$oauth_verifier = $this->input->get('oauth_verifier');
		print_r($_REQUEST);
		$response = $this->linkedin->retrieveTokenAccess($oauth_token, $oauth_token_secret, $oauth_verifier);
		$this->add_model->add_token($response['linkedin'] , $id);				
		$response = $this->linkedin->setTokenAccess($response['linkedin']);
		$profile = $this->linkedin->profile('~:(id,first-name,last-name,picture-url)');
		$user = json_decode($profile['linkedin']);
		$user_array = array('linkedin_id' => $user->id , 'second_name' => $user->lastName , 'profile_picture' => $user->pictureUrl , 'first_name' => $user->firstName);
		$this->add_model->add_token($user_array, $id);
		if($first_login == 1)
		{
		redirect(base_url()."brand/add/success/$id");	
		}
		else{
			redirect(base_url()."brand/edit/$id");
		}
		
	}
	function show($id) {
			$this->load->model('add_model');
			$get_token = array('user_id' => $this->session->userdata("user_id"), 'encrypt_key' => $id );
			$tokens = $this->add_model->get_linkedin_token($get_token);
			//$tokens = $this->session->userdata('tokens');
			print_r($tokens);
			$access_token = array('oauth_token' => $tokens->oauth_token, 'oauth_token_secret' => $tokens->oauth_token_secret, 'oauth_expires_in' => $tokens->oauth_expires_in, 'oauth_authorization_expires_in' => $tokens->oauth_authorization_expires_in);
			$response = $this->linkedin->setTokenAccess($access_token);
			$profile = $this->linkedin->profile('~:(id,first-name,last-name,picture-url)');
			$profile_connections = $this->linkedin->profile('~/connections:(id,first-name,last-name,picture-url,industry)');
	//		$companies = $this->linkedin->company('1337:(id,name,ticker,description,logo-url,locations:(address,is-headquarters))');
			$user = json_decode($profile['linkedin']);
			$user_array = array('linkedin_id' => $user->id , 'second_name' => $user->lastName , 'profile_picture' => $user->pictureUrl , 'first_name' => $user->firstName);
			$this->add_model->add_token($user_array, $id);
			print_r($user);
	//
	}
 
 }
?>