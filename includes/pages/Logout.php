<?php

class PageLogout extends Page {
	
	protected function render ( ) {
		gfGetAuth()->logout();
		
		gfRedirect();
	}
}
