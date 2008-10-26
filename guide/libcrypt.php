<?php

$author_id  = 2;
$title      = 'PlayStation: LibCrypt protection';
$created    = '2007-05-29';
$lastupdate = '2007-08-17';
$language   = 'eng';
$contents   = <<<end_delimiter
<h2>Common information</h2>
<div class="textblock">
<p>Sony first introduced LibCrypt protection in PAL version of MediEvil in October 1998. Since then more than hundred games containing LibCrypt have been released. What's special about this protection is that it uses subchannels to store some non-<a href="http://en.wikipedia.org/wiki/Yellow_Book_%28CD_standard%29">Yellow Book</a> data, thus breaking the standard.</p>
<p>Every CD sector contains 2352 bytes of main channel data and 96 (+ 2 sync) bytes of subchannels data. While main channel stores user data, sync and error-correction codes, subchannels data was not intended to contain user data. 96 bytes of subchannels are divided to 8 12-byte channels: P, Q, R, S, T, U, V, W. In case of non-protected CD channel P contains pause info, channel Q contains current track flags and current sector address; other subchannels are zeroed. As all this data can be included in <a href="secondary">CUE</a>, it's one of the reasons PSXDB images contain only main channel data. The other reason is that it's nearly impossible to make perfect copy of subchannels data, because they don't have error-correction codes, not being encoded with <a href="http://en.wikipedia.org/wiki/Cross-interleaved_Reed-Solomon_coding">CIRC</a>. So, both dumping subchannels and calculating their checksums makes no sense.</p>
<p>Discs with LibCrypt protection have 16 or 32 sectors with slightly modified Q-channel, comparing to the same sectors in standard Yellow Book disc. The first half of the sectors is located on 3rd minute, and the second half on 9th minute. All modified sectors can be divided into pairs, the distance between sectors in each pair is 5 sectors. At the moment we have found 3 different protected sectors generation schemas.</p>
</div>
<h2>Protected sectors generation schemas</h2>
<div class="textblock">
<ol>
<li>2 bits from both MSFs are modified, CRC-16 is recalculated and XORed with 0x0080.
	<ul><li>Games: <a href="/disc/592/">MediEvil (E)</a>.
	</li></ul>
</li>
<li>2 bits from both MSFs are modified, original CRC-16 is XORed with 0x8001.
	<ul><li>Games: <a href="/disc/798/">CTR: Crash Team Racing (E) (No EDC)</a>, <a href="/disc/897/">CTR: Crash Team Racing (E) (EDC)</a>, <a href="/disc/710/">Dino Crisis (E)</a>, <a href="/disc/880/">Eagle One: Harrier Attack (E)</a> et al.
	</li></ul>
</li>
<li>Either 2 bits or none from both MSFs are modified, CRC-16 is recalculated and XORed with 0x0080.
	<ul><li>Games: <a href="/disc/1128/">Ape Escape (S)</a> et al.
	</li></ul>
</li>
</ol>
</div>
<h2>Detecting and dumping LibCrypted data</h2>
<h3>Method one: psxt001z</h3> 
<div class="textblock">
<p>For this you need <a href="http://soft.dremora.com/psxt001z-020b13fix.7z">psxt001z 0.20 beta 13</a>. Insert your PlayStation CD in drive and type in command line:</p>
<blockquote><p>psxt001z --libcryptdrvfast [drive_letter]</p></blockquote>
<p>for example,</p>
<blockquote><p>psxt001z --libcryptdrvfast D:</p></blockquote>
<p>In case tool reports error, try method two. If eveything is OK, it will start scanning disc for modified sectors. Please note that "modified" doesn't mean LibCrypted, because sectors contents also depends on disc quality and drive. When scanning finished, the number of modified sectors will be printed. Please include "sectors.log" file (which contains list of modified sectors along with their contents) when posting disc info to the forum or using internal site function. If error occured during the scanning process, try running the tool again or method two.</p>
</div>
<h3>Method two: CloneCD + psxt001z</h3>
<div class="textblock">
<p>For this you need <a href="http://static.slysoft.com/SetupCloneCD.exe">CloneCD</a> and <a href="http://soft.dremora.com/psxt001z-020b13fix.7z">psxt001z 0.20 beta 12</a>. Install CloneCD, insert your PlayStation CD in drive, run CloneCD, press first button, select drive from the list, then select "Game CD" profile (<b>not</b> "Protected PC Game"), and start dumping. When image is created, delete all files but "IMAGE.SUB". Then type in command line:</p>
<blockquote><p>psxt001z --libcrypt [path_to_sub]&gt;sectors.log</p></blockquote>
<p>for example,</p>
<blockquote><p>psxt001z --libcrypt "C:\IMAGE.SUB">sectors.log</p></blockquote>
<p>Please include generated "sectors.log" file (which contains list of modified sectors along with their contents) when posting disc info to the forum or using internal site function.</p>
</div>
<h2>Storing LibCrypt data</h2>
<div class="textblock">
<p>To store LibCrypt data, we use SBI format, which contains modified-only sectors with Q-channel data. SBIs for LibCrypted discs can be downloaded from PSXDB (link "SBI subchannels" on protected disc page). SBI format is supported by:</p>
<ul>
<li>ePSXe, PlayStation emulator. To use SBI, name it after EXE name, leaving extension .sbi, and put in /epsxe/pathes folder. For example, "SLES_025.29.sbi" for PAL version of Resident Evil 3: Nemesis.</li>
<li>P.E.Op.S. CDR plugin, which is supported by many PlayStation emulators. To use SBI, open plugin settings window, select "Use subchannel SBI/M3S info file" option from "subchannel reading" menu, then choose your SBI in "File" field.</li>
</ul>
<p>Unfortunately, no disc emulation or burning software currently supports SBI, neither do image formats. Our new image format with SBI support is in early development stage.</p>
</div>
<h2>Links</h2>
<div class="textblock">
<ul>
	<li><a href="http://www.geocities.com/psxcplist/">PSXCPLIST</a> &mdash; PlayStation Copy Protection list &mdash; list of both LibCrypt and Anti-Modchip protected games. May not work in Mozilla Firefox.</li>
</ul>
</div>
end_delimiter;

?>