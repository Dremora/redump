<?php

if (!defined('PSXDB')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

$psxdb['script'][] = '<script type="text/javascript" src="/javascript/disc.js"></script>';

//Checking ID
$query = 'SELECT * FROM `discs` AS `d`,`systems` AS `s` WHERE `s`.`s_id`=`d`.`d_media` AND `d`.`d_id`='.intval($_GET['id']);
if (!defined('ADMIN') && !defined('MODERATOR')) $query .= ' AND `s`.`s_public`=1';
if (!in_array($psxdb_user['id'], $psxdb_config['red_users'])) $query .= ' AND `d`.`d_status`>=4';
$query = $mysqli->query($query);
if ($query->num_rows != 1)
	error('Disc with ID "'.htmlspecialchars($_GET['id']).'" doesn\'t exist.');
$disc = $query->fetch_array();

/**************/
/* Disc tools */
/**************/

function get_disc_title($disc) {
	$title = htmlspecialchars(title($disc['d_title']));
	if ($disc['d_title_foreign'] != '')
		$title .= ' &bull; '.htmlspecialchars(title($disc['d_title_foreign']));
	if (isset($disc['d_number']) || $disc['d_label'] != '') {
		$title .= '<br />';
		if (isset($disc['d_number']))
			$title .= 'Disc '.$disc['d_number'].'';
		if ($disc['d_label'] != '')
			$title .= ' ('.htmlspecialchars(title($disc['d_label'])).')';
	}
	return $title;
}

switch ($_GET['action']) {
	case 'cue':
	case 'gdi':
		$psxdb['title'] = $disc['d_cue_title'];
		echo $disc['d_cue_contents'];
		downloadText();
		break;
	case 'sfv':
		$psxdb['title'] = discfilename($disc).'.sfv';
		if ($disc['s_media'] == 1) {
			$tracks = $mysqli->query('SELECT * FROM tracks WHERE d_id='.$_GET['id'].' ORDER BY t_number');
			while ($track = $tracks->fetch_array()) {
				if ($disc['s_description'] == 2) {
					echo 'Track'.str_pad($track['t_number'], 2, '0', STR_PAD_LEFT).'.'.$disc['s_extension'].' '.$track['t_crc32']."\r\n";
				} else {
					echo filename($disc, $track['t_number']).'.'.$disc['s_extension'].' '.$track['t_crc32']."\r\n";
				}
			}
			if ($disc['s_description'] == 1) {
				echo discfilename($disc).'.cue'.' '.$disc['d_cue_crc32']."\r\n";
			}
			if ($disc['s_description'] == 2) {
				echo discfilename($disc).'.gdi'.' '.$disc['d_cue_crc32']."\r\n";
			}
		} else if ($disc['s_media'] == 2) {
			$dvdquery = $mysqli->query('SELECT * FROM `dvd` WHERE `dvd`.`d_id`='.intval($_GET['id']));
			$dvd = $dvdquery->fetch_array();
			echo discfilename($disc).'.'.$disc['s_extension'].' '.$dvd['d_crc32']."\r\n";
		}
		downloadText();
		break;
	case 'md5':
		$psxdb['title'] = discfilename($disc).'.md5';
		if ($disc['s_media'] == 1) {
			$tracks = $mysqli->query('SELECT * FROM tracks WHERE d_id='.$_GET['id'].' ORDER BY t_number');
			while ($track = $tracks->fetch_array()) {
				if ($disc['s_description'] == 2) {
					echo $track['t_md5'].' *Track'.str_pad($track['t_number'], 2, '0', STR_PAD_LEFT).'.'.$disc['s_extension']."\r\n";
				} else {
					echo $track['t_md5'].' *'.filename($disc, $track['t_number']).'.'.$disc['s_extension']."\r\n";
				}
			}
			if ($disc['s_description'] == 1) {
				echo $disc['d_cue_md5'].' *'.discfilename($disc).'.cue'."\r\n";
			}
			if ($disc['s_description'] == 2) {
				echo $disc['d_cue_md5'].' *'.discfilename($disc).'.gdi'."\r\n";
			}
		} else if ($disc['s_media'] == 2) {
			$dvdquery = $mysqli->query('SELECT * FROM `dvd` WHERE `dvd`.`d_id`='.intval($_GET['id']));
			$dvd = $dvdquery->fetch_array();
			echo $dvd['d_md5'].' *'.discfilename($disc).'.'.$disc['s_extension']."\r\n";
		}
		downloadText();
		break;
	case 'sbi':
		if ($disc['d_libcrypt'] == NULL)
			error('Disc with ID "'.htmlspecialchars($_GET['id']).'" doesn\'t have LibCrypt protection.');
		$array = str_split($disc['d_libcrypt']);
		$sectors = count($array) / 15;
		echo "SBI\0";
		for ($i = 0; $i < $sectors; $i++)
			echo $array[$i * 15].$array[$i * 15 + 1].$array[$i * 15 + 2].chr(1).$array[$i * 15 + 3].$array[$i * 15 + 4].$array[$i * 15 + 5].$array[$i * 15 + 6].$array[$i * 15 + 7].$array[$i * 15 + 8].$array[$i * 15 + 9].$array[$i * 15 + 10].$array[$i * 15 + 11].$array[$i * 15 + 12];
		$psxdb['title'] = discfilename($disc).'.sbi';
		downloadText();
		break;
	case 'lsd':
		if ($disc['d_libcrypt'] == NULL) {
			error('Disc with ID "'.htmlspecialchars($_GET['id']).'" doesn\'t have LibCrypt protection.');
		}
		$array = str_split($disc['d_libcrypt']);
		for ($i = 0; $i < count($array); $i++) {
			echo $array[$i];
		}
		$psxdb['title'] = discfilename($disc).'.lsd';
		downloadText();
		break;
	case 'rebuildcue':
	case 'rebuildgdi':
		if (!defined('ADMIN') && !defined('MODERATOR')) {
			redirect('http://'.$_SERVER['HTTP_HOST'].'/disc/'.$disc['d_id'].'/');
		}
		if (!make_cues(intval($_GET['id'])))
			error('Error making cue for disc with ID '.htmlspecialchars($_GET['id']).'!');
		redirect('http://'.$_SERVER['HTTP_HOST'].'/disc/'.$disc['d_id'].'/');
		break;
	// AJAX response
	case 'mydisc':
		if (!defined('LOGGED')) redirect('http://'.$_SERVER['HTTP_HOST'].'/disc/'.$disc['d_id'].'/');
		$mydiscquery = $mysqli->query('SELECT * FROM `du` WHERE `du`.`d_id`='.$disc['d_id'].' AND `du`.`u_id`='.$psxdb_user['id']);
		if (!$mydiscquery->num_rows) {
			$mysqli->query('INSERT INTO `du` VALUES ('.$disc['d_id'].','.$psxdb_user['id'].',0)');
			$ajax = new Ajax(0, '<text>have</text><id>'.$disc['d_id'].'</id><userid>'.$psxdb_user['id'].'</userid><username>'.htmlspecialchars($psxdb_user['username']).'</username>');
		} else {
			$mydisc = $mydiscquery->fetch_array();
			if ($mydisc['du_status'] == 0) {
				$mysqli->query('DELETE FROM `du` WHERE `du`.`d_id`='.$disc['d_id'].' AND `du`.`u_id`='.$psxdb_user['id']);
				$ajax = new Ajax(0, '<text>miss</text><id>'.$disc['d_id'].'</id><userid>'.$psxdb_user['id'].'</userid>');
			} else if ($mydisc['du_status'] == 1) {
				$mysqli->query('UPDATE `du` SET du_status=2 WHERE `du`.`d_id`='.$disc['d_id'].' AND `du`.`u_id`='.$psxdb_user['id']);
				$ajax = new Ajax(0, '<text>have</text><id>'.$disc['d_id'].'</id>');
			} else if ($mydisc['du_status'] == 2) {
				$mysqli->query('UPDATE `du` SET du_status=1 WHERE `du`.`d_id`='.$disc['d_id'].' AND `du`.`u_id`='.$psxdb_user['id']);
				$ajax = new Ajax(0, '<text>miss</text><id>'.$disc['d_id'].'</id>');
			}
		}
		$ajax->display();
		break;
	case 'delete':
		if (!defined('ADMIN') && !defined('MODERATOR')) {
			redirect('http://'.$_SERVER['HTTP_HOST'].'/disc/'.$disc['d_id'].'/');
		}
		echo '<div class="error"><b>Are you sure you want to delete this disc?</b> <br /><br /><button onclick="javascript:location.href = \'http://'.$_SERVER['HTTP_HOST'].'/disc/'.$disc['d_id'].'/deleteok\'">Delete</button> <a href="javascript:history.go(-1)">Go back</a></div>';
		break;

	case 'deleteok':
		if (!defined('ADMIN') && !defined('MODERATOR')) {
			redirect('http://'.$_SERVER['HTTP_HOST'].'/disc/'.$disc['d_id'].'/');
		}
		$rss = new Rss('['.$disc['s_short_media'].'][DELETED] '.htmlspecialchars(discfilename($disc)), '', $disc['d_id']);
		$rss->row('DB ID', $disc['d_id']);
		if (!$rss->query()) {
			errorXML('Error adding RSS!');
		}
		$mysqli->query('DELETE FROM `du` WHERE `d_id`='.$disc['d_id']);
		$mysqli->query('DELETE FROM `tracks` WHERE `d_id`='.$disc['d_id']);
		$mysqli->query('DELETE FROM `discs` WHERE `d_id`='.$disc['d_id']);
		echo '<div style="margin: 5px 15px; border: 1px solid #ccffcc; background: #eeffee; padding: 5px">Disc was successfully deleted.</div>';
		display();
		break;

	case 'changes':
		if (!defined('LOGGED')) {
			redirect('http://'.$_SERVER['HTTP_HOST'].'/disc/'.$disc['d_id'].'/');
		}
		$changes = $mysqli->query('SELECT rss.*,users.username FROM rss,users WHERE users.id=rss.u_id AND d_id='.$disc['d_id'].' ORDER BY r_id DESC');
		echo '<h1><a href="/disc/'.$disc['d_id'].'">';

		echo htmlspecialchars(title($disc['d_title']));
			if (isset($disc['d_number']))
				echo ' (Disc '.$disc['d_number'].')';
			if ($disc['d_label'] != '')
				echo ' ('.htmlspecialchars(title($disc['d_label'])).')';
		echo '</a>: ';
		if ($changes->num_rows == 0)
		{
			echo 'No changes</h1>';
			display();
		}
		echo 'Changes</h1><ul class="changes">';
		while ($change = $changes->fetch_array()) {
			echo '<li><dl>';
			echo '<dt>Date:</dt><dd>'.format_datetime($change['r_datetime_new'], 'M d Y, H:i').'</dd>';
			echo '<dt>User:</dt><dd>'.htmlspecialchars($change['username']).'</dd>';
			echo '<dt>Changes:</dt><dd>'.$change['r_contents'].'</dd>';
			echo '</dl></li>';
		}
		echo '</ul>';
		$psxdb['title'] = get_disc_title($disc).': Changes';
		display();
		break;
}

/*************/
/* Main page */
/*************/

$dumpers = $mysqli->query('SELECT * FROM `users`,`du` WHERE `users`.`id`=`du`.`u_id` AND (`du`.`du_status`=1 OR `du`.`du_status`=2) AND `du`.`d_id`='.$disc['d_id']);

// Owners
if (defined('ADMIN') || defined('MODERATOR')) {
	$owners = $mysqli->query('SELECT * FROM `users`,`du` WHERE `users`.`id`=`du`.`u_id` AND du.du_status=0 AND `du`.`d_id`='.$disc['d_id'].' ORDER BY `users`.`username`');
} else if (defined('DUMPER')) {
	$owners = $mysqli->query('SELECT * FROM `users`,`du` WHERE `users`.`id`=`du`.`u_id` AND du.du_status=0 AND `users`.`u_show_list`<=2 AND `du`.`d_id`='.$disc['d_id'].' ORDER BY `users`.`username`');
}
/*
else if (defined('LOGGED'))
	$owners = $mysqli->query('SELECT * FROM `users`,`du` WHERE `users`.`id`=`du`.`u_id` AND du.du_status=0 AND `users`.`u_show_list`<=1 AND `du`.`d_id`='.$disc['d_id'].' ORDER BY `users`.`username`');
else
	$owners = $mysqli->query('SELECT * FROM `users`,`du` WHERE `users`.`id`=`du`.`u_id` AND du.du_status=0 AND `users`.`u_show_list`=0 AND `du`.`d_id`='.$disc['d_id'].' ORDER BY `users`.`username`');
*/

// Tracks
if ($disc['s_media'] == 1)
	$tracks = $mysqli->query('SELECT * FROM `tracks` WHERE `tracks`.`d_id`='.$_GET['id'].' ORDER BY `tracks`.`t_id`');
// DVDs
else {
	$dvdquery = $mysqli->query('SELECT * FROM `dvd` WHERE `dvd`.`d_id`='.intval($_GET['id']));
	$dvd = $dvdquery->fetch_array();
}

// Tools
$tools = array();
$tools[] = '<b>Download:</b> <a href="/disc/'.$disc['d_id'].'/md5/">MD5</a>';
$tools[] = '<a href="/disc/'.$disc['d_id'].'/sfv/">SFV</a>';
if ($disc['s_description'] == 1)
	$tools[] = '<a href="/disc/'.$disc['d_id'].'/cue/">Cuesheet</a>';
else if ($disc['s_description'] == 2)
	$tools[] = '<a href="/disc/'.$disc['d_id'].'/gdi/">GDI</a>';
if ($disc['d_libcrypt'] != NULL) {
	$tools[] = '<a href="/disc/'.$disc['d_id'].'/sbi/">SBI subchannels</a>';
}
if (defined('LOGGED')) {
	$mygamequery = $mysqli->query('SELECT `du`.* FROM `du` WHERE `du`.`d_id`='.intval($_GET['id']).' AND `du`.`u_id`='.$psxdb_user['id']);
	if ($mygamequery->num_rows)
	{
		$mygame = $mygamequery->fetch_array();
		if ($mygame['du_status'] == 0 || $mygame['du_status'] == 2) $tools[] = '<a id="mydisctext" href="javascript:ChangeDiscStatus('.$disc['d_id'].');">Remove from my discs</a>';
		else $tools[] = '<a id="mydisctext" href="javascript:ChangeDiscStatus('.$disc['d_id'].');">Add to my discs</a>';
	} else
	{
		$tools[] = '<a id="mydisctext" href="javascript:ChangeDiscStatus('.$disc['d_id'].');">Add to my discs</a>';
	}
	$tools[] = '<a href="/disc/'.$disc['d_id'].'/changes/">View edit history</a>';
}

//if ($rels = relationships(0, $_GET['id'])) {
//	$psxdb['overlay'] = $rels.'<button onclick="HideOverlay();">Close</button>';
//	$tools[] = '<a href="javascript:ShowOverlay();">View game discs</a>';
//}

if (defined('ADMIN') || defined('MODERATOR')) {
	$tools[] = '<b>Admin tools:</b> <a href="/disc/'.$disc['d_id'].'/edit/">Edit disc</a>';
	if ($disc['s_description'] == 1) {
		$tools[] = '<a href="/disc/'.$disc['d_id'].'/rebuildcue/">Rebuild cuesheet</a>';
	} else if ($disc['s_description'] == 2) {
		$tools[] = '<a href="/disc/'.$disc['d_id'].'/rebuildgdi/">Rebuild GDI</a>';
	}
	$tools[] = '<a style="color: #cc0000;" href="/disc/'.$disc['d_id'].'/delete/">Delete</a>';
}

if (count($tools) > 0)
	echo '<div class="tools">'.implode(' &bull; ', $tools).'</div>';

// Title
echo '<h1>'.htmlspecialchars(title($disc['d_title']));
	if (isset($disc['d_number']))
		echo ' (Disc '.$disc['d_number'].')';
	if ($disc['d_label'] != '')
		echo ' ('.htmlspecialchars(title($disc['d_label'])).')';
echo '</h1>';
if ($disc['d_title_foreign'] != '') {
	echo '<h2>'.htmlspecialchars(title($disc['d_title_foreign'])).'</h2>';
}

// Game info
echo '<div class="game"><table class="gameinfo" cellspacing="0">
<tr><th>System</th><td><a href="/discs/system/'.strtolower(htmlspecialchars($disc['s_short'])).'/">'.htmlspecialchars($disc['s_full']).'</a></td></tr>
<tr><th>Media</th><td>'.$disc['s_media_text'].'</td></tr>
<tr><th>Category</th><td>'.$psxdb['categories'][$disc['d_category']].'</td></tr>
<tr><th>Region</th><td><a href="/discs/region/'.$disc['d_region'].'/">'.region($disc['d_region']).'</a></td></tr>
';
if ($disc['d_languages'])
	echo '<tr><th>Languages</th><td>'.languages($disc['d_languages']).'</td></tr>
';
if ($disc['d_serial'])
	echo '<tr><th>Serial</th><td>'.htmlspecialchars($disc['d_serial']).'</td></tr>
';
if ($disc['d_date'] != '')
	echo '<tr><th>EXE date</th><td>'.$disc['d_date'].'</td></tr>
';
if ($disc['d_version'])
	echo '<tr><th>Version</th><td>'.htmlspecialchars(title($disc['d_version'])).'</td></tr>
';
if ($disc['d_edition'])
	echo '<tr><th>Edition</th><td>'.htmlspecialchars($disc['d_edition']).'</td></tr>
';
if ($disc['d_media'] == 1)
	echo '<tr><th>EDC</th><td>'.booleaninfo($disc['d_edc']).'</td></tr>
<tr><th>Anti-modchip</th><td>'.booleaninfo($disc['d_protection_a']).'</td></tr>
<tr><th>LibCrypt</th><td>'.libcrypt($disc['d_protection_l']).'</td></tr>
';
if ($disc['s_media'] == 1) {
	if ($disc['d_errors'] != '') {
		echo '<tr><th>Errors count</th><td>'.$disc['d_errors'].'</td></tr>'."\r\n";
	}
	echo '<tr><th>Number of tracks</th><td>'.$disc['d_tracks_count'].'</td></tr>'."\r\n";
}
if ($disc['d_offset'] != '') {
	echo '<tr><th>Write offset</th><td>'.write_offset($disc['d_offset']).'</td></tr>
';
} else if ($disc['d_tracks_count'] > 1) {
		$tracks2 = $mysqli->query('SELECT `t_type`,`d_id`,`t_number` FROM `tracks` WHERE `tracks`.`d_id`='.$_GET['id'].' AND `tracks`.`t_number`=2');
		$tracks2a = $tracks2->fetch_array();
		if ($tracks2a['t_type'] == 2)
			echo '<tr><th>Offset status</th><td>EAC</td></tr>
';
}
if ($disc['d_datetime_added'] != 0)
	echo '<tr><th>Added</th><td>'.format_datetime($disc['d_datetime_added'], 'Y-m-d H:i').'</td></tr>
';
if ($disc['d_datetime_updated'] != 0 && $disc['d_datetime_added'] != $disc['d_datetime_updated'])
	echo '<tr><th>Last modified</th><td>'.format_datetime($disc['d_datetime_updated'], 'Y-m-d H:i').'</td></tr>
';
echo '</table>
<table class="dumpinfo" cellspacing="0">
<tr><th>Status</th><td>'.status($disc['d_status']).'</td></tr>
';

if ($dumpers->num_rows || $disc['d_dumpers']) {
	echo '<tr><th>Dumpers</th><td>';
	while ($dumper = $dumpers->fetch_array()) echo '<a href="/discs/dumper/'.htmlspecialchars($dumper['username']).'/">'.htmlspecialchars($dumper['username']).'</a><br />';
	if ($disc['d_dumpers'] != '') echo htmlspecialchars($disc['d_dumpers']).'<br />';
	echo '</td></tr>';
}

if ($owners->num_rows) {
	echo '<tr><th>Image owners</th><td id="owners">';
	while ($owner = $owners->fetch_array()) echo '<a id="owner'.$owner['u_id'].'" href="http://forum.'.$_SERVER['HTTP_HOST'].'/profile.php?id='.$owner['id'].'">'.htmlspecialchars($owner['username']).'</a>';
	echo '</td></tr>';
}

echo '</table>
';

if ($disc['d_comments'] || $disc['d_ring'] || $disc['d_barcode']) echo '<table class="gamecomments" cellspacing="0">';
if ($disc['d_ring']) echo '<tr><th>Ring</th></tr><tr><td>'.nl2br(htmlspecialchars($disc['d_ring'])).'</td></tr>';
if ($disc['d_barcode']) echo '<tr><th>Barcode</th></tr><tr><td>'.htmlspecialchars($disc['d_barcode']).'</td></tr>';
if ($disc['d_comments'] != '') echo '<tr><th>Comments</th></tr><tr><td>'.psxdbcode(nl2br($disc['d_comments'])).'</td></tr>';
if ($disc['d_comments'] || $disc['d_ring'] || $disc['d_barcode']) echo '</table>';

echo '</div>';

// Tracks info
if ($tracks->num_rows && $disc['s_media'] == 1) {
	$totaltracksize = 0;
	echo '<div class="game"><table class="tracks" cellspacing="0">';
	echo '<tr><th>#</th><th>Type</th><th>Pregap</th><th>Length</th><th>Sectors</th><th>Size</th><th>CRC-32</th><th>MD5</th><th>SHA-1</th></tr>';
	while ($track = $tracks->fetch_array()) {
		$totaltracksize += $track['t_size'];
		$lba = $track['t_size'] / 2352;
		$min = str_pad(floor($lba / 60 / 75), 2, '0', STR_PAD_LEFT);
		$sec = str_pad(floor(($lba - ($min * 60 * 75)) / 75), 2, '0', STR_PAD_LEFT);
		$frame = str_pad($lba - ($min * 60 * 75) - ($sec * 75), 2, '0', STR_PAD_LEFT);
		$gapmin = str_pad(floor($track['t_pregap'] / 60 / 75), 2, '0', STR_PAD_LEFT);
		$gapsec = str_pad(floor($track['t_pregap'] / 75) % 60, 2, '0', STR_PAD_LEFT);
		$gapframe = str_pad($track['t_pregap'] % 75, 2, '0', STR_PAD_LEFT);
		echo '<tr><td>'.$track['t_number'].'</td><td>'.tracktype($track['t_type']).'</td><td>'.$gapmin.':'.$gapsec.':'.$gapframe.'</td><td>'.$min.':'.$sec.':'.$frame.'</td><td>'.$lba.'</td><td>'.$track['t_size'].'</td><td>'.$track['t_crc32'].'</td><td>'.$track['t_md5'].'</td><td>'.$track['t_sha1'].'</td></tr>';
		if ($tracks->num_rows > 1 && $track['t_number'] == $disc['d_tracks_count']) {
			$lba = $totaltracksize / 2352;
			$min = str_pad(floor($lba / 60 / 75), 2, '0', STR_PAD_LEFT);
			$sec = str_pad(floor(($lba - ($min * 60 * 75)) / 75), 2, '0', STR_PAD_LEFT);
			$frame = str_pad($lba - ($min * 60 * 75) - ($sec * 75), 2, '0', STR_PAD_LEFT);
			echo '<tr><td></td><td><b>Total</b></td><td></td><td>'.$min.':'.$sec.':'.$frame.'</td><td>'.$lba.'</td><td>'.$totaltracksize.'</td><td></td><td></td><td></td></tr>';
		}
	}
	echo '</table></div>';
}

if ($disc['s_media'] == 2) {
	echo '<div class="game"><table class="dvd" cellspacing="0"><tr><th>Sectors</th><td>'.$dvd['d_size'] / 2048 .'</td></tr>
<tr><th>Size</th><td>'.$dvd['d_size'].'</td></tr>
<tr><th>CRC-32</th><td>'.$dvd['d_crc32'].'</td></tr>
<tr><th>MD5</th><td>'.$dvd['d_md5'].'</td></tr>
<tr><th>SHA-1</th><td>'.$dvd['d_sha1'].'</td></tr>';

	echo '
</table></div>';
}

/************/
/* LibCrypt */
/************/

$lc1sector1  = chr(0x03).chr(0x08).chr(0x05).chr(0x41).chr(0x01).chr(0x01).chr(0x07).chr(0x06).chr(0x05).chr(0x00).chr(0x23).chr(0x08).chr(0x05).chr(0x38).chr(0x39);
$lc1sector2  = chr(0x03).chr(0x08).chr(0x10).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x06).chr(0x11).chr(0x00).chr(0x03).chr(0x08).chr(0x90).chr(0x5d).chr(0xa0);
$lc1sector3  = chr(0x03).chr(0x09).chr(0x56).chr(0x41).chr(0x01).chr(0x01).chr(0x07).chr(0x07).chr(0x56).chr(0x00).chr(0x23).chr(0x09).chr(0x56).chr(0xdf).chr(0xde);
$lc1sector4  = chr(0x03).chr(0x09).chr(0x61).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x07).chr(0x60).chr(0x00).chr(0x03).chr(0x09).chr(0xe1).chr(0xf2).chr(0x50);
$lc1sector5  = chr(0x03).chr(0x13).chr(0x10).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x13).chr(0x10).chr(0x00).chr(0x03).chr(0x53).chr(0x10).chr(0x50).chr(0xec);
$lc1sector6  = chr(0x03).chr(0x13).chr(0x15).chr(0x41).chr(0x01).chr(0x01).chr(0x43).chr(0x11).chr(0x15).chr(0x00).chr(0x01).chr(0x13).chr(0x15).chr(0x23).chr(0x1e);
$lc1sector7  = chr(0x03).chr(0x14).chr(0x29).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x12).chr(0x09).chr(0x00).chr(0x03).chr(0x14).chr(0x2d).chr(0x04).chr(0x73);
$lc1sector8  = chr(0x03).chr(0x14).chr(0x34).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x1a).chr(0x34).chr(0x00).chr(0x03).chr(0x04).chr(0x34).chr(0xe2).chr(0xcf);
$lc1sector9  = chr(0x03).chr(0x15).chr(0x24).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x13).chr(0x20).chr(0x00).chr(0x03).chr(0x15).chr(0x04).chr(0x82).chr(0x35);
$lc1sector10 = chr(0x03).chr(0x15).chr(0x29).chr(0x41).chr(0x01).chr(0x01).chr(0x01).chr(0x13).chr(0x29).chr(0x00).chr(0x43).chr(0x15).chr(0x29).chr(0x72).chr(0xe2);
$lc1sector11 = chr(0x03).chr(0x18).chr(0x49).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x1e).chr(0x49).chr(0x00).chr(0x03).chr(0x08).chr(0x49).chr(0x32).chr(0xc5);
$lc1sector12 = chr(0x03).chr(0x18).chr(0x54).chr(0x41).chr(0x01).chr(0x01).chr(0x01).chr(0x16).chr(0x54).chr(0x00).chr(0x43).chr(0x18).chr(0x54).chr(0xd4).chr(0x79);
$lc1sector13 = chr(0x03).chr(0x20).chr(0x56).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x18).chr(0x57).chr(0x00).chr(0x03).chr(0x20).chr(0xd6).chr(0xbc).chr(0x27);
$lc1sector14 = chr(0x03).chr(0x20).chr(0x61).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x38).chr(0x61).chr(0x00).chr(0x03).chr(0x24).chr(0x61).chr(0x91).chr(0xa9);
$lc1sector15 = chr(0x03).chr(0x21).chr(0x55).chr(0x41).chr(0x01).chr(0x01).chr(0x0b).chr(0x19).chr(0x55).chr(0x00).chr(0x13).chr(0x21).chr(0x55).chr(0x14).chr(0x07);
$lc1sector16 = chr(0x03).chr(0x21).chr(0x60).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x19).chr(0x62).chr(0x00).chr(0x03).chr(0x21).chr(0x20).chr(0x5d).chr(0x48);
$lc1sector17 = chr(0x03).chr(0x23).chr(0x17).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x23).chr(0x17).chr(0x00).chr(0x03).chr(0x63).chr(0x17).chr(0x6d).chr(0xc6);
$lc1sector18 = chr(0x03).chr(0x23).chr(0x22).chr(0x41).chr(0x01).chr(0x01).chr(0x43).chr(0x21).chr(0x22).chr(0x00).chr(0x01).chr(0x23).chr(0x22).chr(0x24).chr(0x89);
$lc1sector19 = chr(0x03).chr(0x24).chr(0x12).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x02).chr(0x12).chr(0x00).chr(0x03).chr(0x20).chr(0x12).chr(0x49).chr(0x43);
$lc1sector20 = chr(0x03).chr(0x24).chr(0x17).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x22).chr(0x07).chr(0x00).chr(0x03).chr(0x24).chr(0x1f).chr(0x3a).chr(0xb1);
$lc1sector21 = chr(0x03).chr(0x25).chr(0x03).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x23).chr(0x13).chr(0x00).chr(0x03).chr(0x25).chr(0x0b).chr(0x93).chr(0xc9);
$lc1sector22 = chr(0x03).chr(0x25).chr(0x08).chr(0x41).chr(0x01).chr(0x01).chr(0x0b).chr(0x23).chr(0x08).chr(0x00).chr(0x13).chr(0x25).chr(0x08).chr(0xce).chr(0x5d);
$lc1sector23 = chr(0x03).chr(0x28).chr(0x28).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x06).chr(0x28).chr(0x00).chr(0x03).chr(0x2c).chr(0x28).chr(0xd7).chr(0xd6);
$lc1sector24 = chr(0x03).chr(0x28).chr(0x33).chr(0x41).chr(0x01).chr(0x01).chr(0x0b).chr(0x26).chr(0x33).chr(0x00).chr(0x13).chr(0x28).chr(0x33).chr(0x9c).chr(0x29);
$lc1sector25 = chr(0x03).chr(0x32).chr(0x19).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x30).chr(0x59).chr(0x00).chr(0x03).chr(0x32).chr(0x1b).chr(0x2c).chr(0xc6);
$lc1sector26 = chr(0x03).chr(0x32).chr(0x24).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x20).chr(0x24).chr(0x00).chr(0x03).chr(0x3a).chr(0x24).chr(0xe6).chr(0xac);
$lc1sector27 = chr(0x03).chr(0x33).chr(0x56).chr(0x41).chr(0x01).chr(0x01).chr(0x13).chr(0x31).chr(0x56).chr(0x00).chr(0x0b).chr(0x33).chr(0x56).chr(0x97).chr(0xed);
$lc1sector28 = chr(0x03).chr(0x33).chr(0x61).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x31).chr(0x65).chr(0x00).chr(0x03).chr(0x33).chr(0x41).chr(0xba).chr(0x63);
$lc1sector29 = chr(0x03).chr(0x34).chr(0x51).chr(0x41).chr(0x01).chr(0x01).chr(0x01).chr(0x32).chr(0x51).chr(0x00).chr(0x43).chr(0x34).chr(0x51).chr(0xd7).chr(0xa9);
$lc1sector30 = chr(0x03).chr(0x34).chr(0x56).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x33).chr(0x56).chr(0x00).chr(0x03).chr(0xb4).chr(0x56).chr(0xc0).chr(0x9a);
$lc1sector31 = chr(0x03).chr(0x35).chr(0x42).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x32).chr(0x42).chr(0x00).chr(0x03).chr(0xb5).chr(0x42).chr(0x69).chr(0xe2);
$lc1sector32 = chr(0x03).chr(0x35).chr(0x47).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x33).chr(0x07).chr(0x00).chr(0x03).chr(0x35).chr(0x45).chr(0x1a).chr(0x10);
$lc1sector33 = chr(0x09).chr(0x20).chr(0x45).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x18).chr(0x65).chr(0x00).chr(0x09).chr(0x20).chr(0x41).chr(0x40).chr(0x72);
$lc1sector34 = chr(0x09).chr(0x20).chr(0x50).chr(0x41).chr(0x01).chr(0x01).chr(0x19).chr(0x18).chr(0x50).chr(0x00).chr(0x01).chr(0x20).chr(0x50).chr(0x25).chr(0xeb);
$lc1sector35 = chr(0x09).chr(0x22).chr(0x16).chr(0x41).chr(0x01).chr(0x01).chr(0x08).chr(0x20).chr(0x16).chr(0x00).chr(0x89).chr(0x22).chr(0x16).chr(0x95).chr(0xa8);
$lc1sector36 = chr(0x09).chr(0x22).chr(0x21).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x20).chr(0x01).chr(0x00).chr(0x09).chr(0x22).chr(0x25).chr(0xb8).chr(0x26);
$lc1sector37 = chr(0x09).chr(0x25).chr(0x57).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x23).chr(0x53).chr(0x00).chr(0x09).chr(0x25).chr(0x77).chr(0x21).chr(0x03);
$lc1sector38 = chr(0x09).chr(0x25).chr(0x62).chr(0x41).chr(0x01).chr(0x01).chr(0x0b).chr(0x23).chr(0x62).chr(0x00).chr(0x49).chr(0x25).chr(0x62).chr(0x68).chr(0x4c);
$lc1sector39 = chr(0x09).chr(0x27).chr(0x55).chr(0x41).chr(0x01).chr(0x01).chr(0x0d).chr(0x25).chr(0x55).chr(0x00).chr(0x29).chr(0x27).chr(0x55).chr(0xae).chr(0x41);
$lc1sector40 = chr(0x09).chr(0x27).chr(0x60).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x25).chr(0x61).chr(0x00).chr(0x09).chr(0x27).chr(0xe0).chr(0xe7).chr(0x0e);
$lc1sector41 = chr(0x09).chr(0x28).chr(0x71).chr(0x41).chr(0x01).chr(0x01).chr(0x08).chr(0x26).chr(0x71).chr(0x00).chr(0x89).chr(0x28).chr(0x71).chr(0x95).chr(0xcb);
$lc1sector42 = chr(0x09).chr(0x29).chr(0x01).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x27).chr(0x21).chr(0x00).chr(0x09).chr(0x29).chr(0x05).chr(0x80).chr(0x4b);
$lc1sector43 = chr(0x09).chr(0x30).chr(0x63).chr(0x41).chr(0x01).chr(0x01).chr(0x0b).chr(0x28).chr(0x63).chr(0x00).chr(0x49).chr(0x30).chr(0x63).chr(0xed).chr(0x18);
$lc1sector44 = chr(0x09).chr(0x30).chr(0x68).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x29).chr(0x68).chr(0x00).chr(0x09).chr(0xb0).chr(0x68).chr(0xb0).chr(0x8c);
$lc1sector45 = chr(0x09).chr(0x33).chr(0x37).chr(0x41).chr(0x01).chr(0x01).chr(0x29).chr(0x31).chr(0x37).chr(0x00).chr(0x0d).chr(0x33).chr(0x37).chr(0x6c).chr(0x68);
$lc1sector46 = chr(0x09).chr(0x33).chr(0x42).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x31).chr(0x4a).chr(0x00).chr(0x09).chr(0x33).chr(0x52).chr(0x7c).chr(0x8b);
$lc1sector47 = chr(0x09).chr(0x35).chr(0x52).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x73).chr(0x52).chr(0x00).chr(0x09).chr(0x37).chr(0x52).chr(0x4b).chr(0x06);
$lc1sector48 = chr(0x09).chr(0x35).chr(0x57).chr(0x41).chr(0x01).chr(0x01).chr(0x19).chr(0x33).chr(0x57).chr(0x00).chr(0x01).chr(0x35).chr(0x57).chr(0x38).chr(0xf4);
$lc1sector49 = chr(0x09).chr(0x37).chr(0x14).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x35).chr(0x04).chr(0x00).chr(0x09).chr(0x37).chr(0x1c).chr(0x54).chr(0x6a);
$lc1sector50 = chr(0x09).chr(0x37).chr(0x19).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x31).chr(0x19).chr(0x00).chr(0x09).chr(0x17).chr(0x19).chr(0xa4).chr(0xbd);
$lc1sector51 = chr(0x09).chr(0x38).chr(0x04).chr(0x41).chr(0x01).chr(0x01).chr(0x01).chr(0x36).chr(0x04).chr(0x00).chr(0x19).chr(0x38).chr(0x04).chr(0x9c).chr(0xdf);
$lc1sector52 = chr(0x09).chr(0x38).chr(0x09).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x36).chr(0x0b).chr(0x00).chr(0x09).chr(0x38).chr(0x49).chr(0x6c).chr(0x08);
$lc1sector53 = chr(0x09).chr(0x38).chr(0x58).chr(0x41).chr(0x01).chr(0x01).chr(0x49).chr(0x36).chr(0x58).chr(0x00).chr(0x0b).chr(0x38).chr(0x58).chr(0x99).chr(0xbf);
$lc1sector54 = chr(0x09).chr(0x38).chr(0x63).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x36).chr(0x73).chr(0x00).chr(0x09).chr(0x38).chr(0x6b).chr(0xfe).chr(0x96);
$lc1sector55 = chr(0x09).chr(0x41).chr(0x59).chr(0x41).chr(0x01).chr(0x01).chr(0x0b).chr(0x39).chr(0x59).chr(0x00).chr(0x49).chr(0x41).chr(0x59).chr(0x54).chr(0x0d);
$lc1sector56 = chr(0x09).chr(0x41).chr(0x64).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x39).chr(0x24).chr(0x00).chr(0x09).chr(0x41).chr(0x66).chr(0x9e).chr(0x67);
$lc1sector57 = chr(0x09).chr(0x46).chr(0x13).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x44).chr(0x1b).chr(0x00).chr(0x09).chr(0x46).chr(0x03).chr(0x78).chr(0x0d);
$lc1sector58 = chr(0x09).chr(0x46).chr(0x18).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x46).chr(0x18).chr(0x00).chr(0x09).chr(0x06).chr(0x18).chr(0x25).chr(0x99);
$lc1sector59 = chr(0x09).chr(0x47).chr(0x29).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x45).chr(0x2b).chr(0x00).chr(0x09).chr(0x47).chr(0x69).chr(0xd3).chr(0xc5);
$lc1sector60 = chr(0x09).chr(0x47).chr(0x34).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x05).chr(0x34).chr(0x00).chr(0x09).chr(0x45).chr(0x34).chr(0x35).chr(0x79);
$lc1sector61 = chr(0x09).chr(0x48).chr(0x59).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x44).chr(0x59).chr(0x00).chr(0x09).chr(0x08).chr(0x59).chr(0x6e).chr(0x0a);
$lc1sector62 = chr(0x09).chr(0x48).chr(0x64).chr(0x41).chr(0x01).chr(0x01).chr(0x49).chr(0x46).chr(0x64).chr(0x00).chr(0x0b).chr(0x48).chr(0x64).chr(0xa4).chr(0x60);
$lc1sector63 = chr(0x09).chr(0x50).chr(0x62).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x08).chr(0x62).chr(0x00).chr(0x09).chr(0x52).chr(0x62).chr(0x03).chr(0x5a);
$lc1sector64 = chr(0x09).chr(0x50).chr(0x67).chr(0x41).chr(0x01).chr(0x01).chr(0x19).chr(0x48).chr(0x67).chr(0x00).chr(0x01).chr(0x50).chr(0x67).chr(0x70).chr(0xa8);

$lc2sector1  = chr(0x03).chr(0x08).chr(0x05).chr(0x41).chr(0x01).chr(0x01).chr(0x23).chr(0x06).chr(0x05).chr(0x00).chr(0x03).chr(0x08).chr(0x01).chr(0x96).chr(0xca);
$lc2sector2  = chr(0x03).chr(0x08).chr(0x10).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x06).chr(0x11).chr(0x00).chr(0x03).chr(0x88).chr(0x10).chr(0x6c).chr(0xe8);
$lc2sector3  = chr(0x03).chr(0x09).chr(0x56).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x07).chr(0x56).chr(0x00).chr(0x03).chr(0x09).chr(0x56).chr(0x5f).chr(0x5f);
$lc2sector4  = chr(0x03).chr(0x09).chr(0x61).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x07).chr(0x65).chr(0x00).chr(0x03).chr(0x29).chr(0x61).chr(0xfd).chr(0x31);
$lc2sector5  = chr(0x03).chr(0x13).chr(0x10).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x11).chr(0x12).chr(0x00).chr(0x03).chr(0x53).chr(0x10).chr(0x99).chr(0x22);
$lc2sector6  = chr(0x03).chr(0x13).chr(0x15).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x01).chr(0x15).chr(0x00).chr(0x0b).chr(0x13).chr(0x15).chr(0x10).chr(0xba);
$lc2sector7  = chr(0x03).chr(0x14).chr(0x29).chr(0x41).chr(0x01).chr(0x01).chr(0x0b).chr(0x12).chr(0x29).chr(0x00).chr(0x03).chr(0x14).chr(0x39).chr(0x05).chr(0x6e);
$lc2sector8  = chr(0x03).chr(0x14).chr(0x34).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x12).chr(0x74).chr(0x00).chr(0x03).chr(0x16).chr(0x34).chr(0x15).chr(0x44);
$lc2sector9  = chr(0x03).chr(0x15).chr(0x24).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x33).chr(0x24).chr(0x00).chr(0x07).chr(0x15).chr(0x24).chr(0xeb).chr(0x7c);
$lc2sector10 = chr(0x03).chr(0x15).chr(0x29).chr(0x41).chr(0x01).chr(0x01).chr(0x02).chr(0x13).chr(0x29).chr(0x00).chr(0x03).chr(0x15).chr(0xa9).chr(0xdb).chr(0x8a);
$lc2sector11 = chr(0x03).chr(0x18).chr(0x49).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x16).chr(0x41).chr(0x00).chr(0x03).chr(0x08).chr(0x49).chr(0xb3).chr(0x1a);
$lc2sector12 = chr(0x03).chr(0x18).chr(0x54).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x56).chr(0x54).chr(0x00).chr(0x01).chr(0x18).chr(0x54).chr(0x50).chr(0x88);
$lc2sector13 = chr(0x03).chr(0x20).chr(0x56).chr(0x41).chr(0x01).chr(0x01).chr(0x43).chr(0x18).chr(0x56).chr(0x00).chr(0x03).chr(0x20).chr(0x54).chr(0xc1).chr(0x08);
$lc2sector14 = chr(0x03).chr(0x20).chr(0x61).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x18).chr(0x63).chr(0x00).chr(0x03).chr(0x60).chr(0x61).chr(0x58).chr(0x67);
$lc2sector15 = chr(0x03).chr(0x21).chr(0x55).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x19).chr(0x45).chr(0x00).chr(0x03).chr(0x29).chr(0x55).chr(0x19).chr(0x75);
$lc2sector16 = chr(0x03).chr(0x21).chr(0x60).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x19).chr(0x60).chr(0x00).chr(0x03).chr(0x21).chr(0x60).chr(0xdd).chr(0xc9);
$lc2sector17 = chr(0x03).chr(0x23).chr(0x17).chr(0x41).chr(0x01).chr(0x01).chr(0x13).chr(0x21).chr(0x17).chr(0x00).chr(0x03).chr(0x23).chr(0x1f).chr(0x5b).chr(0x34);
$lc2sector18 = chr(0x03).chr(0x23).chr(0x22).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x21).chr(0x22).chr(0x00).chr(0x03).chr(0x23).chr(0x22).chr(0xa4).chr(0x08);
$lc2sector19 = chr(0x03).chr(0x24).chr(0x12).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x62).chr(0x12).chr(0x00).chr(0x01).chr(0x24).chr(0x12).chr(0xcd).chr(0xb2);
$lc2sector20 = chr(0x03).chr(0x24).chr(0x17).chr(0x41).chr(0x01).chr(0x01).chr(0x01).chr(0x22).chr(0x17).chr(0x00).chr(0x03).chr(0x24).chr(0x57).chr(0x92).chr(0x17);
$lc2sector21 = chr(0x03).chr(0x25).chr(0x03).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x33).chr(0x03).chr(0x00).chr(0x0b).chr(0x25).chr(0x03).chr(0xa0).chr(0x6d);
$lc2sector22 = chr(0x03).chr(0x25).chr(0x08).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x23).chr(0x08).chr(0x00).chr(0x03).chr(0x25).chr(0x08).chr(0x4e).chr(0xdc);
$lc2sector23 = chr(0x03).chr(0x28).chr(0x28).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00); //
$lc2sector24 = chr(0x03).chr(0x28).chr(0x33).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00); //
$lc2sector25 = chr(0x03).chr(0x32).chr(0x19).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x30).chr(0x19).chr(0x00).chr(0x03).chr(0x32).chr(0x19).chr(0xac).chr(0x47);
$lc2sector26 = chr(0x03).chr(0x32).chr(0x24).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x34).chr(0x24).chr(0x00).chr(0x23).chr(0x32).chr(0x24).chr(0xe6).chr(0x4a);
$lc2sector27 = chr(0x03).chr(0x33).chr(0x56).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x31).chr(0x56).chr(0x00).chr(0x03).chr(0x33).chr(0x56).chr(0x17).chr(0x6c);
$lc2sector28 = chr(0x03).chr(0x33).chr(0x61).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x31).chr(0x65).chr(0x00).chr(0x03).chr(0x13).chr(0x61).chr(0xb5).chr(0x02);
$lc2sector29 = chr(0x03).chr(0x34).chr(0x51).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x30).chr(0x51).chr(0x00).chr(0x43).chr(0x34).chr(0x51).chr(0xc1).chr(0xc5);
$lc2sector30 = chr(0x03).chr(0x34).chr(0x56).chr(0x41).chr(0x01).chr(0x01).chr(0x13).chr(0x32).chr(0x56).chr(0x00).chr(0x03).chr(0x34).chr(0x5e).chr(0xf6).chr(0x68);
$lc2sector31 = chr(0x03).chr(0x35).chr(0x42).chr(0x41).chr(0x01).chr(0x01).chr(0x03).chr(0x33).chr(0x42).chr(0x00).chr(0x03).chr(0x35).chr(0x42).chr(0xe9).chr(0x63);
$lc2sector32 = chr(0x03).chr(0x35).chr(0x47).chr(0x41).chr(0x01).chr(0x01).chr(0x07).chr(0x33).chr(0x47).chr(0x00).chr(0x03).chr(0x35).chr(0x67).chr(0x7f).chr(0x35);
$lc2sector33 = chr(0x09).chr(0x20).chr(0x45).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x1a).chr(0x45).chr(0x00).chr(0x49).chr(0x20).chr(0x45).chr(0x56).chr(0x1e);
$lc2sector34 = chr(0x09).chr(0x20).chr(0x50).chr(0x41).chr(0x01).chr(0x01).chr(0x19).chr(0x18).chr(0x50).chr(0x00).chr(0x09).chr(0x20).chr(0x58).chr(0x13).chr(0x19);
$lc2sector35 = chr(0x09).chr(0x22).chr(0x16).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x20).chr(0x17).chr(0x00).chr(0x09).chr(0xa2).chr(0x16).chr(0xa4).chr(0xe0);
$lc2sector36 = chr(0x09).chr(0x22).chr(0x21).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x28).chr(0x21).chr(0x00).chr(0x19).chr(0x22).chr(0x21).chr(0x76).chr(0x86);
$lc2sector37 = chr(0x09).chr(0x25).chr(0x57).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x63).chr(0x57).chr(0x00).chr(0x0b).chr(0x25).chr(0x57).chr(0xa5).chr(0xf2);
$lc2sector38 = chr(0x09).chr(0x25).chr(0x62).chr(0x41).chr(0x01).chr(0x01).chr(0x0b).chr(0x23).chr(0x62).chr(0x00).chr(0x09).chr(0x25).chr(0x22).chr(0xc0).chr(0xea);
$lc2sector39 = chr(0x09).chr(0x27).chr(0x55).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x25).chr(0x51).chr(0x00).chr(0x09).chr(0x07).chr(0x55).chr(0xa1).chr(0x20);
$lc2sector40 = chr(0x09).chr(0x27).chr(0x60).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x05).chr(0x60).chr(0x00).chr(0x0d).chr(0x27).chr(0x60).chr(0x8e).chr(0x47);
$lc2sector41 = chr(0x09).chr(0x28).chr(0x71).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x26).chr(0x71).chr(0x00).chr(0x09).chr(0x28).chr(0x71).chr(0x15).chr(0x4a);
$lc2sector42 = chr(0x09).chr(0x29).chr(0x01).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x27).chr(0x05).chr(0x00).chr(0x09).chr(0x09).chr(0x01).chr(0x8f).chr(0x2a);
$lc2sector43 = chr(0x09).chr(0x30).chr(0x63).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x2a).chr(0x63).chr(0x00).chr(0x49).chr(0x30).chr(0x63).chr(0xfb).chr(0x74);
$lc2sector44 = chr(0x09).chr(0x30).chr(0x68).chr(0x41).chr(0x01).chr(0x01).chr(0x19).chr(0x28).chr(0x68).chr(0x00).chr(0x09).chr(0x30).chr(0x60).chr(0x86).chr(0x7e);
$lc2sector45 = chr(0x09).chr(0x33).chr(0x37).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x31).chr(0x33).chr(0x00).chr(0x09).chr(0x13).chr(0x37).chr(0x63).chr(0x09);
$lc2sector46 = chr(0x09).chr(0x33).chr(0x42).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x11).chr(0x42).chr(0x00).chr(0x0d).chr(0x33).chr(0x42).chr(0x15).chr(0xc2);
$lc2sector47 = chr(0x09).chr(0x35).chr(0x52).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x33).chr(0x72).chr(0x00).chr(0x09).chr(0x31).chr(0x52).chr(0x0f).chr(0xf7);
$lc2sector48 = chr(0x09).chr(0x35).chr(0x57).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x32).chr(0x57).chr(0x00).chr(0x89).chr(0x35).chr(0x57).chr(0xc6).chr(0x8f);
$lc2sector49 = chr(0x09).chr(0x37).chr(0x14).chr(0x41).chr(0x01).chr(0x01).chr(0x29).chr(0x35).chr(0x14).chr(0x00).chr(0x09).chr(0x37).chr(0x10).chr(0xfa).chr(0x99);
$lc2sector50 = chr(0x09).chr(0x37).chr(0x19).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x35).chr(0x18).chr(0x00).chr(0x09).chr(0xb7).chr(0x19).chr(0x95).chr(0xf5);
$lc2sector51 = chr(0x09).chr(0x38).chr(0x04).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x36).chr(0x14).chr(0x00).chr(0x09).chr(0x30).chr(0x04).chr(0x91).chr(0xad);
$lc2sector52 = chr(0x09).chr(0x38).chr(0x09).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x36).chr(0x09).chr(0x00).chr(0x09).chr(0x38).chr(0x09).chr(0xec).chr(0x89);
$lc2sector53 = chr(0x09).chr(0x38).chr(0x58).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x36).chr(0x18).chr(0x00).chr(0x09).chr(0x3a).chr(0x58).chr(0x6e).chr(0x34);
$lc2sector54 = chr(0x09).chr(0x38).chr(0x63).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x34).chr(0x63).chr(0x00).chr(0x49).chr(0x38).chr(0x63).chr(0xe8).chr(0xfa);
$lc2sector55 = chr(0x09).chr(0x41).chr(0x59).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00); //
$lc2sector56 = chr(0x09).chr(0x41).chr(0x64).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00).chr(0x00); //
$lc2sector57 = chr(0x09).chr(0x46).chr(0x13).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x44).chr(0x1b).chr(0x00).chr(0x09).chr(0x56).chr(0x13).chr(0xf9).chr(0xd2);
$lc2sector58 = chr(0x09).chr(0x46).chr(0x18).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x04).chr(0x18).chr(0x00).chr(0x0b).chr(0x46).chr(0x18).chr(0xa1).chr(0x68);
$lc2sector59 = chr(0x09).chr(0x47).chr(0x29).chr(0x41).chr(0x01).chr(0x01).chr(0x08).chr(0x45).chr(0x29).chr(0x00).chr(0x09).chr(0x47).chr(0xa9).chr(0x7a).chr(0xad);
$lc2sector60 = chr(0x09).chr(0x47).chr(0x34).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x45).chr(0x3c).chr(0x00).chr(0x09).chr(0x57).chr(0x34).chr(0xb4).chr(0xa6);
$lc2sector61 = chr(0x09).chr(0x48).chr(0x59).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x46).chr(0x59).chr(0x00).chr(0x09).chr(0x48).chr(0x59).chr(0xee).chr(0x8b);
$lc2sector62 = chr(0x09).chr(0x48).chr(0x64).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x46).chr(0x60).chr(0x00).chr(0x09).chr(0x68).chr(0x64).chr(0xab).chr(0x01);
$lc2sector63 = chr(0x09).chr(0x50).chr(0x62).chr(0x41).chr(0x01).chr(0x01).chr(0x49).chr(0x48).chr(0x62).chr(0x00).chr(0x09).chr(0x50).chr(0x60).chr(0x7e).chr(0x75);
$lc2sector64 = chr(0x09).chr(0x50).chr(0x67).chr(0x41).chr(0x01).chr(0x01).chr(0x09).chr(0x48).chr(0x65).chr(0x00).chr(0x09).chr(0x10).chr(0x67).chr(0xb9).chr(0x66);

if ($disc['d_media'] == 1 && $disc['d_libcrypt'] != '') {
	echo '<h3>Sectors with LibCrypt protection</h3><table class="sectors" cellspacing="0"><tr><th>Sector</th><th>MSF</th><th>Contents</th><th>XOR</th><th>Comments</th></tr>';
	$array = str_split($disc['d_libcrypt']);
	$sectors = count($array) / 15;
	for ($i = 0; $i < $sectors; $i++) {
		$sector = (btoi(ord($array[$i * 15])) * 60 * 75) + (btoi(ord($array[$i * 15 + 1])) * 75) + (btoi(ord($array[$i * 15 + 2])));
		$array2 = qsector($sector);
		echo '<tr><td>'.$sector.'</td><td>'.sprintf('%02x', ord($array[$i * 15])).':'.sprintf('%02x', ord($array[$i * 15 + 1])).':'.sprintf('%02x', ord($array[$i * 15 + 2])).'</td><td>';
		if (ord($array[$i * 15 + 3]) == $array2[0])
			echo sprintf('%02x', ord($array[$i * 15 + 3])).' ';
		else
			echo '<span style="color: #ff0000;">'.sprintf('%02x', ord($array[$i * 15 + 3])).'</span> ';
		if (ord($array[$i * 15 + 4]) == $array2[1])
			echo sprintf('%02x', ord($array[$i * 15 + 4])).' ';
		else
			echo '<span style="color: #ff0000;">'.sprintf('%02x', ord($array[$i * 15 + 4])).'</span> ';
		if (ord($array[$i * 15 + 5]) == $array2[2])
			echo sprintf('%02x', ord($array[$i * 15 + 5])).' ';
		else
			echo '<span style="color: #ff0000;">'.sprintf('%02x', ord($array[$i * 15 + 5])).'</span> ';
		if (ord($array[$i * 15 + 6]) == $array2[3])
			echo sprintf('%02x', ord($array[$i * 15 + 6])).' ';
		else
			echo '<span style="color: #ff0000;">'.sprintf('%02x', ord($array[$i * 15 + 6])).'</span> ';
		if (ord($array[$i * 15 + 7]) == $array2[4])
			echo sprintf('%02x', ord($array[$i * 15 + 7])).' ';
		else
			echo '<span style="color: #ff0000;">'.sprintf('%02x', ord($array[$i * 15 + 7])).'</span> ';
		if (ord($array[$i * 15 + 8]) == $array2[5])
			echo sprintf('%02x', ord($array[$i * 15 + 8])).' ';
		else
			echo '<span style="color: #ff0000;">'.sprintf('%02x', ord($array[$i * 15 + 8])).'</span> ';
		if (ord($array[$i * 15 + 9]) == $array2[6])
			echo sprintf('%02x', ord($array[$i * 15 + 9])).' ';
		else
			echo '<span style="color: #ff0000;">'.sprintf('%02x', ord($array[$i * 15 + 9])).'</span> ';
		if (ord($array[$i * 15 + 10]) == $array2[7])
			echo sprintf('%02x', ord($array[$i * 15 + 10])).' ';
		else
			echo '<span style="color: #ff0000;">'.sprintf('%02x', ord($array[$i * 15 + 10])).'</span> ';
		if (ord($array[$i * 15 + 11]) == $array2[8])
			echo sprintf('%02x', ord($array[$i * 15 + 11])).' ';
		else
			echo '<span style="color: #ff0000;">'.sprintf('%02x', ord($array[$i * 15 + 11])).'</span> ';
		if (ord($array[$i * 15 + 12]) == $array2[9])
			echo sprintf('%02x', ord($array[$i * 15 + 12])).' ';
		else
			echo '<span style="color: #ff0000;">'.sprintf('%02x', ord($array[$i * 15 + 12])).'</span> ';
		if (ord($array[$i * 15 + 13]) == $array2[10])
			echo sprintf('%02x', ord($array[$i * 15 + 13])).' ';
		else
			echo '<span style="color: #ff0000;">'.sprintf('%02x', ord($array[$i * 15 + 13])).'</span> ';
		if (ord($array[$i * 15 + 14]) == $array2[11])
			echo sprintf('%02x', ord($array[$i * 15 + 14])).' ';
		else
			echo '<span style="color: #ff0000;">'.sprintf('%02x', ord($array[$i * 15 + 14])).'</span> ';

		$crc1 = ord($array[$i * 15 + 13]) * 0x100 + ord($array[$i * 15 + 14]); // crc from image
		$crc2 = crc16($array2, 10);                                            // generated crc from non-protected sector
		$crc3 = crc16(array_slice($array, $i * 15 + 3), 10);                   // generated crc from protected sector

		$xor1 = $crc1 ^ $crc2;
		$xor2 = $crc1 ^ $crc3;

		echo '</td><td>'.sprintf('%04x %04x', $xor1, $xor2).'</td><td>';
		switch ($xor1) {
			case 0:
				switch ($xor2) {
					case 0:
						echo 'Original';
						break;
					case 0x0080:
					case 0x8001:
						echo '?';
						break;
					default:
						echo 'Data was dumped with errors';
				}
				break;
			case 0x0080:
				switch ($xor2) {
					case 0x0080:
						echo 'LC2 sector, no errors in dumped data &amp; CRC-16, changes in CRC-16';
						break;
					case 0:
					case 0x8001:
					default:
						echo '?';
				}
				break;
			case 0x8001:
				switch ($xor2) {
					case 0:
					case 0x0080:
					case 0x8001:
						echo '?';
					default:
						for ($a = 1; $a <= 64; $a++) {
							if (bufcmp(${'lc1sector'.$a}, $array, 0, $i * 15, 15)) {
								$a = 0;
								echo 'LC1 sector, no errors in data &amp; CRC-16';
								break;
							}
						}
						if ($a != 0)
							echo 'LC1 sector, errors in data, no errors in CRC-16';
				}
				break;
			default:
				switch ($xor2) {
					case 0:
						echo 'Data was generated with errors';
						break;
					case 0x0080:
						for ($a = 1; $a <= 64; $a++) {
							if (bufcmp(${'lc2sector'.$a}, $array, 0, $i * 15, 12)) {
								$a = 0;
								echo 'LC2 sector, no errors in dumped data &amp; CRC-16, changes in data &amp; CRC-16';
								break;
							}
						}
						if ($a != 0)
							echo 'LC2 sector - unknown contents';
						break;
					case 0x8001:
						echo '?';
						break;
					default:
						if ($xor1 == $xor2)
							echo 'CRC-16 was dumped with errors';
						else if ($xor1 != $xor2)
							echo 'Data and CRC-16 were dumped with errors';
				}
		}

		echo '</td></tr>';
	}
	echo '
<tr><th colspan="5"><b>Total: '.$sectors.' sectors</b></th></tr>
</table>';
}

/// Title
$psxdb['title'] = get_disc_title($disc);

display();

?>
