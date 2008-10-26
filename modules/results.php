<?php

if (!defined('PSXDB')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

if ($_POST['quicksearch'] != '')
	$quicksearch = 'quicksearch/'.url_string($_POST['quicksearch']).'/';

if ($_POST['title'] != '')
	$title = 'title/'.url_string($_POST['title']).'/';

redirect('http://'.$_SERVER['HTTP_HOST'].'/discs/'.$title.$quicksearch);

?>