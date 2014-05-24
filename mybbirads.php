<?php

define("IN_MYBB", 1);
define("NO_ONLINE", 1);
define('THIS_SCRIPT', 'mybbirads.php');

require_once "./global.php";

if(isset($mybb->input['id'])) {
	$mybb->input['id'] = intval($mybb->input['id']);
} else {
	$mybb->input['id'] = 0;
}

if($mybb->input['id'])
{
	$query = $db->query("
		SELECT *
		FROM ".TABLE_PREFIX."mybbirads
		WHERE id = '".$mybb->input['id']."'
		LIMIT 1
	");
	$ads = $db->fetch_array($query);
	if(substr($ads['url'], 0, 7) != 'http://' && substr($ads['url'], 0, 8) != 'https://' && substr($ads['url'], 0, 6) != 'ftp://')
	{
		$ads['url'] = 'http://'.$ads['url'];
	}
	header('Location: '.$ads['url']);
		
	$db->write_query("
			 UPDATE ".TABLE_PREFIX."mybbirads
			 SET click = click+1
			 WHERE id = ".$mybb->input['id']."
			 LIMIT 1");
}	
?>
