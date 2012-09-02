<?php

abstract class Ajax {
	protected $data = array();
	
	public function __construct ( ) {
		$this->perform();
		$this->output();
	}
	
	abstract protected function perform();
	
	protected function setError ( $msg ) {
		$this->data['error'] = $msg;
		$this->data['status'] = 1;
	}
	
	protected function setSuccess ( $msg=null ) {
		if ( is_null($msg) ) {
			$this->data['success'] = gfMsg('ajax-success');
		} else {
			$this->data['success'] = $msg;
		}
		$this->data['status'] = 0;
	}
	
	protected function output ( ) {
		header ( 'Content-Type: application/json' );
		echo json_encode($this->data);
	}
}
