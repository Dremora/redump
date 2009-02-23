<?php

if (!defined('PSXDB') || (!defined('ADMIN') && !defined('MODERATOR'))) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

// TODO:
// Protection -- add to `systems`
// remove d_cue_title
// pregap -- add two fields



include_once 'classes/Form.php';

/***************/
/* Adding disc */
/***************/

if (isset($_GET['action'])) {
	// 0. Querying existing disc info

	// Disc
	$query = $mysqli->query('SELECT * FROM `discs`,`systems` WHERE `systems`.`s_id`=`discs`.`d_media` AND `discs`.`d_id`='.intval($_POST['d_id']));
	if (!$disc = $query->fetch_array())
		errorXML('Error querying disc info!');

	// 1. Checking POST variables

	// a) common info

	// Title
	if (count($_POST['d_title']) > 255 || !$_POST['d_title'])
		errorXML('Disc title length should be between 1 and 255 characters!');
	$_POST['d_title'] = str_replace(array(' - ', '  '), array(': ', ' '), $_POST['d_title']);
	
	// Alternative title
	if (count($_POST['d_title_foreign']) > 255)
		errorXML('Disc alternative title length shouldn\'t exceed 255 characters!');
	$_POST['d_title_foreign'] = str_replace(array(' - ', '  '), array(': ', ' '), $_POST['d_title_foreign']);
	
	// Number
	if (count($_POST['d_number']) > 10)
		errorXML('Disc number length shouldn\'t exceed 10 characters!');
	
	// Label / Disc title
	if (count($_POST['d_label']) > 255)
		errorXML('Disc title length shouldn\'t exceed 255 characters!');
	
	// Category
	if (!array_key_exists($_POST['d_category'], $psxdb['categories'])) {
		errorXML('Please select disc category!');
	}
	
	// Region
	if (!array_key_exists($_POST['d_region'], $psxdb['regions']))
		errorXML('Please select disc region!');
	
	// Ring
	if (count($_POST['d_ring']) > 127) {
		errorXML('Disc ring length shouldn\'t exceed 127 characters!');
	}
	
	// Barcode
	if (count($_POST['d_barcode']) > 255) {
		errorXML('Disc barcode length shouldn\'t exceed 255 characters!');
	}
	
	// Languages
	if ($_POST['d_languages'] != '') {
		foreach ($_POST['d_languages'] as $language) {
			if (!in_array($language, $psxdb['languages']))
				errorXML('Language "'.htmlspecialchars($language).'" doesn\'t exist!');
		}
		$_POST['d_languages'] = strtolower(implode(',', $_POST['d_languages']));
		if (count($_POST['d_languages']) > 127)
			errorXML('Please check languages!');
	}
	
	// Serial
	if (count($_POST['d_serial']) > 127) {
		errorXML('Disc serial length shouldn\'t exceed 127 characters!');
	}
	
	// EXE date
	if ($disc['s_date']) {
		if (!preg_match('@^(((19)|(20))[0-9][0-9]-[01][0-9](-[0123][0-9])?)?$@', $_POST['d_date']))
			errorXML('Disc EXE date should be in YYYY-MM-DD format or blank!');
	} else
		unset($_POST['d_date']);
	
	// EDC
	if ($disc['s_edc']) {
		if (!preg_match('@^[012]$@', $_POST['d_edc']))
			errorXML('Please check EDC status!');
	} else {
		unset($_POST['d_edc']);
	}
	
	// Errors count
	if ($disc['s_media'] == 1 && $_POST['d_errors'] != '') {
		if (!preg_match('@^[0-9]{1,6}$@', $_POST['d_errors'])) {
			errorXML('Please check errors count!');
		}
	} else {
		unset($_POST['d_errors']);
	}
	
	// Comments
	$_POST['d_comments'] = str_replace(array('&amp;lt;', '&amp;gt;'), array('&lt;', '&gt;'), str_replace('&', '&amp;', $_POST['d_comments']));
	if (count($_POST['d_comments']) > 50000)
		errorXML('Disc comments length shouldn\'t exceed 50000 characters!');
	
	// b) version
	
	// Version
	if (count($_POST['d_version']) > 127)
		errorXML('Disc version length shouldn\'t exceed 127 characters!');
	
	// Version (datfile)
	if (count($_POST['d_version_datfile']) > 127)
		errorXML('Disc version (datfile) length shouldn\'t exceed 127 characters!');

	// Edition
	if ($_POST['d_editions'] != '') {
		if (is_array($_POST['d_editions'])) {
			foreach ($_POST['d_editions'] as $edition) {
				$editions[] = $edition;
			}
		} else {
			$editions[] = $_POST['d_editions'];
		}
		unset($_POST['d_editions']);
	}
	if ($_POST['d_editions_text'] != '') {
		$editions[] = $_POST['d_editions_text'];
		unset($_POST['d_editions_text']);
	}
	if (isset($editions)) {
		$_POST['d_edition'] = implode(', ', $editions);
		unset($editions);
	}
	if (count($_POST['d_edition']) > 255)
		errorXML('Disc editions length shouldn\'t exceed 255 characters!');

	// c) protection
	if ($disc['s_id'] == 1) {
		// Anti-modchip protection
		if (!preg_match('@^[012]$@', $_POST['d_protection_a']))
			errorXML('Please select disc anti-modchip protection status!');
		// LibCrypt protection
		if (!preg_match('@^[012]$@', $_POST['d_protection_l']))
			errorXML('Please select disc LibCrypt protection status!');
		if (count($_POST['d_libcrypt']) > 50000000)
			errorXML('Disc LibCrypt data length shouldn\'t exceed 50000000 characters!');
		if (!preg_match('@^(MSF: [0-9][0-9]:[0-9][0-9]:[0-9][0-9] Q-Data:([ :]?[A-Fa-f0-9][A-Fa-f0-9]){12})?((\n|\r\n)MSF: [0-9][0-9]:[0-9][0-9]:[0-9][0-9] Q-Data:([ :]?[A-Fa-f0-9][A-Fa-f0-9]){12})*$@', $_POST['d_libcrypt']))
			errorXML('Please check LibCrypt data!');
		$_POST['d_libcrypt'] = str_ireplace(array('MSF', 'Q-Data', ' ', ':', "\n", "\r"), '', $_POST['d_libcrypt']);
		if ($_POST['d_libcrypt'] != '')
			$_POST['d_libcrypt'] = '0x'.$_POST['d_libcrypt'];
	} else
		unset($_POST['d_protection_a'], $_POST['d_protection_l'], $_POST['d_libcrypt']);

	// d) dumpers
	// Shared
	if (!preg_match('@^[012]$@', $_POST['d_shared']))
		errorXML('Please select disc sharing status!');
	// Status
	if (!preg_match('@^[1245]$@', $_POST['d_status']))
		errorXML('Please select disc dumping status!');
	// Dumpers
	if ($_POST['d_dumpers'] != '') {
		// Checking IDs
		foreach ($_POST['d_dumpers'] as $dumper) {
			if (!preg_match('@^[1-9][0-9]{0,4}$@', $dumper))
				errorXML('Please check dumpers\' IDs!');
			$query = $mysqli->query('SELECT `u`.`group_id`,`u`.`id`,`u`.`username` FROM `users` AS `u` WHERE (`u`.`group_id`=1 OR `u`.`group_id`=4 OR `u`.`group_id`=5) AND `u`.`id`='.$dumper);
			if (!$dumper = $query->fetch_array())
				errorXML('Dumper with ID="'.$dumper.'" doesn\'t exist!');
			$dumpers_names[] = $dumper['username'];
		}
	}
	// Additional dumpers
	if ($_POST['d_dumpers_text'] != '') {
		if (count($_POST['d_dumpers_text']) > 255)
			errorXML('Other dumpers length shouldn\'t exceed 255 characters!');
		foreach (explode(',', str_replace(array(', ', '  '), array(',', ' '), $_POST['d_dumpers_text'])) as $dumper)
			$dumpers_names[] = $dumper;
	}
	if ($dumpers_names != '') {
		sort($dumpers_names);
		$dumpers_names = implode(', ', $dumpers_names);
	}

	// e) tracks, checksums
	if ($disc['s_media'] == 1) {
		// Tracks
		$cue = new Cueparser();
		$cue_error = $cue->loadCuesheet($_POST['d_cue']);
		if ($cue_error) {
			errorXML('Cuesheet: row '.$cue->row.', error '.$cue_error);
		}
		$_POST['d_tracks'] = trim(strtolower(str_replace(array("\r\n", "\r"), "\n", $_POST['d_tracks'])));
		if (!preg_match('@^[^\n]*size [0-9]{6,10} crc [0-9a-f]{8} md5 [0-9a-f]{32} sha1 [0-9a-f]{40}[^\n]*(\n[^\n]*size [0-9]{6,10} crc [0-9a-f]{8} md5 [0-9a-f]{32} sha1 [0-9a-f]{40}[^\n]*){0,98}$@', $_POST['d_tracks'])) {
			errorXML('Please check ClrMamePro data!');
		}
		
		$_POST['d_tracks'] = explode("\n", $_POST['d_tracks']);
		$tracks_count = 0;
		if (count($_POST['d_tracks']) != $cue->trackscount) {
			errorXML('Different tracks count in cuesheet and ClrMamePro data!');
		}
		foreach ($_POST['d_tracks'] as $track) {
			preg_match('@^[^\n]*size ([0-9]{6,10}) crc ([0-9a-f]{8}) md5 ([0-9a-f]{32}) sha1 ([0-9a-f]{40})[^\n]*$@', $track, $matches);
			$tracks_count++;
			$tracks[$tracks_count]['size']      = $matches[1];
			$tracks[$tracks_count]['crc32']     = $matches[2];
			$tracks[$tracks_count]['md5']       = $matches[3];
			$tracks[$tracks_count]['sha1']      = $matches[4];
			$tracks[$tracks_count]['pregap']    = $cue->tracks[$tracks_count]['pregap'];
			$tracks[$tracks_count]['type']      = $cue->tracks[$tracks_count]['type'];
			$tracks[$tracks_count]['flags']     = $cue->tracks[$tracks_count]['flags'];
		}
		unset($_POST['d_tracks']);

		// Write offset
		if ($_POST['d_offset'] != '') {
			foreach ($_POST['d_offset'] as $offset_value)
				$offset[] = $offset_value;
			unset($_POST['d_offset']);
		}
		if ($_POST['d_offset_text'] != '') {
			foreach (explode(',', str_replace(' ', '', $_POST['d_offset_text'])) as $offset_value)
				$offset[] = $offset_value;
			unset($_POST['d_offset_text']);
		}
		if (isset($offset)) {
			sort($offset);
			$offset = implode(', ', $offset);
			if (count($offset) > 127)
				errorXML('Offset length shouldn\'t exceed 127 characters!');
		}// else if ($tracks_count > 1)
			//errorXML('Please input offset for multitrack disc!');

		unset($_POST['d_size'], $_POST['d_crc32'], $_POST['d_md5'], $_POST['d_sha1'], $_POST['d_ed2k'], $_POST['d_dol_md5']);
	} else if ($disc['s_media'] == 2) {
		// Size
		$_POST['d_size']    = trim($_POST['d_size']);
		if (!preg_match('@^[0-9]{7,11}$@', $_POST['d_size']))
			errorXML('Please check disc size!');
		// CRC-32
		$_POST['d_crc32']   = strtolower(trim($_POST['d_crc32']));
		if (!preg_match('@^[0-9a-f]{8}$@', $_POST['d_crc32']))
			errorXML('Please check disc CRC-32!');
		// MD5
		$_POST['d_md5']   = strtolower(trim($_POST['d_md5']));
		if (!preg_match('@^[0-9a-f]{32}$@', $_POST['d_md5']))
			errorXML('Please check disc MD5!');
		// SHA-1
		$_POST['d_sha1']   = strtolower(trim($_POST['d_sha1']));
		if (!preg_match('@^[0-9a-f]{40}$@', $_POST['d_sha1']))
			errorXML('Please check disc SHA-1!');
		// ed2k
		$_POST['d_ed2k']   = strtolower(trim($_POST['d_ed2k']));
		if (!preg_match('@^([0-9a-f]{32})?$@', $_POST['d_ed2k']))
			errorXML('Please check disc ed2k!');
		// DOL MD5
		$_POST['d_dol_md5'] = strtolower(trim($_POST['d_dol_md5']));
		if (!preg_match('@^([0-9a-f]{32})?$@', $_POST['d_dol_md5']))
			errorXML('Please check disc DOL MD5!');

		unset($_POST['d_offset']);
	}
	
	// RSS
	if (defined('ADMIN')) {
		if (!preg_match('@^1?$@', $_POST['rss']))
			errorXML('Please check RSS status!');
	} else {
		$_POST['rss'] = 1;
	}
	if (!$disc['s_public']) {
		$_POST['rss'] = 0;
	}

	// 2. Adding disc info
	$query = 'REPLACE INTO `discs` (
	`d_id`,
	`d_barcode`,
	`d_category`,
	`d_comments`,
	`d_date`,
	`d_dumpers`,
	`d_edc`,
	`d_errors`,
	`d_label`,
	`d_languages`,
	`d_number`,
	`d_protection_a`,
	`d_protection_l`,
	`d_libcrypt`,
	`d_region`,
	`d_ring`,
	`d_serial`,
	`d_status`,
	`d_shared`,
	`d_media`,
	`d_title`,
	`d_title_foreign`,
	`d_tracks_count`,
	`d_version`,
	`d_version_datfile`,
	`d_edition`,
	`d_offset`,
	`d_datetime_added`,
	`d_datetime_updated`
) VALUES (
	'.$disc['d_id'].',
	'.(($_POST['d_barcode'] != '') ? '"'.addslashes($_POST['d_barcode']).'"' : 'NULL').',
	'.intval($_POST['d_category']).',
	'.(($_POST['d_comments'] != '') ? '"'.addslashes($_POST['d_comments']).'"' : 'NULL').',
	'.(($_POST['d_date'] != '') ? '"'.addslashes($_POST['d_date']).'"' : 'NULL').',
	'.(($_POST['d_dumpers_text'] != '') ? '"'.addslashes($_POST['d_dumpers_text']).'"' : 'NULL').',
	'.(($_POST['d_edc'] != '') ? $_POST['d_edc'] : 'NULL').',
	'.(($_POST['d_errors'] != '') ? $_POST['d_errors'] : 'NULL').',
	'.(($_POST['d_label'] != '') ? '"'.addslashes($_POST['d_label']).'"' : 'NULL').',
	'.(($_POST['d_languages'] != '') ? '"'.addslashes($_POST['d_languages']).'"' : 'NULL').',
	'.(($_POST['d_number'] != '') ? '"'.addslashes($_POST['d_number']).'"' : 'NULL').',
	'.(($_POST['d_protection_a'] != '') ? $_POST['d_protection_a'] : 'NULL').',
	'.(($_POST['d_protection_l'] != '') ? $_POST['d_protection_l'] : 'NULL').',
	'.(($_POST['d_libcrypt'] != '') ? addslashes($_POST['d_libcrypt']) : 'NULL').',
	"'.addslashes($_POST['d_region']).'",
	'.(($_POST['d_ring'] != '') ? '"'.addslashes($_POST['d_ring']).'"' : 'NULL').',
	'.(($_POST['d_serial'] != '') ? '"'.addslashes($_POST['d_serial']).'"' : 'NULL').',
	'.intval($_POST['d_status']).',
	'.intval($_POST['d_shared']).',
	'.intval($disc['d_media']).',
	"'.addslashes($_POST['d_title']).'",
	'.(($_POST['d_title_foreign'] != '') ? '"'.addslashes($_POST['d_title_foreign']).'"' : 'NULL').',
	'.(($tracks_count != '') ? $tracks_count : 'NULL').',
	'.(($_POST['d_version'] != '') ? '"'.addslashes($_POST['d_version']).'"' : 'NULL').',
	'.(($_POST['d_version_datfile'] != '') ? '"'.addslashes($_POST['d_version_datfile']).'"' : 'NULL').',
	'.(($_POST['d_edition'] != '') ? '"'.addslashes($_POST['d_edition']).'"' : 'NULL').',
	'.(($offset != '') ? '"'.addslashes($offset).'"' : 'NULL').',
	'.$disc['d_datetime_added'].',
	'.time().')';
	
	if (!$mysqli->query($query))
		errorXML('Error updating disc info!', str_replace(array("\n", "\r", "\t", '  '), ' ', $query));
		
	// 3. Querying updated disc info
	$query = $mysqli->query('SELECT * FROM `discs`,`systems` WHERE `systems`.`s_id`=`discs`.`d_media` AND `discs`.`d_id`='.$disc['d_id']);
	if (!$newdisc = $query->fetch_array())
		errorXML('Error querying new disc info!');

	// 4. Removing old dumpers, adding new dumpers
	
	// Creating current dumpers array
	$query = $mysqli->query('SELECT * FROM `du`,`users` WHERE `du`.`u_id`=`users`.`id` AND (`du`.`du_status`=1 OR `du`.`du_status`=2) AND `du`.`d_id`='.$disc['d_id']);
	while ($dumper = $query->fetch_array()) {
		$old_dumpers_names[] = $dumper['username'];
	}
	if ($disc['d_dumpers'] != '') {
		foreach (explode(',', str_replace(array(', ', '  '), array(',', ' '), $disc['d_dumpers'])) as $dumper)
			$old_dumpers_names[] = $dumper;
	}
	if ($old_dumpers_names != '') {
		sort($old_dumpers_names);
		$old_dumpers_names = implode(', ', $old_dumpers_names);
	}

	// Removing old dumpers, adding new dumpers
	$olddumpers ='';
	if ($_POST['d_dumpers'] != '') {
		$olddumpers = ' AND u_id<>'.implode(' AND u_id<>', $_POST['d_dumpers']);
	}
	if (!$mysqli->query('DELETE FROM `du` WHERE (`du`.`du_status`=1 OR `du`.`du_status`=2) AND `du`.`d_id`='.$disc['d_id'].$olddumpers))
		errorXML('Error removing dumpers!');
	if ($_POST['d_dumpers'] != '') {
		for ($i = 0; $i < count($_POST['d_dumpers']); $i++) {
			if (!$mysqli->query('INSERT INTO `du` (`d_id`,`u_id`,`du_status`) VALUES ('.$disc['d_id'].','.intval($_POST['d_dumpers'][$i]).',2)')) {
				if (!$mysqli->query('REPLACE INTO `du` (`d_id`,`u_id`,`du_status`) VALUES ('.$disc['d_id'].','.intval($_POST['d_dumpers'][$i]).',2)'))
					errorXML('Error adding dumper with ID='.$_POST['d_dumpers'][$i].'!');
			}
		}
	}
	
	// 5. RSS
	if ($_POST['rss']) {
		$rss = new Rss('['.$disc['s_short_media'].'][UPD] '.discfilename($newdisc), '/disc/'.$disc['d_id'].'/', $disc['d_id']);
		$rss->changes('Title', '<b>'.htmlspecialchars(title($disc['d_title'])).'</b>', '<b>'.htmlspecialchars(title($newdisc['d_title'])).'</b>');
		$rss->changes('Alternative title', htmlspecialchars(title($disc['d_title_foreign'])), htmlspecialchars(title($newdisc['d_title_foreign'])));
		$rss->changes('Disc number', $disc['d_number'], $newdisc['d_number']);
		$rss->changes('Disc title', htmlspecialchars(title($disc['d_label'])), htmlspecialchars(title($newdisc['d_label'])));
		$rss->row('System', htmlspecialchars($disc['s_full']));
		$rss->row('Media', htmlspecialchars($disc['s_media_text']));
		$rss->changes('Category', $psxdb['categories'][$disc['d_category']], $psxdb['categories'][$newdisc['d_category']]);
		$rss->changes('Region', region($disc['d_region']), region($newdisc['d_region']));
		$rss->changes('Languages', languages($disc['d_languages']), languages($newdisc['d_languages']));
		$rss->changes('Serial', htmlspecialchars($disc['d_serial']), htmlspecialchars($newdisc['d_serial']));
		$rss->changes('Ring', htmlspecialchars($disc['d_ring']), htmlspecialchars($newdisc['d_ring']));
		$rss->changes('Barcode', htmlspecialchars($disc['d_barcode']), htmlspecialchars($newdisc['d_barcode']));
		$rss->changes('EXE date', $disc['d_date'], $newdisc['d_date']);
		$rss->changes('Version', htmlspecialchars($disc['d_version']), htmlspecialchars($newdisc['d_version']));
		$rss->changes('Edition', htmlspecialchars($disc['d_edition']), htmlspecialchars($newdisc['d_edition']));
		if ($disc['s_edc']) {
			$rss->changes('EDC', booleaninfo($disc['d_edc']), booleaninfo($newdisc['d_edc']));
		}
		if ($disc['s_media'] == 1) {
			$rss->changes('Errors count', $disc['d_errors'], $newdisc['d_errors']);
		}
		if ($disc['d_media'] == 1) {
			$rss->changes('Anti-modchip protection', booleaninfo($disc['d_protection_a']), booleaninfo($newdisc['d_protection_a']));
			$rss->changes('LibCrypt protection', libcrypt($disc['d_protection_l']), libcrypt($newdisc['d_protection_l']));
		}
		$rss->changes('Shared', sharing_status($disc['d_shared']), sharing_status($newdisc['d_shared']));
		$rss->changes('Status', status($disc['d_status']), status($newdisc['d_status']));
		$rss->changes('Dumpers', $old_dumpers_names, $dumpers_names);
		$rss->changes('Comments', psxdbcode(nl2br($disc['d_comments'])), psxdbcode(nl2br($newdisc['d_comments'])));
		$rss->changes('Write offset', write_offset($disc['d_offset']), write_offset($newdisc['d_offset']));
		$rss->changes('Tracks count', $disc['d_tracks_count'], $newdisc['d_tracks_count']);
	}

	// 6. Tracks/DVD data
	if ($disc['s_media'] == 1) {
		// a) Old tracks query
		$oldtracks = $mysqli->query('SELECT * FROM `tracks` AS `t` WHERE `t`.`d_id`='.$disc['d_id'].' ORDER BY `t`.`t_number`');
		
		// b) Update tracks
		for ($number = 1; $number <= max($disc['d_tracks_count'], $newdisc['d_tracks_count']); $number++) {
			if ($number <= min($disc['d_tracks_count'], $newdisc['d_tracks_count'])) {
				if (!$mysqli->query('UPDATE `tracks` SET `t_size`='.intval($tracks[$number]['size']).', `t_crc32`="'.addslashes($tracks[$number]['crc32']).'", `t_md5`="'.addslashes($tracks[$number]['md5']).'", `t_sha1`="'.addslashes($tracks[$number]['sha1']).'", `t_type`='.$tracks[$number]['type'].', `t_flags`='.$tracks[$number]['flags'].', `t_pregap`='.$tracks[$number]['pregap'].' WHERE `d_id`='.$disc['d_id'].' AND `t_number`='.$number)) {
					errorXML('Error updating track '.$number.'!');
				}
				$oldtrack = $oldtracks->fetch_array();
				// RSS
				if ($_POST['rss']) {
					$rss->blankrow();
					if ($newdisc['d_tracks_count'] > 1)
						$rss->row('Track number', '<b>Track '.$number.' of '.$newdisc['d_tracks_count'].'</b>');
					else
						$rss->row('Track number', '<b>Track '.$number.'</b>');
					$rss->changes('Size', $oldtrack['t_size'], $tracks[$number]['size']);
					$rss->changes('Sectors', $oldtrack['t_size'] / 2352, $tracks[$number]['size'] / 2352);
					$rss->changes('Length', stomsf($oldtrack['t_size'] / 2352), stomsf($tracks[$number]['size'] / 2352));
					$rss->changes('CRC-32', $oldtrack['t_crc32'], $tracks[$number]['crc32']);
					$rss->changes('MD5', $oldtrack['t_md5'], $tracks[$number]['md5']);
					$rss->changes('SHA-1', $oldtrack['t_sha1'], $tracks[$number]['sha1']);
					$rss->changes('Track type', tracktype($oldtrack['t_type']), tracktype($tracks[$number]['type']));
					$rss->changes('Flags', flags($oldtrack['t_flags']), flags($tracks[$number]['flags']));
					if (($oldtrack['t_pregap'] || $tracks[$number]['pregap']))
						$rss->changes('Pregap', stomsf($oldtrack['t_pregap']), stomsf($tracks[$number]['pregap']));
				}
			} else if ($number > $disc['d_tracks_count'] && $number <= $newdisc['d_tracks_count']) {
				if (!$mysqli->query('INSERT INTO `tracks` (`t_number`,`t_size`,`t_crc32`,`t_md5`,`t_sha1`,`t_type`,`t_pregap`,`d_id`) VALUES ('.intval($number).','.intval($tracks[$number]['size']).',"'.addslashes($tracks[$number]['crc32']).'","'.$tracks[$number]['md5'].'","'.$tracks[$number]['sha1'].'",'.$tracks[$number]['type'].','.$tracks[$number]['pregap'].','.$disc['d_id'].')')) {
					errorXML('Error addding track '.$number.'!');
				}
				// RSS
				if ($_POST['rss']) {
					$rss->blankrow();
					if ($newdisc['d_tracks_count'] > 1)
						$rss->changes('Track number', '', '<b>Track '.$number.' of '.$newdisc['d_tracks_count'].'</b>');
					else
						$rss->changes('Track number', '', '<b>Track '.$number.'</b>');
					$rss->changes('Size', '', $tracks[$number]['size']);
					$rss->changes('Sectors', '', $tracks[$number]['size'] / 2352);
					$rss->changes('Length', '', stomsf($tracks[$number]['size'] / 2352));
					$rss->changes('CRC-32', '', $tracks[$number]['crc32']);
					$rss->changes('MD5', '', $tracks[$number]['md5']);
					$rss->changes('SHA-1', '', $tracks[$number]['sha1']);
					$rss->changes('Track type', '', tracktype($tracks[$number]['type']));
					$rss->changes('Flags', '', flags($tracks[$number]['flags']));
					if ($tracks[$number]['pregap']) {
						$rss->changes('Pregap', '', stomsf($tracks[$number]['pregap']));
					}
				}
			} else {
				if (!$mysqli->query('DELETE FROM `tracks` WHERE `d_id`='.$disc['d_id'].' AND `t_number`='.$number))
					errorXML('Error deleting track '.$number.'!');
				// RSS
				$oldtrack = $oldtracks->fetch_array();
				if ($_POST['rss']) {
					$rss->blankrow();
					$rss->changes('Track number', '<b>Track '.$number.'</b>', '');
					$rss->changes('Size', $oldtrack['t_size'], '');
					$rss->changes('Sectors', $oldtrack['t_size'] / 2352, '');
					$rss->changes('Length', stomsf($oldtrack['tsize'] / 2352), '');
					$rss->changes('CRC-32', $oldtrack['t_crc32'], '');
					$rss->changes('MD5', $oldtrack['t_md5'], '');
					$rss->changes('SHA-1', $oldtrack['t_sha1'], '');
					$rss->changes('Track type', tracktype($oldtrack['t_type']), '');
					$rss->changes('Flags', flags($oldtrack['t_flags']), '');
					if ($oldtrack['t_pregap']) {
						$rss->changes('Pregap', stomsf($oldtrack['t_pregap']), '');
					}
				}
			}
		}
	} else if ($disc['s_media'] == 2) {
		$olddvd = $mysqli->query('SELECT * FROM `dvd` WHERE `dvd`.`d_id`='.$disc['d_id'])->fetch_array();
		if ($_POST['rss']) {
			$rss->changes('Size', $olddvd['d_size'], $_POST['d_size']);
			$rss->changes('CRC-32', $olddvd['d_crc32'], $_POST['d_crc32']);
			$rss->changes('MD5', $olddvd['d_md5'], $_POST['d_md5']);
			$rss->changes('SHA-1', $olddvd['d_sha1'], $_POST['d_sha1']);
			$rss->changes('ed2k', $olddvd['d_ed2k'], $_POST['d_ed2k']);
			$rss->changes('DOL MD5', $olddvd['d_dol_md5'], $_POST['d_dol_md5']);
		}
		if (!$mysqli->query('REPLACE INTO `dvd` (`d_id`,`d_size`,`d_crc32`,`d_md5`,`d_sha1`,`d_ed2k`,`d_dol_md5`) VALUES ('.$disc['d_id'].','.$_POST['d_size'].',"'.$_POST['d_crc32'].'","'.$_POST['d_md5'].'","'.$_POST['d_sha1'].'","'.$_POST['d_ed2k'].'","'.$_POST['d_dol_md5'].'")'))
			errorXML('Error adding DVD info!');
	} 

	// 7. Adding RSS, making cue, displaying output
	if ($_POST['rss']) {
		if (!$rss->query())
			errorXML('Error adding RSS!');
	}
	if (!make_cues($disc['d_id']))
		errorXML('Error making cue!');
	okXML('Done. <a href="/disc/'.$disc['d_id'].'/">Go to the disc</a>');
}

/****************/
/* Display form */
/****************/

// a) edit disc

// 1. Checking disc id, system query
$query = $mysqli->query('SELECT * FROM `discs` AS `d` WHERE `d`.`d_id`='.intval($_GET['id']));
if (!$disc = $query->fetch_array())
	error('Disc with ID='.htmlspecialchars($_GET['id']).' doesn\'t exist.');
$query = $mysqli->query('SELECT * FROM `systems` AS `s` WHERE `s`.`s_id`='.$disc['d_media']);
if (!$system = $query->fetch_array())
	error('System with ID='.$disc['d_media'].' doesn\'t exist.');

// 2. Regions array
while (list($key, $region) = each($psxdb['regions']))
	$regions[] = array(region($key).' '.$psxdb['regions'][$key], $key);

// 3. Languages array
while (list($key, $language) = each($psxdb['languages']))
	$languages[] = array(language($language).' '.$psxdb['languages_names'][$language], $language);

// 4. Categories array
while (list($key, $category) = each($psxdb['categories'])) {
	$categories[] = array($category, $key);
}

// 5. Form

// a) common info
$form = new Form('Common disc info');
$form->statictext(array('caption' => 'System', 'contents' => htmlspecialchars($system['s_full'])));
$form->statictext(array('caption' => 'Media', 'contents' => htmlspecialchars($system['s_media_text'])));
$form->text(array('name' => 'd_title', 'caption' => 'Title', 'value' => $disc['d_title']));
$form->text(array('name' => 'd_title_foreign', 'caption' => 'Alternative title', 'value' => $disc['d_title_foreign']));
$form->text(array('name' => 'd_number', 'caption' => 'Disc number', 'value' => $disc['d_number']));
$form->text(array('name' => 'd_label', 'caption' => 'Disc title', 'value' => $disc['d_label']));
$form->select(array('name' => 'd_category', 'caption' => 'Category', 'option' => $categories, 'select' => array($disc['d_category'])));
$form->radio(array('name' => 'd_region', 'caption' => 'Region', 'radio' => $regions, 'check' => $disc['d_region']));
$form->checkbox(array('name' => 'd_languages', 'caption' => 'Languages', 'checkbox' => $languages, 'check' => explode(',', $disc['d_languages'])));
$form->text(array('name' => 'd_serial', 'caption' => 'Serial', 'value' => $disc['d_serial']));
$form->textarea(array('name' => 'd_ring', 'caption' => 'Ring', 'value' => $disc['d_ring']));
$form->text(array('name' => 'd_barcode', 'caption' => 'Barcode', 'value' => $disc['d_barcode']));
if ($system['s_date']) {
	$form->text(array('name' => 'd_date', 'caption' => 'EXE date', 'value' => $disc['d_date']));
}
if ($system['s_edc']) {
	$form->radio(array('name' => 'd_edc', 'caption' => 'EDC', 'radio' => array(array(booleaninfo(0), 0), array(booleaninfo(1), 1), array(booleaninfo(2), 2)), 'check' => $disc['d_edc']));
}
if ($system['s_media'] == 1) {
	$form->text(array('name' => 'd_errors', 'caption' => 'Errors count', 'value' => $disc['d_errors']));
}
$form->textarea(array('name' => 'd_comments', 'caption' => 'Comments', 'value' => str_replace('&amp;', '&', $disc['d_comments'])));

// b) version
$form->fieldset('Version and editions');
$form->text(array('name' => 'd_version', 'caption' => 'Version', 'value' => $disc['d_version']));
if (defined('ADMIN') || defined('MODERATOR'))
	$form->text(array('name' => 'd_version_datfile', 'caption' => 'Version (datfile)', 'value' => $disc['d_version_datfile']));
$disc['d_edition'] = explode(', ', $disc['d_edition']);
foreach (explode('|', $system['s_editions']) as $edition) {
	$editions[] = array($edition, $edition);
	$pos = array_search($edition, $disc['d_edition']);
	if ($pos !== false) {
		$editions_check[] = $edition;
		unset($disc['d_edition'][$pos]);
	}
}
$disc['d_edition'] = implode(', ', $disc['d_edition']);
$form->checkbox(array('name' => 'd_editions', 'caption' => 'Common editions/releases', 'checkbox' => $editions, 'check' => $editions_check));
$form->text(array('name' => 'd_editions_text', 'caption' => 'Other editions/releases', 'value' => $disc['d_edition']));

// c) protection
if ($system['s_id'] == 1) {
	$form->fieldset('Copy protection');
	$form->radio(array('name' => 'd_protection_a', 'caption' => 'Anti-modchip protection', 'radio' => array(array(booleaninfo(0), 0), array(booleaninfo(1), 1), array(booleaninfo(2), 2)), 'check' => $disc['d_protection_a']));
	if (defined('ADMIN') || defined('MODERATOR')) {
		$form->radio(array('name' => 'd_protection_l', 'caption' => 'LibCrypt protection', 'radio' => array(array(libcrypt(0), 0), array(libcrypt(1), 1), array(libcrypt(2), 2)), 'check' => $disc['d_protection_l']));
		if ($disc['d_libcrypt'] != NULL)
			$libcrypt = data2string($disc['d_libcrypt']);
		$form->textarea(array('name' => 'd_libcrypt', 'caption' => 'LibCrypt data', 'value' => $libcrypt));
	} else {
		$form->textarea(array('name' => 'd_libcrypt', 'caption' => 'LibCrypt data'));
		$form->checkbox(array('name' => 'd_libcrypt_no', 'checkbox' => array(array('No LibCrypt (after psxt001z scan)', 1))));
	}
}
// d) dumpers
if (defined('ADMIN') || defined('MODERATOR')) {
	$form->fieldset('Dumpers and status');
	$query = $mysqli->query('SELECT `u`.`group_id`,`u`.`id`,`u`.`username` FROM `users` AS `u` WHERE `u`.`group_id`=1 OR `u`.`group_id`=4 OR `u`.`group_id`=5 ORDER BY `u`.`username`');
	while ($dumper = $query->fetch_array())
		$dumpers[] = array($dumper['username'], $dumper['id']);
	$query = $mysqli->query('SELECT * FROM `du` WHERE (`du`.`du_status`=1 OR `du`.`du_status`=2) AND `du`.`d_id`='.$disc['d_id']);
	while ($cur_dumper = $query->fetch_array())
		$cur_dumpers[] = $cur_dumper['u_id'];
	$form->radio(array('name' => 'd_shared', 'caption' => 'Shared', 'radio' => array(array(sharing_status(0).' '.sharing_status_text(0), 0), array(sharing_status(1).' '.sharing_status_text(1), 1), array(sharing_status(2).' '.sharing_status_text(2), 2)), 'check' => $disc['d_shared']));
	$form->radio(array('name' => 'd_status', 'caption' => 'Status', 'radio' => array(array(status(1).' '.statustext(1), 1), array(status(2).' '.statustext(2), 2), array(status(4).' '.statustext(4), 4), array(status(5).' '.statustext(5), 5)), 'check' => $disc['d_status']));
	$form->select(array('name' => 'd_dumpers', 'caption' => 'Dumpers', 'multiple' => 1, 'option' => $dumpers, 'select' => $cur_dumpers));
	$form->text(array('name' => 'd_dumpers_text', 'caption' => 'Other dumpers', 'value' => $disc['d_dumpers']));
}

// e) tracks, checksums
if ($system['s_media'] == 1) {
	$tracksquery = $mysqli->query('SELECT * FROM `tracks` WHERE `tracks`.`d_id`='.$disc['d_id'].' ORDER BY `tracks`.`t_number`');
	$cue = new Cueparser();
	while ($track = $tracksquery->fetch_array()) {
		// Checksums
		$tracks .= 'size '.$track['t_size'].' crc '.$track['t_crc32'].' md5 '.$track['t_md5'].' sha1 '.$track['t_sha1']."\n";
		// Cue
		$cue->addTrack(array('type' => $track['t_type'], 'pregap' => $track['t_pregap'], 'flags' => $track['t_flags']));
	}
	$form->fieldset('Tracks');
	$form->textarea(array('name' => 'd_tracks', 'caption' => 'ClrMamePro data', 'help' => 'Sizes and checksums from clrmamepro\'s datfile', 'value' => $tracks));
	$form->textarea(array('name' => 'd_cue', 'caption' => 'Cuesheet', 'value' => $cue->returnCuesheet()));
	$disc['d_offset'] = explode(', ', $disc['d_offset']);
	$offsets_array = array('+13', '+2', '0', '-12', '-572', '-647');
	foreach ($offsets_array as $offset) {
		$offsets[] = array(write_offset($offset), $offset);
		$pos = array_search($offset, $disc['d_offset']);
		if ($pos !== false) {
			$offsets_check[] = $offset;
			unset($disc['d_offset'][$pos]);
		}
	}
	$disc['d_offset'] = implode(', ', $disc['d_offset']);
	$form->checkbox(array('name' => 'd_offset', 'caption' => 'Common write offsets', 'checkbox' => $offsets, 'check' => $offsets_check));
	$form->text(array('name' => 'd_offset_text', 'caption' => 'Other write offsets', 'value' => $disc['d_offset']));	
} else if ($system['s_media'] == 2) {
	$dvd = $mysqli->query('SELECT * FROM `dvd` WHERE `dvd`.`d_id`='.$disc['d_id'])->fetch_array();
	$form->fieldset('Size &amp; checksums');
	$form->text(array('name' => 'd_size', 'caption' => 'Size', 'value' => $dvd['d_size']));	
	$form->text(array('name' => 'd_crc32', 'caption' => 'CRC-32', 'value' => $dvd['d_crc32']));
	$form->text(array('name' => 'd_md5', 'caption' => 'MD5', 'value' => $dvd['d_md5']));
	$form->text(array('name' => 'd_sha1', 'caption' => 'SHA-1', 'value' => $dvd['d_sha1']));
	$form->text(array('name' => 'd_ed2k', 'caption' => 'ed2k', 'value' => $dvd['d_ed2k']));
	$form->text(array('name' => 'd_dol_md5', 'caption' => 'DOL MD5', 'value' => $dvd['d_dol_md5']));
}

// f) other
if (defined('ADMIN'))
	$form->checkbox(array('name' => 'rss', 'checkbox' => array(array('Show changes in RSS', 1)), 'check' => array(1)));
$form->hidden(array('name' => 'd_id', 'value' => $disc['d_id']));

$form->submit(array('caption' => 'Save changes'));
$psxdb['title'] = 'Edit disc &mdash; '.htmlspecialchars($disc['d_title']);
echo $form->contents();
display();

?>