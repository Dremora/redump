<?php

if (!defined('PSXDB')/* || !defined('ADMIN')*/) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

$query = $mysqli->query('SELECT * FROM (
	(SELECT `d`.*,`s`.*,`t`.`t_number`,`t`.`t_size`,`t`.`t_crc32`,`t`.`t_md5`,`t`.`t_sha1`,NULL AS `t_ed2k`,`t`.`t_type` ,`t`.`t_pregap` FROM `discs` AS `d`,`systems` AS `s`,`tracks` AS `t` WHERE `d`.`d_media`=`s`.`s_id` AND `d`.`d_id`=`t`.`d_id` AND `s`.`s_media`=1)
	UNION
	(SELECT `d`.*,`s`.*,1 AS `t_number`,`dvd`.`d_size` AS `t_size`,`dvd`.`d_crc32` AS `t_crc32`,`dvd`.`d_md5` AS `t_md5`,`dvd`.`d_sha1` AS `t_sha1`,`dvd`.`d_ed2k` AS `t_ed2k`,NULL AS `t_type`,NULL AS `t_pregap` FROM `discs` AS `d`,`dvd`,`systems` AS `s` WHERE `d`.`d_id`=`dvd`.`d_id` AND `d`.`d_media`=`s`.`s_id`)
) AS table1
LEFT OUTER JOIN
	(SELECT `du`.`d_id`,GROUP_CONCAT(DISTINCT `username` ORDER BY `username` SEPARATOR \', \') FROM `du`,`users` WHERE `du`.`u_id`=`users`.`id` AND `du`.`du_status`=1 GROUP BY `d_id`)
AS table2
	ON table1.d_id=table2.d_id
	ORDER BY d_title,d_region,d_version,d_version_datfile,table1.d_id,t_number
');

header('Content-Type: application/xml');

echo "<discs>\n";

while ($disc = $query->fetch_array()) {
	if ($disc['t_number'] == 1) {
		echo '	<disc title="'.htmlspecialchars($disc['d_title']).'" alttitle="'.htmlspecialchars($disc['d_title_foreign']).'" number="'.$disc['d_number'].'" system="'.htmlspecialchars($disc['s_full']).'" edition="'.htmlspecialchars($disc['d_edition']).'" version="'.htmlspecialchars($disc['d_version']).'" media="'.htmlspecialchars($disc['s_media_text']).'" category="'.$psxdb['categories'][$disc['d_category']].'" region="'.$disc['d_region'].'" languages="'.$disc['d_languages'].'" serial="'.$disc['d_serial'].'" exe_date="'.$disc['d_date'].'" status="'.$disc['d_status'].'"';
		if ($disc['s_media'] == 1) {
			echo " edc=\"".$disc['d_edc']."\" protection_amc=\"".$disc['d_protection_a']."\" protection_lc=\"".$disc['d_protection_l']."\" offset=\"".$disc['d_write_offset']."\">\n";
			echo "		<tracks>\n";
		}
		else
			echo " size=\"".$disc['t_size']."\" crc32=\"".$disc['t_crc32']."\" md5=\"".$disc['t_md5']."\" sha1=\"".$disc['t_sha1']."\" ed2k=\"".$disc['t_ed2k']."\" />\n";
	}
	if ($disc['s_media'] == 1) {
		echo "			<track number=\"".$disc['t_number']."\" type=\"".$disc['t_type']."\" size=\"".$disc['t_size']."\" crc32=\"".$disc['t_crc32']."\" md5=\"".$disc['t_md5']."\" sha1=\"".$disc['t_sha1']."\" pregap=\"".$disc['t_pregap']."\" />\n";
		if ($disc['t_number'] == $disc['d_tracks_count']) echo "		</tracks>\n	</disc>\n";
	}
}

echo "</discs>\n";

?>