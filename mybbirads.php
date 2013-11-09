<?php

define("IN_MYBB", 1);
define("NO_ONLINE", 1);
define('THIS_SCRIPT', 'mybbirads.php');

require_once "./global.php";
global $db;
if($mybb->input['id'])
{
$query = $db->query("
	SELECT *
	FROM ".TABLE_PREFIX."mybbirads
	WHERE id = '".$mybb->input['id']."'
	LIMIT 1
");
$ads = $db->fetch_array($query);

	header('Location:'.$ads['url']);
	
$db->write_query("
		UPDATE ".TABLE_PREFIX."mybbirads
		SET click = click+1
	    WHERE id = ".$mybb->input['id']." LIMIT 1");
}	
?>