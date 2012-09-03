<?php

class PageList extends Page {
	
	protected function render ( ) {
		if ( gfGetAuth()->isLoggedIn() )
			$this->renderUserList();
		else
			gfRedirect();
	}
	
	private function renderUserList ( ) {
		$this->getEpisodes();
		
		$this->content .= gfRawMsg('<h1>$1</h1>
<div class="episodelist userlist" id="ranked"></div>',
			gfMsg('index-userlist')
		);
		
		$this->content .= gfRawMsg('<h2>$1</h2>
<div class="episodelist userlist" id="unranked"></div>',
			gfMsg('index-userlist-unranked')
		);
		
		$this->content .= $this->listSortBox();
	}
}
