<?php

$author_id  = 2;
$title      = 'PlayStation 2: dumping guide';
$created    = '2007-07-20';
$language   = 'eng';
$contents   = <<<end_delimiter



<h2>1. Installing and setting up soft</h2>
<div class="textblock">
<ul>
<li>Download and install <a href="/download/isobuster_all_lang.exe">IsoBuster 2.1</a><a class="external" href="http://www.isobuster.com/"></a>. Launch  the program, select <b>Options<b> -> </b>File system settings</b> menu item, in <b>Display time stamp</b> options group select <b>Local time stamp</b> option.</li>
<li>Download and install <a href="/download/hashcalc.zip">HashCalc 2.02</a><a class="external" href="http://www.slavasoft.com/hashcalc/index.htm"></a>.</li>
</ul>
</div>


<h2>2. Determining disc type</h2>
<div class="textblock">
<ul>
<li>PlayStation 2 discs with <b>green</b> working surface are <b>CDs</b>.</li>
<li>PlayStation 2 discs with <b>pink</b> working surface are <b>DVDs</b>.</li>
</ul>
</div>

<h2>3. Dumping disc</h2>
<div class="textblock">
<p>Insert the disc into the drive and launch IsoBuster. If you have multiple drives with discs inserted, make sure you have select the right one from the list.</p>
<ul>
<li><b><i>Dumping CD</i></b>:<br />If CD has more that one track, use <a href="/guide/psxdumping">PlayStation dumping guide</a>. Otherwise right click on <b>Track 01</b>, select <b>Extract Track 01</b> -> <b>Extract RAW Data (2352 bytes/block) (*.bin, *.iso)</b> menu item, then choose folder for extraction and press <b>OK</b>.</li>
<li><b><i>Dumping DVD</i></b>:<br />Right click on <b>Track 01</b>, select <b>Extract Track 01</b> -> <b>Extract User Data (*.tao, *.iso, *.wav)</b> menu item, then choose folder for extraction and press <b>OK</b>.</li>
</ul>
<p>The copying process may take several minutes. If you get "Unreadable sector" errors during it, press <b>Retry</b> button. If the error appears again, your disc is unsuitable for preservation.</p>
<p>After the disc has been copied, launch HashCalc, check MD5, SHA1, CRC32 and eDonkey/eMule rows, uncheck others. Select <b>File</b> option from the <b>Data format</b> list, then press button next to the <b>Data</b> field, select dumped image and press <b>Calculate</b>. The calculation process may take up to several minutes. These checksums should be posted along with other info.</p>
<p>Dump your disc again and compare old dump checksums with the new ones. If checksums are the same, your disc has been dumped properly.</p>
</div>

<h2>4. Gathering other info</h2>
<div class="textblock">
<ul>
<li><b>Game title</b>: most obvious, is located everywhere. Please include subtitle.</li>
<li><b>Disc ID</b>: it is located on disc and commonly is in the form of "<b>XXXX-YYYYY</b>" (where X is a letter and Y is a number), also it may have some additional characters appended. Examples: SLUS-21359, SCES-51578#2.</li>
<li><b>Disc title</b>: some games stored on multiple discs may have discs with titles. Examples: <a href="http://psxdb.com/disc/585/">C&amp;C Disc 1 (GDI Disc)</a> and <a href="http://psxdb.com/disc/586/">Disc 2 (NOD Disc)</a>.</li>
<li><b>EXE date</b>: EXE is usually named like disc ID in the form of "<b>XXXX_YYY.YY</b>" (where X is a letter and Y is a number). If you are not sure, check EXE name in the file <b>SYSTEM.CNF</b>. EXE date should be looked in IsoBuster in <b>Modified</b> column.</li>
<li><b>Game languages</b>: just start the game on the console and look for "Language" menu in options</li>
<li><b>Disc version</b>: Go to the root directory of the disc and open <b>SYSTEM.CNF</b> file in the text editor. Disc version is located in the row which starts with "VER", for example, "VER = 1.00".</li>
<li><b>Image size</b>: look for it in any file manager (but not in IsoBuster).</li>
<li><b>Edition</b>: some games were rereleased in different packages, for example, <b>Platinum</b>/<b>Greatest Hits</b>/<b>PlayStation 2 the Best</b> series. If game was released in original package, post edition as "Original".</li>
</ul>
</div>

<h2>5. Posting info</h2>
<div class="textblock">
<p>Note: you must have dumper rights to be able to post info using forms. Otherwise use <a href="http://forum.psxdb.com/post.php?fid=3">the forum</a>.</p>
<ul>
<li><b><i><a href="/newdisc/PS2_CD/">PlayStation 2 CD</a></i></b></li>
<li><b><i><a href="/newdisc/PS2_DVD-5/">PlayStation 2 DVD-5</a></i></b></li>
<li><b><i><a href="/newdisc/PS2_DVD-9/">PlayStation 2 DVD-9</a></i></b> (size > 4 700 000 000 bytes)</li>
</ul>
</div>

end_delimiter;

?>