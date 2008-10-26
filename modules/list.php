<?php

if (!defined('PSXDB')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

// User
//levels
//0 - all
//1 - registered
//2 - dumpers
//3 - nobody
if (defined('ADMIN') || defined('MODERATOR'))
	$query = $mysqli->query('SELECT * FROM `users` WHERE `users`.`username`="'.addslashes($_GET['user']).'"');
else if (defined('DUMPER'))
	$query = $mysqli->query('SELECT * FROM `users` WHERE `users`.`u_show_list`<=2 AND `users`.`username`="'.addslashes($_GET['user']).'"');
else if (defined('LOGGED'))
	$query = $mysqli->query('SELECT * FROM `users` WHERE `users`.`u_show_list`<=1 AND `users`.`username`="'.addslashes($_GET['user']).'"');
else
	$query = $mysqli->query('SELECT * FROM `users` WHERE `users`.`u_show_list`=0 AND `users`.`username`="'.addslashes($_GET['user']).'"');

if (!$query->num_rows)
	redirect('http://'.$_SERVER['HTTP_HOST'].'/');
$user = $query->fetch_array();

// Console
switch ($_GET['console']) {
	case '':
		$console = 'Redump';
		break;
	default:
		$systems = $mysqli->query('SELECT * FROM `systems` WHERE `systems`.`s_short`="'.addslashes($_GET['console']).'"');
		if (!$systems->num_rows)
			error('System "'.htmlspecialchars($_GET['console']).'" doesn\'t exist.');
		while ($system = $systems->fetch_array()) {
			$systems_query[] = '`d`.`d_media`='.$system['s_id'];
			$console = $system['s_full'];
		}
		$systems_query = ' AND ('.implode(' OR ', $systems_query).')';
}

if (!in_array($psxdb_user[id], $psxdb_config['red_users'])) $queryadd = ' AND `d`.`d_status`>=4';

// All discs
$totaldiscs = $mysqli->query('SELECT COUNT(`d`.`d_id`) AS `discs` FROM `discs` AS `d` WHERE 1'.$systems_query.$queryadd);
$discscount = $totaldiscs->fetch_array();

// Discs
if ($_GET['type'] == 'miss') {
	$query = 'SELECT * FROM (SELECT * FROM `discs` AS `d`,`systems` AS `s` WHERE `s`.`s_id`=`d`.`d_media`'.$queryadd.$systems_query.') AS `d`
	LEFT OUTER JOIN
	(SELECT * FROM `du` WHERE `du`.`u_id`='.$user['id'].' AND (du_status=0 OR du_status=2)) AS `table2`
	ON `d`.`d_id`=`table2`.`d_id` WHERE `u_id` IS NULL
	ORDER BY `d`.`d_title`,`d`.`d_region`,`d`.`d_version`,`d`.`d_number`';
	$discs = $mysqli->query($query);
	//echo $query."\r\n\r\n";
	echo $user['username'].' is missing '.$discs->num_rows.' of '.$discscount[0].' known '.$console.' disc images.'."\r\n\r\n";
} else {
	$discs = $mysqli->query('SELECT * FROM `discs` AS `d`,`systems` AS `s`,`du` WHERE `s`.`s_id`=`d`.`d_media` AND `du`.`u_id`='.$user['id'].$queryadd.$systems_query.' AND `d`.`d_id`=`du`.`d_id` AND (du_status=0 OR du_status=2) ORDER BY `d`.`d_title`,`d`.`d_region`,`d`.`d_version`,`d`.`d_number`');
	echo $user['username'].' has '.$discs->num_rows.' of '.$discscount[0].' known '.$console.' disc images.'."\r\n\r\n";
}

while ($disc = $discs->fetch_array())
	echo discFilename($disc)."\r\n";

header('Content-type: text/plain; charset=ISO-8859-1');

?>