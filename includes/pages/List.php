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
		
		$userid = gfGetAuth()->getUserData('userid');
		
		$i = gfDBQuery("SELECT r.`ranking_ranking` AS `ranking`,
			r.`ranking_rating` AS `rating`, e.`episode_id`,
			e.`episode_name`,
			e.`episode_season`, e.`episode_seasonnumber`
			FROM `rankings` r
				JOIN `episodes` e
					ON e.`episode_id` = r.`episode_id`
			WHERE r.`user_id` = $userid
			ORDER BY r.`ranking_ranking` DESC, e.`episode_number` ASC");
		
		$ranked = array();
		
		$list = $this->makeList($i, $ranked);
		
		$list = gfRawMsg('<h1>$1</h1>
<div class="episodelist" id="ranked">$2</div>',
			gfMsg('index-userlist'),
			$list
		);
		
		$this->content .= $list;
		
		$where = '';
		
		foreach ( $ranked as $i ) {
			if ( $where != '' )
				$where .= ', ';
			$where .= $i;
		}
		
		$i = gfDBQuery("SELECT NULL AS `ranking`,
			NULL AS `rating`, e.`episode_id`,
			e.`episode_name`,
			e.`episode_season`, e.`episode_seasonnumber`
			FROM `episodes` e
			WHERE e.`episode_id` NOT IN ($where)
			ORDER BY e.`episode_number` ASC");
		
		if ( gfDBGetNumRows($i) > 0 ) {
			$ranked = array();
			
			$list = $this->makeList($i, $ranked, false);
			
			$list = gfRawMsg('<h2>$1</h2>
<div class="episodelist" id="unranked">$2</div>',
				gfMsg('index-userlist-unranked'),
				$list
			);
			
			$this->content .= $list;
		}
		
		$this->content .= $this->listSortBox();
	}
}
