<?php

class AjaxError extends Ajax {
	
	protected function perform ( ) {
		$this->setError(gfMsg('ajax-err-noaction'));
	}
}
