<?php

require ( 'includes/Init.php' );

require ( 'includes/Page.php' );

$pageName = Page::GetPageName();

require ( 'includes/pages/'.$pageName.'.php' );

$className = "Page$pageName";

$page = new $className();

require ( 'includes/Output.php' );

Output::Render($page);
