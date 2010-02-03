<?php

if (!defined('PSXDB') || (!defined('ADMIN') && !defined('DUMPER') && !defined('MODERATOR'))) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

if ($_GET['action'] == 'remove' && isset($_GET['id'])) {
	$query = $mysqli->query('SELECT * FROM `discs_wip` WHERE `discs_wip`.`d_id`='.intval($_GET['id']));
	if ($query->num_rows)
		$mysqli->query('UPDATE `discs_wip` SET `discs_wip`.`d_added`=1 WHERE `discs_wip`.`d_id`='.intval($_GET['id']));
	redirect('http://'.$_SERVER['HTTP_HOST'].'/discs-wip/');
}

// Queries
$discs = $mysqli->query('SELECT * FROM `discs_wip`,`users`,`systems` WHERE `discs_wip`.`d_dumper`=`users`.`id` AND `systems`.`s_id`=`discs_wip`.`d_media` AND `discs_wip`.`d_added`=0');

// Display
$psxdb['title'] = 'Discs (WIP)';

if (!$discs->num_rows) {
	error('Search returned no results.');
}

echo '<div class="gamesblock"><table class="games" cellspacing="0">'."\n\t".'<tr>';
echo '<th>Region</th><th>Title</th><th>Version</th><th>Edition</th><th>EXE date</th><th>Languages</th><th>System</th><th>Dumper</th><th>Serial</th></tr>';
while ($disc = $discs->fetch_array()) {
	echo "\n\t".'<tr><td class="align-center">'.region($disc['d_region']).'</td><td>';
	if (defined('ADMIN') || defined('MODERATOR')) echo '<a href="/newdisc/'.$disc['d_id'].'/">';
	echo htmlspecialchars(title($disc['d_title']));
	if (isset($disc['d_number']))
		echo ' (Disc '.$disc['d_number'].')';
	if (defined('ADMIN') || defined('MODERATOR')) echo '</a>';
	echo '</td><td>'.htmlspecialchars($disc['d_version']).'</td><td>'.htmlspecialchars($disc['d_edition']).'</td><td>'.$disc['d_date'].'</td><td>'.languages($disc['d_languages']).'</td><td>'.$disc['s_short'].'</td><td>'.$disc['username'].'</td><td>'.$disc['d_serial'].'</td></tr>';
}
echo "\n</table></div>";
display();

?>