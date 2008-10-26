<?php

if (!defined('PSXDB') && !defined('ADMIN')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

$discs = $mysqli->query('SELECT `d`.`d_id`,`s`.* FROM `discs` AS `d`, `systems` AS `s` WHERE `d`.`d_media`=`s`.`s_id` AND `s`.`s_description`<>0');
while ($disc = $discs->fetch_array()) {
	if (!make_cues($disc['d_id']))
		error('Error making cuesheet for game with ID="'.$disc['d_id'].'"!');
}
if (isset($_SERVER['HTTP_REFERER']))
	redirect($_SERVER['HTTP_REFERER']);
else
	redirect('http://'.$_SERVER['HTTP_HOST'].'/');

?>