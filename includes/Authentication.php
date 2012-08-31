<?php

class Authentication {

	private $verifiedLogins = array();
	private $loggedIn = false;
	private $reviewer = false;
	private $admin = false;
	private $userData = array(
		'username' => '',
		'userid'   => 0,
	);
	
	public static function get ( ) {
		global $auth;
		if ( is_null($auth) )
			$auth = new Authentication();
		return $auth;
	}
	
	public function __construct ( ) {
		$this->loggedIn = $this->checkCookieLogin();
		$this->mode = $this->getSetMode();
	}
	
	public function getUserData($data) {
		if ( $this->isLoggedIn() )
			return $this->userData[$data];
		return null;
	}
	
	public function isReviewer() {
		return $this->reviewer;
	}
	
	public function isAdmin() {
		return $this->admin;
	}
	
	private function checkCookieLogin() {
		$username = $_COOKIE['ratings-username'];
		$password = $_COOKIE['ratings-password'];
		
		if(!$username || !$password)
			return false;
		
		if(!$this->verifyLoginCombo($username, $password, true))
			return false;
		
		$i = gfDBQuery("SELECT `username`, `id`, `reviewer`, `admin`
			FROM `users`
			WHERE LOWER(`username`) = '".strtolower($username)."'");
		
		$result = gfDBGetResult($i);
		
		$this->userData['username'] = $result['username'];
		$this->userData['userid'] = $result['id'];
		$this->reviewer = ($result['reviewer']==1);
		$this->admin = ($result['admin']==1);
		
		return true;
	}
	
	public function isLoggedIn() {
		return $this->loggedIn;
	}	

	public function verifyLoginCombo($username, $password,
			$passwordIsMd5 = false) {
		
		$username = gfDBSanitise($username);
		
		if ( !$passwordIsMd5 )
			$password = md5($password);
		
		if ( isset($this->verifiedLogins[$username][$password]) )
			return $this->verifiedLogins[$username][$password];
		
		$i = gfDBQuery("SELECT `password` FROM `users` WHERE LOWER(`username`) = '".strtolower($username)."'");
		
		if ( gfDBGetNumRows($i) != 1 ) {
			$this->verifiedLogins[$username] = array($password => false);
			return false;
		}
		
		$result = gfDBGetResult($i);
		
		if ( $password != $result['password'] ) {
			$this->verifiedLogins[$username] = array($password => false);
			return false;
		}
		
		$this->verifiedLogins[$username] = array($password => true);
		return true;
	}
	
	public function performLogin($username, $password,
			$passwordIsMd5 = false) {
		if ( !$this->verifyLoginCombo($username, $password,
				$passwordIsMd5) )
			return false;
		
		if ( !$passwordIsMd5 )
			$password = md5($password);
		
		setcookie('ratings-username', $username,
			time()+365*24*60*60);
		setcookie('ratings-password', $password,
			time()+365*24*60*60);
		
		return true;
	}

}

$auth = null;
