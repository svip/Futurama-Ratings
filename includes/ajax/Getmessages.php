<?php

class AjaxGetmessages extends Ajax {
	
	protected function perform ( ) {
		global $messages;
		
		$this->data['messages'] = array();
		
		foreach ( $messages as $msg => $message ) {
			if ( !preg_match('@^js-.*@', $msg) )
				continue;
			$this->data['messages'][str_replace('js-', '', $msg)] = $message;
		}
	}
}
