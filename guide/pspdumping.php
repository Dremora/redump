<?php

$author_id  = 73;
$title      = 'PlayStation Portable: Dumping guide';
$created    = '2007-03-16';
$updated    = '2007-08-09';
$language   = 'eng';
$contents   = <<<end_delimiter

<h2>1. Dump with "UMD DAX Dumper Beta 0.2 by Dark_AleX"</h2>
<div class="textblock">
<ul>
<li><i>Note: <b>Please do not use USBSSS &mdash; it makes bad dumps!</b></i></li>
<li>PSP must have FW 1.5 or any OE.</li>
<li>Download <b><a href="http://ajax16384.dremora.com/umddaxdumper02.zip">umddaxdumper02.zip</a></b>.</li>
<li>Extract folder "PSP" to already connected to computer memory stick.</li>
<li>Launch PSP dumper and select "ISO" at Format option.</li>
<li>Start dump and upon finish move image from root folder ms0:/ISO to computer.</li>
</ul>
</div>
<h2>2. Extract info</h2>
<div class="textblock">
<ul>
<li>Use <b><a href="http://psxdb.com/download/hashcalc.zip">HashCalc</a></b> to obtain various hashes: <b>MD5</b>, <b>SHA-1</b>, <b>CRC32</b> and <b>eDonkey/eMule</b>.</li>
<li>Download <b><a href="http://ajax16384.narod.ru/SFOInfo.rar">SFOInfo</a></b> (this tool needs .NET 2.0).</li>
<li>Mount PSP image as ordinary disk emulation drive and run "SFOInfo s r:\ D:\myinfo.txt" (r: emulation disk letter).</li>
<li>Post myinfo.txt, image hashes and in-game languages as PSP dump info.</li>
</ul>
</div>
end_delimiter;

?>