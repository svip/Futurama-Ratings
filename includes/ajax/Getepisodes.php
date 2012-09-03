<?php

class AjaxGetepisodes extends Ajax {
	
	protected function perform ( ) {
		$order = (gfGetQuery('order', 'asc') == 'asc'
			?'ASC'
			:'DESC'
		);
		$sortby = (gfGetQuery('sortby', 'ranking') == 'ranking'
			?'ranking'
			:'rating'
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
			ORDER BY `$sortby` $order");
		
		$this->data['episodes'] = array();
		
		while ( $result = gfDBGetResult($i) ) {
			$this->data['episodes'][] = array (
				'id'            => $result['episode_id'],
				'avgranking'    => (is_null($result['ranking'])
				                    ?null
				                    :$result['ranking']),
				'name'          => $result['episode_name'],
				'rating'        => null,
				'avgrating'     => (is_null($result['rating'])
				                    ?null
				                    :$result['rating']/10.0),
				'number'        => $result['episode_number'],
				'season'        => $result['episode_season'],
				'seasonnumber'  => $result['episode_seasonnumber'],
			);
		}
		
		$j = 1;
		
		if ( ($order == 'DESC' && $sortby == 'ranking')
			|| ($order == 'ASC' && $sortby == 'rating') )
			$j = count($this->data['episodes']);
		
		foreach ( $this->data['episodes'] as $i => $episode ) {
			$this->data['episodes'][$i]['ranking'] = $j;
			
			if ( ($order == 'DESC' && $sortby == 'ranking')
				|| ($order == 'ASC' && $sortby == 'rating') )
				$j--;
			else
				$j++;
		}
		
		$this->setSuccess();
	}
}
