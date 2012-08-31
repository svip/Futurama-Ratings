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
	}
	
	public function newUser ( $username, $password ) {
		if ( trim($username) == ''
			|| trim($password) == '' )
			return false;
		
		$saltedPassword = $this->generateSaltedPassword($password);
		$username = gfDBSanitise($username);
		
		$i = gfDBQuery("SELECT `user_id` 
			FROM `users`
			WHERE LOWER(`user_name`) = '".strtolower($username)."'");
		
		if ( gfDBGetNumRows($i) > 0 )
			return false;
		
		$i = gfDBQuery("INSERT INTO `users` (`user_name`,
			`user_password`, `user_joined`)
			VALUES ('$username', '$saltedPassword', NOW() )");
		
		$userid = gfDBGetInsertId($i);
		
		return $userid;
	}
	
	/**
	 * Change the password for a registered user.
	 *
	 * @param $userid The userid of the user.
	 * @param $password The raw password.
	 * @return Boolean whether it was a success.
	 */
	public function changePassword ( $userid, $password ) {
		$saltedPassword = $this->generateSaltedPassword($password);
		
		gfDBQuery ("UPDATE `users`
			SET `user_password` = '$saltedPassword'
			WHERE `user_id` = $userid");
		
		return true;
	}
	
	/**
	 * Generate a password with salt.
	 *
	 * @param $password The raw password.
	 * @param $salt (optional) The salt to use; if not set, a random salt will
	 *                         be generated.
	 * @return The salt along with the salted password.
	 */
	public function generateSaltedPassword ( $password, $salt=null ) {
		if ( is_null($salt) )
			$salt = substr($this->generateToken(),0,8);
		return ':'.$salt.':'.md5($salt . '-' . md5($password));
	}
	
	/**
	 * Generate a random 32 character token.
	 *
	 * @return The token.
	 */
	public function generateToken ( ) {
		return md5( mt_rand( 0, 0x7fffffff ));
	}
	
	/**
	 * Get the user's current IP, with handling for cache servers.
	 *
	 * @return The IP.
	 */
	public function getUserIP ( ) {
		global $CacheServers;
		
		if ( !in_array($_SERVER['REMOTE_ADDR'], $CacheServers) )
			return $_SERVER['REMOTE_ADDR'];
		else
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	
	public function getUserData($data) {
		if ( $this->isLoggedIn()
			&& isset($this->userData[$data]) )
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
		if ( !isset($_COOKIE['ratings-userid']) 
			|| !isset($_COOKIE['ratings-password']) )
			return false;
		
		$userid = gfDBSanitise($_COOKIE['ratings-userid'], true);
		$password = $_COOKIE['ratings-password'];
		
		if( !$userid || !$password )
			return false;
		
		if( !$this->verifyLoginCombo($userid, $password, true) )
			return false;
		
		$i = gfDBQuery("SELECT `user_name`, `user_id`
			FROM `users`
			WHERE `user_id` = $userid");
		
		$result = gfDBGetResult($i);
		
		$this->loggedIn = true;
		
		$this->userData['username'] = $result['user_name'];
		$this->userData['userid'] = $result['user_id'];
		
		return true;
	}
	
	public function isLoggedIn() {
		return $this->loggedIn;
	}
	
	/**
	 * Compare an unsalted password with a salted password.
	 *
	 * @param $testPassword
	 * @param $againstPassword
	 * @return Boolean Whether they match.
	 */
	public function comparePassword ( $testPassword, 
		$againstPassword, $salted=false ) {
		if ( $salted ) {
			$againstPassword = explode(':', $againstPassword);
			return $testPassword == $againstPassword[2];
		} else {
			$salt = explode(':', $againstPassword);
			return $this->generateSaltedPassword($testPassword, $salt[1]) == $againstPassword;
		}
	}
	
	public function verifyLoginCombo ( $userid, $password,
			$passwordIsHashed = false ) {
		
		$i = gfDBQuery("SELECT `user_id`, `user_password`
			FROM `users`
			WHERE `user_id` = $userid");
		
		if ( gfDBGetNumRows($i) != 1 ) {
			return false;
		}
		
		$result = gfDBGetResult($i);
		
		return $this->comparePassword($password, $result['user_password'], $passwordIsHashed);
	}
	
	public function performLogin ( $username, $password,
			$passwordIsHashed=false ) {
		$i = gfDBQuery("SELECT `user_id`
			FROM `users`
			WHERE LOWER(`user_name`) = '".strtolower($username)."'");
		
		$result = gfDBGetResult($i);
		
		return $this->performLoginWithUserid($result['user_id'],
			$password, $passwordIsHashed);
	}
	
	public function performLoginWithUserid($userid, $password,
			$passwordIsHashed = false) {
		$i = gfDBQuery("SELECT `user_id`, `user_password`
			FROM `users`
			WHERE `user_id` = $userid");
		
		if ( gfDBGetNumRows($i) != 1 ) {
			return false;
		}
		
		$result = gfDBGetResult($i);
		
		if ( !$this->comparePassword($password, $result['user_password'], $passwordIsHashed) )
			return false;
		
		if ( !$passwordIsHashed )
			$password = $result['user_password'];
		
		$password = explode(':', $password);
		$password = $password[count($password)-1];
		
		setcookie('ratings-userid', $userid,
			time()+365*24*60*60);
		setcookie('ratings-password', $password,
			time()+365*24*60*60);
		
		return true;
	}

}

$auth = null;
