<?php

class PageIndex extends Page {
	
	protected function render ( ) {
		$this->renderGlobalList();
	}
	
	private function renderGlobalList ( ) {
		$list = gfRawMsg('<h1>$1</h1>
<div class="episodelist fulllist" id="ranked"></div>',
			gfMsg('index-globallist')
		);
		
		$this->content .= $list;
		
		$this->content .= $this->listSortBox();
	}
}
