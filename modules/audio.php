<?php

if (!defined('PSXDB')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

if (!in_array($psxdb_user[id], $psxdb_config['red_users'])) $queryadd = ' AND `discs`.`d_status`>=4';

$discs = $mysqli->query('SELECT * FROM `discs`,`tracks`,`systems` WHERE `systems`.`s_id`=`discs`.`d_media` AND `tracks`.`d_id`=`discs`.`d_id` AND `tracks`.`t_number`=2 AND `tracks`.`t_type`=3'.$queryadd.' ORDER BY `discs`.`d_offset`,`discs`.`d_title`,`discs`.`d_region`,`discs`.`d_version`,`discs`.`d_number`');
$discsredumpquery = $mysqli->query('SELECT COUNT(`discs`.`d_id`) FROM `discs` WHERE `discs`.`d_tracks_count`>=2 AND `discs`.`d_offset` IS NULL'.$queryadd);
$discsredump = $discsredumpquery->fetch_array();

if (defined('LOGGED')) {
	$mydiscsarray = array();
	$mydiscs = $mysqli->query('SELECT * FROM du WHERE (du_status=0 OR du_status=2) AND `du`.`u_id`='.$psxdb_user['id']);
	while ($mydisc = $mydiscs->fetch_array()) $mydiscsarray[] = $mydisc['d_id'];
}

echo '<h3>Discs with audio tracks ('.$discs->num_rows.') ('.$discsredump[0].' to redump)</h3><div class="textblock"><p>';
while ($disc = $discs->fetch_array()) {
	
	if (defined('LOGGED')) {
		if (in_array($disc['d_id'], $mydiscsarray))
			echo status(5).' ';
		else
			echo status(2).' ';
	}
	
	
	
	echo '['.(isset($disc['d_offset']) ? write_offset($disc['d_offset']) : 'EAC').'] <a href="/disc/'.$disc['d_id'].'/">'.htmlspecialchars(discfilename($disc)).'</a> ('.$disc['d_tracks_count'].' tracks)<br />';
}
echo '</p></div>';
$psxdb['title'] = 'Discs with audio tracks ('.$discs->num_rows.') ('.$discsredump[0].' to redump)';
display();

?>