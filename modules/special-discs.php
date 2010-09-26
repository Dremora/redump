<?php

if (!defined('PSXDB')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

$psxdb['title'] = 'Special discs';

// My discs
if (defined('LOGGED')) {
	$mydiscsarray = array();
	$mydiscs = $mysqli->query('SELECT * FROM `du` WHERE (du_status=0 OR du_status=2) AND `du`.`u_id`='.$psxdb_user['id']);
	while ($mydisc = $mydiscs->fetch_array()) $mydiscsarray[] = $mydisc['d_id'];
}

if (!in_array($psxdb_user[id], $psxdb_config['red_users'])) $queryadd = ' AND `d`.`d_status`>=4';

// Discs with LibCrypt protection
$discs = $mysqli->query('SELECT * FROM `discs` AS `d` WHERE `d`.`d_media`=1 AND `d`.`d_protection_l`=2'.$queryadd.' ORDER BY `d`.`d_title`,`d`.`d_region`');
echo '<h2>PSX CD with LibCrypt protection ('.$discs->num_rows.')</h2>';
echo '<div class="textblock"><p>';
while ($disc = $discs->fetch_array()) {
	if (defined('LOGGED')) {
		if (in_array($disc['d_id'], $mydiscsarray))
			echo status(5).' ';
		else
			echo status(2).' ';
	} 
	echo '<a href="/disc/'.$disc['d_id'].'/">'.htmlspecialchars(discfilename($disc)).'</a><br />';
}
echo "</p></div>";

// Discs with anti-modchip protection
$discs = $mysqli->query('SELECT * FROM `discs` AS `d` WHERE `d`.`d_media`=1 AND `d`.`d_protection_a`=2'.$queryadd.' ORDER BY `d`.`d_title`,`d`.`d_region`');
echo '<h2>PSX CD with anti-modchip protection ('.$discs->num_rows.')</h2>';
echo '<div class="textblock"><p>';
while ($disc = $discs->fetch_array()) {
	if (defined('LOGGED')) {
		if (in_array($disc['d_id'], $mydiscsarray))
			echo status(5).' ';
		else
			echo status(2).' ';
	} 
	echo '<a href="/disc/'.$disc['d_id'].'/">'.htmlspecialchars(discfilename($disc)).'</a><br />';
}
echo "</p></div>";

// Discs with unknown date
$discs = $mysqli->query('SELECT * FROM `discs` AS `d` WHERE `d`.`d_date`="" AND (`d`.`d_media`=1 OR `d`.`d_media`=3 OR `d`.`d_media`=4)'.$queryadd.' ORDER BY `d`.`d_title`,`d`.`d_region`');
echo '<h2>PSX CD/PS2 CD/PS2 DVD with unknown date ('.$discs->num_rows.')</h2>';
echo '<div class="textblock"><p>';
while ($disc = $discs->fetch_array()) {
	if (defined('LOGGED')) {
		if (in_array($disc['d_id'], $mydiscsarray))
			echo status(5).' ';
		else
			echo status(2).' ';
	} 
	echo '<a href="/disc/'.$disc['d_id'].'/">'.htmlspecialchars(discfilename($disc)).'</a><br />';
}
echo "</p></div>";

// Discs with unknown languages
$discs = $mysqli->query('SELECT * FROM `discs` AS `d` WHERE `d`.`d_languages`=""'.$queryadd.' ORDER BY `d`.`d_title`,`d`.`d_region`');
echo '<h2>Discs with unknown languages ('.$discs->num_rows.')</h2>';
echo '<div class="textblock"><p>';
while ($disc = $discs->fetch_array()) {
	if (defined('LOGGED')) {
		if (in_array($disc['d_id'], $mydiscsarray))
			echo status(5).' ';
		else
			echo status(2).' ';
	} 
	echo '<a href="/disc/'.$disc['d_id'].'/">'.htmlspecialchars(discfilename($disc)).'</a><br />';
}
echo "</p></div>";

// Discs with unknown version
$discs = $mysqli->query('SELECT * FROM `discs` AS `d` WHERE (`d`.`d_media`=3 OR `d`.`d_media`=4) AND `d`.`d_version`=""'.$queryadd.' ORDER BY `d`.`d_title`,`d`.`d_region`');
echo '<h2>PS2 CD/PS2 DVD with unknown version ('.$discs->num_rows.')</h2>';
echo '<div class="textblock"><p>';
while ($disc = $discs->fetch_array()) {
	if (defined('LOGGED')) {
		if (in_array($disc['d_id'], $mydiscsarray))
			echo status(5).' ';
		else
			echo status(2).' ';
	} 
	echo '<a href="/disc/'.$disc['d_id'].'/">'.htmlspecialchars(discfilename($disc)).'</a><br />';
}
echo "</p></div>";

display();

?>
