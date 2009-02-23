<?php

if (!defined('PSXDB')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}


$psxdb['script'][] = '<script type="text/javascript" src="/javascript/discs.js"></script>';

$input = explode('/', $_GET['string']);
unset($_GET);
for ($i = 0; $i < count($input); $i++) {
	$_GET[$input[$i]] = $input[$i + 1];
	$i++;
}
unset($i);
unset($input);

function filterlink($string, $value, $title) {
	global $sort;
	
	// 1. System
	if ($string == 'system') {
		if ($_GET['system'] == $value) return '<b>'.$title.'</b>';
		if ($value != '') $vars .= 'system/'.$value.'/';
	} else {
		if ($_GET['system'] != '') $vars .= 'system/'.$_GET['system'].'/';
	}
	// 2. Region
	if ($string == 'region') {
		if ($_GET['region'] == $value) return '<b>'.$title.'</b>';
		if ($value != '') $vars .= 'region/'.$value.'/';
	} else {
		if ($_GET['region'] != '') $vars .= 'region/'.$_GET['region'].'/';
	}
	// 3. Status
	if ($string == 'status') {
		if ($_GET['status'] == $value) return '<b>'.$title.'</b>';
		if ($value != '') $vars .= 'status/'.$value.'/';
	} else {
		if ($_GET['status'] != '') $vars .= 'status/'.$_GET['status'].'/';
	}
	// 4. Title
	if ($string == 'title') {
		if ($_GET['title'] == $value) return '<b>'.$title.'</b>';
		if ($value != '') $vars .= 'title/'.$value.'/';
	} else {
		if ($_GET['title'] != '') $vars .= 'title/'.$_GET['title'].'/';
	}
	// 4.1. Quick search
	if ($string == 'quicksearch') {
		if ($_GET['quicksearch'] == $value) return '<b>'.$title.'</b>';
		if ($value != '') $vars .= 'quicksearch/'.$value.'/';
	} else {
		if ($_GET['quicksearch'] != '') $vars .= 'quicksearch/'.$_GET['quicksearch'].'/';
	}
	// 5. Page
	if ($string == 'page') {
		if ($_GET['page'] == $value || ($_GET['page'] == '' && $value == 1)) return '<b>'.$title.'</b>';
		if ($value != '' && $value != 1) $vars .= 'page/'.$value.'/';
	} else {
		if ($_GET['page'] == 'all') $vars .= 'page/all/';
	}
	// 6. Sorting
	if ($string == 'sort') {
		if ($value != '') $vars .= 'sort/'.$value.'/';
		if ($_GET['sort'] == $value && $_GET['dir'] != 'desc') $vars .= 'dir/desc/';
	} else {
		if ($_GET['sort'] != '') $vars .= 'sort/'.$_GET['sort'].'/';
		if ($_GET['dir'] != '') $vars .= 'dir/'.$_GET['dir'].'/';
	}
	// 7. Serial
	if ($string == 'serial') {
		if ($_GET['serial'] == $value) return '<b>'.$title.'</b>';
		if ($value != '') $vars .= 'status/'.$value.'/';
	} else {
		if ($_GET['serial'] != '') $vars .= 'serial/'.$_GET['serial'].'/';
	}
	// 8. EDC
	if ($string == 'edc') {
		if ($_GET['edc'] == $value) return '<b>'.$title.'</b>';
		if ($value != '') $vars .= 'edc/'.$value.'/';
	} else {
		if ($_GET['edc'] != '') $vars .= 'edc/'.$_GET['edc'].'/';
	}
	// 9. Offset
	if ($string == 'offset') {
		if ($_GET['offset'] == $value) return '<b>'.$title.'</b>';
		if ($value != '') $vars .= 'offset/'.$value.'/';
	} else {
		if ($_GET['offset'] != '') $vars .= 'offset/'.$_GET['offset'].'/';
	}
	// 10. Letter
	if ($string == 'letter') {
		if ($_GET['letter'] == $value) return '<b>'.$title.'</b>';
		if ($value != '') $vars .= 'letter/'.$value.'/';
	} else {
		if ($_GET['letter'] != '') $vars .= 'letter/'.$_GET['letter'].'/';
	}
	// 11. Dumper
	if ($string == 'dumper') {
		if ($_GET['dumper'] == $value) return '<b>'.$title.'</b>';
		if ($value != '') $vars .= 'dumper/'.$value.'/';
	} else {
		if ($_GET['dumper'] != '') $vars .= 'dumper/'.htmlspecialchars($_GET['dumper']).'/';
	}
	// 12. Dumpers
	if ($string == 'dumpers') {
		if ($_GET['dumpers'] == $value) return '<b>'.$title.'</b>';
		if ($value != '') $vars .= 'dumpers/'.$value.'/';
	} else {
		if ($_GET['dumpers'] != '') $vars .= 'dumpers/'.$_GET['dumpers'].'/';
	}
	// 13. LibCrypt
	if ($string == 'libcrypt') {
		if ($_GET['libcrypt'] == $value) return '<b>'.$title.'</b>';
		if ($value != '') $vars .= 'libcrypt/'.$value.'/';
	} else {
		if ($_GET['libcrypt'] != '') $vars .= 'libcrypt/'.$_GET['libcrypt'].'/';
	}
	// 14. Anti-modchip
	if ($string == 'antimodchip') {
		if ($_GET['antimodchip'] == $value) return '<b>'.$title.'</b>';
		if ($value != '') $vars .= 'antimodchip/'.$value.'/';
	} else {
		if ($_GET['antimodchip'] != '') $vars .= 'antimodchip/'.$_GET['antimodchip'].'/';
	}
	// 15. Language
	if ($string == 'language') {
		if ($_GET['language'] == $value) return '<b>'.$title.'</b>';
		if ($value != '') $vars .= 'language/'.$value.'/';
	} else {
		if ($_GET['language'] != '') $vars .= 'language/'.$_GET['language'].'/';
	}
	// 16. Tracks
	if ($string == 'tracks') {
		if ($_GET['tracks'] == $value) return '<b>'.$title.'</b>';
		if ($value != '') $vars .= 'tracks/'.$value.'/';
	} else {
		if ($_GET['tracks'] != '') $vars .= 'tracks/'.$_GET['tracks'].'/';
	}
	return '<a href="/discs/'.$vars.'">'.$title.'</a>';
	
}

/**********/
/* Filter */
/**********/

// Starting disc
$limit[0] = 0;
// Discs count
$limit[1] = 50;

// 1. System
if ($_GET['system'] != '') {
	$systems = $mysqli->query('SELECT * FROM `systems` WHERE `systems`.`s_short`="'.addslashes($_GET['system']).'" OR `systems`.`s_short_media`="'.addslashes(str_replace('_', ' ', $_GET['system'])).'"');
	if (!$systems->num_rows)
		error('System "'.htmlspecialchars($_GET['system']).'" doesn\'t exist.');
	while ($system = $systems->fetch_array()) {
		$system_title = $system['s_full'];
		$systems_query[] = '`d`.`d_media`='.$system['s_id'];
	}
	$query .= ' AND ('.implode(' OR ', $systems_query).')';
	$filter .= '<br />System: '.htmlspecialchars($system_title).' '.filterlink('system', '', '(remove filter)');
}

// 2. Region
if (isset($_GET['region'])) {
	if ($_GET['region'] == 'Eu')
		$query .= ' AND `d`.`d_region`<>"U" AND `d`.`d_region`<>"J" AND `d`.`d_region`<>"A" AND `d`.`d_region`<>"K"';
	else if ($_GET['region'] == 'As')
		$query .= ' AND (`d`.`d_region`="J" OR `d`.`d_region`="A" OR `d`.`d_region`="K")';
	else {
		if (!array_key_exists($_GET['region'], $GLOBALS['psxdb']['regions'])) {
			error('Unknown region: '.htmlspecialchars($_GET['region']).'.');
		}
		$query .= ' AND `d`.`d_region`="'.addslashes($_GET['region']).'"';
	}
}

// 3. Status
if (!preg_match('@^[1245]?$@', $_GET['status'])) error('Unknown status - '.htmlspecialchars($_GET['status']).'.');
if ($_GET['status'] != '')
	$query .= ' AND `d`.`d_status`='.$_GET['status'];

// 4. Title
if (!preg_match('@^[^/ _]*$@', $_GET['title'])) error('Unknown title - '.htmlspecialchars($_GET['title']).'.');
if (isset($_GET['title'])) {
	$title_array = explode('-', $_GET['title']);
	while (list($key, $val) = each($title_array))
		$query .= ' AND `d`.`d_title` LIKE "%'.addslashes($val).'%"';
}

// 4.1. Quick search (title + serial)
if (!preg_match('@^[^/ _]*$@', $_GET['quicksearch'])) error('Unknown quick search parametrs - '.htmlspecialchars($_GET['quicksearch']).'.');
if (isset($_GET['quicksearch'])) {
	$title_array = explode('-', $_GET['quicksearch']);
	while (list($key, $val) = each($title_array))
		$query .= ' AND (`d`.`d_title` LIKE "%'.addslashes($val).'%" OR `d`.`d_serial` LIKE "%'.addslashes($val).'%" OR `d`.`d_title_foreign` LIKE "%'.addslashes($val).'%")';
	$filter .= '<br />Search for: '.str_replace('-', ' ', htmlspecialchars($_GET['quicksearch'])).' '.filterlink('quicksearch', '', '(remove filter)');
}

// 5. Page
if (!preg_match('@^(([1-9][0-9]*)|all)?$@', $_GET['page'])) error('Unknown page number - '.htmlspecialchars($_GET['page']).'.');
if (isset($_GET['page'])) switch ($_GET['page']) {
	case 'all':
		$limit[1] = 10000;
		break;
	default:
		$limit[0] = ($_GET['page'] - 1) * 50;
}

// 6.1 Sorting direction
if (!preg_match('@^(desc)?$@', $_GET['dir'])) error('Unknown sorting direction - '.htmlspecialchars($_GET['dir']).'.');
if ($_GET['dir'] == 'desc')
	$direction = ' DESC';

// 6.2 Sort
if (!preg_match('@^(region|system|version|edition|languages|serial|status|date|added|offset)?$@', $_GET['sort'])) error('Unknown sorting column - '.htmlspecialchars($_GET['sort']).'.');
switch ($_GET['sort']) {
	case 'region':
		$sortquery = ' ORDER BY `d`.`d_region`'.$direction.',`d`.`d_title`'.$direction.',`d`.`d_version`'.$direction.',`d`.`d_number`'.$direction;
		break;
	case 'system':
		$sortquery = ' ORDER BY `s`.`s_order`'.$direction.',`d`.`d_title`'.$direction.',`d`.`d_region`'.$direction.',`d`.`d_version`'.$direction.',`d`.`d_number`'.$direction.'';
		break;
	case 'version':
		$sortquery = ' ORDER BY `d`.`d_version`'.$direction.',`d`.`d_title`'.$direction.',`d`.`d_region`'.$direction.',`d`.`d_number`'.$direction;
		break;
	case 'edition':
		$sortquery = ' ORDER BY `d`.`d_edition`,`d`.`d_version`'.$direction.',`d`.`d_title`'.$direction.',`d`.`d_region`'.$direction.',`d`.`d_number`'.$direction;
		break;
	case 'languages':
		$sortquery = ' ORDER BY `d`.`d_languages`'.$direction.',`d`.`d_title`'.$direction.',`d`.`d_region`'.$direction.',`d`.`d_version`'.$direction.',`d`.`d_number`'.$direction;
		break;
	case 'serial':
		$sortquery = ' ORDER BY `d`.`d_serial`'.$direction.',`d`.`d_title`'.$direction.',`d`.`d_region`'.$direction.',`d`.`d_version`'.$direction.',`d`.`d_number`'.$direction;
		break;
	case 'status':
		$sortquery = ' ORDER BY `d`.`d_status`'.$direction.',`d`.`d_title`'.$direction.',`d`.`d_region`'.$direction.',`d`.`d_version`'.$direction.',`d`.`d_number`'.$direction;
		break;
	case 'date':
		$sortquery = ' ORDER BY `d`.`d_date`'.$direction.',`d`.`d_title`'.$direction.',`d`.`d_region`'.$direction.',`d`.`d_version`'.$direction.',`d`.`d_number`'.$direction.',`d`.`d_languages`'.$direction.'';
		break;
	case 'added':
		$sortquery = ' ORDER BY `d`.`d_datetime_added`'.$direction.',`d`.`d_title`'.$direction.',`d`.`d_region`'.$direction.',`d`.`d_version`'.$direction.',`d`.`d_number`'.$direction.',`d`.`d_languages`'.$direction.'';
		break;
	case 'offset':
		$sortquery = ' ORDER BY `d`.`d_offset`'.$direction.',`d`.`d_title`'.$direction.',`d`.`d_region`'.$direction.',`d`.`d_version`'.$direction.',`d`.`d_number`'.$direction.',`d`.`d_languages`'.$direction.'';
		break;
	default:
		$sortquery = ' ORDER BY `d`.`d_title`'.$direction.',`d`.`d_region`'.$direction.',`d`.`d_version`'.$direction.',`d`.`d_number`'.$direction.',`d`.`d_languages`'.$direction.'';
}

// 7. Serial
if (!preg_match('@^((ESPM|SCED|SCES|SCPS|SCUS|SLAS|SIPS|SLBM|SLED|SLES|SLUS|SLPM|SLPS|PAPX|PBPX|SLKA)-[0-9]{5})|[A-Z]{4}|$@', $_GET['serial'])) error('Unknown serial - '.htmlspecialchars($_GET['serial']).'.');
if (isset($_GET['serial']))
	$query .= ' AND `d`.`d_serial`="'.addslashes($_GET['serial']).'"';

// 8. EDC
if (isset($_GET['edc'])) {
	if (!preg_match('@^(yes|no|unknown)$@', $_GET['edc']))
		error('Unknown EDC status - '.htmlspecialchars($_GET['edc']).'.');
	switch ($_GET['edc']) {
		case 'yes':
			$query .= ' AND `d`.`d_edc`=2';
			break;
		case 'no':
			$query .= ' AND `d`.`d_edc`=1';
			break;
		case 'unknown':
			$query .= ' AND `d`.`d_edc`=0';
			break;
	}
}

// 9. Offset
if (isset($_GET['offset']) && $_GET['offset'] != '') {
	if (!preg_match('@^([0123456789\+\-]+)|all|old$@', $_GET['offset']))
		error('Unknown offset: '.htmlspecialchars($_GET['offset']).'.');
	if ($_GET['offset'] == 'all')
		$query .= ' AND `d`.`d_offset` IS NOT NULL AND `d`.`d_tracks_count`>1';
	else if ($_GET['offset'] == 'old')
		$query .= ' AND `d`.`d_offset` IS NULL AND `d`.`d_tracks_count`>1';
	else
		$query .= ' AND (`d`.`d_offset`="'.$_GET['offset'].'" OR `d`.`d_offset` LIKE "'.$_GET['offset'].', %" OR `d`.`d_offset` LIKE "%, '.$_GET['offset'].'") AND `d`.`d_tracks_count`>1';
	$filter .= '<br />Offset: '.htmlspecialchars($_GET['offset']).' '.filterlink('offset', '', '(remove filter)');
}

// 10. Letter
if (!preg_match('@^([a-z]|~)?$@', $_GET['letter'])) error('Unknown letter - '.htmlspecialchars($_GET['letter']).'.');
if (isset($_GET['letter']))
	switch ($_GET['letter']) {
		case '~':
			$query .= ' AND `d`.`d_title` NOT LIKE "a%" AND `d`.`d_title` NOT LIKE "b%" AND `d`.`d_title` NOT LIKE "c%" AND `d`.`d_title` NOT LIKE "d%" AND `d`.`d_title` NOT LIKE "e%" AND `d`.`d_title` NOT LIKE "f%" AND `d`.`d_title` NOT LIKE "g%" AND `d`.`d_title` NOT LIKE "h%" AND `d`.`d_title` NOT LIKE "i%" AND `d`.`d_title` NOT LIKE "j%" AND `d`.`d_title` NOT LIKE "k%" AND `d`.`d_title` NOT LIKE "l%" AND `d`.`d_title` NOT LIKE "m%" AND `d`.`d_title` NOT LIKE "n%" AND `d`.`d_title` NOT LIKE "o%" AND `d`.`d_title` NOT LIKE "p%" AND `d`.`d_title` NOT LIKE "q%" AND `d`.`d_title` NOT LIKE "r%" AND `d`.`d_title` NOT LIKE "s%" AND `d`.`d_title` NOT LIKE "t%" AND `d`.`d_title` NOT LIKE "u%" AND `d`.`d_title` NOT LIKE "v%" AND `d`.`d_title` NOT LIKE "w%" AND `d`.`d_title` NOT LIKE "x%" AND `d`.`d_title` NOT LIKE "y%" AND `d`.`d_title` NOT LIKE "z%"';
			break;
		default:
			$query .= ' AND `d`.`d_title` LIKE "'.$_GET['letter'].'%"';
	}

// 11. Dumper
if (isset($_GET['dumper']) && $_GET['dumper'] != '') {
	$join .= ' NATURAL JOIN (SELECT d_id FROM du,users WHERE users.id=du.u_id AND (du_status=1 OR du_status=2) AND username="'.addslashes($_GET['dumper']).'") AS discdumper';
	$filter .= '<br />Dumper: '.htmlspecialchars($_GET['dumper']).' '.filterlink('dumper', '', '(remove filter)');
}

// 12. Dumpers
if (isset($_GET['dumpers']) && $_GET['dumpers'] != '') {
	$query .= ' AND (`d`.`d_dumpers`="'.addslashes($_GET['dumpers']).'" OR `d`.`d_dumpers` LIKE "'.addslashes($_GET['dumpers']).', %" OR `d`.`d_dumpers` LIKE "%, '.addslashes($_GET['dumpers']).'")';
	$filter .= '<br />Dumpers: '.htmlspecialchars($_GET['dumpers']).' '.filterlink('dumpers', '', '(remove filter)');
}

// 13. LibCrypt
if (!preg_match('@^[012]?$@', $_GET['libcrypt'])) error('Unknown libcrypt status - '.htmlspecialchars($_GET['libcrypt']).'.');
if (isset($_GET['libcrypt']))
	$query .= ' AND `d`.`d_protection_l`='.$_GET['libcrypt'];

// 13. Anti-modchip
if (!preg_match('@^[012]?$@', $_GET['antimodchip'])) error('Unknown anti-modchip status - '.htmlspecialchars($_GET['antimodchip']).'.');
if (isset($_GET['antimodchip']))
	$query .= ' AND `d`.`d_protection_a`='.$_GET['antimodchip'];

// 15. Language
if (isset($_GET['language']) && $_GET['language'] != '') {
	if (!in_array($_GET['language'], $psxdb['languages']))
		error('Unknown language: '.htmlspecialchars($_GET['language']).'.');
	$query .= ' AND `d`.`d_languages` LIKE "%'.addslashes($_GET['language']).'%"';
	$filter .= '<br />Language: '.language($_GET['language']).' '.filterlink('language', '', '(remove filter)');
}

// 16. Tracks
if (isset($_GET['tracks']) && $_GET['tracks'] != '') {
	if (!preg_match('@^[1-9][0-9]?$@', $_GET['tracks'])) {
		error('Unknown number of tracks - '.htmlspecialchars($_GET['tracks']).'.');
	}
	$query .= ' AND `d`.`d_tracks_count`='.addslashes($_GET['tracks']);
	$filter .= '<br />Number of tracks: '.$_GET['tracks'].' '.filterlink('tracks', '', '(remove filter)');
}

// XX.
if (!in_array($psxdb_user['id'], $psxdb_config['red_users'])) $query .= ' AND `d`.`d_status`>=4';

// My discs
if (defined('LOGGED')) {
	$join .= ' NATURAL LEFT JOIN (SELECT d_id, 1 AS have FROM du WHERE u_id='.$psxdb_user['id'].' AND (du_status=0 OR du_status=2)) AS du';
}

// Queries
if (!defined('ADMIN') && !defined('MODERATOR') && !defined('DUMPER')) {
	$query .= ' AND `s`.`s_public`=1';
}

$discs           = $mysqli->query('SELECT *                 FROM discs d JOIN systems s ON d.d_media=s.s_id'.$join.' WHERE 1'.$query.$sortquery.' LIMIT '.$limit[0].','.$limit[1]);
$totaldiscsquery = $mysqli->query('SELECT COUNT(`d`.`d_id`) FROM discs d JOIN systems s ON d.d_media=s.s_id'.$join.' WHERE 1'.$query);


$totaldiscs = $totaldiscsquery->fetch_array();

// Display
$psxdb['title'] = 'Discs';
//if (defined('ADMIN')) echo '<div class="textblock"><p>SELECT * FROM discs d JOIN systems s ON d.d_media=s.s_id'.$join.' WHERE 1'.$query.$sortquery.' LIMIT '.$limit[0].','.$limit[1].'</p></div>';

if (($_GET['quicksearch'] != '' || $_GET['title'] != '') && $totaldiscs[0] == 1) {
	$disc = $discs->fetch_array();
	redirect('http://'.$_SERVER['HTTP_HOST'].'/disc/'.$disc['d_id'].'/');
}
$dispres = ($limit[0] + 1).' - '.($limit[0] + $discs->num_rows);

echo '<div class="textblock"><p>
<a href="/discs/"><b>Reset</b></a> &bull; <b>Displaying results '.$dispres.' of '.$totaldiscs[0].'</b>';
echo '</p><p>
Starts with: '.filterlink('letter', '', 'All').' | '.filterlink('letter', '~', '~').' '.filterlink('letter', 'a', 'A').' '.filterlink('letter', 'b', 'B').' '.filterlink('letter', 'c', 'C').' '.filterlink('letter', 'd', 'D').' '.filterlink('letter', 'e', 'E').' '.filterlink('letter', 'f', 'F').' '.filterlink('letter', 'g', 'G').' '.filterlink('letter', 'h', 'H').' '.filterlink('letter', 'i', 'I').' '.filterlink('letter', 'j', 'J').' '.filterlink('letter', 'k', 'K').' '.filterlink('letter', 'l', 'L').' '.filterlink('letter', 'm', 'M').' '.filterlink('letter', 'n', 'N').' '.filterlink('letter', 'o', 'O').' '.filterlink('letter', 'p', 'P').' '.filterlink('letter', 'q', 'Q').' '.filterlink('letter', 'r', 'R').' '.filterlink('letter', 's', 'S').' '.filterlink('letter', 't', 'T').' '.filterlink('letter', 'u', 'U').' '.filterlink('letter', 'v', 'V').' '.filterlink('letter', 'w', 'W').' '.filterlink('letter', 'x', 'X').' '.filterlink('letter', 'y', 'Y').' '.filterlink('letter', 'z', 'Z').'
 &bull; Region: '.filterlink('region', '', 'All').' | '.filterlink('region', 'Eu', 'Europe').' &bull; '.filterlink('region', 'U', 'USA').' &bull; '.filterlink('region', 'As', 'Asia').'
 &bull; Status: '.filterlink('status', '', 'All').' | ';
if (in_array($psxdb_user['id'], $psxdb_config['red_users'])) {
	echo filterlink('status', '0', status(0)).' '.filterlink('status', '1', status(1)).' '.filterlink('status', '2', status(2)).' ';
}
echo filterlink('status', '4', status(4)).' '.filterlink('status', '5', status(5));

echo $filter;

if ($totaldiscs[0] > 50) {
	echo '</p><p>Page:';
	for ($i = 1; $i <= ceil($totaldiscs[0] / 50); $i++)
		echo ' '.filterlink('page', $i, $i);
	echo ' | '.filterlink('page', 'all', 'All');
}

echo '</p></div>';

if (!$discs->num_rows) {
	echo '<div class="textblock"><p>No discs found.</p></div>';
	display();
}

echo '<div class="gamesblock"><table class="games" cellspacing="0">'."\n\t".'<tr class="th">';
if (defined('LOGGED')) {
	echo '<th class="havemiss"></th>';
}
echo '<th>'.filterlink('sort', 'region', 'Region').'</th><th>'.filterlink('sort', '', 'Title').'</th><th>'.filterlink('sort', 'system', 'System').'</th><th>'.filterlink('sort', 'version', 'Version').'</th><th>'.filterlink('sort', 'edition', 'Edition').'</th><th>'.filterlink('sort', 'languages', 'Languages').'</th><th>'.filterlink('sort', 'serial', 'Serial').'</th><th>'.filterlink('sort', 'status', 'Status').'</th></tr>';
while ($disc = $discs->fetch_array()) {
	echo "\n\t<tr>";
	if (defined('LOGGED')) {
		if ($disc['have']) {
			echo '<td class="havemiss"><a id="ad'.$disc['d_id'].'" href="javascript:changeDiscStatus('.$disc['d_id'].');"><img src="/images/have.png" alt="" title="Have (click to change)" /></a></td>';
		} else {
			echo '<td class="havemiss"><a id="ad'.$disc['d_id'].'" href="javascript:changeDiscStatus('.$disc['d_id'].');"><img src="/images/miss.png" alt="" title="Miss (click to change)" /></a></td>';
		}
	}
	echo '<td class="align-center">'.region($disc['d_region']).'</td><td><a href="/disc/'.$disc['d_id'].'/">'.htmlspecialchars(title($disc['d_title']));
	if (isset($disc['d_number']))
		echo ' (Disc '.$disc['d_number'].')';
	if ($disc['d_label'] != '')
		echo ' ('.$disc['d_label'].')';
	if ($disc['d_title_foreign'] != '')
		echo '<br /><span class="small">'.htmlspecialchars(title($disc['d_title_foreign'])).'</span>';
	echo '</a></td><td>'.$disc['s_short'].'</td><td>'.htmlspecialchars($disc['d_version']).'</td><td>'.htmlspecialchars($disc['d_edition']).'</td><td>'.languages($disc['d_languages']).'</td>';
	$serial = $disc['s_serial'] > 0 ? substr($disc['d_serial'], 0, $disc['s_serial']) : $disc['d_serial'];
	if ($serial == $disc['d_serial']) {
		echo '<td>'.htmlspecialchars($serial).'</td>';
	} else {
		echo '<td style="cursor: help;" title="'.htmlspecialchars($disc['d_serial']).'">'.htmlspecialchars($serial).'&nbsp;…</td>';
	}
	echo '<td class="align-center">'.status($disc['d_status']);
	echo '</td></tr>';
}
echo "\n</table></div>";
display();

?>