<?php

$author_id  = 8;
$title      = 'CD dumping guide';
$created    = '2007-01-01';
$lastupdate = '2010-08-28';
$language   = 'eng';
$contents   = <<<end_delimiter

<div class="textblock" style=" background: #febaba; padding: 10px;">
<span style="font-size: 18px; font-weight: bold;">Please dump all tracks at least twice (if possible, using different drives) to be sure that they are correct!
</span>
</div>

<h3>Introduction</h3>
<div class="textblock">
<p>This guide will explain how to preserve CD-based games in the best possible way.</p>
</div>

<h3>Software needed</h3>
<div class="textblock">
<ul>
	<li><a href="http://www.isobuster.com/">IsoBuster</a></li>
	<li><a href="http://www.exactaudiocopy.de/eac-0.99pb4.exe">ExactAudioCopy V0.99 Prebeta 4</a></li>
	<li><a href="/download/psxt001z-0.21b1.7z">psxt001z</a> (PSX 
	only)</li>
</ul>
</div>

<h3>Determining disc type</h3>
<div class="textblock">
<p>Before we dump the disc, we need to know if it contains any audio tracks.<br />
<br />
Insert the disc into the drive and launch IsoBuster.<br />
<br />
<p>A disc with audio tracks will look as follows (notice how the audio track icon differs from the data track one):</p><br />
<img src="/images/dumpingguide/isobuster1.PNG" alt="" style="width: 328px; height: 322px;" /><br />
<br />A disc with audio tracks will unfortunately take a bit longer to dump, because audio tracks require a different treatment than data tracks.</p>
</div>

<h2>Dumping discs without audio tracks</h2>
<h3>Dumping the data track with IsoBuster</h3>
<div class="textblock">
<ul>
	<li>Start IsoBuster;</li>
	<li>Insert the disc (If you have multiple drives with cd's inserted, make sure you select the right one);</li>
	<li>Right mouse button on Track 01 -&gt; Extract Track 01 -&gt; Extract RAW Data (2352 bytes/block) (*.bin, *.iso);</li>
	<li>Choose a destination folder.</li>
</ul>
<br /> 
If you get an 'Unreadable sector' error, make sure that the disc is free of scratches.<br />
<br /><p>When the extraction is complete, you will have an image file of the data track.</p>
<br /><p>Now you can head on to the 'Final steps' part.</p>
</div>

<h2>Dumping discs with audio tracks</h2>
<h3>Setting up EAC the first time</h3>
<div class="textblock">
<ul>
	<li>Start EAC;</li>
	<li>Cancel the configuration wizard (if there is one);</li>
	<li>Select the drive you want to use for ripping in the combobox;</li>
	<li>Open the EAC Options (EAC -&gt; EAC Options or press F9);</li>
	<li>Select the &quot;Tools&quot; tab and make sure 'activate beginner mode' is disabled;</li>
	<li>Select the &quot;Extraction&quot; tab. For &quot;Error recovery quality&quot; choose High;</li>
	<li>Also in the &quot;Extraction&quot; tab, disable &quot;No use of NULL samples for CRC calculations&quot;;</li>
	<li>Open the compression options (EAC -&gt; Compression Options or press F11);</li>
	<li>Select the &quot;Waveform&quot; tab. For &quot;Wave format&quot; choose &quot;Microsoft PCM Convertor&quot;;</li>
	<li>For &quot;Sample format&quot;, make sure that &quot;44,100 kHz; 16 Bit; Stereo&quot; is selected;</li>
	<li>In the same tab, make sure that &quot;Do not write WAV header to file&quot; is checked;</li>
	<li>Also make sure that &quot;High quality (slow) is selected&quot;;</li>
	<li>In the &quot;File extension for headerless files&quot; box, enter &quot;.bin&quot;;</li>
	<li>Open the drive options (EAC -&gt; Drive Options or press F10);</li>
	<li>Select the first tab (&quot;Extraction Method&quot;) and click on &quot;Detect Read Features&quot;;</li>
	<li>EAC should now start detecting the features of your drive;</li>
	<li>Select &quot;Secure Mode&quot; (on the same tab);</li>
	<li>Click on the tab &quot;Gap Detection&quot; and set the detection accuracy to &quot;Secure&quot;;</li>
	<li>Set the Gap/Index retrival method to &quot;Method A&quot;. If you have problems detecting the gap, try changing to B or C (see &quot;Ripping the audio tracks&quot;);</li>
	<li>Change to the &quot;Offset / Speed&quot; tab and enable &quot;overread into lead-in and lead-out&quot;;</li>
	<li>Look up the read offset value of your drive in the Accuraterip database: <a href="http://www.accuraterip.com/driveoffsets.htm">http://www.accuraterip.com/driveoffsets.htm</a>;</li>
	<li>Write down the value for your drive, as we will use it later on in the guide.</li>
</ul><br />
<p>The above steps have to be done only once!</p>
</div>

<h3>Determining the pregap</h3>
<div class="textblock">
<ul>
	<li>Insert the CD you want to rip (make sure the disc has a clean surface);</li>
	<li>Press F4 (or select Action -&gt; Detect Gaps);</li>
	<li>EAC will now detect gaps between the tracks.</li>
</ul>
<p><br />
<img src="/images/dumpingguide/eac-pregap.PNG" alt="" /><br />
&nbsp;</p>
<p>You can see that in this picture, EAC detected a Track02 pregap of 4 seconds. 
In most cases the pregap will be 2 seconds.</p><br />
<p>Each second of audio data equals 176400 bytes, so a pregap of 2 seconds is 
2*176400 = 352800 bytes.</p>
<br />
<p>Each second also equals 75 sectors, so a pregap of 2 seconds is 150 sectors. 
A pregap of 1.74 seconds is 149 sectors.</p>
<br />
Now that you know the Track02 pregap length, go to the next step.</p></div>

<h3>Determining the combined offset</h3>
	<div class="textblock">
<p><b>Note:</b> Plextor users can skip this section and use a different (faster and more reliable) method instead: <a href="http://forum.redump.org/viewtopic.php?id=2468">http://forum.redump.org/viewtopic.php?id=2468</a>. However, you will still need the sector number for extracting the data track, so find it in the next step.</p><br />
<p>To determine the combined offset we will use IsoBuster to browse 
to the relevant cd sector:</p><br />
<ul>
	<li>Insert the CD you want to rip (make sure the disc is clean);</li>
	<li>Start IsoBuster;</li>
	<li>Make sure that the correct CD Reader is selected;</li>
	<li>In the track list in the left column, click on Track 02 with right mouse 
	button and select 'Sector View';</li>
	<li>Make sure that the 'RAW' checkbox is enabled;</li>
	<li>If the Track02 pregap was 2 seconds, or 150 sectors, first go back 
	(pregap -1) sectors = 149 sectors 
	(substract 149 from from the number in the white box);</li>
	<li>Now use the arrow to go back one more sector (if you go back 150 right 
	away there's a chance the data won't show). If everything is alright, 
	your screen will look similar to this:</li>
</ul>
<p>&nbsp;</p>
<p><img src="/images/dumpingguide/isobuster-scrambled.PNG" alt="" /></p>
<p><br />
Now you should get a number of rows that show (scrambled) 
binary data, followed by rows of zeroes. It is also possible that the last 
row of data is not filled completely, but is partly zeroed.</p>
<p>&nbsp;</p>
<p>Each row is 16 bytes, so if we have 8 full rows (like in the screenshot above) before the zeroes, this means we have 8*16 = 128 bytes. 
</p>
<p><br />
The amount of data in bytes has to be divided by 4 to get the amount of samples, so in our screenshot it's 128 bytes of data /4 = 32 samples = +32. This is the combined offset value that we will use in EAC for ripping the audio tracks.<br />
&nbsp;</p>
<blockquote style="white-space: normal;">
	<p><b>Note 1:</b> It can happen that the sector shows no data at all, but only zeroes. If you are 100% sure that you are reading the correct sector and it shows no 
	scrambled data (and going forth and back one sector also doesn't help),&nbsp; it's best to retry the 
	audio ripping part 
	using a different drive, but this time try using 
	a drive with a bigger read offset (a list of read offsets for each drive can 
	be viewed here: http://www.accuraterip.com/driveoffsets.htm).<br />
&nbsp;</p>
	<p><b>Note 2:</b> It is also possible that the sector is full of data. A full sector contains 2352 bytes of data. If the first sector is full of data, browse on to the next sector(s) and make sure all data is counted until you reach the end of the data (start of zeroes).</p></blockquote>
<p><br />Write down the sector number of the sector containing the scrambled data, as we'll be using this soon number for dumping the data track (in case there were multiple sectors, write down the first one).
<p><br />Before we change the offset value in EAC to the combined offset one that we just determined, we will first calculate the write offset value.<br />
<br />
The combined offset can be split up in 2 parts: the read offset and the write offset. The write offset can vary for each disc, but the read offset will remain constant, as long as you’re dumping from the same drive.<br />
<br />
The write offset value will be used for documentation purposes and needs to be supplied with your dump. To find it, we need to subtract the read offset from the combined offset. We already found the read offset while setting up EAC, so we can now subtract it from the combined offset:<br />For example, if reading the sector gives you +32 samples (or 128 bytes 
(=8 rows) of data) and the read from the Accuraterip database is +30, 
then the factory write offset is: +32 = (?? + 30) &gt; ?? = <b>+2</b>.<br /><br />
</p>
<p>In the next step, we will enter the combined offset in EAC.</p>
</div>

<h3>Changing the offset in EAC</h3>
<div class="textblock">
<p>Start EAC and change the offset to the combined offset value that we obtained in the previous section:</p>
<br />
<ul>
	<li>Open the drive options (EAC -&gt; Drive Options or press F10);</li>
	<li>Change to the "Offset / Speed" tab and change the value in the 'Read sample offset correction value' to the new value. For example, if the combined offset is 32 samples, the value that we enter here is '+32';</li>
	</ul>
<br /><p><img src="/images/dumpingguide/eac2-offset.PNG" alt="" style="width: 418px; height: 183px;" /></p>
<br /><p>Now we're finally ready to start ripping the audio tracks.</p>
</div>

<h3>Dumping the audio tracks with EAC</h3>
<div class="textblock">
<ul>
	<li>Insert the CD you want to rip (make sure the disc has a clean surface);</li>
	<li>Press F4 (or select Action -&gt; Detect Gaps);</li>
	<li>EAC will now detect gaps between the tracks;</li>
	<li>Select Action -&gt; Append gaps to next track;</li>
	<li>Select the tracks you want to rip;</li>
	<li>Press Shift + F6 (or select Action -&gt; Test &amp; Copy selected tracks -&gt; compressed from the menu);</li>
	<li>Select a directory to rip the files to and press ok;</li>
	<li>EAC will now start ripping the selected tracks. When the extraction is complete, a 'Status and Error Messages' window will appear;</li>
	<li>Be sure to click the 'Create Log', as this .log file is mandatory information!;</li>
	<li>When ripping is done, the Read and Write CRC columns should contain equal CRC numbers!;</li>
	<li>After extracting, create a cuesheet. Do this by selecting Action -&gt; Create CUESheet -&gt; Current Gap Settings.</li>
</ul>
</div>

<h3>Dumping the data track with IsoBuster (for discs with with audio tracks)</h3>
<div class="textblock">
<ul>
	<li>Start IsoBuster;</li>
	<li>Right mouse button on Track 01 -&gt; Extract From-To</li>
	<li>In the Length (LBA) box, enter the sector number of the sector containing the scrambled data that you obtained earlier.</li>
	<li>Click 'Start Extraction' and save it as 'Track 01.iso'.</li>
</ul>
<br />
<img src="/images/dumpingguide/isobuster2.PNG" alt="" style="width: 585px; height: 246px;" /><br />
<br /> 
If you get an 'Unreadable sector' error, make sure that the disc is free of scratches.<br />
<br />
<br /><p>When the extraction is complete, you will have an image file of the data track.</p>
</div>

<h3>Checking and repairing the postgap (PSX only)</h3>
<div class="textblock">
We now have an image of the data track that is ready for error checking and repairing. For this step we will use the 'psxt001z' tool by Dremora. This tool will only work with PSX images.
<br /><br />Psxt001z requires you to work in Command Prompt.<p>Make sure that the psxt001z.exe file is in the same folder as the Data track image, and use the following command:</p>
<br /><blockquote><p>psxt001z.exe --fix "Track 01.iso"</p></blockquote><br />
<p>If the disc that you are trying to dump has no audio tracks, we are almost ready. You can go to the part 'Final Steps'.</p>
</div>

<h3>Final steps</h3>
<div class="textblock">
<p>After dumping, you will end up with the following files:</p>
<ul>
	<li>Data track and audio track binaries, properly dumped using the guide;</li>
	<li>Cuesheets and .log file(s) created by EAC (only for games with audio tracks).</li>
</ul>
<br /><p>You can now help our project by supplying us with the information about the dump.</p>
<p>The information that we need is specified in the next paragraph.</p>
<br /><p>If you need any additional help, contact details can be found on this site in the 'Site' menu.</p>
<p>Feel free to idle or ask for any help in our IRC channel: ForeverChat #redump.</p>
</div>

<h3>Mandatory information</h3>
<div class="textblock">
<ul>
<li>Game title (<i>Including subtitle</i>);</li>
<li>Disc titles (<i>Only if the game consists of multiple discs and if the 
titles on the discs are different, or if it's a compilation and every disc is a seperate game</i>);</li>
<li>Disc ID / Serial (not for IBM discs) (<i>E.g. "SLUS-12345"</i>);</li>
<li>EXE date in YY-MM-DD format (not for IBM discs) (<i>Date of EXE file, e.g. "SLUS_123.45" (or any other file which's name is located in SYSTEM.CNF file). Date should be looked in IsoBuster, but be sure to turn on option "<b>Options -> File System Setting -> General -> Time Stamps -> Display time stamp -> Local time stamp</b>"</i>);</li>
<li>Game languages;</li>
<li>LibCrypt output (PSX only) (use <a href="/guide/libcrypt/">this guide</a> for LibCrypt protection checking);</li>
<li>Size, CRC-32, MD5 and SHA-1 checksums of all tracks (<i>All track should be dumped 
<b>twice</b> to make sure they were dumped correctly! Checksums you can get using HashCalc, DAMN Hash Calculator or ClrMamePro, and size using Windows Explorer/Total Commander/FAR, but 
<b>not IsoBuster!</b></i>);</li>
<li>Factory write offset value.</li>
</ul>
</div>
end_delimiter;

?>

