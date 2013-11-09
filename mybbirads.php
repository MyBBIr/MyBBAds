<?php
define("IN_MYBB", 1);
define("NO_ONLINE", 1);
define('THIS_SCRIPT', 'mybbirads.php');
require_once "./global.php";

if(isset($mybb->input['id']) && intval($mybb->input['id']) > 0)
{
	$query = $db->query("
		SELECT *
		FROM ".TABLE_PREFIX."mybbirads
		WHERE id = '".intval($mybb->input['id'])."' and enabled = '1'
		LIMIT 1
	");
	if($db->num_rows($query) == 1) {
		$ads = $db->fetch_array($query);
		header('Location:'.$ads['url']);
		$db->write_query("
				UPDATE ".TABLE_PREFIX."mybbirads
				SET click = click+1
				WHERE id = '".intval($mybb->input['id'])."' and enabled = '1' LIMIT 1");
	}
}
exit;
?>