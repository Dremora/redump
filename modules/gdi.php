<?php

if (!defined('PSXDB')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

$query = 'SELECT * FROM `discs` AS `d`,`systems` AS `s` WHERE `s`.`s_id`=`d`.`d_media` AND `s`.`s_description`=2 ORDER BY `d`.`d_title`,`d`.`d_region`,`d`.`d_version`,`d`.`d_languages`,`d`.`d_number`';
$discs = $mysqli->query($query);
$zip = new Zipfile();
while ($disc = $discs->fetch_array())
	$zip->addFile($disc['d_cue_contents'], $disc['d_cue_title']);
echo $zip->file();
$psxdb['title'] = 'Redump - GDI Files ('.$discs->num_rows.')('.date('Y-m-d').').zip';
downloadText();

?>
