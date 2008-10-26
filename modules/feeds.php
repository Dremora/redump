<?php

if (!defined('PSXDB')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

include 'parser.php';

if ($_GET['feed'] != '') switch ($_GET['format']) {
	case 'atom':
		require_once 'classes/feeds/atom.php';
		break;
	case 'rss':
		require_once 'classes/feeds/rss20.php';
		break;
	default:
		exit();
}

switch ($_GET['feed']) {
	case 'recentchanges':
		$items = $mysqli->query('SELECT * FROM `rss`,`users` WHERE `rss`.`u_id`=`users`.`id` ORDER BY `r_id` DESC LIMIT 0,50');
		$status = $items->fetch_array();
		$items->data_seek(0);
		$feed = new Feed(array('title' => 'Redump — Recent changes', 'link' => 'http://'.$_SERVER['HTTP_HOST'].'/', 'description' => 'Redump — Recent changes', 'updated' => $status['r_datetime_new']));
		while ($item = $items->fetch_array()) {
			$feed->addItem(array('title' => $item['r_title'], 'link' => 'http://'.$_SERVER['HTTP_HOST'].$item['r_url'], 'description' => $item['r_contents'], 'author' => $item['username'], 'added' => $item['r_datetime_new'], 'guid' => 'http://psxdb.com/update/'.$item['r_id'].'/'));
		}
		$feed->display();
		break;

	case 'recentdumps':
		$items = $mysqli->query('SELECT * FROM `rss`,`users` WHERE `rss`.`u_id`=`users`.`id` AND `rss`.`r_title` LIKE "%[NEW]%" ORDER BY `r_id` DESC LIMIT 0,50');
		$status = $items->fetch_array();
		$items->data_seek(0);
		$feed = new Feed(array('title' => 'Redump — Recent dumps', 'link' => 'http://'.$_SERVER['HTTP_HOST'].'/', 'description' => 'Redump — Recent dumps', 'updated' => $status['r_datetime_new']));
		while ($item = $items->fetch_array()) {
			$feed->addItem(array('title' => $item['r_title'], 'link' => 'http://'.$_SERVER['HTTP_HOST'].$item['r_url'], 'description' => $item['r_contents'], 'author' => $item['username'], 'added' => $item['r_datetime_new'], 'guid' => 'http://psxdb.com/update/'.$item['r_id'].'/'));
		}
		$feed->display();
		break;

	case 'forum':
		$items = $mysqli->query('SELECT t.subject, p.message, p.poster, p.id AS pid, f.forum_name, p.posted, p.edited, IF(p.edited IS NULL,p.posted,p.edited) AS updated FROM `posts` AS `p`, `topics` AS `t`, `forums` AS `f` WHERE `p`.`topic_id`=`t`.`id` AND `t`.`forum_id`=`f`.`id` AND `t`.`forum_id`<>5 AND `t`.`forum_id`<>8 AND `t`.`forum_id`<>9 ORDER BY updated DESC LIMIT 0,50');
		$status = $items->fetch_array();
		$items->data_seek(0);
		$feed = new Feed(array('title' => 'Redump Forum — Recent posts', 'link' => 'http://forum.'.$_SERVER['HTTP_HOST'].'/', 'description' => 'Redump Forum — Recent posts', 'updated' => $status['updated']));
		while ($item = $items->fetch_array()) {
			$feed->addItem(array('title' => $item['subject'], 'link' => 'http://forum.'.$_SERVER['HTTP_HOST'].'/viewtopic.php?pid='.$item['pid'], 'description' => parse_message($item['message']), 'author' => $item['poster'], 'added' => $item['posted'], 'guid' => 'http://forum.'.$_SERVER['HTTP_HOST'].'/viewtopic.php?pid='.$item['pid'], 'category' => $item['forum_name']));
		}
		$feed->display();
		break;

	case 'derus':
		$items = $mysqli->query('SELECT t.subject, p.message, p.poster, p.id AS pid, f.forum_name, p.posted, p.edited, IF(p.edited IS NULL,p.posted,p.edited) AS updated FROM `posts` AS `p`, `topics` AS `t`, `forums` AS `f` WHERE `p`.`topic_id`=`t`.`id` AND `t`.`forum_id`=`f`.`id` AND `t`.`forum_id`=5 ORDER BY updated DESC LIMIT 0,50');
		$status = $items->fetch_array();
		$items->data_seek(0);
		$feed = new Feed(array('title' => 'Derus — Recent patches', 'link' => 'http://forum.'.$_SERVER['HTTP_HOST'].'/viewforum.php?id=5', 'description' => 'Derus — Recent patches', 'updated' => $status['updated']));
		while ($item = $items->fetch_array()) {
			$array = array();
			if (preg_match('@\[([^\[\]]+)\](.*)@', $item['subject'], $array)) {
				$category = $array[1];
				$subject  = trim($array[2]);
			} else {
				$category = '';
				$subject  = $item['subject'];
			}
			$feed->addItem(array('title' => $subject, 'link' => 'http://forum.'.$_SERVER['HTTP_HOST'].'/viewtopic.php?pid='.$item['pid'], 'description' => parse_message($item['message']), 'author' => $item['poster'], 'added' => $item['posted'], 'guid' => 'http://forum.'.$_SERVER['HTTP_HOST'].'/viewtopic.php?pid='.$item['pid'], 'category' => $category));
		}
		$feed->display();
		break;


	default:
		echo '<h3>PSXDB Feeds</h3>
<div class="textblock"><p>Please select a feed:</p>
<ul>
	<li><b>Redump — Recent changes</b>
		<ul>
			<li><a href="/feeds/recentchanges/rss/">RSS 2.0</a></li>
		</ul>
	</li>
	<li><b>Redump Forum — Recent posts</b>
		<ul>
			<li><a href="/feeds/forum/rss/">RSS 2.0</a></li>
		</ul>
	</li>
	<li><b>Derus — Recent patches</b>
		<ul>
			<li><a href="/feeds/derus/rss/">RSS 2.0</a></li>
		</ul>
	</li>
</ul>
</div>
';
		$psxdb['title'] = 'PSXDB Feeds';
		display();
}

?>