<?php

class AjaxSetrating extends Ajax {
	
	protected function perform ( ) {
		if ( !gfGetQuery('id')
			|| !gfGetQuery('rating') ) {
			$this->setError(gfMsg('ajax-err-missingfields'));
			return;
		}
		
		if ( !gfGetAuth()->isLoggedIn() ) {
			$this->setError(gfMsg('ajax-err-notloggedin'));
			return;
		}
		
		$id = gfDBSanitise(gfGetQuery('id'), true);
		$rating = gfDBSanitise(gfGetQuery('rating'), true);
		
		if ( $id == 0 || $rating == 0 ) {
			$this->setError(gfMsg('ajax-err-badinput'));
			return;
		}
		
		$rating = round($rating*10, 0);
		
		if ( $rating < 0 || $rating > 100 ) {
			$this->setError(gfMsg('ajax-err-valueoutofrange', 0, 10));
			return;
		}
		
		$userid = gfGetAuth()->getUserData('userid');
		
		$i = gfDBQuery("SELECT `ranking_ranking`
			FROM `rankings`
			WHERE `user_id` = $userid
				AND `episode_id` = $id");
		
		if ( gfDBGetNumRows($i) > 0 ) {
			gfDBQuery("UPDATE `rankings`
				SET `ranking_rating` = $rating
				WHERE `user_id` = $userid
					AND `episode_id` = $id");
		} else {
			gfDBQuery("INSERT INTO `rankings`
				SET `user_id` = $userid,
					`episode_id` = $id,
					`ranking_rating` = $rating");
		}
		
		$this->data['id'] = $id;
		$this->data['rating'] = $rating;
		
		$this->setSuccess();
	}
}
