<?php

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var array $OBJECTS */

$TEMPLATE = new Template("static/help");

echo $TEMPLATE->render($OBJECTS);




