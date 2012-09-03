<?php

class AjaxGetuserepisodes extends Ajax {
	
	protected function perform ( ) {
		$userid = gfDBSanitise(gfGetQuery('userid'), true);
		
		if ( $userid == 0 ) {
			if ( gfGetAuth()->isLoggedIn() ) {
				$userid = gfGetAuth()->getUserData('userid');
			} else {
				$this->setError(gfMsg('ajax-err-nouserid'));
				return;
			}
		}
		
		$type = (gfGetQuery('type', 'ranked') == 'ranked'
			? 'ranked'
			: 'unranked'
		);
		$order = (gfGetQuery('order', 'asc') == 'asc'
			?'ASC'
			:'DESC'
		);
		
		if ( $type == 'unranked' ) {
			$this->getUnranked($userid, $order);
		} else {
			$this->getRanked($userid, $order);
		}
	}
	
	private function getUnranked ( $userid, $order ) {
		$i = gfDBQuery("SELECT 
			NULL AS `ranking`,
			NULL AS `rating`,
			e.`episode_id`, e.`episode_name`,
			e.`episode_season`, e.`episode_seasonnumber`,
			e.`episode_number`, e.`episode_id`
			FROM `episodes` e
				JOIN `rankings` r
					ON r.`episode_id` = e.`episode_id`
						AND r.`user_id` != $userid
			GROUP BY e.`episode_id`
			ORDER BY e.`episode_number` $order");
		
		$this->makeList($i);
	}
	
	private function getRanked ( $userid, $order ) {
		$i = gfDBQuery("SELECT 
			r.`ranking_ranking` AS `ranking`,
			r.`ranking_rating` AS `rating`,
			e.`episode_id`, e.`episode_name`,
			e.`episode_season`, e.`episode_seasonnumber`,
			e.`episode_number`, e.`episode_id`
			FROM `rankings` r
				JOIN `episodes` e
					ON r.`episode_id` = e.`episode_id`
			WHERE r.`user_id` = $userid
			GROUP BY e.`episode_id`
			ORDER BY `ranking` $order");
		
		$this->makeList($i);
	}
	
	private function makeList ( $i ) {
		$this->data['episodes'] = array();
		
		while ( $result = gfDBGetResult($i) ) {
			$this->data['episodes'][] = array (
				'id'            => $result['episode_id'],
				'ranking'       => $result['ranking'],
				'avgranking'    => null,
				'name'          => $result['episode_name'],
				'rating'        => $result['rating'],
				'avgrating'     => null,
				'number'        => $result['episode_number'],
				'season'        => $result['episode_season'],
				'seasonnumber'  => $result['episode_seasonnumber'],
			);
		}
		
		$this->setSuccess();
	}
}
