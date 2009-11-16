<?php

if (!defined('PSXDB')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

echo '<div id="header"><img src="/images/logo-left.png" style="float: left;" alt="" /></div>
<form action="/results/" method="post"><div class="menu"><input id="quicksearch" type="text" name="quicksearch" value="Quick search" /><a href="/">Main</a><a href="/discs/" id="menu1" onmouseout="hideelement(\'1\');" onmouseover="showelement(\'1\');">Discs</a><a id="menu2" onmouseout="hideelement(\'2\');" onmouseover="showelement(\'2\');">Downloads</a><a id="menu3" onmouseout="hideelement(\'3\');" onmouseover="showelement(\'3\');">Site</a>'.(defined('LOGGED') ? '<a id="menu5" onmouseout="hideelement(\'5\');" onmouseover="showelement(\'5\');">User</a>' : '').'<a href="/guide/">Guide</a><a id="menu4" onmouseout="hideelement(\'4\');" onmouseover="showelement(\'4\');">Affiliates</a><a href="http://forum.'.$_SERVER['HTTP_HOST'].'/">Forum</a><a id="rss" href="/feeds/"><img src="/images/feed.png" alt="Feeds" title="Feeds" /></a></div></form>';
?>
<div class="submenu" id="submenu1" onmouseout="hideelement('1');" onmouseover="showelement('1');">
<a href="/discs/system/psx/">&bull; PlayStation</a>
<a href="/discs/system/ps2/">&bull; PlayStation 2</a>
<a href="/discs/system/psp/">&bull; PlayStation Portable</a>
<a href="/discs/system/gc/">&bull; GameCube</a>
<a href="/discs/system/wii/">&bull; Wii</a>
<a href="/discs/system/scd/">&bull; Mega-CD</a>
<a href="/discs/system/ss/">&bull; Saturn</a>
<a href="/discs/system/dc/">&bull; Dreamcast</a>
<a href="/discs/system/pce/">&bull; PC Engine CD</a>
<a href="/discs/system/3do/">&bull; 3DO</a>
<a href="/discs/system/xbox/">&bull; Xbox</a>
<a href="/discs/system/cdtv/">&bull; Amiga CDTV</a>
<a href="/discs/system/cd32/">&bull; Amiga CD32</a>
<a href="/discs/system/acd/">&bull; Amiga CD</a>
<a href="/discs/system/pc/">&bull; IBM PC compatible</a>
<a href="/discs/system/pc-98/">&bull; PC-98 series</a>
<a href="/discs/system/playdia/">&bull; Playdia</a>
<a href="/discs/system/pippin/">&bull; Pippin</a>
<a href="/discs/system/mac/">&bull; Macintosh</a>
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
<a href="/datfile/psx/">PlayStation datfile</a>
<a href="/datfile/psx-bios/">PlayStation BIOS datfile</a>
<a href="/datfile/ps2/">PlayStation 2 datfile</a>
<a href="/datfile/psp/">PlayStation Portable datfile</a>
<a href="/datfile/gc/">GameCube datfile</a>
<a href="/datfile/wii/">Wii datfile</a>
<a href="/datfile/scd/">Mega-CD datfile</a>
<a href="/datfile/ss/">Saturn datfile</a>
<a href="/datfile/dc/">Dreamcast datfile</a>
<a href="/datfile/pce/">PC Engine CD datfile</a>
<a href="/datfile/3do/">3DO datfile</a>
<a href="/datfile/xbox/">Xbox datfile</a>
<a href="/datfile/cdtv/">Amiga CDTV datfile</a>
<a href="/datfile/cd32/">Amiga CD32 datfile</a>
<a href="/datfile/acd/">Amiga CD datfile</a>
<a href="/datfile/pc/">IBM PC compatible datfile</a>
<a href="/datfile/pc-98/">PC-98 series datfile</a>
<a href="/datfile/playdia/">Playdia datfile</a>
<a href="/datfile/pippin/">Pippin datfile</a>
<a href="/datfile/mac/">Macintosh datfile</a>
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
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/psx/">My PlayStation discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/ps2/">My PlayStation 2 discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/psp/">My PlayStation Portable discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/gc/">My GameCube discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/wii/">My Wii discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/scd/">My Mega-CD discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/ss/">My Saturn discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/dc/">My Dreamcast discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/pce/">My PC Engine CD discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/3do/">My 3DO discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/xbox/">My Xbox discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/cdtv/">My Amiga CDTV discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/cd32/">My Amiga CD32 discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/acd/">My Amiga CD discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/pc/">My IBM PC compatible discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/pc-98/">My PC-98 series discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/playdia/">My Playdia discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/pippin/">My Pippin discs</a>
<a href="/list/have/<?php echo $psxdb_user['username']; ?>/mac/">My Macintosh discs</a>
</div>
<?php endif; ?>
<div id="main">