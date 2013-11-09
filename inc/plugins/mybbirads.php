<?php

if(!defined('IN_MYBB'))
{
	die('This file cannot be accessed directly.');
}

$plugins->add_hook('admin_forum_action_handler','mybbirads_admin_action');
$plugins->add_hook('admin_forum_menu','mybbirads_admin_forum_menu');
$plugins->add_hook('admin_load','mybbirads_admin');
$plugins->add_hook('index_start','mybbirads');
$plugins->add_hook('global_start','mybbiradspagepeel');
$plugins->add_hook('global_start','mybbiradstasks');
$plugins->add_hook('forumdisplay_start','mybbiradstasksthread');
$plugins->add_hook("postbit", "mybbiradspostbit");
function mybbirads_info()
{
	global $lang;
	$lang->load('mybbirads');
	return array(
		"name"		=> $lang->mybbirads_name,
		"description"		=> $lang->mybbirads_desc,
		"website"		=> "http://my-bb.ir",
		"author"		=> "AliReza_Tofighi",
		"authorsite"		=> "http://my-bb.ir",
		"version"		=> "1.1",
		"compatibility"	=> "1*"
		);
}


function mybbirads_install()
{
	global $db, $charset, $lang;
	$lang->load('mybbirads');
	$query = $db->simple_select("settinggroups", "COUNT(*) as rows");
	$rows = $db->fetch_field($query, "rows");
	
	$insertarray = array(
		'name' => 'mybbirads',
		'title' => $lang->mybbirads_name,
		'description' => $lang->mybbirads_desc,
		'disporder' => $rows+1,
		'isdefault' => 0
	);
	$group['gid'] = $db->insert_query("settinggroups", $insertarray);
	$mybb->mybbirads_insert_gid = $group['gid'];

		$insertarray = array(
		'name' => 'showmybbiradsnumberrow',
		'title' => $lang->showmybbiradsnumberrow,
		'description' => '',
		'optionscode' => 'text',
		'value' => 2,
		'disporder' => 8,
		'gid' => $group['gid']
	);
	$db->insert_query("settings", $insertarray);
	
			$insertarray = array(
		'name' => 'showmybbiradsnumbertotal',
		'title' => $lang->showmybbiradsnumbertotal,
		'description' => '',
		'optionscode' => 'text',
		'value' => 1,
		'disporder' => 9,
		'gid' => $group['gid']
	);
	$db->insert_query("settings", $insertarray);
	
	
			$insertarray = array(
		'name' => 'showmybbiradsnposts',
		'title' => $lang->showmybbiradsnposts,
		'description' => '',
		'optionscode' => 'text',
		'value' => 1,
		'disporder' => 10,
		'gid' => $group['gid']
	);
	$db->insert_query("settings", $insertarray);	
		
	
	rebuild_settings();	
	
	$db->write_query("
	CREATE TABLE IF NOT EXISTS ".TABLE_PREFIX."mybbirads
(id int(10) NOT NULL auto_increment,
title varchar(100) NOT NULL,
url varchar(100) NOT NULL,
img varchar(100) NOT NULL,
type int(10) NOT NULL,
view int(10) NOT NULL,
maxview int(10) NOT NULL,
click int(10) NOT NULL,
maxclick int(10) NOT NULL,
distime varchar(100) NOT NULL,
enabled tinyint(4) NOT NULL default '1',
PRIMARY KEY  (id))");


}


function mybbirads_is_installed()
{

	global $db;
	if($db->table_exists("mybbirads"))
	{
		return true;
	}
	return false;
}


function mybbirads_uninstall()
{
	global $db;

	// Drop the Table
	$db->drop_table("mybbirads");
	
	
	$query = $db->query("SELECT gid FROM ".TABLE_PREFIX."settinggroups WHERE name='mybbirads' LIMIT 1");
	$qinfo = $db->fetch_array($query);
	if($db->num_rows($query)) {
		$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE gid='{$qinfo['gid']}'");
	}
	$db->delete_query("settinggroups", "name = 'mybbirads'");
	
	rebuild_settings();		

}


function mybbirads_activate()
{
	global $mybb;
	include MYBB_ROOT."/inc/adminfunctions_templates.php";
	
	find_replace_templatesets("index", "#".preg_quote('{$showadshead}')."#i", '', 0);
	find_replace_templatesets("postbit", "#".preg_quote('{$showadspostbit}')."#i", '', 0);	
	find_replace_templatesets("index", "#".preg_quote('{$showadsfoot}')."#i", '', 0);	
	find_replace_templatesets("header", "#".preg_quote('{$mybbiradspagepeelright}')."#i", '', 0);	
	find_replace_templatesets("header", "#".preg_quote('{$mybbiradspagepeelleft}')."#i", '', 0);		
	find_replace_templatesets("header", "#".preg_quote('{$mybbiradsgooshe}')."#i", '', 0);		
	find_replace_templatesets("forumdisplay", "#".preg_quote('{$mybbiradsthlisthead}')."#i", '', 0);	
	find_replace_templatesets("forumdisplay", "#".preg_quote('{$mybbiradsthlistfoot}')."#i", '', 0);		
	find_replace_templatesets("postbit_classic", "#".preg_quote('{$post[\'showadspostbit\']}')."#i", '', 0);	
	
	
	find_replace_templatesets("index", "#".preg_quote('{$header}')."#i", '{$header}{$showadshead}');	
	find_replace_templatesets("index", "#".preg_quote('{$boardstats}')."#i", '{$showadsfoot}{$boardstats}');
	find_replace_templatesets("header", "#".preg_quote('{$pm_notice}')."#i", '{$pm_notice}{$mybbiradspagepeelright}');		
	find_replace_templatesets("header", "#".preg_quote('{$pm_notice}')."#i", '{$pm_notice}{$mybbiradspagepeelleft}');		
	find_replace_templatesets("header", "#".preg_quote('{$pm_notice}')."#i", '{$pm_notice}{$mybbiradsgooshe}');
	find_replace_templatesets("forumdisplay", "#".preg_quote('{$threadslist}')."#i", '{$mybbiradsthlisthead}{$threadslist}');	
	find_replace_templatesets("forumdisplay", "#".preg_quote('{$threadslist}')."#i", '{$threadslist}{$mybbiradsthlistfoot}');		
	
	find_replace_templatesets("postbit_classic", '#button_delete_pm(.*)<\/tr>(.*)<\/table>#is', 'button_delete_pm$1</tr>$2</table>{\$post[\'showadspostbit\']}');
	find_replace_templatesets("postbit", '#button_delete_pm(.*)<\/tr>(.*)<\/table>#is', 'button_delete_pm$1</tr>$2</table>{\$post[\'showadspostbit\']}');	
	

}

function mybbirads_deactivate()
{
	global $mybb;
	include MYBB_ROOT."/inc/adminfunctions_templates.php";
	
	find_replace_templatesets("index", "#".preg_quote('{$showadshead}')."#i", '', 0);
	find_replace_templatesets("index", "#".preg_quote('{$showadsfoot}')."#i", '', 0);	
	find_replace_templatesets("postbit", "#".preg_quote('{$post[\'showadspostbit\']}')."#i", '', 0);	
	find_replace_templatesets("postbit_classic", "#".preg_quote('{$post[\'showadspostbit\']}')."#i", '', 0);	
	find_replace_templatesets("header", "#".preg_quote('{$mybbiradspagepeelright}')."#i", '', 0);	
	find_replace_templatesets("header", "#".preg_quote('{$mybbiradspagepeelleft}')."#i", '', 0);			
	find_replace_templatesets("header", "#".preg_quote('{$mybbiradsgooshe}')."#i", '', 0);	
	find_replace_templatesets("forumdisplay", "#".preg_quote('{$mybbiradsthlisthead}')."#i", '', 0);	
	find_replace_templatesets("forumdisplay", "#".preg_quote('{$mybbiradsthlistfoot}')."#i", '', 0);		
	
}

function mybbirads_admin_action(&$action)
{
	$action['mybbirads'] = array('active'=>'mybbirads');
}

function mybbirads_admin_forum_menu(&$admim_menu)
{
	global $lang;
	$lang->load('mybbirads');
	end($admim_menu);

	$key = (key($admim_menu)) + 10;

	$admim_menu[$key] = array
	(
		'id' => 'mybbirads',
		'title' => $lang->mybbirads_name,
		'link' => 'index.php?module=forum/mybbirads'
	);

}

function mybbirads_admin()
{
	global $mybb, $db, $page, $lang;
	$lang->load('mybbirads');
	if ($page->active_action != 'mybbirads')
		return false;

	require_once MYBB_ADMIN_DIR."inc/class_form.php";

	// Create Admin Tabs
	$tabs['mybbirads'] = array
		(
			'title' => $lang->listofads,
			'link' =>'index.php?module=forum/mybbirads',
			'description'=> $lang->listofads_desc
		);
	$tabs['mybbirads_add'] = array
		(
			'title' => $lang->addads,
			'link' => 'index.php?module=forum/mybbirads&action=add',
			'description' => $lang->addads_desc
		);

	// No action
	if(!$mybb->input['action'])
	{


		$page->output_header($lang->mybbirads_name);
		$page->add_breadcrumb_item($lang->mybbirads_name);
		$page->output_nav_tabs($tabs,'mybbirads');

		$table = new Table;



		$table = new Table;
		$table->construct_header($lang->subjectads);
		$table->construct_header($lang->linkads);
		$table->construct_header($lang->typeads);
		$table->construct_header($lang->viewsads);		
		$table->construct_header($lang->maxviewads);	
		$table->construct_header($lang->clicksads);	
		$table->construct_header($lang->maxclicksads);	
		$table->construct_header($lang->timerads);			
		$table->construct_header($lang->enableads);		
		$table->construct_header($lang->edit);			
		$query = $db->query("
			SELECT *
			FROM ".TABLE_PREFIX."mybbirads

		");
		while($ad = $db->fetch_array($query))
		{

			$table->construct_cell($ad['title']);
			$table->construct_cell('<a href="' . $ad['url'] . '" target="_blank"><img src="' . htmlspecialchars_uni($ad['img']) . '" width="200" height="75" /></a>');
			if ($ad['type'] == 1) { $typeads = $lang->headerads;} if ($ad['type'] == 2) { $typeads = $lang->footerads;} if ($ad['type'] == 3) { $typeads = $lang->postbit;}  if ($ad['type'] == 4) { $typeads = $lang->pegepeelright;}  if ($ad['type'] == 5) { $typeads = $lang->pegepeelleft;}   if ($ad['type'] == 6) { $typeads = $lang->topofthreadslist;}    if ($ad['type'] == 7) { $typeads = $lang->bottomofthreadslist;}    if ($ad['type'] == 8) { $typeads = $lang->topleft;}    if ($ad['type'] == 9) { $typeads = $lang->bottomleft;}    if ($ad['type'] == 10) { $typeads = $lang->topright;}    if ($ad['type'] == 11) { $typeads = $lang->bottomright;}  
			$table->construct_cell($typeads);
			$table->construct_cell($ad['view']);	
			$table->construct_cell($ad['maxview']);		
			$table->construct_cell($ad['click']);	
			$table->construct_cell($ad['maxclick']);	
			$distime = 0; if ($ad['distime'] != 0) { $distime = ($ad['distime'] - TIME_NOW) / (60*60); }
			$distime = round($distime, 2);
			$table->construct_cell($distime);				
			$table->construct_cell( ($ad['enabled'] ? $lang->enabled : $lang->disabled));

			$table->construct_cell('
			<a href="index.php?module=forum/mybbirads&action=edit&id=' . $ad['id'] . '">'.$lang->edit.'</a>&nbsp;|&nbsp; <a href="index.php?module=forum/mybbirads&action=delete&id=' . $ad['id'] . '">'.$lang->delete.'</a>

			');
			$table->construct_row();

		}

		if($table->num_rows() == 0)
		{
			$table->construct_cell($lang->thereisnoads, array('colspan' => 10));
			$table->construct_row();

		}

		// Show our Donation Page
		$table->construct_cell('By: <a href="http://my-bb.ir">My-BB.Ir</a>', array('colspan' => 10));
		$table->construct_row();


		$table->output($lang->mybbirads_name);

		$page->output_footer();



	}

	// Add Menu
	if ($mybb->input['action'] == 'add' || $mybb->input['action'] == 'add2')
	{


		if ($mybb->input['action'] == 'add2')
		{

if ($_REQUEST['distime'] != 0)
{
$distime = TIME_NOW + ($_REQUEST['distime']*(60*60));
}
				$db->write_query("INSERT IGNORE INTO ".TABLE_PREFIX."mybbirads
				(title, url, view, maxview, click, maxclick, type, img, distime)
		VALUES
			('".$_REQUEST['title']."','".$_REQUEST['url']."','".$_REQUEST['view']."',
		 	'".$_REQUEST['maxview']."', '".$_REQUEST['click']."', '".$_REQUEST['maxclick']."', '".$_REQUEST['type']."', '".$_REQUEST['img']."', '".$distime."')");


				admin_redirect("index.php?module=forum/mybbirads");



		}


		$page->output_header($lang->add);
		$page->add_breadcrumb_item($lang->add);
		$page->output_nav_tabs($tabs, 'mybbirads_add');



		$form = new Form("index.php?module=forum/mybbirads&amp;action=add2", "post");


		$table = new Table;



		$table->construct_cell($lang->subjectads);
		$table->construct_cell('<input type="text" size="50" name="title" value="' . $_REQUEST['title'] . '" />');
		$table->construct_row();

		$table->construct_cell($lang->linkads);
		$table->construct_cell('<input type="text" size="50" name="url" value="' . $_REQUEST['url'] . '" />');
		$table->construct_row();
		
		$table->construct_cell($lang->imgads);
		$table->construct_cell('<input type="text" size="50" name="img" value="' . $_REQUEST['img'] . '" />');
		$table->construct_row();
		
		$table->construct_cell($lang->adstype);
		$table->construct_cell('<select name="type">
		<option value="1" >'.$lang->headerads.'</option>
		<option value="2" >'.$lang->footerads.'</option>	
		<option value="3" >'.$lang->postbit.'/option>		
		<option value="4" >'.$lang->pegepeelright.'</option>			
		<option value="5" >'.$lang->pegepeelleft.'</option>		
		<option value="6" >'.$lang->topofthreadslist.'</option>		
		<option value="7" >'.$lang->bottomofthreadslist.'</option>	
		<option value="8" >'.$lang->topleft.'</option>	
		<option value="9" >'.$lang->bottomleft.'</option>	
		<option value="10" >'.$lang->topright.'</option>	
		<option value="11" >'.$lang->bottomright.'</option>			
		</select>');
		$table->construct_row();
		
		$table->construct_cell($lang->viewsads);
		$table->construct_cell('<input type="text" size="50" name="view" value="' . $_REQUEST['view'] . '" />');
		$table->construct_row();

		$table->construct_cell($lang->maxviewads);
		$table->construct_cell('<input type="text" size="50" name="maxview" value="' . $_REQUEST['maxview'] . '" />');
		$table->construct_row();
		
		$table->construct_cell($lang->clicksads);
		$table->construct_cell('<input type="text" size="50" name="click" value="' . $_REQUEST['click'] . '" />');
		$table->construct_row();
		
		$table->construct_cell($lang->maxclicksads);
		$table->construct_cell('<input type="text" size="50" name="maxclick" value="' . $_REQUEST['maxclick'] . '" />');
		$table->construct_row();
		
		$table->construct_cell($lang->timerads);
		$table->construct_cell('<input type="text" size="50" name="distime" value="' . $_REQUEST['distime'] . '" />');
		$table->construct_row();		
		
		


		$table->construct_cell('<input type="submit" value="'.$lang->add.'" />', array('colspan' => 2));
		$table->construct_row();

		$form->end;
		$table->output($lang->add);

		$page->output_footer();
	}

	if ($mybb->input['action'] == 'edit' || $mybb->input['action'] == 'edit2')
	{


		$id = (int) $_REQUEST['id'];

		$query = $db->query("
			SELECT *
			FROM ".TABLE_PREFIX."mybbirads
			WHERE id = '{$id}' LIMIT 1
		");
		$adsRow = $db->fetch_array($query);

		if ($mybb->input['action'] == 'edit2')
		{
			$id = (int) $_REQUEST['id'];

			if ($_REQUEST['distime'] != 0)
			{
				$distime = TIME_NOW + ($_REQUEST['distime']*(60*60));
			}
				$db->write_query("
					UPDATE ".TABLE_PREFIX."mybbirads
					SET
							title = '".$_REQUEST['title']."', url = '".$_REQUEST['url']."', enabled = '".$_REQUEST['enabled']."',
						img = '".$_REQUEST['img']."', view = '".$_REQUEST['view']."', maxview = '".$_REQUEST['maxview']."',
						click = '".$_REQUEST['click']."',maxclick = '".$_REQUEST['maxclick']."', type = '".$_REQUEST['type']."', distime = '".$distime."'
					WHERE id = $id LIMIT 1"

				);

				admin_redirect("index.php?module=forum/mybbirads");



		}


		$page->output_header($lang->edit);
		$page->add_breadcrumb_item($lang->edit);
		$page->output_nav_tabs($tabs, 'mybbirads');



		$form = new Form("index.php?module=forum/mybbirads&amp;action=edit2", "post");


		$table = new Table;



	

		$table->construct_cell($lang->subjectads);
		$table->construct_cell('<input type="text" size="50" name="title" value="' . $adsRow['title'] . '" />');
		$table->construct_row();

		$table->construct_cell($lang->linkads);
		$table->construct_cell('<input type="text" size="50" name="url" value="' . $adsRow['url'] . '" />');
		$table->construct_row();
		
		$table->construct_cell($lang->imgads);
		$table->construct_cell('<input type="text" size="50" name="img" value="' . $adsRow['img'] . '" />');
		$table->construct_row();
		if ($adsRow['type'] == 1) {$type1 = "selected";}
		if ($adsRow['type'] == 2) {$type2 = "selected";}
		if ($adsRow['type'] == 3) {$type3 = "selected";}		
		if ($adsRow['type'] == 4) {$type4 = "selected";}	
		if ($adsRow['type'] == 5) {$type5 = "selected";}		
		if ($adsRow['type'] == 6) {$type6 = "selected";}	
		if ($adsRow['type'] == 7) {$type7 = "selected";}	
		if ($adsRow['type'] == 8) {$type8 = "selected";}	
		if ($adsRow['type'] == 9) {$type9 = "selected";}	
		if ($adsRow['type'] == 10) {$type10 = "selected";}	
		if ($adsRow['type'] == 11) {$type11 = "selected";}			
		$table->construct_cell($lang->typeads);
		$table->construct_cell('<select name="type">
		<option value="1" '.$type1.'>'.$lang->headerads.'</option>
		<option value="2" '.$type2.'>'.$lang->footerads.'</option>		
		<option value="3" '.$type3.'>'.$lang->postbit.'</option>		
		<option value="4" '.$type4.'>'.$lang->pegepeelright.'</option>				
		<option value="5" '.$type5.'>'.$lang->pegepeelleft.'</option>	
		<option value="6" '.$type6.'>'.$lang->topofthreadslist.'</option>		
		<option value="7" '.$type7.'>'.$lang->bottomofthreadslist.'</option>	
		<option value="8" '.$type8.'>'.$lang->topleft.'</option>	
		<option value="9" '.$type9.'>'.$lang->bottomleft.'</option>	
		<option value="10" '.$type10.'>'.$lang->topright.'</option>	
		<option value="11" '.$type11.'>'.$lang->bottomright.'</option>				
		</select>');
		$table->construct_row();
		
		$table->construct_cell($lang->viewsads);
		$table->construct_cell('<input type="text" size="50" name="view" value="' . $adsRow['view'] . '" />');
		$table->construct_row();

		$table->construct_cell($lang->maxviewads);
		$table->construct_cell('<input type="text" size="50" name="maxview" value="' . $adsRow['maxview'] . '" />');
		$table->construct_row();
		
		$table->construct_cell($lang->clicksads);
		$table->construct_cell('<input type="text" size="50" name="click" value="' . $adsRow['click'] . '" />');
		$table->construct_row();
		
		$table->construct_cell($lang->maxclicksads);
		$table->construct_cell('<input type="text" size="50" name="maxclick" value="' . $adsRow['maxclick'] . '" />');
		$table->construct_row();		

		if ($adsRow['distime'] != 0 )
		{
		$distimea = ($adsRow['distime']-TIME_NOW)/(60*60); } else { $distimea = 0;}
			$distimea = round($distimea, 2);		
		$table->construct_cell($lang->timerads);
		$table->construct_cell('<input type="text" size="50" name="distime" value="' . $distimea . '" />');
		$table->construct_row();		
		
				if ($adsRow['enabled'] == 1) {$enabled1 = "selected";}
		if ($adsRow['enabled'] == 0) {$enabled2 = "selected";}
		$table->construct_cell($lang->enableads);
		$table->construct_cell('<select name="enabled">
		<option value="1" '.$enabled1.'>'.$lang->enabled.'</option>
		<option value="0" '.$enabled2.'>'.$lang->disabled.'</option>		
		</select>');
		$table->construct_row();
		
		

		$table->construct_cell('
		<input type="hidden" name="id" value="' . $id . '" />
		<input type="submit" value="'.$lang->edit.'" />', array('colspan' => 2));
		$table->construct_row();		

		$form->end;
		$table->output($lang->edit);

		$page->output_footer();

	}




	if ($mybb->input['action'] == 'delete')
	{
		$id = (int) $_REQUEST['id'];
		$db->write_query("DELETE FROM ".TABLE_PREFIX."mybbirads  WHERE id = $id
				");

		admin_redirect("index.php?module=forum/mybbirads");
	}




}

function mybbiradstasks()
{
	global $mybb, $db, $settings;

	// task mybbir ads

	$queryview = $db->query("SELECT * FROM ".TABLE_PREFIX."mybbirads  WHERE maxview != 0 and enabled = 1");

	while($mybbiradsview = $db->fetch_array($queryview))
	{
		if ($mybbiradsview['maxview'] <= $mybbiradsview['view'])
		{
			$db->write_query("
					UPDATE ".TABLE_PREFIX."mybbirads
					SET enabled = 0
					WHERE id = ".$mybbiradsview['id']." LIMIT 1");
		}
	}


	$queryclick = $db->query("SELECT * FROM ".TABLE_PREFIX."mybbirads  WHERE maxclick != 0 and enabled = 1");

	while($mybbiradsclick = $db->fetch_array($queryclick))
	{
		if ($mybbiradsclick['maxclick'] <= $mybbiradsclick['click'])
		{
			$db->write_query("
					UPDATE ".TABLE_PREFIX."mybbirads
					SET enabled = 0
					WHERE id = ".$mybbiradsclick['id']." LIMIT 1");
		}
	}

	$querytime = $db->query("SELECT * FROM ".TABLE_PREFIX."mybbirads  WHERE distime != 0 and enabled = 1");
	while($mybbiradstime = $db->fetch_array($querytime))
	{
		if ($mybbiradstime['distime'] <= TIME_NOW)
		{
			$db->write_query("
					UPDATE ".TABLE_PREFIX."mybbirads
					SET enabled = 0
					WHERE id = ".$mybbiradstime['id']." LIMIT 1");
		}
	}

}

function get_mybbirads($type, $max=0)
{
	global $mybb, $db;
	$showadshead = "";
	if ($db->num_rows($db->query("SELECT * FROM ".TABLE_PREFIX."mybbirads  WHERE type= ".$type." and enabled = 1")) > 0) {
		$showadshead .= "<table class=\"tborder\"><tr><td class=\"thead\"  colspan=\"".$mybb->settings['showmybbiradsnumberrow']."\">{$lang->ads}</td></tr>";
		for ($i = 1 ; $i <= $mybb->settings['showmybbiradsnumbertotal'] ; $i++)
		{
			$showadshead .= "<tr>";
			$querymybbir = $db->query("SELECT * FROM ".TABLE_PREFIX."mybbirads  WHERE type=".$type." and enabled = 1 ORDER BY RAND() LIMIT ".$mybb->settings['showmybbiradsnumberrow']);
			while($mybbirads = $db->fetch_array($querymybbir))
			{
				$showadshead .= "<td class=\"trow1\" align=\"center\"><a href=\"mybbirads.php?id=".$mybbirads['id']."\" target=\"_blank\" title=\"".$mybbirads['title']."\"><img src=\"".$mybbirads['img']."\" alt=\"".$mybbirads['title']."\" /></a></td>";
				$addview = "";
				$addview = $mybbirads['view']+1;
				$db->write_query("
						UPDATE ".TABLE_PREFIX."mybbirads
						SET view = '".$addview."'
						WHERE id = ".$mybbirads['id']." LIMIT 1");
			}
			$showadshead .= "</tr>";
		}
		$showadshead .= "</table><span style=\"float:left;\" class=\"smalltext\">By: <a href=\"http://my-bb.ir\" target=\"_blank\">My-BB.Ir</a></span><br />";
	}
	return $showadshead;
}

function mybbirads()
{
	global $mybb, $db, $settings, $showadshead, $showadsfoot;
	// header
	$showadshead = get_mybbirads(1);

	// footer
	$showadsfoot = get_mybbirads(2);
}

function mybbiradspostbit(&$post) 
{
	global $mybb, $db, $settings;
	$postcountera= "";
	$querymybbir = $db->query("SELECT * FROM ".TABLE_PREFIX."posts  WHERE tid = '".$post['tid']."' ORDER BY pid DESC");
	$postcountera= $mybb->settings['showmybbiradsnposts']-1;
	while($mybbirads = $db->fetch_array($querymybbir))
	{
		$postcountera++;
		$postcounter[$mybbirads['pid']] = $postcountera;
	}
	if (($postcounter[$post['pid']]%$mybb->settings['showmybbiradsnposts']) == 0)
	{
		$post['showadspostbit'] = get_mybbirads(3);
	}
}


function mybbiradstasksthread() 
{
	global $mybb, $mybbiradsthlisthead, $mybbiradsthlistfoot;
	$mybbiradsthlisthead = get_mybbirads(6);
	$mybbiradsthlistfoot = get_mybbirads(7);
}


function mybbiradspagepeel() 
{
	global $mybb, $db, $settings, $mybbiradspagepeelright, $mybbiradspagepeelleft, $mybbiradsgooshe;
	// pagepeelright
	if (get_mybbirads(4)) {
		$querymybbir = $db->query("SELECT * FROM ".TABLE_PREFIX."mybbirads  WHERE type= 4 and enabled = 1 ORDER BY RAND() LIMIT 1");
		while($mybbirads = $db->fetch_array($querymybbir))
		{
			$mybbiradspagepeelright = "<style type=\"text/css\"> 
			.pagepeelright a {
				position:fixed;
				top:0;
				right:0;
				width:100px;
				height:100px;
				transition:width 2s, height 2s, background 2s;
				-moz-transition:width 2s, height 2s, background 2s; /* Firefox 4 */
				-webkit-transition:width 2s, height 2s, background 2s; /* Safari and Chrome */
				-o-transition:width 2s, height 2s, background 2s; /* Opera */
				background:url(images/pagepeel_r.png);
			}

			.pagepeelright a:hover
			{
				width:500px;
				height:500px;
			}

			.pagepeelright:hover
			{
				width:500px;
				height:500px;
				background-size:500px 500px;
				-moz-background-size:500px 500px; /* Firefox 3.6 */
				background-repeat:no-repeat;
				transition:width 2s, height 2s;
				-moz-transition:width 2s, height 2s; /* Firefox 4 */
				-webkit-transition:width 2s, height 2s; /* Safari and Chrome */
				-o-transition:width 2s, height 2s; /* Opera */
			}

			.pagepeelright 
			{
				background:url(".$mybbirads['img'].") top right;
				background-size:100px 100px;
				-moz-background-size:100px 100px; /* Firefox 3.6 */
				background-repeat:no-repeat;
				position:fixed;
				 top:0;
				 right:0;
				width:100px;
				height:100px;
				border-bottom-left-radius:100px;
				transition:width 2s, height 2s, background 2s;
				-moz-transition:width 2s, height 2s, background 2s; /* Firefox 4 */
				-webkit-transition:width 2s, height 2s, background 2s; /* Safari and Chrome */
				-o-transition:width 2s, height 2s, background 2s; /* Opera */
				background-size:500px 500px;
				-moz-background-size:500px 500px; /* Firefox 3.6 */
			}
			</style>
			<div class=\"pagepeelright\"><a href=\"mybbirads.php?id=".$mybbirads['id']."\" target=\"_blank\" title=\"".$mybbirads['title']."\"></a></div>";
			$addview = "";
			$addview = $mybbirads['view']+1;
			$db->write_query("
					UPDATE ".TABLE_PREFIX."mybbirads
					SET view = '".$addview."'
					WHERE id = ".$mybbirads['id']." LIMIT 1");
		}
	}

	//pagepeelleft

	if (get_mybbirads(5)) {

		$querymybbir = $db->query("SELECT * FROM ".TABLE_PREFIX."mybbirads  WHERE type= 5 and enabled = 1 ORDER BY RAND() LIMIT 1");
		while($mybbirads = $db->fetch_array($querymybbir))
		{
			$mybbiradspagepeelleft = "<style type=\"text/css\"> 
			.pagepeelleft a
			{
			position:fixed;
			 top:0;
			 left:0;
			width:100px;
			height:100px;
			transition:width 2s, height 2s, background 2s;
			-moz-transition:width 2s, height 2s, background 2s; /* Firefox 4 */
			-webkit-transition:width 2s, height 2s, background 2s; /* Safari and Chrome */
			-o-transition:width 2s, height 2s, background 2s; /* Opera */
			background:url(images/pagepeel_l.png) top left;
			background-repeat:no-repeat;
			background-size:99px 100px;
			-moz-background-size:99px 100px; /* Firefox 3.6 */

			}

			.pagepeelleft a:hover
			{
			width:500px;
			height:500px;
			background-size:500px 520px;
			-moz-background-size:500px 520px; /* Firefox 3.6 */
			}

			.pagepeelleft:hover
			{
			width:500px;
			height:500px;
			background-size:500px 500px;
			-moz-background-size:500px 500px; /* Firefox 3.6 */
			background-repeat:no-repeat;
			transition:width 2s, height 2s;
			-moz-transition:width 2s, height 2s; /* Firefox 4 */
			-webkit-transition:width 2s, height 2s; /* Safari and Chrome */
			-o-transition:width 2s, height 2s; /* Opera */
			}

			.pagepeelleft 
			{
			background:url(".$mybbirads['img'].") top left;
			background-size:9px 9px;
			-moz-background-size:9px 9px; /* Firefox 3.6 */
			background-repeat:no-repeat;
			position:fixed;
			 top:0;
			 left:0;
			width:99px;
			height:94px;
			border-bottom-right-radius:100px;
			transition:width 2s, height 2s, background 2s;
			-moz-transition:width 2s, height 2s, background 2s; /* Firefox 4 */
			-webkit-transition:width 2s, height 2s, background 2s; /* Safari and Chrome */
			-o-transition:width 2s, height 2s, background 2s; /* Opera */
			background-size:500px 500px;
			-moz-background-size:500px 500px; /* Firefox 3.6 */
			}

			</style>
			<div class=\"pagepeelleft\"><a href=\"mybbirads.php?id=".$mybbirads['id']."\" target=\"_blank\" title=\"".$mybbirads['title']."\"></a></div>";
			$addview = "";
			$addview = $mybbirads['view']+1;
			$db->write_query("
					UPDATE ".TABLE_PREFIX."mybbirads
					SET view = '".$addview."'
					WHERE id = ".$mybbirads['id']." LIMIT 1");
		}
	}

	$querymybbir = $db->query("SELECT * FROM ".TABLE_PREFIX."mybbirads  WHERE type= 10 and enabled = 1 ORDER BY RAND() LIMIT 1");
	while($mybbirads = $db->fetch_array($querymybbir))
	{
		list($adsmybbirwidth, $adsmybbirheight) = getimagesize($mybbirads['img']);
		$mybbiradsgooshe .= "<div><a href=\"mybbirads.php?id=".$mybbirads['id']."\" target=\"_blank\" title=\"".$mybbirads['title']."\" style=\"position:fixed;top:0;right:0;width:".$adsmybbirwidth."px;height:".$adsmybbirheight."px;background:url(".$mybbirads['img'].") top right;\"></a></div>";
		$addview = "";
		$addview = $mybbirads['view']+1;
		$db->write_query("
				UPDATE ".TABLE_PREFIX."mybbirads
				SET view = '".$addview."'
				WHERE id = ".$mybbirads['id']." LIMIT 1");
	}

	$querymybbir = $db->query("SELECT * FROM ".TABLE_PREFIX."mybbirads  WHERE type= 11 and enabled = 1 ORDER BY RAND() LIMIT 1");
	while($mybbirads = $db->fetch_array($querymybbir))
	{
		list($adsmybbirwidth, $adsmybbirheight) = getimagesize($mybbirads['img']);
		$mybbiradsgooshe .= "<div><a href=\"mybbirads.php?id=".$mybbirads['id']."\" target=\"_blank\" title=\"".$mybbirads['title']."\" style=\"position:fixed;bottom:0;right:0;width:".$adsmybbirwidth."px;height:".$adsmybbirheight."px;background:url(".$mybbirads['img'].") bottom right;\"></a></div>";
		$addview = "";
		$addview = $mybbirads['view']+1;
		$db->write_query("
				UPDATE ".TABLE_PREFIX."mybbirads
				SET view = '".$addview."'
				WHERE id = ".$mybbirads['id']." LIMIT 1");
	}

	$querymybbir = $db->query("SELECT * FROM ".TABLE_PREFIX."mybbirads  WHERE type= 8 and enabled = 1 ORDER BY RAND() LIMIT 1");
	while($mybbirads = $db->fetch_array($querymybbir))
	{
		list($adsmybbirwidth, $adsmybbirheight) = getimagesize($mybbirads['img']);
		$mybbiradsgooshe .= "<div><a href=\"mybbirads.php?id=".$mybbirads['id']."\" target=\"_blank\" title=\"".$mybbirads['title']."\" style=\"position:fixed;top:0;left:0;width:".$adsmybbirwidth."px;height:".$adsmybbirheight."px;background:url(".$mybbirads['img'].") top left;\"></a></div>";
		$addview = "";
		$addview = $mybbirads['view']+1;
		$db->write_query("
				UPDATE ".TABLE_PREFIX."mybbirads
				SET view = '".$addview."'
				WHERE id = ".$mybbirads['id']." LIMIT 1");
	}

	$querymybbir = $db->query("SELECT * FROM ".TABLE_PREFIX."mybbirads  WHERE type= 9 and enabled = 1 ORDER BY RAND() LIMIT 1");
	while($mybbirads = $db->fetch_array($querymybbir))
	{
		list($adsmybbirwidth, $adsmybbirheight) = getimagesize($mybbirads['img']);
		$mybbiradsgooshe .= "<div><a href=\"mybbirads.php?id=".$mybbirads['id']."\" target=\"_blank\" title=\"".$mybbirads['title']."\" style=\"position:fixed;bottom:0;left:0;width:".$adsmybbirwidth."px;height:".$adsmybbirheight."px;background:url(".$mybbirads['img'].") bottom left;\"></a></div>";
		$addview = "";
		$addview = $mybbirads['view']+1;
		$db->write_query("
				UPDATE ".TABLE_PREFIX."mybbirads
				SET view = '".$addview."'
				WHERE id = ".$mybbirads['id']." LIMIT 1");
	}
}
?>