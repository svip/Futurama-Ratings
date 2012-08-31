<?php

abstract class Page {
	protected $content = '';
	protected $title = '';
	
	public function __construct ( ) {
		$this->render();
	}
	
	public static function GetPageName ( ) {
		if ( !isset($_GET['p']) )
			return 'Index';
		$page = $_GET['p'];
		$pageName = ucfirst(strtolower($page));
		if ( file_exists('includes/pages/'.$pageName.'.php') )
			return $pageName;
		return 'Index';
	}
	
	abstract protected function render();
	
	public function getContent ( ) {
		return $this->content;
	}
	
	public function getTitle ( ) {
		return $this->title;
	}
}
