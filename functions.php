<?php

if (!defined('PSXDB')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

function __autoload($class_name) {
    require_once './classes/'.$class_name.'.php';
}

function sql_connect() {
	global $psxdb_config, $mysqli;
	$mysqli = new mysqli($psxdb_config['db_host'], $psxdb_config['db_username'], $psxdb_config['db_password'], $psxdb_config['db_name']);
	if ($mysqli->connect_errno) {
		error("Can't connect to MySQL DB! This problem will be fixed soon, stay tuned!");
	}
	$mysqli->query("SET NAMES 'utf8'");
}

function authenticate_user($user, $password, $password_is_hash = false) {
	global $mysqli, $psxdb_user;

	// Check if there's a user matching $user and $password
	$query = 'SELECT u.*, g.*, o.logged, o.idle, o.csrf_token, o.prev_url FROM users AS u INNER JOIN groups AS g ON g.g_id=u.group_id LEFT JOIN online AS o ON o.user_id=u.id WHERE ';

	// Are we looking for a user ID or a username?
	$query .= is_int($user) ? 'u.id='.intval($user) : 'u.username=\''.$mysqli->real_escape_string($user).'\'';

	$result = $mysqli->query($query);
	$psxdb_user = $result->fetch_assoc();

	if (!isset($psxdb_user['id']) ||
		($password_is_hash && $password != $psxdb_user['password']) ||
		(!$password_is_hash && forum_hash($password, $psxdb_user['salt']) != $psxdb_user['password']))
		set_default_user();
}

function forum_hash($str, $salt) {
	return sha1($salt.sha1($str));
}

function forum_setcookie($name, $value, $expire) {
	// Enable sending of a P3P header
	header('P3P: CP="CUR ADM"');

	if (version_compare(PHP_VERSION, '5.2.0', '>='))
		setcookie($name, $value, $expire, '/', '.'.$_SERVER['HTTP_HOST'], false, true);
	else
		setcookie($name, $value, $expire, '/'.'; HttpOnly', '.'.$_SERVER['HTTP_HOST'], false);
}

function random_key($len, $readable = false, $hash = false) {
	$key = '';

	if ($hash)
		$key = substr(sha1(uniqid(rand(), true)), 0, $len);
	else if ($readable)
	{
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

		for ($i = 0; $i < $len; ++$i)
			$key .= substr($chars, (mt_rand() % strlen($chars)), 1);
	}
	else
		for ($i = 0; $i < $len; ++$i)
			$key .= chr(mt_rand(33, 126));

	return $key;
}

function cookie_login() {
	global $psxdb_config, $psxdb_user, $mysqli;

	$now = time();
	$expire = $now + 1209600;	// The cookie expires after 14 days

	// We assume it's a guest
	$cookie = array('user_id' => 1, 'password_hash' => 'Guest', 'expiration_time' => 0, 'expire_hash' => 'Guest');

	// If a cookie is set, we get the user_id and password hash from it
	if (!empty($_COOKIE[$psxdb_config['cookie_name']])) {
		$cookie_data = explode('|', base64_decode($_COOKIE[$psxdb_config['cookie_name']]));

		if (!empty($cookie_data) && count($cookie_data) == 4)
			list($cookie['user_id'], $cookie['password_hash'], $cookie['expiration_time'], $cookie['expire_hash']) = $cookie_data;
	}

	// If this a cookie for a logged in user and it shouldn't have already expired
	if (intval($cookie['user_id']) > 1 && intval($cookie['expiration_time']) > $now) {
		authenticate_user(intval($cookie['user_id']), $cookie['password_hash'], true);

		// We now validate the cookie hash
		if ($cookie['expire_hash'] !== sha1($psxdb_user['salt'].$psxdb_user['password'].forum_hash(intval($cookie['expiration_time']), $psxdb_user['salt'])))
			set_default_user();

		// If we got back the default user, the login failed
		if ($psxdb_user['id'] == '1') {
			forum_setcookie($psxdb_config['cookie_name'], base64_encode('1|'.random_key(8, false, true).'|'.$expire.'|'.random_key(8, false, true)), $expire);
			return;
		}

		// Send a new, updated cookie with a new expiration timestamp
		$expire = (intval($cookie['expiration_time']) > $now + $psxdb_config['timeout_visit']) ? $now + 1209600 : $now + $psxdb_config['timeout_visit'];
		forum_setcookie($psxdb_config['cookie_name'], base64_encode($psxdb_user['id'].'|'.$psxdb_user['password'].'|'.$expire.'|'.sha1($psxdb_user['salt'].$psxdb_user['password'].forum_hash($expire, $psxdb_user['salt']))), $expire);

		// Update the online list
		if (!$psxdb_user['logged']) {
			$psxdb_user['logged'] = $now;
			$psxdb_user['csrf_token'] = random_key(40, false, true);
			$mysqli->query('REPLACE INTO online (user_id, ident, logged, csrf_token) VALUES ('.$psxdb_user['id'].', \''.$mysqli->real_escape_string($psxdb_user['username']).'\', '.$psxdb_user['logged'].', \''.$psxdb_user['csrf_token'].'\')');
		} else {
			// Special case: We've timed out, but no other user has browsed the forums since we timed out
			if ($psxdb_user['logged'] < ($now-$psxdb_config['timeout_visit'])) {
				$mysqli->query('UPDATE users SET last_visit='.$psxdb_user['logged'].' WHERE id='.$psxdb_user['id']);
				$psxdb_user['last_visit'] = $psxdb_user['logged'];
			}

			if ($psxdb_user['idle'] == '1')
				$query['SET'] .= ', idle=0';

			$mysqli->query('UPDATE online SET logged='.$now.$query['SET'].' WHERE user_id='.$psxdb_user['id']);
		}

		define('LOGGED', true);
		switch ($psxdb_user['group_id']) {
			case 1:
				define('ADMIN', true);
				break;
			case 3:
				define('USER', true);
				break;
			case 4:
				define('MODERATOR', true);
				break;
			case 5:
				define('DUMPER', true);
				break;
		}
	}
	else {
		set_default_user();
	}
}

function set_default_user() {
	global $mysqli, $psxdb_user, $psxdb_config;
	define('GUEST', true);
	// Fetch guest user
	$result = $mysqli->query('SELECT u.*, g.*, o.logged, o.csrf_token, o.prev_url, o.last_post, o.last_search FROM users AS u INNER JOIN groups AS g ON g.g_id=u.group_id LEFT JOIN online AS o ON o.ident=\''.$mysqli->real_escape_string($_SERVER['REMOTE_ADDR']).'\' WHERE u.id=1');
	if (!$result->num_rows) {
		exit('Unable to fetch guest information. The table \'users\' must contain an entry with id = 1 that represents anonymous users.');
	}
	$psxdb_user = $result->fetch_assoc();
	// Update online list
	if (!$psxdb_user['logged']) {
		$psxdb_user['logged'] = time();
		$psxdb_user['csrf_token'] = random_key(40, false, true);
		if (isset($_GET['module']) && $_GET['module'] == 'feeds' && isset($_GET['feed']) && $_GET['feed'] != '') {
			$mysqli->query('REPLACE INTO online_feedreaders (ident, logged) VALUES("'.$mysqli->real_escape_string($_SERVER['REMOTE_ADDR']).'", '.time().')') or error('Unable to insert into online feed readers list.');
		} else {
			$mysqli->query('REPLACE INTO online (user_id, ident, logged, csrf_token) VALUES (1, \''.$mysqli->real_escape_string($_SERVER['REMOTE_ADDR']).'\', '.$psxdb_user['logged'].', \''.$psxdb_user['csrf_token'].'\')');
		}
	} else {
		if (isset($_GET['module']) && $_GET['module'] == 'feeds' && isset($_GET['feed']) && $_GET['feed'] != '') {
			$mysqli->query('UPDATE online_feedreaders SET logged='.time().' WHERE ident=\''.$mysqli->real_escape_string($_SERVER['REMOTE_ADDR']).'\'');
		} else {
			$mysqli->query('UPDATE online SET logged='.time().' WHERE ident=\''.$mysqli->real_escape_string($_SERVER['REMOTE_ADDR']).'\'');
		}
	}
	$psxdb_user['timezone'] = $psxdb_config['timezone'];
}

function update_users_online() {
	global $mysqli, $psxdb_config;
	$now = time();
	$result = $mysqli->query('SELECT o.* FROM online AS o WHERE o.logged<'.($now-$psxdb_config['timeout_online']));
	while ($cur_user = $result->fetch_assoc()) {
		// If the entry is a guest, delete it
		if ($cur_user['user_id'] == '1') {
			$mysqli->query('DELETE FROM online WHERE ident=\''.$mysqli->real_escape_string($cur_user['ident']).'\'');
		} else {
			// If the entry is older than "timeout_visit", update last_visit for the user in question, then delete him/her from the online list
			if ($cur_user['logged'] < ($now-$psxdb_config['timeout_visit'])) {
				$mysqli->query('UPDATE users SET last_visit='.$cur_user['logged'].' WHERE id='.$cur_user['user_id']);
				$mysqli->query('DELETE FROM online WHERE user_id='.$cur_user['user_id']);
			} else if ($cur_user['idle'] == '0') {
				$mysqli->query('UPDATE online SET idle=1 WHERE user_id='.$cur_user['user_id']);
			}
		}
	}
	// Remove old feed readers
	$mysqli->query('DELETE FROM online_feedreaders WHERE logged<'.($now-$psxdb_config['timeout_online_feedreaders'])) or error('Unable to delete from online feed readers list.');
}

function show_users_online() {
	global $mysqli;
	$result = $mysqli->query('SELECT COUNT(*) FROM online_feedreaders') or error('Unable to fetch online feed readers list.');
	$online_feedreaders = $result->fetch_array();
	$num_feedreaders = $online_feedreaders[0];
	
	// Fetch users online info and generate strings for output
	$num_guests = 0;
	$users = array();
	$result = $mysqli->query('SELECT user_id, id, ident, group_id FROM online,users WHERE idle=0 AND user_id=users.id ORDER BY ident') or error('Unable to fetch online list.');

	while ($user_online = $result->fetch_assoc()) {
		if ($user_online['user_id'] > 1)
			$users[] = '<a href="http://forum.'.$_SERVER['HTTP_HOST'].'/profile.php?id='.$user_online['user_id'].'">'.htmlspecialchars($user_online['ident']).'</a>';
		else {
			$num_guests++;
		}
	}

	$num_users = count($users);
	$showdata = 'Registered users online: <b>'.$num_users.'</b> &bull; Guests online: <b>'.$num_guests.'</b> &bull; Feed readers online: <b>'.$num_feedreaders.'</b><br />';
	if ($num_users > 0) {
		$showdata .= '<b>Online</b>: '.implode(', ', $users);
	}
	return $showdata;
}

function confirm_referrer($script) {
	if (!preg_match('#^'.preg_quote('http://'.$_SERVER['HTTP_HOST'].'/'.$script, '#').'#i', (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''))) {
		error('Bad referrer');
	}
}

function redirect($redirect) {
	header("Location: ".$redirect);
	exit();
}

function utf2lat($string) {
	$array = array (
		'"' => '',
		'*' => '-',
		':' => ' -',
		'/' => '-',
		'?' => '',
		'°' => '',
		'Ä' => 'A',
		'ä' => 'a',
		'É' => 'E',
		'é' => 'e',
		'ё' => 'e',
		'Böse' => 'Boese',
		'Ö' => 'O',
		'ö' => 'o',
		'Ñ' => 'N',
		'ñ' => 'n',
		'³' => ' 3',
		'α' => 'Alpha'
	);
	return strtr($string, $array);
}

function data2string($data) {
	$array = str_split($data);
	$length = count($array) / 15;
	for ($i = 0; $i < $length; $i++)
		$string .= sprintf('MSF: %02x:%02x:%02x Q-Data: %02x%02x%02x %02x:%02x:%02x %02x %02x:%02x:%02x %02x%02x'."\n", ord($array[$i * 15]), ord($array[$i * 15 + 1]), ord($array[$i * 15 + 2]), ord($array[$i * 15 + 3]), ord($array[$i * 15 + 4]), ord($array[$i * 15 + 5]), ord($array[$i * 15 + 6]), ord($array[$i * 15 + 7]), ord($array[$i * 15 + 8]), ord($array[$i * 15 + 9]), ord($array[$i * 15 + 10]), ord($array[$i * 15 + 11]), ord($array[$i * 15 + 12]), ord($array[$i * 15 + 13]), ord($array[$i * 15 + 14]));
	return trim($string);
}

function url_string($string) {
	if ($string != '') {
		$string = trim(str_replace(array(' ', '_', '/', ':', '&'), '-', strtolower($string)), '-');
		while (substr_count($string, '--')) $string = str_replace('--', '-', $string);
	}
	return $string;
}

function qsector($sector2) {
	$sector = $sector2 - 150;
	$array[0] = 0x41;
	$array[1] = 0x01;
	$array[2] = 0x01;
	$array[6] = 0x00;
	
	$min = floor($sector/60/75);
	$sec = floor(($sector - ($min * 60 * 75)) / 75);
	$frame = $sector - ($min * 60 * 75) - ($sec * 75);
	$array[3] = itob($min);
	$array[4] = itob($sec);
	$array[5] = itob($frame);

	$min = floor($sector2/60/75);
	$sec = floor(($sector2 - ($min * 60 * 75)) / 75);
	$frame = $sector2 - ($min * 60 * 75) - ($sec * 75);
	$array[7] = itob($min);
	$array[8] = itob($sec);
	$array[9] = itob($frame);
	
	return $array;
}

function btoi($b) {
	return (floor($b/16)*10) + ($b%16);
}

function itob($i) {
	return (floor($i/10)*16) + ($i%10);
}

function psxdbcode($string) {
	$array = array (
		'[T:NYG]' => '<b>Net Yaroze Games</b>:',
		'[T:TD]' => '<b>Techno Demos</b>:',
		'[T:PD]' => '<b>Playable Demos</b>:',
		'[T:UD]' => '<b>Unplayable Demos</b>:',
		'[T:V]' => '<b>Videos</b>:'
	);
	return strtr($string, $array);
}

function format_datetime($timestamp, $format = 'Y-m-d H:i:s') {
	global $psxdb_user, $psxdb_config;
	return date($format, $timestamp + (($psxdb_user['timezone'] - $psxdb_config['timezone']) * 3600));
}

function display() {
	global $psxdb, $psxdb_user;
	// Contents
	$tpl_main = trim(ob_get_clean());
	// gzip contents
	ob_start('ob_gzhandler');
	if ((isset($_SERVER['HTTP_ACCEPT']) && stristr($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml')) || stristr($_SERVER["HTTP_USER_AGENT"], 'W3C_Validator')) {
		header("Content-type: application/xhtml+xml");
		echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
	} else header("Content-type: text/html; charset=utf-8");
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>redump.org &bull; '.str_replace('<br />', ' &bull; ', $psxdb['title']).'</title>
<meta name="author" content="Redump Team" />
<meta name="description" content="Redump - disc images information" />
<meta name="keywords" content="PSX, PSone, PS1, PS2, Sony, PlayStation, dumping project, serials, chesksums, crc, crc-32, crc32, md5, sha-1, sha1, datfile, datfiles, iso, bin, cue, mdf, ccd, img, sub, Clone, CloneCD, EAC, IsoBuster, emulation, emulators, epsxe, pcsx, clrmamepro, database, goodpsx, SLUS, SCUS, SLES, SCES, SLPS" />
<meta name="verify-v1" content="BE+hBNbeseE7psVMax1S2M7PMKlwT7ohSUgC5hvZy3k=" />
<link rel="alternate" type="application/rss+xml" title="Redump RSS - Recent changes" href="/rss/recentchanges/" />
<link rel="alternate" type="application/rss+xml" title="Redump Forum RSS - Latest posts" href="/rss/forum/" />
<link rel="search" type="application/opensearchdescription+xml" href="/opensearch.xml" title="Redump" />
<link rel="icon" href="/favicon.ico" />
<link rel="shortcut icon" href="/favicon.ico" />
<style type="text/css" media="screen">@import url(\'/styles/default.css\');</style>
<script src="http://www.google-analytics.com/urchin.js" type="text/javascript"></script>
<script type="text/javascript">
<!--//--><![CDATA[//><!--
_uacct = "UA-695695-1";
urchinTracker();
//--><!]]>
</script>
<script type="text/javascript" src="/javascript/ajax.js"></script>';
	echo (count($psxdb['script']) > 0) ? "\r\n".implode("\r\n", $psxdb['script']) : '';
	echo '
<script type="text/javascript">
<!--//--><![CDATA[//><!--
window.onload = function () {
	document.getElementById(\'quicksearch\').onfocus = function () {if (this.value == \'Quick search\') this.value = \'\';}
	document.getElementById(\'quicksearch\').onblur  = function () {if (this.value == \'\') this.value = \'Quick search\';}';
	echo (count($psxdb['onload']) > 0) ? "\r\n\t".implode("\r\n\t", $psxdb['onload']) : '';
	echo '
}
//--><!]]>
</script>
';
	echo (count($psxdb['css']) > 0) ? implode("\r\n", $psxdb['css']) : '';
	echo '<script type="text/javascript" src="/javascript/display.js"></script>
</head>
<body>';
	include_once './header.php';
	echo $tpl_main;
	include_once './footer.php';
echo '</body>
</html>';
	exit;
}

function error($error, $title = '') {
	global $psxdb;
	$psxdb['error'] = true;
	ob_end_clean();
	ob_start();
	echo '<div class="error">'.$error.'</div>';
	$psxdb['title'] = ($title != '') ? $title : 'Error';
	display();
}

function downloadText() {
	global $psxdb;
	header('Content-type: application/x-ms-download; charset=ISO-8859-1');
	header('Content-Disposition: attachment; filename="'.$psxdb['title'].'"');
	exit;
}

$crc16_tab = array(
    0x0000, 0x1021, 0x2042, 0x3063, 0x4084, 0x50a5, 0x60c6, 0x70e7, 
    0x8108, 0x9129, 0xa14a, 0xb16b, 0xc18c, 0xd1ad, 0xe1ce, 0xf1ef, 
    0x1231, 0x0210, 0x3273, 0x2252, 0x52b5, 0x4294, 0x72f7, 0x62d6, 
    0x9339, 0x8318, 0xb37b, 0xa35a, 0xd3bd, 0xc39c, 0xf3ff, 0xe3de, 
    0x2462, 0x3443, 0x0420, 0x1401, 0x64e6, 0x74c7, 0x44a4, 0x5485, 
    0xa56a, 0xb54b, 0x8528, 0x9509, 0xe5ee, 0xf5cf, 0xc5ac, 0xd58d, 
    0x3653, 0x2672, 0x1611, 0x0630, 0x76d7, 0x66f6, 0x5695, 0x46b4, 
    0xb75b, 0xa77a, 0x9719, 0x8738, 0xf7df, 0xe7fe, 0xd79d, 0xc7bc, 
    0x48c4, 0x58e5, 0x6886, 0x78a7, 0x0840, 0x1861, 0x2802, 0x3823, 
    0xc9cc, 0xd9ed, 0xe98e, 0xf9af, 0x8948, 0x9969, 0xa90a, 0xb92b, 
    0x5af5, 0x4ad4, 0x7ab7, 0x6a96, 0x1a71, 0x0a50, 0x3a33, 0x2a12, 
    0xdbfd, 0xcbdc, 0xfbbf, 0xeb9e, 0x9b79, 0x8b58, 0xbb3b, 0xab1a, 
    0x6ca6, 0x7c87, 0x4ce4, 0x5cc5, 0x2c22, 0x3c03, 0x0c60, 0x1c41, 
    0xedae, 0xfd8f, 0xcdec, 0xddcd, 0xad2a, 0xbd0b, 0x8d68, 0x9d49, 
    0x7e97, 0x6eb6, 0x5ed5, 0x4ef4, 0x3e13, 0x2e32, 0x1e51, 0x0e70, 
    0xff9f, 0xefbe, 0xdfdd, 0xcffc, 0xbf1b, 0xaf3a, 0x9f59, 0x8f78, 
    0x9188, 0x81a9, 0xb1ca, 0xa1eb, 0xd10c, 0xc12d, 0xf14e, 0xe16f, 
    0x1080, 0x00a1, 0x30c2, 0x20e3, 0x5004, 0x4025, 0x7046, 0x6067, 
    0x83b9, 0x9398, 0xa3fb, 0xb3da, 0xc33d, 0xd31c, 0xe37f, 0xf35e, 
    0x02b1, 0x1290, 0x22f3, 0x32d2, 0x4235, 0x5214, 0x6277, 0x7256, 
    0xb5ea, 0xa5cb, 0x95a8, 0x8589, 0xf56e, 0xe54f, 0xd52c, 0xc50d, 
    0x34e2, 0x24c3, 0x14a0, 0x0481, 0x7466, 0x6447, 0x5424, 0x4405, 
    0xa7db, 0xb7fa, 0x8799, 0x97b8, 0xe75f, 0xf77e, 0xc71d, 0xd73c, 
    0x26d3, 0x36f2, 0x0691, 0x16b0, 0x6657, 0x7676, 0x4615, 0x5634, 
    0xd94c, 0xc96d, 0xf90e, 0xe92f, 0x99c8, 0x89e9, 0xb98a, 0xa9ab, 
    0x5844, 0x4865, 0x7806, 0x6827, 0x18c0, 0x08e1, 0x3882, 0x28a3, 
    0xcb7d, 0xdb5c, 0xeb3f, 0xfb1e, 0x8bf9, 0x9bd8, 0xabbb, 0xbb9a, 
    0x4a75, 0x5a54, 0x6a37, 0x7a16, 0x0af1, 0x1ad0, 0x2ab3, 0x3a92, 
    0xfd2e, 0xed0f, 0xdd6c, 0xcd4d, 0xbdaa, 0xad8b, 0x9de8, 0x8dc9, 
    0x7c26, 0x6c07, 0x5c64, 0x4c45, 0x3ca2, 0x2c83, 0x1ce0, 0x0cc1, 
    0xef1f, 0xff3e, 0xcf5d, 0xdf7c, 0xaf9b, 0xbfba, 0x8fd9, 0x9ff8, 
    0x6e17, 0x7e36, 0x4e55, 0x5e74, 0x2e93, 0x3eb2, 0x0ed1, 0x1ef0, 
);

function crc16($string, $len) {
	global $crc16_tab;
	$cksum = 0;
	for ($i = 0;  $i < $len;  $i++) {
		if (is_string($string[$i]))
			$string[$i] = ord($string[$i]);
		$cksum = $crc16_tab[(($cksum >> 8) ^ $string[$i]) & 0xFF] ^ ($cksum << 8);
	}
	return (~$cksum) & 0xFFFF;
}

function bufcmp($buffer1, $buffer2, $offset1, $offset2, $len) {
	for ($i = 0; $i < $len; $i++) {
		if ($buffer1[$offset1 + $i] != $buffer2[$offset2 + $i])
			return 0;
	}
	return 1;
}



/*************************/
/* Global game functions */
/*************************/

/************/
/* Contents */
/************/

/*
	0. Offset
	1. Region          View game/disc region image, input data: integer
	2. Status          View disc dumping status image, input data: integer
	3. Languages       View disc language images, input data: array
	3.1. Language      View language image, input data: string
	4. Media           View disc media text, input data: integer
	4.1. Short Media   View disc media short text, input data: integer
	5. Boolean info    View disc boolean info (LC/AMC protection, EDC) text (Y/N/U), input data: integer
	6.1. Track type    View track type text, input data: integer
	6.2. Pregap type   View pregap type text, input data: integer
	7.1 Disc filename  View disc filename text, input data: array
	7.2 Track filename View track filename text, input data: array
	7.3 Filename       Function used by 7.2. and 7.3., input data: array, integer
	8.  Sectors to MSF  Convert sectors to MM:SS:FF, input data: integer
	9.  Title           Moves article to the beginning of title, input data: string
*/

// 0. Write offset size

function write_offset($string) {
	return $string;
	/*if (!isset($string) || $string == '')
		return;
	$array_old = explode(',', str_replace(' ', '', $string));
	foreach ($array_old as $offset) {
		$array_new[] = $offset.' EAC/'.((intval($offset) > 0) ? '+' : '').(intval($offset) + 30);
	}
	return implode(', ', $array_new);*/
}

// 1. Region

function region($region) {
	global $psxdb;
	return '<img src="/images/regions/'.$region.'.png" alt="'.$psxdb['regions'][$region].'" title="'.$psxdb['regions'][$region].'" />';
}

// 2. Status
function status($integer) {
	switch ($integer) {
		case 1: return '<img src="/images/status/grey.png" alt="One source" title="One source" />';
		case 2: return '<img src="/images/status/red.png" alt="Many sources (matrix)" title="Many sources (matrix)" />';
		case 4: return '<img src="/images/status/blue.png" alt="Dumped from original media" title="Dumped from original media" />';
		case 5: return '<img src="/images/status/green.png" alt="2 and more dumps from original media [!]" title="2 and more dumps from original media [!]" />';
	}
}

function statustext($integer) {
	switch ($integer) {
		case 1: return 'One source';
		case 2: return 'Many sources (matrix)';
		case 4: return 'Original media';
		case 5: return '2 and more dumps from original media [!]';
	}
}

// 2.1 Sharing status

function sharing_status($integer) {
	switch ($integer) {
		case 0: return '<img src="/images/status/red.png" alt="" title="Not shared" />';
		case 1: return '<img src="/images/status/green.png" alt="" title="Shared" />';
		case 2: return '<img src="/images/status/black.png" alt="" title="Won\'t be shared" />';
	}
}

function sharing_status_text($integer) {
	switch ($integer) {
		case 0: return 'Not shared';
		case 1: return 'Shared';
		case 2: return 'Won\'t be shared';
	}
}

// 3. Languages
function languages($string) {
	global $psxdb;
	$langs_array = explode(',', $string);
	$gamelanguages = '';
	reset($psxdb['languages']);
	do {
		$language = current($psxdb['languages']);
		if (in_array($language, $langs_array)) $gamelanguages .= language($language);
	} while (next($psxdb['languages']) != NULL);
	return $gamelanguages;
}

// 3.1. Language
function language($language) {
	global $psxdb;
	return '<img src="/images/languages/'.$language.'.ico" alt="'.$psxdb['languages_names'][$language].'" title="'.$psxdb['languages_names'][$language].'" /> ';
}

// 5. Boolean info
function booleaninfo($integer) {
	switch ($integer) {
		case 0: return 'Unknown'; break;
		case 1: return 'No'; break;
		case 2: return 'Yes'; break;
	}
}

// 5.1 LibCrypt protection
function libcrypt($integer) {
	switch ($integer) {
		case 0: return 'Not checked'; break;
		case 1: return 'No'; break;
		case 2: return 'Yes'; break;
		//case 3: return 'No (?) <img src="/images/status/yellow.png" title="Verified?" alt="Verified?" />'; break;
		//case 4: return 'Yes <img src="/images/status/green.png" title="Verified" alt="Verified" />'; break;
		//case 5: return 'No <img src="/images/status/green.png" title="Verified" alt="Verified" />'; break;
	}
}

// 6.1. Track type
function tracktype($integer) {
	switch ($integer) {
		case 1: return 'Data/Mode 1'; break;
		case 2: return 'Data/Mode 2'; break;
		case 3: return 'Audio'; break;
	}
}

// 6.2. Pregap type
function pregaptype($integer) {
	switch ($integer) {
		case 1: return 'PREGAP'; break;
		case 2: return 'INDEX 00'; break;
	}
}

// 7.1 Disc filename
function discfilename($array, $nointro = 0) {
	return filename($array, 0, $nointro);
}

// 7.2 Track filename
function trackfilename($array, $nointro = 0) {
	return filename($array, $array['t_number'], $nointro);
}

// 7.3 Filename
function filename($disc, $track, $nointro = 0) {
	global $psxdb;
	if ($nointro) {
		// Title, region
		$filename = utf2lat($disc['d_title']).' ('.$psxdb['regions'][$disc['d_region']].')';
		
		// Languages
		$lang_array = explode(',', $disc['d_languages']);
		if (count($lang_array) > 1) {
			$lang_array2 = array();
			foreach ($psxdb['languages_no-intro'] as $k => $v) {
				if (in_array($k, $lang_array) && !in_array($v, $lang_array2)) {
					$lang_array2[] = $v;
				}
			}
			if (count($lang_array2) > 1) {
				$filename .= ' ('.implode(',', $lang_array2).')';
			}
		}		
	} else {	
		// Title, region
		$filename = utf2lat($disc['d_title']);
		if ($disc['d_region'] != 'W') {
			$filename .= ' ('.$disc['d_region'].')';
		}
	}

	// Languages
	//$languagescount = count(explode(',', $disc['d_languages']));	
	//if ($languagescount > 1) $filename .= ' (M'.$languagescount.')';

	// Version
	if ($disc['d_version_datfile'] != '')
		$filename .= ' ('.$disc['d_version_datfile'].')';

	// Disc number
	if (isset($disc['d_number']) && $disc['d_number'] != '')
		$filename .= ' (Disc '.$disc['d_number'].')';

	// Disc label
	if ($disc['d_label'] != '')
		$filename .= ' ('.utf2lat($disc['d_label']).')';

	// Track number
	if ($track != 0 && $disc['d_tracks_count'] > 1) {
			if ($disc['d_tracks_count'] < 10) $filename .= ' (Track '.$track.')';
			elseif ($disc['d_tracks_count'] > 9 && $track < 10) $filename .= ' (Track 0'.$track.')';
			elseif ($disc['d_tracks_count'] > 9 && $track > 9) $filename .= ' (Track '.$track.')';
	}
	
	if (!$nointro) {
		// Serial
		if ($disc['d_serial'] != '' && $disc['s_serial'] != 0)
			$filename .= ' ['.substr($disc['d_serial'], 0, $disc['s_serial']).']';
	} else if ($disc['d_category'] == 2) {
		// Sample
		$filename .= ' (Sample)';
	}
	return $filename;
}

// 8. Sectors to MSF
function stomsf($integer) {
	$min = str_pad(floor($integer / 60 / 75), 2, '0', STR_PAD_LEFT);
	$sec = str_pad(floor(($integer - ($min * 60 * 75)) / 75), 2, '0', STR_PAD_LEFT);
	$frame = str_pad($integer - ($min * 60 * 75) - ($sec * 75), 2, '0', STR_PAD_LEFT);
	return $min.':'.$sec.':'.$frame;
}

// 9. Title
function title($string) {
	$array = explode(', The', $string, 2);
	if (count($array) == 2) return 'The '.$array[0].$array[1];
	$array = explode(', Der', $string, 2);
	if (count($array) == 2) return 'Der '.$array[0].$array[1];
	$array = explode(', Die', $string, 2);
	if (count($array) == 2) return 'Die '.$array[0].$array[1];
	$array = explode(', Das', $string, 2);
	if (count($array) == 2) return 'Das '.$array[0].$array[1];
	return $string;
}

// 10. Flags
function flags($flags) {
	switch ($flags) {
		case 1: return "Pre-emphasis"; break;
		case 2: return "DCP"; break;
		case 3: return "Pre-emphasis, DCP"; break;
		default: return;
	}
}

function make_cues($id) {
	global $mysqli;
	$discs = $mysqli->query('SELECT * FROM `discs`,`systems` WHERE `systems`.`s_id`=`discs`.`d_media` AND `discs`.`d_id`='.intval($id));
	if ($discs->num_rows != 1)
		return 0;
	$disc = $discs->fetch_array();
	if ($disc['s_description'] == 1) {
		$tracks = $mysqli->query('SELECT * FROM `discs`,`tracks`,`systems` WHERE `systems`.`s_id`=`discs`.`d_media` AND `discs`.`d_id`=`tracks`.`d_id` AND `discs`.`d_id`='.$id.' ORDER BY `tracks`.`t_number`');
		while ($track = $tracks->fetch_array()) {
			$cue .= 'FILE "'.trackfilename($track).'.'.$disc['s_extension'].'" BINARY'."\r\n".'  TRACK '.str_pad($track['t_number'], 2, '0', STR_PAD_LEFT).' '.Cueparser::trackType($track['t_type'])."\r\n";
			if ($track['t_pregap_type'] == 1) {
				if ($track['t_number'] > 1 && $track['t_pregap'] > 0) {
					$gaplength = $track['t_pregap'];
					$gapsec = str_pad(floor($gaplength / 75), 2, '0' ,STR_PAD_LEFT);
					$gapframe = str_pad($gaplength - ($gapsec * 75), 2, '0', STR_PAD_LEFT);
					$cue .= "    PREGAP 00:".$gapsec.":".$gapframe."\r\n";
				}
			  $cue .= "    INDEX 01 00:00:00\r\n";
			} elseif ($track['t_pregap_type'] == 2) {
				if ($track['t_number'] > 1 && $track['t_pregap'] > 0) $cue .= "    INDEX 00 00:00:00\r\n";
				$gaplength = $track['t_pregap'];
				$gapsec = str_pad(floor($gaplength/75),2, '0', STR_PAD_LEFT);
				$gapframe = str_pad($gaplength-($gapsec*75),2, '0', STR_PAD_LEFT);
				$cue .= "    INDEX 01 00:".$gapsec.":".$gapframe."\r\n";
			}
			
			
			$cue_ni .= 'FILE "'.trackfilename($track, 1).'.'.$disc['s_extension'].'" BINARY'."\r\n".'  TRACK '.str_pad($track['t_number'], 2, '0', STR_PAD_LEFT).' '.Cueparser::trackType($track['t_type'])."\r\n";
			if ($track['t_pregap_type'] == 1) {
				if ($track['t_number'] > 1 && $track['t_pregap'] > 0) {
					$gaplength = $track['t_pregap'];
					$gapsec = str_pad(floor($gaplength / 75), 2, '0' ,STR_PAD_LEFT);
					$gapframe = str_pad($gaplength - ($gapsec * 75), 2, '0', STR_PAD_LEFT);
					$cue_ni .= "    PREGAP 00:".$gapsec.":".$gapframe."\r\n";
				}
			  $cue_ni .= "    INDEX 01 00:00:00\r\n";
			} elseif ($track['t_pregap_type'] == 2) {
				if ($track['t_number'] > 1 && $track['t_pregap'] > 0) $cue_ni .= "    INDEX 00 00:00:00\r\n";
				$gaplength = $track['t_pregap'];
				$gapsec = str_pad(floor($gaplength/75),2, '0', STR_PAD_LEFT);
				$gapframe = str_pad($gaplength-($gapsec*75),2, '0', STR_PAD_LEFT);
				$cue_ni .= "    INDEX 01 00:".$gapsec.":".$gapframe."\r\n";
			}
		}
		$cue_size  = strlen($cue);
		$cue_crc32 = strtolower(sprintf('%08X', crc32($cue)));
		$cue_md5   = md5($cue);
		$cue_sha1  = sha1($cue);
		$cue_title = discfilename($disc).'.cue';
		
		$cue_size_ni  = strlen($cue_ni);
		$cue_crc32_ni = strtolower(sprintf('%08X', crc32($cue_ni)));
		$cue_md5_ni   = md5($cue_ni);
		$cue_sha1_ni  = sha1($cue_ni);
		$cue_title_ni = discfilename($disc, 1).'.cue';
	} else if ($disc['s_description'] == 2) {
		$cue = $disc['d_tracks_count']."\r\n";
		$ssector = 0;
		$tracks = $mysqli->query('SELECT * FROM `discs`,`tracks` WHERE `discs`.`d_id`=`tracks`.`d_id` AND `discs`.`d_id`='.$id.' ORDER BY `tracks`.`t_number`');
		while ($track = $tracks->fetch_array()) {
			switch ($track['t_type']) {
				case 1:
				case 2:
					$ttype = 4;
					break;
				case 3:
					$ttype = 0;
					break;
			}
			if ($track['t_number'] == 3)
				$ssector = 45000;
			$cue .= $track['t_number'].' '.$ssector.' '.$ttype.' 2352 Track'.str_pad($track['t_number'], 2, '0', STR_PAD_LEFT).'.bin 0'."\r\n";
			$ssector += ($track['t_size'] / 2352);
		}
		$cue_size  = strlen($cue);
		$cue_crc32 = strtolower(sprintf('%08X', crc32($cue)));
		$cue_md5   = md5($cue);
		$cue_sha1  = sha1($cue);
		$cue_title = discfilename($disc).'.gdi';
		
		$cue_size_ni  = 0;
		$cue_crc32_ni = NULL;
		$cue_md5_ni   = NULL;
		$cue_sha1_ni  = NULL;
		$cue_title_ni = NULL;
		$cue_ni       = NULL;
	} else {
		$cue_size  = 0;
		$cue_crc32 = NULL;
		$cue_md5   = NULL;
		$cue_sha1  = NULL;
		$cue_title = NULL;
		$cue       = NULL;
		
		$cue_size_ni  = 0;
		$cue_crc32_ni = NULL;
		$cue_md5_ni   = NULL;
		$cue_sha1_ni  = NULL;
		$cue_title_ni = NULL;
		$cue_ni       = NULL;
	}
	
	return $mysqli->query('UPDATE `discs` SET `discs`.`d_cue_size`='.$cue_size.',`discs`.`d_cue_crc32`="'.$cue_crc32.'",`discs`.`d_cue_md5`="'.$cue_md5.'",`discs`.`d_cue_sha1`="'.$cue_sha1.'",`discs`.`d_cue_title`="'.addslashes($cue_title).'",`discs`.`d_cue_contents`="'.addslashes($cue).'",`discs`.`d_cue_size_ni`='.$cue_size_ni.',`discs`.`d_cue_crc32_ni`="'.$cue_crc32_ni.'",`discs`.`d_cue_md5_ni`="'.$cue_md5_ni.'",`discs`.`d_cue_sha1_ni`="'.$cue_sha1_ni.'",`discs`.`d_cue_title_ni`="'.addslashes($cue_title_ni).'",`discs`.`d_cue_contents_ni`="'.addslashes($cue_ni).'" WHERE `discs`.`d_id`='.$id);
}


?>