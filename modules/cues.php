<?php

if (!defined('PSXDB')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

if (isset($_GET['nointro']) && $_GET['nointro'] == 1) {
	$discs = $mysqli->query('SELECT * FROM `discs` AS `d`,`systems` AS `s` WHERE `s`.`s_id`=`d`.`d_media` AND `s`.`s_short`="'.addslashes($_GET['system']).'" AND `s`.`s_description`=1 ORDER BY `d`.`d_title`,`d`.`d_region`,`d`.`d_version`,`d`.`d_languages`,`d`.`d_number`');
	if (!$discs->num_rows)
		error('System "'.htmlspecialchars($_GET['console']).'" doesn\'t exist.');
	$zip = new Zipfile();
	while ($disc = $discs->fetch_array()) {
		$psxdb['title'] = $disc['s_company'].' - '.str_replace('/', '-', $disc['s_title']).' (Cuesheets).zip';
		$zip->addFile($disc['d_cue_contents_ni'], $disc['d_cue_title_ni']);
	}
	echo $zip->file();
	downloadText();
} else {
	$query = 'SELECT * FROM `discs` AS `d`,`systems` AS `s` WHERE `s`.`s_id`=`d`.`d_media` AND `s`.`s_description`=1 ORDER BY `d`.`d_title`,`d`.`d_region`,`d`.`d_version`,`d`.`d_languages`,`d`.`d_number`';
	$discs = $mysqli->query($query);
	$zip = new Zipfile();
	while ($disc = $discs->fetch_array())
		$zip->addFile($disc['d_cue_contents'], str_replace(array('/', '\\'), '-', $disc['s_full']).'/'.$disc['d_cue_title']);
	echo $zip->file();
	$psxdb['title'] = 'Redump - Cuesheets ('.$discs->num_rows.')('.date('Y-m-d').').zip';
	downloadText();
}

?>