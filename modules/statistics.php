<?php

if (!defined('PSXDB')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

if (!defined('ADMIN') && !defined('MODERATOR')) {
	$sysqueryadd = ' WHERE `s`.`s_public`=1';
}
$systems = $mysqli->query('SELECT * FROM `systems` AS `s`'.$sysqueryadd.' GROUP BY `s`.`s_short` ORDER BY `s`.`s_order`');

echo '<table class="statistics" cellspacing="0">
<tr><th></th><th>Europe</th><th>USA</th><th>Asia</th><th>'.status(4).'</th><th>'.status(5).'</th><th>Total</th></tr>';

while ($system = $systems->fetch_array()) {
	$europe = $mysqli->query('SELECT COUNT(`d`.`d_id`) FROM `discs` AS `d`,`systems` AS `s` WHERE `d`.`d_media`=`s`.`s_id` AND `s`.`s_short`="'.$system['s_short'].'" AND `d`.`d_status`<>1 AND `d`.`d_status`<>2 AND `d`.`d_region`<>"U" AND `d`.`d_region`<>"J" AND `d`.`d_region`<>"A"')->fetch_array();
	$usa    = $mysqli->query('SELECT COUNT(`d`.`d_id`) FROM `discs` AS `d`,`systems` AS `s` WHERE `d`.`d_media`=`s`.`s_id` AND `s`.`s_short`="'.$system['s_short'].'" AND `d`.`d_status`<>1 AND `d`.`d_status`<>2 AND `d`.`d_region`="U"')->fetch_array();
	$asia   = $mysqli->query('SELECT COUNT(`d`.`d_id`) FROM `discs` AS `d`,`systems` AS `s` WHERE `d`.`d_media`=`s`.`s_id` AND `s`.`s_short`="'.$system['s_short'].'" AND `d`.`d_status`<>1 AND `d`.`d_status`<>2 AND (`d`.`d_region`="A" OR `d`.`d_region`="J")')->fetch_array();
	$s4     = $mysqli->query('SELECT COUNT(`d`.`d_id`) FROM `discs` AS `d`,`systems` AS `s` WHERE `d`.`d_media`=`s`.`s_id` AND `s`.`s_short`="'.$system['s_short'].'" AND `d`.`d_status`=4')->fetch_array();
	$s5     = $mysqli->query('SELECT COUNT(`d`.`d_id`) FROM `discs` AS `d`,`systems` AS `s` WHERE `d`.`d_media`=`s`.`s_id` AND `s`.`s_short`="'.$system['s_short'].'" AND `d`.`d_status`=5')->fetch_array();
	$system_discs = $europe[0] + $usa[0] + $asia[0];
	$total_discs += $system_discs;
	echo '<tr><th>'.htmlspecialchars($system['s_full']).'</th><td><a href="/discs/system/'.strtolower($system['s_short']).'/region/Eu/">'.$europe[0].'</a></td><td><a href="/discs/system/'.strtolower($system['s_short']).'/region/U/">'.$usa[0].'</a></td><td><a href="/discs/system/'.strtolower($system['s_short']).'/region/As/">'.$asia[0].'</a></td><td><a href="/discs/system/'.strtolower($system['s_short']).'/status/4/">'.$s4[0].'</a></td><td><a href="/discs/system/'.strtolower($system['s_short']).'/status/5/">'.$s5[0].'</a></td><td><b><a href="/discs/system/'.strtolower($system['s_short']).'/">'.$system_discs.'</a></b></td></tr>';
}

echo '<tr><th>Total</th><td></td><td></td><td></td><td></td><td></td><td><b><a href="/discs/">'.$total_discs.'</a></b></td></tr></table>';

$psxdb['title'] = 'Statistics';
display();

?>