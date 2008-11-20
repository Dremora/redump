<?php

$psxdb_config = array(
	'db_host'                    => 'localhost',
	'db_name'                    => '',
	'db_username'                => '',
	'db_password'                => '',
	'cookie_name'                => 'redump_cookie',
	'timeout_visit'              => 600,
	'timeout_online'             => 300,
	'timeout_online_feedreaders' => 3600,
	'timezone'                   => 0,
	'red_users'                  => array()/*  here goes the array of users' IDs which have access to the red discs */
);

define('PSXDB', true);

?>