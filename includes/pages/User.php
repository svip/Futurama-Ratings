<?php

class PageUser extends Page {
	
	protected function render ( ) {
		$userid = gfDBSanitise(gfGetQuery('id'), true);
		if ( $userid == 0 )
			gfRedirect();
		else
			$this->renderUserList($userid);
	}
	
	private function renderUserList ( $userid ) {
		$i = gfDBQuery("SELECT `user_name`
			FROM `users`
			WHERE `user_id` = $userid");
		
		if ( gfDBGetNumRows($i) != 1 ) {
			gfRedirect();
			return;
		}
		
		$result = gfDBGetResult($i);
		
		$this->content .= gfRawMsg('<input type="hidden" id="list-userid" value="$1" />',
			$userid
		);
		
		$this->content .= gfRawMsg('<h1>$1</h1>
<div class="episodelist userlist" id="ranked"></div>',
			gfMsg('index-otheruserlist', 
				$result['user_name']
			)
		);
		
		$this->content .= $this->listSortBox();
	}
}
