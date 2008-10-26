<?php

if (!defined('PSXDB')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

$discs = $mysqli->query('SELECT * FROM `discs`,`releases`,`dr` WHERE `discs`.`d_id`=`dr`.`d_id` AND `releases`.`r_id`=`dr`.`r_id` AND `releases`.`r_id`='.intval($_GET['id']).' ORDER BY `dr`.`dr_number`');
if (!$discs->num_rows) {
	error('Release with ID '.intval($_GET['id']).' doesn\'t exist!');
}

while ($disc = $discs->fetch_array()) {
	if ($disc['dr_number'] == 1) {
		echo '<div class="game"><table class="" cellspacing="0">';
		echo '<tr><th>Title</th><td>'.$disc['r_title'].'</td></tr>';
		echo '<tr><th>Edition</th><td>'.$disc['r_edition'].'</td></tr>';
		echo '<tr><th>Regions</th><td>'.$disc['r_regions'].'</td></tr>';
		if ($disc['r_serial'] != '') {
			echo '<tr><th>Serial</th><td>'.$disc['r_serial'].'</td></tr>';
		}
		echo '<tr><th>Discs</th><td>';
	}
	echo '<a href="/disc/'.$disc['d_id'].'/">'.title(htmlspecialchars($disc['d_title']));
	if (isset($disc['d_number']))
		echo ' (Disc '.$disc['d_number'].')';
	if ($disc['d_label'] != '')
		echo ' ('.$disc['d_label'].')';
	if ($disc['dr_serial'] != '')
		echo ' ('.$disc['dr_serial'].')';
	echo '</a><br />';
}
echo '</td></tr></table></div>';
$psxdb['title'] = 'Release info';
display();

?>