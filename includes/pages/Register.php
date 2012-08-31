<?php

class PageRegister extends Page {
	
	protected function render ( ) {
		if ( gfGetAuth()->isLoggedIn() ) {
			$this->errorBox('register-alreadyloggedin');
			return;
		}
		$this->title = gfMsg('register-title');
		if ( isset($_POST['register-submit']) ) {
			$this->handleRegisterSubmit();
		} else {
			$this->createRegisterForm();
		}
	}
	
	protected function createRegisterForm ( $errors=null,
			$data=null ) {
		$errors = $this->handleErrors($errors, 'username',
			'password1', 'password2', 'email');
		$form = gfRawMsg('<form method="post">
<fieldset>
<legend>$1</legend>
<label for="register-username">$3$4: $2</label>
<input type="text" name="register-username" id="register-username" />
<label for="register-password1">$5$6: $2</label>
<input type="password" name="register-password1" id="register-password1" />
<label for="register-password2">$7$8: $2</label>
<input type="password" name="register-password2" id="register-password2" />
<label for="register-email">$9$10: $2</label>
<input type="text" name="register-email" id="register-email" />
<input type="submit" name="register-submit" value="$11" />
</fieldset>
</form>',
			gfMsg('registerform-title'),
			gfMsg('form-required'),
			gfMsg('registerform-username'),
				$errors['username'],
			gfMsg('registerform-password1'),
				$errors['password1'],
			gfMsg('registerform-password2'),
				$errors['password2'],
			gfMsg('registerform-email'),
				$errors['email'],
			gfMsg('registerform-submit')
		);
		$this->content = $form;
	}
	
	private function handleRegisterSubmit ( ) {
		$username = $_POST['register-username'];
		$password1 = $_POST['register-password1'];
		$password2 = $_POST['register-password2'];
		$email = $_POST['register-email'];
		
		$errors = array();
		
		if ( empty($username) )
			$errors['username'] = gfMsg('form-err-required');
		if ( empty($password1) )
			$errors['password1'] = gfMsg('form-err-required');
		if ( empty($password2) )
			$errors['password2'] = gfMsg('form-err-required');
		if ( !empty($email) )
			$errors['email'] = gfMsg('registerform-err-emailrequired');
		
		if ( $this->hasErrors('createRegisterForm', $errors) )
			return;
		
		if ( $password1 != $password2 )
			$errors['password1'] = gfMsg('registerform-err-passwordnotmatch');
		
		if ( $this->hasErrors('createRegisterForm', $errors) )
			return;
		
		$userid = gfGetAuth()->newUser($username, $password1);
		if ( $userid === false )
			$errors['username'] = gfMsg('registerform-err-usernameexists');
		
		if ( $this->hasErrors('createRegisterForm', $errors) )
			return;
		
		gfGetAuth()->performLoginWithUserid($userid, $password1, false);
		
		gfRedirect();
	}
}
