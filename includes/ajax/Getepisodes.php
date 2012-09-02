<?php

class AjaxGetepisodes extends Ajax {
	
	protected function perform ( ) {
		$order = (gfGetQuery('order', 'asc') == 'asc'
			?'ASC'
			:'DESC'
		);
		
		$i = gfDBQuery("SELECT 
			SUM(r.`ranking_ranking`)/COUNT(r.`ranking_ranking`)
				AS `ranking`,
			SUM(r.`ranking_rating`)/COUNT(r.`ranking_rating`)
				AS `rating`,
			e.`episode_id`, e.`episode_name`,
			e.`episode_season`, e.`episode_seasonnumber`,
			e.`episode_number`, e.`episode_id`
			FROM `rankings` r
				JOIN `episodes` e
					ON r.`episode_id` = e.`episode_id`
			GROUP BY e.`episode_id`
			ORDER BY `ranking` $order");
		
		$this->data['episodes'] = array();
		
		while ( $result = gfDBGetResult($i) ) {
			$this->data['episodes'][] = array (
				'id'            => $result['episode_id'],
				'avgranking'    => $result['ranking'],
				'name'          => $result['episode_name'],
				'avgrating'     => $result['rating'],
				'number'        => $result['episode_number'],
				'season'        => $result['episode_season'],
				'seasonnumber'  => $result['episode_seasonnumber'],
			);
		}
		
		$j = 1;
		
		if ( $order == 'DESC' )
			$j = count($this->data['episodes']);
		
		foreach ( $this->data['episodes'] as $i => $episode ) {
			$this->data['episodes'][$i]['ranking'] = $j;
			
			if ( $order == 'DESC' )
				$j--;
			else
				$j++;
		}
	}
}
