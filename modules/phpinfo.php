<?php

if (!defined('PSXDB') || !defined('ADMIN')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

phpinfo();

?>