<?php

class PageIndex extends Page {
	
	protected function render ( ) {
		if ( gfGetAuth()->isLoggedIn() )
			$this->renderUserList();
		else
			$this->renderGlobalList();
	}
	
	private function renderGlobalList ( ) {
		$this->content = '<p>Global list not yet implemented, register for now.</p>';
	}
	
	private function renderUserList ( ) {
		$this->getEpisodes();
		
		$unranked = array();
		
		foreach ( $this->episodes as $id => $name ) {
			$unranked[$id] = $name;
		}
		
		$userid = gfGetAuth()->getUserData('userid');
		
		$i = gfDBQuery("SELECT r.`ranking_ranking`,
			r.`ranking_rating`, e.`episode_id`, e.`episode_name`
			FROM `rankings` r
				JOIN `episodes` e
					ON e.`episode_id` = r.`episode_id`
			WHERE r.`user_id` = $userid
			ORDER BY r.`ranking_ranking` DESC, e.`episode_number` ASC");
		
		$list = '';
		$previousRanking = -1;
		
		while ( $result = gfDBGetResult($i) ) {
			if ( $previousRanking != -1
				&& abs($result['ranking_ranking']-$previousRanking) > 1 ) {
				$list .= gfRawMsg('<div class="gap">$1</div>',
					gfMsg('index-gap')
				);
			}
			$list .= gfRawMsg('<div class="episode">
<div class="episode-ranking">$1</div>
<div class="episode-title">$2</div>
<div class="episode-rating">$3</div>
</div>',
				$result['ranking_ranking'],
				$result['episode_name'],
				$this->displayRating($result['ranking_rating'])
			);
			unset($unranked[$result['episode_id']]);
			$previousRanking = $result['ranking_ranking'];
		}
		
		$list = gfRawMsg('<h1>$1</h1>
<div class="episodelist">$2</div>',
			gfMsg('index-userlist'),
			$list
		);
		
		$this->content .= $list;
		
		if ( count($unranked) > 0 ) {
			$list = '';
			
			ksort($unranked);
			
			foreach ( $unranked as $id => $name ) {
				$list .= gfRawMsg('<div class="episode">
<div class="episode-ranking">?</div>
<div class="episode-title">$1</div>
<div class="episode-rating">?</div>
</div>',
					$name
				);
			}
			
			$list = gfRawMsg('<h2>$1</h2>
<div class="episodelist">$2</div>',
				gfMsg('index-userlist-unranked'),
				$list
			);
			
			$this->content .= $list;
		}
	}
}
