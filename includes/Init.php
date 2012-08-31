<?php

require ( 'includes/GlobalVariables.php' );

require ( 'config.php' );

if ( $Debug ) {
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
}

require ( 'includes/Database.php' );

require ( 'includes/GlobalFunctions.php' );

require ( 'includes/Authentication.php' );

require ( 'includes/Parser.php' );
