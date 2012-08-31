<?php

class Parser {
	private $episodes = array();
	
	/**
	 * Parse a text list.
	 */
	public static function UserList ( $content ) {
		$parser = new Parser();
		list($ranking, $notunderstood) = $parser->parseUserList($content);
		return array($ranking, $notunderstood);
	}
	
	public function __construct ( ) {
		$this->getEpisodes();
	}
	
	public function parseUserList ( $content ) {
		$lines = explode("\n", $content);
		$ranking = array(); // id => rank
		// because multiple episodes can have the same rank
		$notunderstood = array(); // array of lines not understood
		foreach ( $lines as $line ) {
			$this->parseLine($ranking, $notunderstood, $line);
		}
		return array($ranking, $notunderstood);
	}
	
	private function parseLine ( &$ranking, &$notunderstood,
			$line ) {
		if ( preg_match('@^[0-9]+/[0-9]+@', $line) ) {
			// Multiple rankings.
			$rank = explode('/', preg_replace('@^[0-9/]+).*@', '$1', $link));
		}
		$rank = intval(preg_replace('@^([0-9]+).*@', '$1', $line));
		if ( !is_numeric($rank) || $rank === 0 ) {
			// we have no rank!
			$notunderstood[] = $line;
			return;
		}
		$split = preg_split('@[,/] @i', $line);
		$prodcode = array();
		foreach ( $split as $section ) {
			if ( preg_match('@.*[0-9]ACV[0-9]{2}@i', $section) ) {
				// production code, use this instead of episode name
				$prodcode[] = preg_replace('@.*([0-9])ACV([0-9]{2}).*@i', '$1ACV$2', $section);
			}
		}
		if ( count($prodcode) > 0 ) {
			foreach ( $prodcode as $code ) {
				$ranking[$this->getEpisodeId($code)] = $rank;
			}
			return;
		} else {
			$name = preg_replace('@[0-9]+.? (.*)@', '$1', $line);
			if ( !$this->getEpisodeId($name) ) {
				// attempt with 'part 1', could be a film
				if ( $this->getEpisodeId(trim($name).' Part 1') ) {
					// success!
					$ranking[$this->getEpisodeId(trim($name).' Part 1')] = $rank;
					$ranking[$this->getEpisodeId(trim($name).' Part 2')] = $rank;
					$ranking[$this->getEpisodeId(trim($name).' Part 3')] = $rank;
					$ranking[$this->getEpisodeId(trim($name).' Part 4')] = $rank;
					return;
				}
			} else {
				$ranking[$this->getEpisodeId($name)] = $rank;
				return;
			}
		}
		$notunderstood[] = $line;
	}
	
	private function getEpisodeId ( $str ) {
		$str = trim($str);
		if (!isset($this->episodes[strtolower($str)])) {
			$str = str_replace(':', '', $str);
		}
		if (!isset($this->episodes[strtolower($str)])) {
			return false;
		}
		return $this->episodes[strtolower($str)];
	}
	
	/**
	 * Get all episode names and point them to their episode id,
	 * as well as the alternate names to episode id.
	 */
	private function getEpisodes ( ) {
		$i = gfDBQuery("SELECT `episode_name`, `episode_id`,
			`episode_season`, `episode_seasonnumber`
			FROM `episodes`");
		$episodes = array();
		while ( $result = gfDBGetResult($i) ) {
			$episodes[strtolower($result['episode_name'])] = $result['episode_id'];
			$episodes[gfRawMsg('$1acv$2',
				$result['episode_season'],
				gfZero($result['episode_seasonnumber'], 10)
			)] = $result['episode_id'];
		}
		$i = gfDBQuery("SELECT `alternatename_name`, `episode_id`
			FROM `alternatenames`");
		while ( $result = gfDBGetResult($i) ) {
			$episodes[strtolower(str_replace(':', '', $result['alternatename_name']))] = $result['episode_id'];
		}
		$this->episodes = $episodes;
	}
}
