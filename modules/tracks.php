<?php

if (!defined('PSXDB')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

//$query = 'SELECT * FROM (SELECT * FROM tracks WHERE t_type=3) AS tracks NATURAL JOIN (SELECT * FROM discs,systems WHERE d_status>=4 AND discs.d_media=systems.s_id AND s_media=1) AS discs WHERE discs.d_tracks_count=tracks.t_number ORDER BY t_size,d_title';
$query = 'SELECT * FROM discs,systems,tracks WHERE tracks.d_id=discs.d_id AND discs.d_status>=4 AND discs.d_media=systems.s_id AND s_media=1 AND tracks.t_type=3 ORDER BY t_size,d_title';
$tracks = $mysqli->query($query);
echo '<div class="gamesblock"><table class="games"><tr><th>Size</th><th>Title</th><th>Region</th><th>Number</th><th>MD5</th></tr>';
$size = 0;
ob_start();
while ($track = $tracks->fetch_array()) {
	if ($track['t_size'] == $size) {
		ob_flush();
		echo '<tr><td>-</td>';
	} else {
		ob_clean();
		echo '<tr><td>'.$track['t_size'].'</td>';
	}
	echo '<td><a href="/disc/'.$track['d_id'].'/">'.htmlspecialchars(discfilename($track)).'</a></td>';
	echo '<td>'.region($track['d_region']).'</td>';
	echo '<td>'.$track['t_number'].'/'.$track['d_tracks_count'].'</td>';
	echo '<td>'.$track['t_md5'].'</td>';
	echo '</tr>';
	if ($track['t_size'] == $size) {
		ob_flush();
	}
	$size = $track['t_size'];
}
ob_end_clean();
echo '</table></div>';
display();

?>