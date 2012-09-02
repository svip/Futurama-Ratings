<?php

class AjaxSetranking extends Ajax {
	
	protected function perform ( ) {
		if ( !isset($_POST['id'])
			|| !isset($_POST['ranking']) ) {
			$this->setError(gfMsg('ajax-err-missingfields'));
			return;
		}
		
		if ( !gfGetAuth()->isLoggedIn() ) {
			$this->setError(gfMsg('ajax-err-notloggedin'));
			return;
		}
		
		$id = gfDBSanitise($_POST['id'], true);
		$ranking = gfDBSanitise($_POST['ranking'], true);
		
		if ( $id == 0 || $ranking == 0 ) {
			$this->setError(gfMsg('ajax-err-badinput'));
			return;
		}
		
		$userid = gfGetAuth()->getUserData('userid');
		
		$i = gfDBQuery("SELECT `ranking_ranking`
			FROM `rankings`
			WHERE `user_id` = $userid
				AND `episode_id` = $id");
		
		if ( gfDBGetNumRows($i) > 0 ) {
			gfDBQuery("UPDATE `rankings`
				SET `ranking_ranking` = $ranking
				WHERE `user_id` = $userid
					AND `episode_id` = $id");
		} else {
			gfDBQuery("INSERT INTO `rankings`
				SET `user_id` = $userid,
					`episode_id` = $id,
					`ranking_ranking` = $ranking");
		}
		
		$this->data['id'] = $id;
		$this->data['ranking'] = $ranking;
		
		$this->setSuccess();
	}
}
