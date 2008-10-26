<?php

if (!defined('PSXDB')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

echo '<h3>Team</h3>
<div class="textblock"><p><b>Founders</b>: LedZeppelin68, <a href="http://dremora.com/">Dremora</a><br /><b>Core members</b>: <a href="http://v.dremora.com/-v-.jpg">-v-</a>, cHrI8l3, F1ReB4LL, p_star, TeeCee, Vigi and many others...</p></div>';
$psxdb['title'] = 'About';
display();

?>