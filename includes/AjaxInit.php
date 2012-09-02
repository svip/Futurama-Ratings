<?php

require ( 'includes/Init.php' );

require ( 'includes/Ajax.php' );

$action = ucfirst(@$_POST['action']);

if ( !file_exists('includes/ajax/'.$action.'.php') ) {
	$action = 'Error';
}

require ( 'includes/ajax/'.$action.'.php' );

$actionClass = "Ajax$action";

$ajax = new $actionClass();
