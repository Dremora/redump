<?php

if (!defined('PSXDB')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

echo '<div id="header"><img src="/images/logo-left.png" style="float: left;" alt="" /></div>
<ul id="icons">
<li><a href="/feeds/"><img src="/images/feed.png" alt="Feeds" title="Feeds" /></a></li>
<li><a href="http://twitter.com/redump"><img src="/images/twitter.png" alt="Twitter" title="Twitter" /></a></li>
</ul>
<form action="/results/" method="post"><div class="menu"><input id="quicksearch" type="text" name="quicksearch" value="Quick search" /><a href="/">Main</a><a href="/discs/" id="menu1" onmouseout="hideelement(\'1\');" onmouseover="showelement(\'1\');">Discs</a><a id="menu2" onmouseout="hideelement(\'2\');" onmouseover="showelement(\'2\');">Downloads</a><a id="menu3" onmouseout="hideelement(\'3\');" onmouseover="showelement(\'3\');">Site</a>'.(defined('LOGGED') ? '<a id="menu5" onmouseout="hideelement(\'5\');" onmouseover="showelement(\'5\');">User</a>' : '').'<a href="/guide/">Guide</a><a id="menu4" onmouseout="hideelement(\'4\');" onmouseover="showelement(\'4\');">Affiliates</a><a href="http://forum.'.$_SERVER['HTTP_HOST'].'/">Forum</a></div></form>';
?>
<div class="submenu" id="submenu1" onmouseout="hideelement('1');" onmouseover="showelement('1');">
<a href="/discs/system/mac/">&bull; Apple Macintosh</a>
<a href="/discs/system/playdia/">&bull; Bandai Playdia</a>
<a href="/discs/system/pippin/">&bull; Bandai / Apple Pippin</a>
<a href="/discs/system/acd/">&bull; Commodore Amiga CD</a>
<a href="/discs/system/cd32/">&bull; Commodore Amiga CD32</a>
<a href="/discs/system/cdtv/">&bull; Commodore Amiga CDTV</a>
<a href="/discs/system/dvd-video/">&bull; DVD-Video</a>
<a href="/discs/system/pc/">&bull; IBM PC compatible</a>
<a href="/discs/system/xbox/">&bull; Microsoft Xbox</a>
<a href="/discs/system/pce/">&bull; NEC PC Engine CD - TurboGrafx-CD</a>
<a href="/discs/system/pc-98/">&bull; NEC PC-98 series</a>
<a href="/discs/system/gc/">&bull; Nintendo GameCube</a>
<a href="/discs/system/wii/">&bull; Nintendo Wii</a>
<a href="/discs/system/3do/">&bull; Panasonic 3DO Interactive Multiplayer</a>
<a href="/discs/system/dc/">&bull; Sega Dreamcast</a>
<a href="/discs/system/scd/">&bull; Sega Mega-CD</a>
<a href="/discs/system/ss/">&bull; Sega Saturn</a>
<a href="/discs/system/psx/">&bull; Sony PlayStation</a>
<a href="/discs/system/ps2/">&bull; Sony PlayStation 2</a>
<a href="/discs/system/psp/">&bull; Sony PlayStation Portable</a>
<?php if (defined('ADMIN') || defined('MODERATOR')) : ?>
<a href="/discs-wip/">WIP discs</a>
<?php endif; ?>
<?php if (defined('ADMIN') || defined('MODERATOR') || defined('DUMPER')): ?>
<a href="/newdisc/">New disc</a>
<?php endif; ?>
<?php if (defined('ADMIN') || defined('MODERATOR')) : ?>
<a href="/rebuildcues/">Rebuild cuesheets</a>
<?php endif; ?>
<a href="/statistics/">Statistics</a>
</div>

<div class="submenu" id="submenu2" onmouseout="hideelement('2');" onmouseover="showelement('2');">
<a href="/datfile/mac/">Apple Macintosh datfile</a>
<a href="/datfile/playdia/">Bandai Playdia datfile</a>
<a href="/datfile/pippin/">Bandai / Apple Pippin datfile</a>
<a href="/datfile/acd/">Commodore Amiga CD datfile</a>
<a href="/datfile/cd32/">Commodore Amiga CD32 datfile</a>
<a href="/datfile/cdtv/">Commodore Amiga CDTV datfile</a>
<a href="/datfile/dvd-video/">DVD-Video datfile</a>
<a href="/datfile/pc/">IBM PC compatible datfile</a>
<a href="/datfile/xbox/">Microsoft Xbox datfile</a>
<a href="/datfile/pce/">NEC PC Engine CD - TurboGrafx-CD datfile</a>
<a href="/datfile/pc-98/">NEC PC-98 series datfile</a>
<a href="/datfile/gc/">Nintendo GameCube datfile</a>
<a href="/datfile/wii/">Nintendo Wii datfile</a>
<a href="/datfile/3do/">Panasonic 3DO Interactive Multiplayer datfile</a>
<a href="/datfile/dc/">Sega Dreamcast datfile</a>
<a href="/datfile/scd/">Sega Mega-CD datfile</a>
<a href="/datfile/ss/">Sega Saturn datfile</a>
<a href="/datfile/psx/">Sony PlayStation datfile</a>
<a href="/datfile/psx-bios/">Sony PlayStation BIOS datfile</a>
<a href="/datfile/ps2/">Sony PlayStation 2 datfile</a>
<a href="/datfile/psp/">Sony PlayStation Portable datfile</a>
<a href="/cues/">Cuesheets archive</a>
<a href="/gdi/">GDI archive</a>
</div>

<div class="submenu" id="submenu3" onmouseout="hideelement('3');" onmouseover="showelement('3');">
<?php if (defined('GUEST')): ?>
<b><a href="http://forum.<?php echo $_SERVER['HTTP_HOST']; ?>/register.php">Register</a></b>
<a href="http://forum.<?php echo $_SERVER['HTTP_HOST']; ?>/login.php">Log in</a>
<?php endif; ?>
<a href="http://forum.<?php echo $_SERVER['HTTP_HOST']; ?>/userlist.php">Users</a>
<a href="irc://irc.foreverchat.net/redump">IRC: #redump</a>
<a href="mailto:redump.team@gmail.com">E-mail</a>
<?php if (defined('ADMIN') || defined('MODERATOR')) : ?>
<a href="http://tracker.redump.org/">Tracker</a>
<?php endif; ?>
</div>

<div class="submenu" id="submenu4" onmouseout="hideelement('4');" onmouseover="showelement('4');">
<a href="http://www.no-intro.org/">No-Intro</a><?php
//<a href="http://psxdata.snesorama.us/">PlayStation DataCenter</a>
//<a href="http://www.defconsoft.co.uk/">PAL PlayStation Collective</a>
?>
</div>

<?php if (defined('LOGGED')): ?>
<div class="submenu" id="submenu5" onmouseout="hideelement('5');" onmouseover="showelement('5');">
<a href="http://forum.<?php echo $_SERVER['HTTP_HOST']; ?>/login.php?action=out&amp;id=<?php echo $psxdb_user['id']; ?>">Log out</a>
<?php if (defined('ADMIN') || defined('MODERATOR') || defined('DUMPER')): ?>
<a href="/discs/dumper/<?php echo $psxdb_user['username']; ?>/">My dumps</a>
<?php endif; ?>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/mac/">My Apple Macintosh discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/playdia/">My Bandai Playdia discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/pippin/">My Bandai / Apple Pippin discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/acd/">My Commodore Amiga CD discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/cd32/">My Commodore Amiga CD32 discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/cdtv/">My Commodore Amiga CDTV discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/dvd-video/">My DVD-Video discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/pc/">My IBM PC compatible discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/xbox/">My Microsoft Xbox discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/pce/">My NEC PC Engine CD - TurboGrafx-CD discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/pc-98/">My NEC PC-98 series discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/gc/">My Nintendo GameCube discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/wii/">My Nintendo Wii discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/3do/">My Panasonic 3DO Interactive Multiplayer discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/dc/">My Sega Dreamcast discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/scd/">My Sega Mega-CD discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/ss/">My Sega Saturn discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/psx/">My Sony PlayStation discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/ps2/">My Sony PlayStation 2 discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/psp/">My Sony PlayStation Portable discs</a>
</div>
<?php endif; ?>
<div id="main">