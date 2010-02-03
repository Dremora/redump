<?php

if (!defined('PSXDB') && !defined('ADMIN')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

$discs = $mysqli->query('SELECT `d`.`d_id`,`s`.* FROM discs d, systems s WHERE d.d_media=7 AND d.d_media=s.s_id AND d_id>5622');
while ($disc = $discs->fetch_array()) {
	if (!make_cues($disc['d_id']))
		error('Error making cuesheet for game with ID="'.$disc['d_id'].'"!');
}
if (isset($_SERVER['HTTP_REFERER']))
	redirect($_SERVER['HTTP_REFERER']);
else
	redirect('http://'.$_SERVER['HTTP_HOST'].'/');

?>