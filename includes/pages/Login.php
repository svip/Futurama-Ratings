<?php

class PageLogin extends Page {
	
	protected function render ( ) {
		if ( gfGetAuth()->isLoggedIn() ) {
			$this->errorBox('login-alreadyloggedin');
			return;
		}
		$this->title = gfMsg('login-title');
		if ( isset($_POST['login-submit']) ) {
			$this->handleLoginSubmit();
		} else {
			$this->createLoginForm();
		}
	}
	
	protected function createLoginForm ( $errors=null,
			$data=null ) {
		$errors = $this->handleErrors($errors, 'username',
			'password');
		$form = gfRawMsg('<form method="post">
<fieldset>
<legend>$1</legend>
<label for="login-username">$3$4: $2</label>
<input type="text" name="login-username" id="login-username" />
<label for="login-password">$5$6: $2</label>
<input type="password" name="login-password" id="login-password" />
<input type="submit" name="login-submit" value="$7" />
</fieldset>
</form>',
			gfMsg('loginform-title'),
			gfMsg('form-required'),
			gfMsg('loginform-username'),
				$errors['username'],
			gfMsg('loginform-password'),
				$errors['password'],
			gfMsg('loginform-submit')
		);
		$this->content = $form;
	}
	
	private function handleLoginSubmit ( ) {
		$username = $_POST['login-username'];
		$password = $_POST['login-password'];
		
		$errors = array();
		
		if ( empty($username) )
			$errors['username'] = gfMsg('form-err-required');
		if ( empty($password) )
			$errors['password'] = gfMsg('form-err-required');
		
		if ( $this->hasErrors('createLoginForm', $errors) )
			return;
		
		if ( !gfGetAuth()->performLogin($username, $password) )
			$errors['username'] = gfMsg('loginform-err-failure');
		
		if ( $this->hasErrors('createLoginForm', $errors) )
			return;
		
		gfRedirect();
	}
}
