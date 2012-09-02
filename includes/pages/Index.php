<?php

class PageIndex extends Page {
	
	protected function render ( ) {
		$this->renderGlobalList();
	}
	
	private function renderGlobalList ( ) {
		$i = gfDBQuery("SELECT 
			SUM(r.`ranking_ranking`)/COUNT(r.`ranking_ranking`) AS `ranking`,
			SUM(r.`ranking_rating`)/COUNT(r.`ranking_rating`) AS `rating`,
			e.`episode_id`, e.`episode_name`,
			e.`episode_season`, e.`episode_seasonnumber`
			FROM `rankings` r
				JOIN `episodes` e
					ON r.`episode_id` = e.`episode_id`
			GROUP BY e.`episode_id`
			ORDER BY `ranking` DESC");
		
		$list = '';
		$previousRanking = -1;
		
		$this->getEpisodes();
		$ranked = array();
		
		$list = $this->makeList($i, $ranked, false);
		
		$list = gfRawMsg('<h1>$1</h1>
<div class="episodelist" id="ranked">$2</div>',
			gfMsg('index-globallist'),
			$list
		);
		
		$this->content .= $list;
		
		$this->content .= $this->listSortBox();
	}
}
