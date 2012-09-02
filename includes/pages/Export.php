<?php

class PageExport extends Page {
	
	private $defaultSyntax = '{RANK}. {TITLE}';
	
	protected function render ( ) {
		if ( !gfGetAuth()->isLoggedIn() )
			gfRedirect();
		
		if ( isset($_POST['export-submit']) )
			$this->handleExportSubmit();
		else
			$this->createExportForm();
	}
	
	private function handleExportSubmit ( ) {
		$syntax = $_POST['export-syntax'];
		$order = (isset($_POST['export-order'])
			?$_POST['export-order']
			:'asc'
		);
		$combineparts = isset($_POST['export-combineparts']);
		
		$errors = array();
		
		if ( !$syntax )
			$errors['syntax'] = gfMsg('exportform-err-syntaxrequired');
		
		if ( $this->hasErrors('createExportForm', $errors) )
			return;
		
		$order = ($order=='asc'?'ASC':'DESC');
		$userid = gfGetAuth()->getUserData('userid');
		
		$i = gfDBQuery("SELECT r.`ranking_ranking`,
			e.`episode_name`, e.`episode_number`,
			e.`episode_season`, e.`episode_seasonnumber`
			FROM `rankings` r
				JOIN `episodes` e
					ON e.`episode_id` = r.`episode_id`
			WHERE r.`user_id` = $userid
			ORDER BY r.`ranking_ranking` $order");
		
		$list = '';
		while ( $result = gfDBGetResult($i) ) {
			$name = $result['episode_name'];
			if ( $combineparts ) {
				if ( preg_match('@.* Part [234]@i', 
					$name) )
					continue;
				else
					$name = str_replace(' Part 1', '', $name);
			}
			$list .= gfRawMsg("$1\n",
				str_replace(
					array(
						'{RANK}',
						'{NO}',
						'{TITLE}',
						'{CODE}',
					),
					array(
						$result['ranking_ranking'],
						$result['episode_number'],
						$name,
						$this->prodCode($result['episode_season'],
							$result['episode_seasonnumber']
						),
					),
					$syntax
				)
			);
		}
		
		$this->content .= gfRawMsg('<fieldset>
<legend>$2</legend>
<textarea cols="52" rows="15">$1</textarea>
</fieldset>',
			$list,
			gfMsg('exportform-result')
		);
		
		$this->createExportForm();
	}
	
	protected function prodCode ( $season, $snumber ) {
		return gfRawMsg('$1ACV$2',
			$season, gfZero($snumber, 10)
		);
	}
	
	protected function createExportForm ( $errors=null, $data=null ) {
		$errors = $this->handleErrors($errors, 'syntax');
		
		$form = gfRawMsg('<form method="post">
<fieldset>
<legend>$1</legend>
<label for="export-syntax">$3$4: $2</label>
<input type="text" id="export-syntax" name="export-syntax" value="$5" />
<div>
$10
</div>
<input type="radio" name="export-order" id="export-order-asc" value="asc" checked="true" />
<label for="export-order-asc" class="tick">$6</label>
<input type="radio" name="export-order" id="export-order-desc" value="desc" />
<label for="export-order-desc" class="tick">$7</label>
<div class="clear"></div>
<input type="checkbox" name="export-combineparts" id="export-combineparts" />
<label for="export-combineparts" class="tick">$8</label>
<div class="clear"></div>
<input type="submit" name="export-submit" value="$9" />
</fieldset>
</form>',
			gfMsg('exportform-title'),
			gfMsg('form-required'),
			gfMsg('exportform-syntax'),
				$errors['syntax'],
			$this->defaultSyntax,
			gfMsg('exportform-order-asc'),
			gfMsg('exportform-order-desc'),
			gfMsg('exportform-combineparts'),
			gfMsg('exportform-submit'),
			$this->syntaxDescription()
		);
		
		$this->content .= $form;
	}
	
	private function syntaxDescription ( ) {
		$elements = array('rank', 'no', 'title', 'code');
		$list = '';
		
		foreach ( $elements as $item ) {
			$list .= gfRawMsg('<li><tt>{$1}</tt>: $2</li>',
				strtoupper($item),
				gfMsg('exportsyntax-'.$item)
			);
		}
		
		$list = gfRawMsg('<ul>$1</ul>', $list);
		
		return $list;
	}
}
