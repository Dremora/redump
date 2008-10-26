<?php

if (!defined('PSXDB')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

include_once 'parser.php';

$all_changes = $mysqli->query('SELECT * FROM `rss`,`discs`,`systems` WHERE `r_title` LIKE "%[NEW]%" AND `discs`.`d_id`=`rss`.`d_id` AND `systems`.`s_id`=`discs`.`d_media` ORDER BY `rss`.`r_id` DESC LIMIT 0,10');
echo '<h2>Recent dumps</h2><div class="textblock"><ul>';
while ($change = $all_changes->fetch_array()) {
	echo '<li>'.date('M d Y, H:i', (strtotime($change['r_datetime']) + (($psxdb_user['timezone'] + 1) * 3600))).' '.region($change['d_region']).' <a href="'.$change['r_url'].'">['.$change['s_short'].'] '.htmlspecialchars($change['d_title']);
	if (isset($change['d_number']))
		echo ' (Disc '.$change['d_number'].')';
	if ($change['d_label'] != '')
		echo ' ('.$change['d_label'].')';
	echo '</a></li>';
}
echo '</ul><a href="/discs/sort/added/dir/desc">More...</a></div><h2>News</h2>';

$all_news = $mysqli->query('SELECT *,`posts`.`id` AS postid,`topics`.`id` AS topicid FROM `posts`,`topics` WHERE `topics`.`forum_id`=2 AND `topics`.`id`=`posts`.`topic_id` AND `topics`.`posted`=`posts`.`posted` ORDER BY `posts`.`id` DESC LIMIT 0,5');
while ($news = $all_news->fetch_array()) {
	echo '<h3>'.htmlspecialchars($news['subject']).'</h3>
<div class="textblock"><div class="textinfo">Posted by <a href="http://forum.'.$_SERVER['HTTP_HOST'].'/profile.php?id='.$news['poster_id'].'">'.htmlspecialchars($news['poster']).'</a> at '.date('F j Y, H:i:s', ($news['posted'] + ($psxdb_user['timezone'] - 2) * 3600)).'</div>'.parse_message($news['message']);
	if (defined('LOGGED')) echo '<div class="textinfo">';
	if (defined('ADMIN'))  echo '<a href="http://forum.'.$_SERVER['HTTP_HOST'].'/edit.php?id='.$news['postid'].'">Edit</a> &bull; ';
	if (defined('LOGGED')) echo '<a href="http://forum.'.$_SERVER['HTTP_HOST'].'/viewtopic.php?pid='.$news['postid'].'">View comments</a> &bull; <a href="http://forum.'.$_SERVER['HTTP_HOST'].'/post.php?tid='.$news['topicid'].'">Add comment</a></div>';
echo '</div>
';
}

$psxdb['title'] = 'Main page';
display();

?>