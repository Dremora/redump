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
echo '<div id="footer"><a href="mailto:redump.team@gmail.com"><img src="/images/mail.png" alt="redump.team@gmail.com" title="Our e-mail" /></a>Redump 0.4<br />&copy; 2005 &mdash; 2008 Redump Team</div>';
?>