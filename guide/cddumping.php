<?php

$author_id  = 8;
$title      = 'CD dumping guide';
$created    = '2007-01-01';
$lastupdate = '2008-07-08';
$language   = 'eng';
$contents   = <<<end_delimiter

<div class="textblock" style=" background: #febaba; padding: 10px;">
<span style="font-size: 22px; font-weight: bold;">Please dump all tracks at least twice (if possible, using different drives) to be sure that they are correct!
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
	<li><a href="/download/resize.rar">Resize</a></li>
	<li><a href="/download/psxt001z-0.21b1.7z">psxt001z</a> (PSX 
	only)</li>
</ul>
</div>

<h3>Determining disc type</h3>
<div class="textblock">
<p>Before we dump the disc, we need to know if it contains any audio tracks.<br />
<br />
Insert the disc into the drive and launch IsoBuster.</p>
<br />
<p>A disc with audio tracks will look as follows (notice how the audio track icon differs from the data track one):<br /><br />
<img src="/images/dumpingguide/isobuster1.PNG" alt="" style="width: 341px; height: 354px;" /><br />
<br />A disc with audio tracks will unfortunately take a bit longer to dump, because audio tracks require a different treatment than data tracks.</p>
</div>

<h2>Ripping the data track</h2>
<h3>Ripping the data track using IsoBuster</h3>
<div class="textblock">
<ul>
	<li>Start IsoBuster;</li>
	<li>Insert the disc (If you have multiple drives with cd's inserted, make sure you select the right one);</li>
	<li>Right mouse button on Track 01 -&gt; Extract Track 01 -&gt; Extract RAW Data (2352 bytes/block) (*.bin, *.iso);</li>
	<li>Choose a destination folder.</li>
</ul>
<br /><p>The data will now be extracted.<br /><br />If you get 'Unreadable sector' errors at the end of the track (this is common 
for discs with audio tracks), pick the option 'Replace with User Data All zeroes' for all unreadable sectors.</p><br />
<p>
<img src="/images/dumpingguide/isobuster2-error1.PNG" alt="" style="width: 264px; height: 327px;" /></p>
<p><br />Errors should only occur at the very end of the track (99%-100% extraction). 
If the error occurs earlier, make sure that the disc is free of scratches.<br />
<br />After extraction, if it asks if you want to delete the file, choose 'No'.</p><br />
<p>
<img src="/images/dumpingguide/isobuster2-error2.PNG" alt="" style="width: 238px; height: 204px;" /></p>
<br /><p>When the extraction is complete, you will have an image file of the data track.</p><br />
<p>If the disc contains audio tracks, go to the next part called 'Fixing the pregap'.</p><br />
<p>If the disc only contains a data track, you are ready to check the image for errors. Head on to the step called 'Checking and repairing the data track'.</p>
</div>

<h3><b>Fixing the pregap</b></h3>
<div class="textblock">
<p>If the disc has audio tracks, we will have to do some more steps to get the 
data track down to the right size.<br /><br />Because IsoBuster is unable to detect where the audio track starts, it will 
add a part from the first audio track to the end of the data track.<br />
<br />
In order to remove the required amount of bytes from the end of the track, it is neccesary 
to determine the length of the Track02 pregap.<br />This can be done using Exact Audio Copy 
(this is also the tool that's we will use to extract the audio tracks).<br />
<br />
Before we can determine the pregap length, EAC needs to be set up properly. Go 
to the next step called 'Setting up EAC the first time'.</p>

</div>

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

<h3>Determining pregap length</h3>
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
In most cases the pregap will be 2 seconds.<br />
Each second of audio data equals 176400 bytes, so a pregap of 2 seconds is 
2*176400 = 352800 bytes.</p>
<p>Each second also equals 75 sectors, so a pregap of 2 seconds is 150 sectors. 
A pregap of 1.74 seconds 149 sectors.<br />
<br />
Now that you know the Track02 pregap length, go to the next step.</p></div>

<h3>Removing the pregap using Resize</h3>
<div class="textblock">
To remove the Track02 pregap from Track 01, we will use a tool called 'Resize'<br />
<br />
To use this tool, download it and extract it in the same folder as the 'Track 01.bin' file. Resize.com requires you to work in Command Prompt.<p>If 
EAC reported a pregap of 2 seconds = 352800 bytes and the filename of the image is 'Track 01.bin', the command would be as follows:</p>
<br /><blockquote><p>RESIZE -r -352800 "Track 01.bin"</p></blockquote><br />
<p><img src="/images/dumpingguide/resize1.PNG" alt="" style="width: 356px; height: 91px;" /></p>
<p><br />When the correct amount of bytes is removed from the end of the image, the data track image is ready for error checking and repairing. Go to the next step called 'Checking and repairing the data track'.</p>

</div>

<h3>Checking and repairing the postgap (PSX only)</h3>
<div class="textblock">
We now have an image of the data track that is ready for error checking and repairing. For this step we will use the 'psxt001z' tool by Dremora. This tool will only work with PSX images.
<br /><br />Psxt001z requires you to work in Command Prompt.<p>Make sure that the psxt001z.exe file is in the same folder as the Data track image, and use the following command:</p>
<br /><blockquote><p>psxt001z.exe --fix "Track 01.bin"</p></blockquote><br />
<p>If the data track was ripped from a disc with audio tracks, you will receive a message similar to this 
(it is also possible that some sectors are fixed, this is normal for discs with 
audio tracks):</p><br />
<p><img src="/images/dumpingguide/psxt001z1.PNG" alt="" style="width: 340px; height: 407px;" /></p>
<br /><p>If the disc that you are trying to dump has no audio tracks, we are almost ready. You can go to the part 'Final Steps'.</p>
<br /><p>If the disc contains audio tracks, you can start ripping the audio tracks. So 
if you are ready, continue to the next part 'Ripping the audio tracks'.</p>
</div>

<h2>Ripping the audio tracks</h2>

<h3>Determining the (factory) write offset</h3>
	<div class="textblock">
<p><b>Note:</b> Plextor users can skip this section and use a different (faster, easier and more flexible) method instead: <a href="http://forum.redump.org/viewtopic.php?id=2468">http://forum.redump.org/viewtopic.php?id=2468</a></p><br />
<p>To determine the factory write offset value we will use IsoBuster to browse 
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
<p>Each row is 16 bytes, so if we have 8 full rows (like in the screenshot above) that are filled with data (and not just zeroes) this means 
we have 8*16 = 128 bytes. 
</p>
<p><br />
The amount of data in bytes has to be divided by 4 to get the amount of samples, so in our screenshot it's 128 bytes of data /4 = 32 samples. This is the offset value that we will use 
in EAC for ripping the audio tracks.<br />
&nbsp;</p>
<blockquote style="white-space: normal;">
	<p><b>Note 1:</b> It can happen that the sector shows no data at all, but only zeroes. If you are 100% sure that you are reading the correct sector and it shows no 
	scrambled data (and going forth and back one sector also doesn't help),&nbsp; it's best to retry the 
	audio ripping part 
	using a different drive, but this time try using 
	a drive with a bigger read offset (a list of read offsets for each drive can 
	be viewed here: http://www.accuraterip.com/driveoffsets.htm). To be able to 
	detect all offsets, most dumpers in our project bought a cheap CDRW drive 
	with a large read offset, such as the Sony CRX-100E or 120E.<br />
&nbsp;</p>
	<p><b>Note 2:</b> It is also possible that the sector is full of data. A full sector contains 2352 bytes of data. If the first sector is full of data, browse on to the next sector(s) and make sure all data is counted until you reach the end of the data (start of zeroes).</p>
</blockquote>
<p><br />
Now we will change the offset value in EAC to the 
one that we just determined. Before we do that, we will 
first calculate the factory write offset.<br />
<br />
The offset value that we just retreived from the sector and that is used in EAC 
to dump the audio tracks is the combined read+write offset. However, for 
documenting purposes we need you to supply us the write offset value alone, so 
the read offset value needs to be substracted from the combined offset value.<br />
<br />
In order to do this we will need the Read offset value of your drive (detected 
in EAC or taken from the accuraterip drive list). Once you know this value, it's 
possible to calculate the factory write offset value:<br />For example, if reading the sector gives you +32 samples (or 128 bytes 
(=8 rows) of data) and the read from the Accuraterip database is +30, 
then the factory write offset is: +32 = (?? + 30) &gt; ?? = <b>+2</b>.<br /><br />
</p>
<p>Now we are finally ready to change the offset value in EAC and start dumping 
the audio tracks. Please make sure that you use the offset value that was calculated using the 
amount of data in the sector for EAC and not the factory write offset value!</p>
</div>

<h3>Changing the offset in EAC</h3>
<div class="textblock">
<p>Start EAC and change the offset to the proper value:</p>
<br />
<ul>
	<li>Open the drive options (EAC -&gt; Drive Options or press F10);</li>
	<li>Change to the "Offset / Speed" tab and change the value in the 'Read sample offset correction value' to the new value. A value of 32 samples (amount of data in the sector) earlier means entering '+32' in EAC;</li>
	<li>The value should always be positive or EAC may cut off data! <b>Therefore tracks should always be dumped on the drives that show data in the previous step!</b></li>
</ul>
<br /><p><img src="/images/dumpingguide/eac2-offset.PNG" alt="" style="width: 418px; height: 183px;" /></p>
<br /><p>Now we're finally ready to start ripping the audio tracks.</p>
</div>
	<h3>Ripping audio tracks with EAC</h3>
<div class="textblock">
<ul>
	<li>Insert the CD you want to rip (make sure the disc has a clean surface);</li>
	<li>Press F4 (or select Action -&gt; Detect Gaps);</li>
	<li>EAC will now detect gaps between the tracks;</li>
	<li>If you think the gaps that you're getting are strange, clean the disc and try again;</li>
	<li>Select Action -&gt; Append gaps to next track;</li>
	<li>Select the tracks you want to rip;</li>
	<li>Press Shift + F6 (or select Action -&gt; Test &amp; Copy selected tracks -&gt; compressed from the menu);</li>
	<li>Select a directory to rip the files to and press ok;</li>
	<li>EAC will now start ripping the selected tracks. When the extraction is complete, a 'Status and Error Messages' window will appear;</li>
	<li>Be sure to click the 'Create Log', as this .log file is mandatory information!;</li>
	<li>When ripping is done, the Read and Write CRC columns should contain equal CRC numbers!;</li>
	<li>After extracting, create a cuesheet. Do this by selecting Action -&gt; Create CUESheet -&gt; Current Gap Settings.</li>
</ul>
<p><br />
Now we are almost ready. Head on to the 'Final steps'.</p></div>

<h3>LibCrypt (PSX only)</h3>
<div class="textblock">
<p>For LibCrypt protection checking please see <a href="/guide/libcrypt/">this guide</a>.</p>
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
<li>Size, CRC-32, MD5 and SHA-1 checksums of all tracks. (<i>All track should be dumped 
<b>twice</b> to make sure they were dumped correctly! Checksums you can get using HashCalc, DAMN Hash Calculator or ClrMamePro, and size using Windows Explorer/Total Commander/FAR, but 
<b>not IsoBuster!</b></i>);</li>
<li>Factory write offset value.</li>
</ul>
</div>
end_delimiter;

?>