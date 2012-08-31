<?php

class Output {
	
	public static function Render ( $page ) {
		$template = file_get_contents('includes/template.html');
		
		echo str_replace (
			array (
				'{{CONTENT}}',
				'{{TITLE}}',
			),
			array (
				$page->getContent(),
				$page->getTitle(),
			),
			$template
		);
	}
}
