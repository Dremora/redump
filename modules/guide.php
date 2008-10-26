<?php

if (!defined('PSXDB')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

switch ($_GET['article']) {
	case '':
		$psxdb['title'] = 'Guide';
		echo '
	<h2></h2>
	<div class="textblock">
		<ul>
			<li>
				<b>CD-based games</b>
				<ul>
					<li><a href="/guide/cddumping/">Dumping guide</a></li>
					<li><a href="/guide/libcrypt/">PlayStation: LibCrypt protection</a></li>
				</ul>
			</li>
			<li>
				<b>PS2</b>
				<ul>
					<li><a href="/guide/ps2dumping/">Dumping guide</a></li>
				</ul>
			</li>
			<li><b>PlayStation Portable</b>
				<ul>
					<li><a href="/guide/pspdumping/">Dumping guide</a></li>
				</ul>
			</li>
			<li>
				<b>Capitalization standard</b>
				<ul>
					<li>For capitalization standard we are using, see <a href="http://wiki.musicbrainz.org/CapitalizationStandard">MusicBrainz</a>.</li>
				</ul>
			</li>
		</ul>
	</div>';
		break;
	default:
		if (!preg_match('@^[A-Za-z0-9_\-]+$@', $_GET['article']) || !file_exists('guide/'.$_GET['article'].'.php'))
			error('Article "'.htmlspecialchars($_GET['article']).'" doesn\'t exist', 'Guide');
		include_once 'guide/'.$_GET['article'].'.php';
		$psxdb['title'] = 'Guide: '.$title;
		$userquery = $mysqli->query('SELECT `username` FROM `users` WHERE `id`='.intval($author_id));
		if ($userquery->num_rows != 1)
			error('User with ID='.htmlspecialchars($author_id).' doesn\'t exist', 'Guide');
		$user = $userquery->fetch_array();
		echo '<div class="textblock"><div class="textinfo">Author: <b><a href="http://forum.psxdb.com/profile.php?id='.$author_id.'">'.$user['username'].'</a></b> &bull; Created: '.$created;
		if ($lastupdate != '')
			echo ' &bull; Last updated: '.$lastupdate;
		echo '</div></div>';
		echo $contents;
		display();
		
}

display();

?>