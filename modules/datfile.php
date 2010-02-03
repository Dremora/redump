<?php

if (!defined('PSXDB')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

$old = ($_GET['old'] && $_GET['old'] == 1) ? 1 : 0;

switch ($_GET['console'])
{
	case 'psx-bios':
		echo file_get_contents('download/Sony PlayStation - BIOS Images (21) (v2007-09-30).dat');
		$psxdb['title'] = 'Sony PlayStation - BIOS Images (21) (v2007-09-30).dat';
		downloadText();
		break;
	case '':
		error('System was not specified.');
		break;
}

if (!defined('ADMIN') && !defined('MODERATOR'))
{
	$sysqueryadd = ' AND `systems`.`s_public`=1';
}
$systems = $mysqli->query('SELECT * FROM `systems` WHERE `systems`.`s_short`="'.addslashes($_GET['console']).'"'.$sysqueryadd);
if (!$systems->num_rows)
{
	error('System "'.htmlspecialchars($_GET['console']).'" doesn\'t exist.');
}
while ($system = $systems->fetch_array())
{
	$systems_query[] = 'd.d_media='.$system['s_id'];
	$title = strlen($system['s_company']) != 0 ? $system['s_company'].' - ' : '';
	$title .= $system['s_title'];
}
$systems_query = '('.implode(' OR ', $systems_query).')';

$totaldiscs = $mysqli->query('SELECT COUNT(d.d_id) AS total,
	GREATEST(MAX(`d`.`d_datetime_updated`),UNIX_TIMESTAMP("2007-07-25 19:31:00")) AS updated
	FROM discs d WHERE d.d_status>2 AND '.$systems_query)
	->fetch_assoc();
$date = date('Ymd H-i-s', $totaldiscs['updated'] - ($psxdb_config['timezone'] * 3600));

$discs = $mysqli->query('SELECT datfile_contents
	FROM discs_cache dc, discs d
	WHERE d.d_id=dc.id AND d.d_status>2 AND '.$systems_query);

echo '<?xml version="1.0"?>'."\r\n".
	'<!DOCTYPE datafile PUBLIC "-//Logiqx//DTD ROM Management Datafile//EN" "http://www.logiqx.com/Dats/datafile.dtd">'."\r\n".
	'<datafile>'."\r\n".
	'	<header>'."\r\n".
	'		<name>'.htmlspecialchars($title).'</name>'."\r\n".
	'		<description>'.htmlspecialchars($title).' '.$date.'</description>'."\r\n".
	'		<version>'.$date.'</version>'."\r\n".
	'		<date>'.$date.'</date>'."\r\n".
	'		<author>redump.org</author>'."\r\n".
	'		<homepage>redump.org</homepage>'."\r\n".
	'		<url>http://redump.org/</url>'."\r\n".
	'	</header>'."\r\n";
while ($disc = $discs->fetch_array())
{
	echo $disc['datfile_contents'];
}
echo '</datafile>'."\r\n";

$zip = new Zipfile();
if (!$old) {
	$zip->addFile(ob_get_clean(), str_replace('/', '-', $title).' ('.$date.').dat');
} else {
	$zip->addFile(ob_get_clean(), str_replace('/', '-', $title).' ('.$totaldiscs['total'].') ('.$date.').dat');
}
header('Content-Type: application/zip');
header('Content-Length: '.strlen($zip->file()));
//header('Last-Modified: '.gmdate('D, d M Y H:i:s', $discs[1]).' GMT');
if (!$old) {
	header('Content-Disposition: attachment; filename="'.str_replace('/', '-', $title).' ('.$date.').zip"');
} else {
	header('Content-Disposition: attachment; filename="'.str_replace('/', '-', $title).' ('.$totaldiscs['total'].') ('.$date.').zip"');
}
echo $zip->file();
