<?php

abstract class Page {
	protected $content = '';
	protected $title = '';
	protected $menu = '';
	protected $episodes = array();
	
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
	
	protected function handleErrors ( $errors ) {
		$args = func_get_args();
		$newErrors = array();
		foreach ( $args as $i => $arg ) {
			if ( $i === 0 ) continue;
			if ( isset($errors[$arg]) ) {
				$newErrors[$arg] = gfRawMsg(' <span class="error">$1</span>', $errors[$arg]);
			} else {
				$newErrors[$arg] = '';
			}
		}
		return $newErrors;
	}
	
	protected function hasErrors ( $callback, $errors, $data=null ) {
		if ( count($errors) == 0 )
			return false;
		
		if ( is_array ( $callback) ) {
			if ( is_null ($data) )
				$callback[0]->$callback[1]($errors);
			else
				$callback[0]->$callback[1]($errors, $data);
		} else {
			if ( is_null ($data) )
				$this->$callback($errors);
			else
				$this->$callback($errors, $data);
		}
		return true;
	}
	
	protected function displayRating ( $rating ) {
		if ( is_null($rating) )
			return '?';
		return gfRawMsg('$1', $rating/100.0);
	}
	
	protected function listSortBox ( ) {
		return gfRawMsg('<div id="listsortbox">
<h3>$1</h3>
<input type="radio" name="listsort" id="listsort-ranking-asc" onchange="listSort(this);" checked="true" />
<label for="listsort-ranking-asc">$2</label><br />
<input type="radio" name="listsort" id="listsort-ranking-desc" onchange="listSort(this);" />
<label for="listsort-ranking-desc">$3</label><br />
<input type="radio" name="listsort" id="listsort-rating-desc" onchange="listSort(this);" />
<label for="listsort-rating-desc">$9</label><br />
<input type="radio" name="listsort" id="listsort-rating-asc" onchange="listSort(this);" />
<label for="listsort-rating-asc">$8</label><br />
<h3>$4</h3>
<input type="radio" name="colourcode" id="colourcode-none" onchange="colourCode(this);" checked="true" />
<label for="colourcode-none">$5</label>
<input type="radio" name="colourcode" id="colourcode-seasons" onchange="colourCode(this);" />
<label for="colourcode-seasons">$6</label>
<input type="radio" name="colourcode" id="colourcode-runs" onchange="colourCode(this);" />
<label for="colourcode-runs">$7</label>
</div>',
			gfMsg('listsort-title'),
			gfMsg('listsort-ranking-asc'),
			gfMsg('listsort-ranking-desc'),
			gfMsg('colourcode-title'),
			gfMsg('colourcode-none'),
			gfMsg('colourcode-seasons'),
			gfMsg('colourcode-runs'),
			gfMsg('listsort-rating-asc'),
			gfMsg('listsort-rating-desc')
		);
	}
	
	protected function getEpisodes ( ) {
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
		if ( empty($this->title) )
			return gfMsg('site-title');
		return gfRawMsg('$1 - $2', $this->title, gfMsg('site-title'));
	}
	
	public function getMenu ( ) {
		$menu = array();
		$menu[] = array(gfLink(), gfMsg('menu-index'));
		if ( gfGetAuth()->isLoggedIn() ) {
			$menu[] = array(gfLink('list'), gfMsg('menu-list'));
			$menu[] = array(gfLink('logout'), gfMsg('menu-logout'));
			$menu[] = array(gfLink('parser'), gfMsg('menu-parser'));
			$menu[] = array(gfLink('export'), gfMsg('menu-export'));
			$menu[] = array(gfLink('user', array('id'=>gfGetAuth()->getUserData('userid'))), gfMsg('menu-user'));
		} else {
			$menu[] = array(gfLink('login'), gfMsg('menu-login'));
			$menu[] = array(gfLink('register'), gfMsg('menu-register'));
		}
		foreach ( $menu as $item ) {
			$this->menu .= gfRawMsg('<li><a href="$1">$2</a></li>',
				$item[0], $item[1]
			);
		}
		$this->menu = gfRawMsg('<ul>$1</ul>', $this->menu);
		return $this->menu;
	}
}
