<?php

class PageParser extends Page {
	
	protected function render ( ) {
		if ( !gfGetAuth()->isLoggedIn() ) {
			$this->errorBox('parser-notloggedin');
			return;
		}
		
		if ( isset($_POST['parser-submit']) ) {
			$this->handleParserSubmit();
		} else {
			$this->createParserForm();
		}
	}
	
	protected function createParserForm ( $errors=null ) {
		$errors = $this->handleErrors($errors, 'text');
		
		$form = gfRawMsg('<form method="post">
<fieldset>
<legend>$1</legend>
<label for="parser-text">$2$3: $4</label>
<textarea cols="52" rows="10" name="parser-text" id="parser-text"></textarea>
<input type="submit" name="parser-submit" value="$5" />
</fieldset>
</form>',
			gfMsg('parserform-title'),
			gfMsg('parserform-text'),
				$errors['text'],
			gfMsg('form-required'),
			gfMsg('parserform-submit')
		);
		$this->content = $form;
	}
	
	private function handleParserSubmit ( ) {
		$text = $_POST['parser-text'];
		
		$errors = array();
		
		if ( !$text )
			$errors['text'] = gfMsg('form-err-required');
		
		if ( $this->hasErrors('createParserForm', $errors) )
			return;
		
		list($ranking, $notunderstood) = Parser::UserList($text);
		
		if ( count($notunderstood) > 0) {
			$tmp = '';
			foreach ( $notunderstood as $line ) {
				if ( $tmp != '' )
					$tmp .= "<br />\n";
				$tmp .= $line;
			}
			$tmp = gfRawMsg('<h3>$1</h3>
<div class="parserresult-notunderstood">$2</div>
<p>$3</p>',
				gfMsg('parserresult-notunderstood-title'),
				$tmp,
				gfMsg('parserresult-notunderstood-description')
			);
			$this->content .= $tmp;
		}
		$list = '';
		$this->getEpisodes();
		foreach ( $ranking as $id => $rank ) {
			if (!isset($this->episodes[$id]))
				continue;
			$list .= gfRawMsg('<li value="$1">$2</li>', 
				$rank,
				$this->episodes[$id]
			);
			$this->ratingToDB($rank, $id);
		}
		$list = gfRawMsg('<ol>$1</ol>', $list);
		$this->content .= gfRawMsg('<h2>$1</h2>
$2',
			gfMsg('parserresult-title'),
			$list
		);
	}
	
	private function ratingToDB ( $rank, $episodeid ) {
		$userid = gfGetAuth()->getUserData('userid');
		$i = gfDBQuery("SELECT `ranking_ranking`
			FROM `rankings`
			WHERE `episode_id` = $episodeid
				AND `user_id` = $userid");
		
		if ( gfDBGetNumRows($i) == 0 ) {
			gfDBQuery("INSERT INTO `rankings`
				SET `user_id` = $userid,
					`episode_id` = $episodeid,
					`ranking_ranking` = $rank");
		} else {
			gfDBQuery("UPDATE `rankings`
				SET `ranking_ranking` = $rank
				WHERE `user_id` = $userid
					AND `episode_id` = $episodeid");
		}
	}
}
