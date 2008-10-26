<?php

if (!defined('PSXDB')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

$systems = $mysqli->query('SELECT `s`.`s_full`,`s`.`s_short`,`s`.`s_id`,GREATEST(MAX(`d`.`d_datetime_updated`),UNIX_TIMESTAMP("2007-07-25 19:31:00")) AS `updated`,COUNT(`d`.`d_id`) AS `count`,`d`.`d_status` FROM `discs` AS `d`,`systems` AS `s` WHERE `d`.`d_media`=`s`.`s_id` AND `d`.`d_status`>3 GROUP BY `s`.`s_short` ORDER BY `s`.`s_order`');
$date = 0;

echo '<?xml version="1.0" encoding="utf-8"?>
<clrmamepro>
';

while ($system = $systems->fetch_array()) {
	$curdate = date('Y-m-d H-i-s', $system['updated'] - ($psxdb_config['timezone'] * 3600));
	echo '	<datfile>
		<name>'.$system['s_full'].'</name>
		<description>'.$system['s_full'].' ('.$system['count'].') ('.$curdate.')</description>
		<version>'.$curdate.'</version>
		<author>LedZeppelin68 | Dremora | '.$_SERVER['HTTP_HOST'].'</author>
		<url>'.$_SERVER['HTTP_HOST'].'/datfile/'.strtolower($system['s_short']).'.zip</url>
		<file>'.$system['s_full'].' ('.$system['count'].') ('.$curdate.').dat</file>
	</datfile>
';
	if ($date < $system['updated'])
		$date = $system['updated'];
}
echo '</clrmamepro>
';

$zip = new Zipfile();
$zip->addFile(ob_get_clean(), 'dats.xml');
ob_start();
echo $zip->file();
header('Content-Type: application/zip');
header('Content-Length: '.strlen($zip->file()));
header('Last-Modified: '.gmdate('D, d M Y H:i:s', $date).' GMT');

?>