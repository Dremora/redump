<?php

if (!defined('PSXDB') || (!defined('ADMIN') && !defined('DUMPER') && !defined('MODERATOR'))) {
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
	// 1. Checking POST variables
	
	// a) common info
	
	// System
	if ($_POST['d_media'] == '')
		errorXML('System was not specifyed!'.print_r($_POST, 1));
	$query = $mysqli->query('SELECT * FROM `systems` AS `s` WHERE `s`.`s_id`='.intval($_POST['d_media']));
	if (!$system = $query->fetch_array())
		errorXML('System with ID="'.htmlspecialchars($_POST['d_media']).'" doesn\'t exist!');
	
	// Title
	if (strlen($_POST['d_title']) > 255 || $_POST['d_title'] == '')
		errorXML('Disc title length should be between 1 and 255 characters!');
	$_POST['d_title'] = str_replace(array(' - ', '  '), array(': ', ' '), $_POST['d_title']);
	
	// Alternative title
	if (strlen($_POST['d_title_foreign']) > 255)
		errorXML('Disc alternative title length shouldn\'t exceed 255 characters!');
	$_POST['d_title_foreign'] = str_replace(array(' - ', '  '), array(': ', ' '), $_POST['d_title_foreign']);
	
	// Number
	if (strlen($_POST['d_number']) > 10)
		errorXML('Disc number length shouldn\'t exceed 10 characters!');
	
	// Label / Disc title
	if (strlen($_POST['d_label']) > 255)
		errorXML('Disc title length shouldn\'t exceed 255 characters!');
	
	// Category
	if (!array_key_exists($_POST['d_category'], $psxdb['categories'])) {
		errorXML('Please select disc category!');
	}
	
	// Region
	if (!array_key_exists($_POST['d_region'], $psxdb['regions']))
		errorXML('Please select disc region!');
	
	// Ring
	if (strlen($_POST['d_ring']) > 127) {
		errorXML('Disc ringcode length shouldn\'t exceed 127 characters!');
	}
	
	// Ring
	if ($_POST['d_media'] != 1 && strlen($_POST['d_ring']) < 5) {
		errorXML('Disc ringcode shouldn\'t be blank!');
	}
	
	// Barcode
	if (strlen($_POST['d_barcode']) > 255) {
		errorXML('Disc barcode length shouldn\'t exceed 255 characters!');
	}
	
	// Languages
	if ($_POST['d_languages'] != '') {
		foreach ($_POST['d_languages'] as $language) {
			if (!in_array($language, $psxdb['languages']))
				errorXML('Language "'.htmlspecialchars($language).'" doesn\'t exist!');
		}
		$_POST['d_languages'] = strtolower(implode(',', $_POST['d_languages']));
		if (strlen($_POST['d_languages']) > 127)
			errorXML('Please check languages!');
	}
	
	// Serial
	if (strlen($_POST['d_serial']) > 127) {
		errorXML('Disc serial length shouldn\'t exceed 127 characters!');
	}
	
	// EXE date
	if ($system['s_date']) {
		if (!preg_match('@^(((19)|(20))[0-9][0-9]-[01][0-9](-[0123][0-9])?)?$@', $_POST['d_date']))
			errorXML('Disc EXE date should be in YYYY-MM-DD format or blank!');
	} else
		unset($_POST['d_date']);
	
	// EDC
	if ($system['s_edc']) {
		if (!preg_match('@^[012]$@', $_POST['d_edc']))
			errorXML('Please check EDC status!');
	} else {
		unset($_POST['d_edc']);
	}
	
	// Errors count
	if ($system['s_media'] == 1 && $_POST['d_errors'] != '') {
		if (!preg_match('@^[0-9]{1,6}$@', $_POST['d_errors'])) {
			errorXML('Please check errors count!');
		}
	} else {
		unset($_POST['d_errors']);
	}
	
	// Comments
	$_POST['d_comments'] = str_replace(array('&amp;lt;', '&amp;gt;'), array('&lt;', '&gt;'), str_replace('&', '&amp;', $_POST['d_comments']));
	if (strlen($_POST['d_comments']) > 50000)
		errorXML('Disc comments length shouldn\'t exceed 50000 characters!');
	
	// b) version
	
	// Version
	if (strlen($_POST['d_version']) > 127)
		errorXML('Disc version length shouldn\'t exceed 127 characters!');
	
	// Version (datfile)
	if (defined('ADMIN') || defined('MODERATOR')) {
		if (strlen($_POST['d_version_datfile']) > 127)
			errorXML('Disc version (datfile) length shouldn\'t exceed 127 characters!');
	}

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
	if (strlen($_POST['d_edition']) > 255)
		errorXML('Disc editions length shouldn\'t exceed 255 characters!');

	// c) protection
	if ($system['s_id'] == 1) {
		// Anti-modchip protection
		if (!preg_match('@^[012]$@', $_POST['d_protection_a']))
			errorXML('Please select disc anti-modchip protection status!');
		// LibCrypt protection
		if (defined('ADMIN') || defined('MODERATOR')) {
			if (!preg_match('@^[012]$@', $_POST['d_protection_l']))
				errorXML('Please select disc LibCrypt protection status!');
			if (strlen($_POST['d_libcrypt']) > 50000000)
				errorXML('Disc LibCrypt data length shouldn\'t exceed 50000000 characters!');
			if (!preg_match('@^(MSF: [0-9][0-9]:[0-9][0-9]:[0-9][0-9] Q-Data:([ :]?[A-Fa-f0-9][A-Fa-f0-9]){12})?((\n|\r\n)MSF: [0-9][0-9]:[0-9][0-9]:[0-9][0-9] Q-Data:([ :]?[A-Fa-f0-9][A-Fa-f0-9]){12})*$@', $_POST['d_libcrypt']))
				errorXML('Please check LibCrypt data!');
			$_POST['d_libcrypt'] = str_ireplace(array('MSF', 'Q-Data', ' ', ':', "\n", "\r"), '', $_POST['d_libcrypt']);
			if ($_POST['d_libcrypt'] != '')
				$_POST['d_libcrypt'] = '0x'.$_POST['d_libcrypt'];
		} else {
			if (strlen($_POST['d_libcrypt']) > 50000000)
				errorXML('Disc LibCrypt data length shouldn\'t exceed 50000000 characters!');
			if ($_POST['d_libcrypt_no'] == 1)
				$_POST['d_libcrypt'] = "NO LIBCRYPT\n\n";
			else if ($_POST['d_libcrypt'] == '')
				errorXML('Please input disc LibCrypt data!');
		}
	} else
		unset($_POST['d_protection_a'], $_POST['d_protection_l'], $_POST['d_libcrypt']);

	// d) dumpers
	if (defined('ADMIN') || defined('MODERATOR')) {
		// Status
		if (!preg_match('@^[45]$@', $_POST['d_status']))
			errorXML('Please select disc dumping status!');
		// Single dumper
		if ($_POST['d_dumper'] != '')
			$_POST['d_dumpers'][] = $_POST['d_dumper'];
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
			if (strlen($_POST['d_dumpers_text']) > 255)
				errorXML('Other dumpers length shouldn\'t exceed 255 characters!');
			foreach (explode(',', str_replace(array(', ', '  '), array(',', ' '), $_POST['d_dumpers_text'])) as $dumper)
				$dumpers_names[] = $dumper;
		}
		if ($dumpers_names != '') {
			sort($dumpers_names);
			$dumpers_names = implode(', ', $dumpers_names);
		}
	}


	// e) tracks, checksums
	if ($system['s_media'] == 1) {
		// Tracks
		$cue = new Cueparser();
		$datfile = new Datfile();
		if ($cue->loadCuesheet($_POST['d_cue']))
		{
			errorXML('Error parsing cuesheet in row '.$cue->row.'.');
		}
		$datfile->parse($_POST['d_tracks'], $cue);

		if ($datfile->tracks_count == 0)
		{
			errorXML('Error parsing datfile.');
		}
		if ($datfile->tracks_count != $cue->trackscount)
		{
			errorXML('Different tracks count in cuesheet and ClrMamePro data!');
		}
		$tracks_count = $datfile->tracks_count;
		$tracks = $datfile->tracks;
			
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
		} //else if ($tracks_count > 1)
			//errorXML('Please input offset for multitrack disc!');

		unset($_POST['d_size'], $_POST['d_crc32'], $_POST['d_md5'], $_POST['d_sha1'], $_POST['d_ed2k'], $_POST['d_dol_md5']);
	} else if ($system['s_media'] == 2) {
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
	if (!$system['s_public']) {
		$_POST['rss'] = 0;
	}

	// 2. Adding disc info
	if (defined('ADMIN') || defined('MODERATOR')) {
		$query = 'INSERT INTO `discs` (
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
	'.intval($_POST['d_media']).',
	"'.addslashes($_POST['d_title']).'",
	'.(($_POST['d_title_foreign'] != '') ? '"'.addslashes($_POST['d_title_foreign']).'"' : 'NULL').',
	'.(($tracks_count != '') ? $tracks_count : 'NULL').',
	'.(($_POST['d_version'] != '') ? '"'.addslashes($_POST['d_version']).'"' : 'NULL').',
	'.(($_POST['d_version_datfile'] != '') ? '"'.addslashes($_POST['d_version_datfile']).'"' : 'NULL').',
	'.(($_POST['d_edition'] != '') ? '"'.addslashes($_POST['d_edition']).'"' : 'NULL').',
	'.(($offset != '') ? '"'.addslashes($offset).'"' : 'NULL').',
	'.time().',
	'.time().')';
	
		if (!$mysqli->query($query))
			errorXML('Error adding disc info!', str_replace(array("\n", "\r", "\t", '  '), ' ', $query));
			
		// 3. Querying disc info
		$query = $mysqli->query('SELECT * FROM `discs`,`systems` WHERE `systems`.`s_id`=`discs`.`d_media` AND `d_id`='.$mysqli->insert_id);
		if (!$disc = $query->fetch_array())
			errorXML('Error querying disc info!');
			
		// 4. Adding dumpers
		if ($_POST['d_dumpers'] != '') {
			for ($i = 0; $i < count($_POST['d_dumpers']); $i++) {
				if (!$mysqli->query('INSERT INTO `du` (`d_id`,`u_id`,`du_status`) VALUES ('.intval($disc['d_id']).','.intval($_POST['d_dumpers'][$i]).',2)'))
					errorXML('Error adding dumper '.$_POST['d_dumpers'][$i].'!');
			}
		}
		
		// 5. RSS
		if ($_POST['rss']) {
			$rss = new Rss('['.$system['s_short_media'].'][NEW] '.htmlspecialchars(discfilename($disc)), '/disc/'.$disc['d_id'].'/', $disc['d_id']);
			$rss->row('Title', '<b>'.htmlspecialchars(title($disc['d_title'])).'</b>');
			$rss->row('Alternative title', htmlspecialchars(title($disc['d_title_foreign'])));
			$rss->row('Disc number', $disc['d_number']);
			$rss->row('Disc title', htmlspecialchars(title($disc['d_label'])));
			$rss->row('System', htmlspecialchars($system['s_full']));
			$rss->row('Media', htmlspecialchars($system['s_media_text']));
			$rss->row('Category', $psxdb['categories'][$disc['d_category']]);
			$rss->row('Region', region($disc['d_region']));
			$rss->row('Languages', languages($disc['d_languages']));
			$rss->row('Serial', htmlspecialchars($disc['d_serial']));
			$rss->row('Ring', htmlspecialchars($disc['d_ring']));
			$rss->row('Barcode', htmlspecialchars($disc['d_barcode']));
			$rss->row('EXE date', $disc['d_date']);
			$rss->row('Version', htmlspecialchars($disc['d_version']));
			$rss->row('Edition', htmlspecialchars($disc['d_edition']));
			if ($disc['s_edc']) {
				$rss->row('EDC', booleaninfo($disc['d_edc']));
			}
			if ($system['s_media'] == 1) {
				$rss->row('Errors count', $disc['d_errors']);
			}
			if ($disc['d_media'] == 1) {
				$rss->row('Anti-modchip protection', booleaninfo($disc['d_protection_a']));
				$rss->row('LibCrypt protection', libcrypt($disc['d_protection_l']));
			}
			$rss->row('Status', status($disc['d_status']));
			$rss->row('Dumpers', $dumpers_names);
			$rss->row('Comments', psxdbcode(nl2br($disc['d_comments'])));
			$rss->row('Write offset', write_offset($disc['d_offset']));
			$rss->row('Tracks count', $disc['d_tracks_count']);
			$rss->row('Size', $_POST['d_size']);
			$rss->row('CRC-32', $_POST['d_crc32']);
			$rss->row('MD5', $_POST['d_md5']);
			$rss->row('SHA-1', $_POST['d_sha1']);
			$rss->row('DOL MD5', $_POST['d_dol_md5']);
		}
		
		// 6. Tracks/DVD data
		if ($system['s_media'] == 1) {
			for ($number = 1; $number <= $tracks_count; $number++) {
				if (!$mysqli->query('INSERT INTO `tracks` (`t_number`,`t_size`,`t_crc32`,`t_md5`,`t_sha1`,`t_type`,`t_flags`,`t_pregap`,`d_id`) VALUES ('.intval($number).','.intval($tracks[$number]['size']).',"'.addslashes($tracks[$number]['crc32']).'","'.$tracks[$number]['md5'].'","'.$tracks[$number]['sha1'].'",'.$tracks[$number]['type'].','.$tracks[$number]['flags'].','.$tracks[$number]['pregap'].','.$disc['d_id'].')')) {
					errorXML('Error addding track info!');
				}
				// RSS
				if ($_POST['rss']) {
					$rss->blankrow();
					if ($tracks_count > 1)
						$rss->row('Track number', '<b>Track '.$number.' of '.$tracks_count.'</b>');
					else
						$rss->row('Track number', '<b>Track '.$number.'</b>');
					$rss->row('Size', $tracks[$number]['size']);
					$rss->row('Sectors', $tracks[$number]['size'] / 2352);
					$rss->row('Length', stomsf($tracks[$number]['size'] / 2352));
					$rss->row('CRC-32', $tracks[$number]['crc32']);
					$rss->row('MD5', $tracks[$number]['md5']);
					$rss->row('SHA-1', $tracks[$number]['sha1']);
					$rss->row('Track type', tracktype($tracks[$number]['type']));
					$rss->row('Flags', flags($tracks[$number]['flags']));
					if ($tracks[$number]['pregap'])
						$rss->row('Pregap', stomsf($tracks[$number]['pregap']));
				}
			}
		} else if ($system['s_media'] == 2) {
			if (!$mysqli->query('INSERT INTO `dvd` (`d_id`,`d_size`,`d_crc32`,`d_md5`,`d_sha1`,`d_ed2k`,`d_dol_md5`) VALUES ('.$disc['d_id'].','.$_POST['d_size'].',"'.$_POST['d_crc32'].'","'.$_POST['d_md5'].'","'.$_POST['d_sha1'].'","'.$_POST['d_ed2k'].'","'.$_POST['d_dol_md5'].'")')) {
				errorXML('Error adding DVD info!');
			}
		}

	if (isset($_POST['d_id']))
		$mysqli->query('UPDATE `discs_wip` SET `discs_wip`.`d_added`=1 WHERE `discs_wip`.`d_id`='.intval($_POST['d_id']));

		// 7. Adding RSS, making cue, displaying output
		if ($_POST['rss']) {
			if (!$rss->query())
				errorXML('Error adding RSS!');
		}
		if (!make_cues($disc['d_id'])) {
			errorXML('Error making cue!');
		}
		generate_datfile($disc['d_id']);
		okXML('Done. <a href="/disc/'.$disc['d_id'].'/">Go to the disc</a>');
	} else {
		if ($system['s_media'] == 1) {
			$cue = new Cueparser();
			for ($number = 1; $number <= $tracks_count; $number++) {
				$_POST['d_tracks'][] = 'size '.$tracks[$number]['size'].' crc '.$tracks[$number]['crc32'].' md5 '.$tracks[$number]['md5'].' sha1 '.$tracks[$number]['sha1'];
				$cue->addTrack(array('type' => $tracks[$number]['type'], 'pregap' => $tracks[$number]['pregap']));
			}
			$_POST['d_tracks'] = implode("\n", $_POST['d_tracks']);
			$_POST['d_cue'] = $cue->returnCuesheet();
		}
		$query = 'INSERT INTO `discs_wip` (
	`d_barcode`,
	`d_category`,
	`d_comments`,
	`d_date`,
	`d_dumper`,
	`d_edc`,
	`d_errors`,
	`d_label`,
	`d_languages`,
	`d_number`,
	`d_protection_a`,
	`d_libcrypt`,
	`d_region`,
	`d_ring`,
	`d_serial`,
	`d_media`,
	`d_title`,
	`d_title_foreign`,
	`d_tracks`,
	`d_cue`,
	`d_version`,
	`d_edition`,
	`d_offset`,
	`d_size`,
	`d_crc32`,
	`d_md5`,
	`d_sha1`,
	`d_ed2k`,
	`d_dol_md5`
) VALUES (
	'.(($_POST['d_barcode'] != '') ? '"'.addslashes($_POST['d_barcode']).'"' : 'NULL').',
	'.intval($_POST['d_category']).',
	'.(($_POST['d_comments'] != '') ? '"'.addslashes($_POST['d_comments']).'"' : 'NULL').',
	'.(($_POST['d_date'] != '') ? '"'.addslashes($_POST['d_date']).'"' : 'NULL').',
	'.intval($psxdb_user['id']).',
	'.(($_POST['d_edc'] != '') ? $_POST['d_edc'] : 'NULL').',
	'.(($_POST['d_errors'] != '') ? $_POST['d_errors'] : 'NULL').',
	'.(($_POST['d_label'] != '') ? '"'.addslashes($_POST['d_label']).'"' : 'NULL').',
	'.(($_POST['d_languages'] != '') ? '"'.addslashes($_POST['d_languages']).'"' : 'NULL').',
	'.(($_POST['d_number'] != '') ? '"'.addslashes($_POST['d_number']).'"' : 'NULL').',
	'.(($_POST['d_protection_a'] != '') ? $_POST['d_protection_a'] : 'NULL').',
	'.(($_POST['d_libcrypt'] != '') ? '"'.addslashes($_POST['d_libcrypt']).'"' : 'NULL').',
	"'.addslashes($_POST['d_region']).'",
	'.(($_POST['d_ring'] != '') ? '"'.addslashes($_POST['d_ring']).'"' : 'NULL').',
	'.(($_POST['d_serial'] != '') ? '"'.addslashes($_POST['d_serial']).'"' : 'NULL').',
	'.intval($_POST['d_media']).',
	"'.addslashes($_POST['d_title']).'",
	'.(($_POST['d_title_foreign'] != '') ? '"'.addslashes($_POST['d_title_foreign']).'"' : 'NULL').',
	'.(($_POST['d_tracks'] != '') ? '"'.addslashes($_POST['d_tracks']).'"' : 'NULL').',
	'.(($_POST['d_cue'] != '') ? '"'.addslashes($_POST['d_cue']).'"' : 'NULL').',
	'.(($_POST['d_version'] != '') ? '"'.addslashes($_POST['d_version']).'"' : 'NULL').',
	'.(($_POST['d_edition'] != '') ? '"'.addslashes($_POST['d_edition']).'"' : 'NULL').',
	'.(($offset != '') ? '"'.addslashes($offset).'"' : 'NULL').',
	'.(($_POST['d_size'] != '') ? $_POST['d_size'] : 'NULL').',
	'.(($_POST['d_crc32'] != '') ? '"'.addslashes($_POST['d_crc32']).'"' : 'NULL').',
	'.(($_POST['d_md5'] != '') ? '"'.addslashes($_POST['d_md5']).'"' : 'NULL').',
	'.(($_POST['d_sha1'] != '') ? '"'.addslashes($_POST['d_sha1']).'"' : 'NULL').',
	'.(($_POST['d_ed2k'] != '') ? '"'.addslashes($_POST['d_ed2k']).'"' : 'NULL').',
	'.(($_POST['d_dol_md5'] != '') ? '"'.addslashes($_POST['d_dol_md5']).'"' : 'NULL').')';
	
		if (!$mysqli->query($query)) {
			//errorXML('Error adding disc info!', str_replace(array("\n", "\r", "\t", '  '), ' ', $query));
			errorXML('Error adding disc info!');
		}
		okXML('Thank you, '.htmlspecialchars($psxdb_user['username']).', your info has been added! | <a href="javascript:resetForm(\''.$_GET['module'].'\')">Add another disc</a>');
	}
}

/*******************/
/* Display systems */
/*******************/
if (((defined('ADMIN') || defined('MODERATOR')) && !(isset($_GET['system']) ^ isset($_GET['id']))) || (defined('DUMPER') && (!isset($_GET['system']) || isset($_GET['id'])))) {
	$systems = $mysqli->query('SELECT * FROM `systems` AS `s` ORDER BY `s`.`s_order`');
	echo '<h3>Please select system</h3>
<div class="textblock"><ul>';
	while ($system = $systems->fetch_array())
		echo '<li><a href="/newdisc/'.str_replace(' ', '_', $system['s_short_media']).'/">'.htmlspecialchars($system['s_full_media']).'</a></li>';
	echo '</ul>
</div>';
	$psxdb['title'] = 'New disc';
	display();
}

/****************/
/* Display form */
/****************/

// a) new disc
// b) WIP disc
// c) new WIP disc by dumper

// 1. Checking WIP disc id, system query
if (isset($_GET['id'])) {         // a) WIP disc
	$query = $mysqli->query('SELECT * FROM `discs_wip` AS `d` WHERE `d`.`d_id`='.intval($_GET['id']));
	if (!$disc = $query->fetch_array())
		error('WIP disc with ID='.htmlspecialchars($_GET['id']).' doesn\'t exist.');
	$query = $mysqli->query('SELECT * FROM `systems` AS `s` WHERE `s`.`s_id`='.$disc['d_media']);
	if (!$system = $query->fetch_array())
		error('System with ID='.$disc['d_media'].' doesn\'t exist.');
} else {                          // b) New disc, c) new WIP disc by dumper
	$query = $mysqli->query('SELECT * FROM `systems` AS `s` WHERE `s`.`s_short_media`="'.addslashes(str_replace('_', ' ', $_GET['system'])).'"');
	if (!$system = $query->fetch_array())
		error('System "'.htmlspecialchars($_GET['system']).'" doesn\'t exist.');
}

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
if ((defined('ADMIN') || defined('MODERATOR')) && isset($_GET['id'])) {
	$form->statictext(array('contents' => '<a class="small" href="/discs/system/'.strtolower($system['s_short']).'/region/'.$disc['d_region'].'/title/'.url_string($disc['d_title']).'/">Search for this title in Redump</a>'));
	$form->statictext(array('contents' => '<a class="small" href="/discs/serial/'.url_string($disc['d_serial']).'/">Search for this serial in Redump</a>'));
	$form->statictext(array('contents' => '<a class="small" href="http://www.mobygames.com/search/quick?q='.urlencode($disc['d_title']).'&amp;p='.$system['s_mobygames_id'].'">Search for this title in Mobygames</a>'));
	$form->statictext(array('contents' => '<a class="small" href="http://www.cdcovers.cc/search/'.strtolower($system['s_short']).'/'.url_string($disc['d_title']).'/">Search for this title in Cdcovers</a>'));
}
$form->statictext(array('caption' => 'System', 'contents' => htmlspecialchars($system['s_full'])));
$form->statictext(array('caption' => 'Media', 'contents' => htmlspecialchars($system['s_media_text'])));
$form->hidden(array('name' => 'd_media', 'value' => $system['s_id']));
$form->text(array('name' => 'd_title', 'caption' => 'Title', 'value' => $disc['d_title']));
$form->text(array('name' => 'd_title_foreign', 'caption' => 'Alternative title', 'value' => $disc['d_title_foreign']));
$form->text(array('name' => 'd_number', 'caption' => 'Disc number', 'value' => $disc['d_number']));
$form->text(array('name' => 'd_label', 'caption' => 'Disc title', 'value' => $disc['d_label']));
$form->select(array('name' => 'd_category', 'caption' => 'Category', 'option' => $categories, 'select' => (!isset($disc['d_category']) ? array(1) : array($disc['d_category']))));
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
$form->textarea(array('name' => 'd_comments', 'caption' => 'Comments', 'value' => $disc['d_comments']));

// b) version
$form->fieldset('Version and editions');
$form->text(array('name' => 'd_version', 'caption' => 'Version', 'value' => $disc['d_version']));
if (defined('ADMIN') || defined('MODERATOR'))
	$form->text(array('name' => 'd_version_datfile', 'caption' => 'Version (datfile)'));
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
		$form->textarea(array('name' => 'd_libcrypt', 'caption' => 'LibCrypt data', 'value' => $disc['d_libcrypt']));
	} else {
		$form->textarea(array('name' => 'd_libcrypt', 'caption' => 'LibCrypt data'));
		$form->checkbox(array('name' => 'd_libcrypt_no', 'checkbox' => array(array('I\'ve checked disc with psxt001z - no LibCrypt', 1))));
	}
}
// d) dumpers
if (defined('ADMIN') || defined('MODERATOR')) {
	$form->fieldset('Dumpers and status');
	if (isset($_GET['id'])) {
		$form->statictext(array('caption' => 'Status', 'contents' => status(4)));
		$form->hidden(array('name' => 'd_status', 'value' => 4));
		$query = $mysqli->query('SELECT * FROM `users` AS `u` WHERE `u`.`id`='.$disc['d_dumper']);
		if (!$user = $query->fetch_array())
			error('User with ID='.$disc['d_dumper'].' doesn\'t exist.');
		$form->statictext(array('caption' => 'Dumper', 'contents' => '<a href="/discs/dumper/'.htmlspecialchars($user['username']).'/">'.htmlspecialchars($user['username']).'</a>'));
		$form->hidden(array('name' => 'd_dumper', 'value' => $disc['d_dumper']));
	} else {
		$query = $mysqli->query('SELECT `u`.`group_id`,`u`.`id`,`u`.`username` FROM `users` AS `u` WHERE `u`.`group_id`=1 OR `u`.`group_id`=4 OR `u`.`group_id`=5 ORDER BY `u`.`username`');
		while ($dumper = $query->fetch_array())
			$dumpers[] = array($dumper['username'], $dumper['id']);
		$form->radio(array('name' => 'd_status', 'caption' => 'Status', 'radio' => array(array(status(4).' '.statustext(4), 4), array(status(5).' '.statustext(5), 5))));
		$form->select(array('name' => 'd_dumpers', 'caption' => 'Dumpers', 'multiple' => 1, 'option' => $dumpers));
		$form->text(array('name' => 'd_dumpers_text', 'caption' => 'Other dumpers'));
	}
}

// e) tracks, checksums
if ($system['s_media'] == 1) {
	$form->fieldset('Tracks');
	$form->textarea(array('name' => 'd_tracks', 'caption' => 'ClrMamePro data', 'help' => 'Sizes and checksums from clrmamepro\'s datfile', 'value' => $disc['d_tracks']));
	$form->textarea(array('name' => 'd_cue', 'caption' => 'Cuesheet', 'value' => $disc['d_cue']));
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
	$form->fieldset('Size &amp; checksums');
	$form->text(array('name' => 'd_size', 'caption' => 'Size', 'value' => $disc['d_size']));	
	$form->text(array('name' => 'd_crc32', 'caption' => 'CRC-32', 'value' => $disc['d_crc32']));
	$form->text(array('name' => 'd_md5', 'caption' => 'MD5', 'value' => $disc['d_md5']));
	$form->text(array('name' => 'd_sha1', 'caption' => 'SHA-1', 'value' => $disc['d_sha1']));
	$form->text(array('name' => 'd_ed2k', 'caption' => 'ed2k', 'value' => $disc['d_ed2k']));
	$form->text(array('name' => 'd_dol_md5', 'caption' => 'DOL MD5', 'value' => $disc['d_dol_md5']));
}

// f) other
if (defined('ADMIN'))
	$form->checkbox(array('name' => 'rss', 'checkbox' => array(array('Show changes in RSS', 1)), 'check' => array(1)));
if ((defined('ADMIN') || defined('MODERATOR')) && isset($_GET['id']))
		$form->hidden(array('name' => 'd_id', 'value' => $disc['d_id']));

if (isset($_GET['id']))
	$form->statictext(array('contents' => '<a class="small" href="/discs-wip/'.$_GET['id'].'/remove/">Don\'t add, remove from WIP discs</a>'));


$form->submit(array('caption' => 'Add disc'));
$psxdb['title'] = 'New disc &mdash; '.htmlspecialchars($system['s_full_media']);
echo $form->contents();
display();

?>