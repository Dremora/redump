<?php

if (!defined('PSXDB')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

if ($_GET['old'] && $_GET['old'] == 1) {
	$old = 1;
} else {
	$old = 0;
}

switch ($_GET['console']) {
	case 'psx-bios':
		echo file_get_contents('download/Sony PlayStation - BIOS Images (21) (v2007-09-30).dat');
		$psxdb['title'] = 'Sony PlayStation - BIOS Images (21) (v2007-09-30).dat';
		downloadText();
		break;
	case '':
		error('System was not specified.');
		break;
	default:
		if (!defined('ADMIN') && !defined('MODERATOR')) {
			$sysqueryadd = ' AND `systems`.`s_public`=1';
		}
		$systems = $mysqli->query('SELECT * FROM `systems` WHERE `systems`.`s_short`="'.addslashes($_GET['console']).'"'.$sysqueryadd);
		if (!$systems->num_rows)
			error('System "'.htmlspecialchars($_GET['console']).'" doesn\'t exist.');
		while ($system = $systems->fetch_array()) {
			$systems_query[] = '`d`.`d_media`='.$system['s_id'];
			$media |= $system['s_media'];
			if ($system['s_media'] == 1)
				$systems_cd_query[] = '`d`.`d_media`='.$system['s_id'];
			else if ($system['s_media'] == 2)
				$systems_dvd_query[] = '`d`.`d_media`='.$system['s_id'];
			$title = $system['s_company'].' - '.$system['s_title'];
		}
		$systems_query = '('.implode(' OR ', $systems_query).')';
		if (isset($systems_cd_query))
			$systems_cd_query = '('.implode(' OR ', $systems_cd_query).')';
		if (isset($systems_dvd_query))
			$systems_dvd_query = '('.implode(' OR ', $systems_dvd_query).')';
		$query_count = 'SELECT COUNT(`d`.`d_id`),GREATEST(MAX(`d`.`d_datetime_updated`),UNIX_TIMESTAMP("2007-07-25 19:31:00")) AS `updated` FROM `discs` AS `d` WHERE `d`.`d_status`<>1 AND `d`.`d_status`<>2 AND '.$systems_query;
		switch ($media) {
			case 1:
				$query = 'SELECT * FROM `discs` AS `d`,`tracks` AS `t`,`systems` AS `s` WHERE `d`.`d_status`<>1 AND `d`.`d_status`<>2 AND `s`.`s_id`=`d`.`d_media` AND `d`.`d_id`=t.`d_id` AND '.$systems_query.' ORDER BY `d`.`d_title`,`d`.`d_region`,`d`.`d_version`,`d`.`d_version_datfile`,`d`.`d_languages`,`d`.`d_number`,`d`.`d_serial`,`t`.`t_number`';
				break;
			case 2:
				$query = 'SELECT `d`.*,`s`.*,`dvd`.`d_size` AS `t_size`,`dvd`.`d_crc32` AS `t_crc32`,`dvd`.`d_md5` AS `t_md5`,`dvd`.`d_sha1` AS `t_sha1` FROM `discs` AS `d`,`dvd`,`systems` AS `s` WHERE `d`.`d_status`<>1 AND `d`.`d_status`<>2 AND `s`.`s_id`=`d`.`d_media` AND `d`.`d_id`=`dvd`.`d_id` AND '.$systems_query.' ORDER BY `d`.`d_title`,`d`.`d_region`,`d`.`d_version`,`d`.`d_number`,`d`.`d_serial`';
				break;
			case 3:
				$query = 'SELECT `d`.*,`s`.*,1 AS `t_number`,`dvd`.`d_size` AS `t_size`,`dvd`.`d_crc32` AS `t_crc32`,`dvd`.`d_md5` AS `t_md5`,`dvd`.`d_sha1` AS `t_sha1` FROM `discs` AS `d`,`dvd`,`systems` AS `s` WHERE `d`.`d_status`<>1 AND `d`.`d_status`<>2 AND `s`.`s_id`=`d`.`d_media` AND `d`.`d_id`=`dvd`.`d_id` AND '.$systems_dvd_query.' UNION SELECT `d`.*,`s`.*,`t`.`t_number`,`t`.`t_size`,`t`.`t_crc32`,`t`.`t_md5`,`t`.`t_sha1` FROM `discs` AS `d`,`tracks` AS `t`,`systems` AS `s` WHERE `s`.`s_id`=`d`.`d_media` AND `d`.`d_id`=`t`.`d_id` AND '.$systems_cd_query.' ORDER BY `d_title`,`d_region`,`d_languages`,`d_version`,`d_version_datfile`,`d_number`,`d_serial`,`t_number`';
		}
}

$discs = $mysqli->query($query_count)->fetch_array();
$discscount = $discs[0];
$date = date('Ymd H-i-s', $discs[1] - ($psxdb_config['timezone'] * 3600));

$tracks = $mysqli->query($query);
echo "clrmamepro (\r\n\tname \"".$title."\"\r\n\tdescription \"".$title." (".$discscount.")\"\r\n\tcategory Console\r\n\tversion \"".$date."\"\r\n\tauthor \"redump.org\"\r\n)\r\n\r\n";
while ($track = $tracks->fetch_array()) {
	if ($track['t_number'] == 1 || $track['t_number'] == '') {
		echo "game (\r\n\tname \"".discfilename($track, $old)."\"\r\n\tdescription \"".discfilename($track, $old)."\"\r\n";
		if (isset($track['d_number'])) {
			if ($track['d_number'] == 1) {
				$clone = "\tcloneof \"".discfilename($track, $old)."\"\r\n";
				$name = $track['d_title'];
			} else {
				if ($name == $track['d_title'])
					echo $clone;
			}
		}
		if ($track['s_description'] == 1) {
			if ($old) {
				echo "\trom ( name \"".discfilename($track, 1).".cue\" size ".$track['d_cue_size_ni']." crc ".$track['d_cue_crc32_ni']." md5 ".$track['d_cue_md5_ni']." sha1 ".$track['d_cue_sha1_ni']." )\r\n";
			} else {
				echo "\trom ( name \"".discfilename($track).".cue\" size ".$track['d_cue_size']." crc ".$track['d_cue_crc32']." md5 ".$track['d_cue_md5']." sha1 ".$track['d_cue_sha1']." )\r\n";
			}
		} else if ($track['s_description'] == 2)
			echo "\trom ( name \"".discfilename($track, $old).".gdi\" size ".$track['d_cue_size']." crc ".$track['d_cue_crc32']." md5 ".$track['d_cue_md5']." sha1 ".$track['d_cue_sha1']." )\r\n";
		//if ($disc['d_media'] == 1)
		//	echo "\trom ( name \"".discfilename($track, $old).".gdi\" size ".$track['d_cue_size']." crc ".$track['d_cue_crc32']." md5 ".$track['d_cue_md5']." sha1 ".$track['d_cue_sha1']." )\r\n";
	}
	if ($track['s_description'] != 2)
		echo "\trom ( name \"".trackfilename($track, $old).'.'.$track['s_extension'].'" size '.$track['t_size'].' crc '.$track['t_crc32'].' md5 '.$track['t_md5'].' sha1 '.$track['t_sha1']." )\r\n";
	else
		echo "\trom ( name \"Track".str_pad($track['t_number'], 2, '0', STR_PAD_LEFT).'.bin" size '.$track['t_size'].' crc '.$track['t_crc32'].' md5 '.$track['t_md5'].' sha1 '.$track['t_sha1']." )\r\n";
	if ($track['t_number'] == $track['d_tracks_count'] || $track['t_number'] == '' || $track['d_tracks_count'] == '') echo ")\r\n\r\n";
}

$zip = new Zipfile();
if ($nointro) {
	$zip->addFile(ob_get_clean(), str_replace('/', '-', $title).' ('.$date.').dat');
} else {
	$zip->addFile(ob_get_clean(), str_replace('/', '-', $title).' ('.$discscount.') ('.$date.').dat');
}
ob_start();
echo $zip->file();
header('Content-Type: application/zip');
header('Content-Length: '.strlen($zip->file()));
//header('Last-Modified: '.gmdate('D, d M Y H:i:s', $discs[1]).' GMT');
if ($nointro) {
	header('Content-Disposition: attachment; filename="'.str_replace('/', '-', $title).' ('.$date.').zip"');
} else {
	header('Content-Disposition: attachment; filename="'.str_replace('/', '-', $title).' ('.$discscount.') ('.$date.').zip"');
}

?>