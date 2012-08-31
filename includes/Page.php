<?php

abstract class Page {
	protected $content = '';
	protected $title = '';
	protected $episodes = array();
	
	public function __construct ( ) {
		$this->getEpisodes();
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
	
	private function getEpisodes ( ) {
		$i = gfDBQuery("SELECT `episode_name`, `episode_id`
			FROM `episodes`");
		while ( $result = gfDBGetResult($i) ) {
			$this->episodes[$result['episode_id']] = $result['episode_name'];
		}
	}
	
	public function getContent ( ) {
		return $this->content;
	}
	
	public function getTitle ( ) {
		return $this->title;
	}
}
