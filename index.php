<?php
ob_start();
$psxdb = $psxdb_user = array();
require_once 'regions.php';
require_once 'config.php';
require_once 'functions.php';
//if ($_SERVER['SERVER_PORT'] == 80) redirect('http://redump.org:81'.$_SERVER['REQUEST_URI']);
sql_connect();
check_cookie();
update_users_online();
$psxdb['module'] = isset($_GET['module']) ? $_GET['module'] : 'main';
if (!preg_match('@^[A-Za-z0-9_\-]+$@', $psxdb['module']) || !file_exists('./modules/'.$psxdb['module'].'.php')) {
	redirect('http://'.$_SERVER['HTTP_HOST']);
} else {
	include_once './modules/'.$psxdb['module'].'.php';
}
?>
