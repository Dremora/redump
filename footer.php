<?php

if (!defined('PSXDB')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

echo '
</div>
';
if (!(isset($psxdb['error']) && $psxdb['error'])) {
	echo '<div id="online">'.show_users_online().'</div>
';
}
echo '<div id="footer">Redump 0.4<br />© 2005&ndash;2008 Redump Team</div>';
?>