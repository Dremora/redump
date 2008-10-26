<?php

$author_id  = 8;
$title      = 'PlayStation: dumping guide';
$created    = '2007-01-01';
$lastupdate = '2007-10-28';
$language   = 'eng';
$contents   = <<<end_delimiter

<div class="textblock" style=" background: #febaba; padding: 10px;">
<span style="font-size: 24px; font-weight: bold;">Please dump all tracks at least twice (if possible, using different drives)<br />to be sure that they are correct!
</span>
</div>

<h2>Information we need to add disc images to the database</h2>

<h3>Mandatory information</h3>
<div class="textblock">
<ul>
<li>Game title (<i>Inclulding subtitle</i>);</li>
<li>Disc IDs (<i>E.g. "SLUS-12345"</i>);</li>
<li>Disc labels (<i>Only if game has many discs, and they are titled, e.g. "Allies disc" or "GT Mode Disc"; or it's a compilation, and every disc contains seperate game, e.g. "Final Fantasy V"</i>);</li>
<li>EXE dates (<i>Date of EXE file, e.g. "SLUS_123.45" (or any other file which's name is located in SYSTEM.CNF file). Date should be looked in IsoBuster, but be sure to turn on option "<b>Options -> File System Setting -> General -> Time Stamps -> Display time stamp -> Local time stamp</b>"</i>);</li>
<li>Game languages;</li>
<li>Size, CRC-32, MD5 and SHA-1 checksums of all tracks. (<i>All track should be dumped <b>twice</b> to make sure they were dumped correctly! Checksums you can get using HashCalc, DAMN Hash Calculator or ClrMamePro, and size using Windows Explorer/Total Commander/FAR, but <b>not IsoBuster!</b></i>)</li>
<li>Game languages;</li>
</ul>
</div>

<h3>Mandatory information (for discs containing audio tracks)</h3>
<div class="textblock">
<ul>
<li>Cuesheet from EAC;</li>
<li>.log-file from EAC.</li>
<li>Cuesheet from Cdrwin;</li>
</ul>
</div>

<h3>Mandatory information (for PSX games only)</h3>
<div class="textblock">
<ul>
<li>Presence of EDC in Form 2 Mode 2 sectors (<i>EDC is not present if last four bytes in sector 12 contain zeroes, otherwise it is</i>);</li>
<li>Several dumps of disc subchannel data;</li>
</ul>
</div>

<h3>Mandatory information (for PS2 games only)</h3>
<div class="textblock">
<ul>
<li>File <b>SYSTEM.CNF</b>.</li>
</ul>
</div>

<h3>Optional information</h3>
<div class="textblock">
<ul>
<li>Front and back cover, manual, CD scans;</li>
<li>Barcode;</li>
<li>Game screenshots, including title screen.</li>
<li>If you want, you can also upload game image (contact us to get access to ftp).</li>
</ul>
</div>

<h2>Dumping guide</h2>

<h3>Introduction</h3>
<div class="textblock">
<p>This guide will explain how to preserve CD-based games in the best possible way.</p>
<p>WARNING: This guide is for experienced users. If you get stuck somewhere or need help, feel free to contact us.</p>
<p>Contact details can be found on this site in the 'The site' menu.</p>
<p>Feel free to idle or ask for any help in our IRC channels on ForeverChat #psxdb for English, NewNet #emu-russia for Russian users.</p>
</div>

<h3>Software needed</h3>
<div class="textblock">
<ul>
	<li><a href="http://www.smart-projects.net/">IsoBuster 1.9.1 from Smart Projects</a></li>
	<li><a href="http://www.exactaudiocopy.de">ExactAudioCopy V0.95 beta 4</a></li>

	<li><a href="http://www.goldenhawk.com/">Cdrwin 4.0</a></li>
	<li><a href="/download/resize.rar">Resize</a></li>
	<li><a href="http://soft.dremora.com/psxt001z-020b13fix.7z">psxt001z</a></li>
</ul>
</div>

<h3>Determining disc type</h3>
<div class="textblock">
<p>Before we start on anything, we will first determine what type of disc we are trying to dump. There are 
three types of discs:</p>
<ol>

	<li>Data track only.</li>
	<li>Data and audio tracks where the audio tracks are not linked to the data 
	track.</li>
	<li>Data and audio tracks where the audio tracks are linked in the ISO 
	structure of the data track.</li>
</ol>
<p>Type 2 and 3 will be dumped the same way, so it doesn't matter if they are 
linked or not.<br />
<br />
To determine which type the disc is, we can insert the disc into the drive and 
launch IsoBuster or Cdrwin.</p>
<ul>
	<li>When using Cdrwin, go to (Extract Disc/Tracks/Sectors);</li>

	<li>Select the drive that has the disc inserted;</li>
	<li>Determine the disc type by looking at the screenshots.</li>
</ul>
<table><tr>
	<td><img src="/images/guide/cdrwin1-data.PNG" alt="" style="width: 187px; height: 211px; margin: 3px;" /></td>
	<td><img src="/images/guide/cdrwin1-audio.PNG" alt="" style="width: 187px; height: 211px; margin: 3px;" /></td>
</tr>
<tr>
	<th>Cdrwin: Data track only</th>
	<th>Cdrwin: Data and audio tracks</th>
</tr></table>
<p>When using IsoBuster, a disc with data and audio tracks will look as 
	follows:<br /><br />
<img src="/images/guide/isobuster1.PNG" alt="" style="width: 341px; height: 354px;" /></p>
<p>Now we know what type of disc we are trying to dump. A disc with data and audio tracks will unfortunately take a lot longer to dump, because it involves more steps.</p>
</div>

<h2>Ripping the data track</h2>
<h3>Method 1: Using IsoBuster</h3>
<div class="textblock">
<ul>
	<li>Start IsoBuster;</li>
	<li>Insert the disc (If you have multiple drives with cd's inserted, make sure you select the right one);</li>
	<li>Right mouse button on Track 01 -&gt; Extract Track 01 -&gt; Extract RAW Data (2352 bytes/block) (*.bin, *.iso);</li>

	<li>Choose a destination folder.</li>
</ul>
<p>The data will now be extracted.<br />If you get 'Unreadable sector' errors at the end of the track (this is common 
for PSX discs with audio tracks), pick the option 'Replace with User Data All 
zeroes' for all unreadable sectors. If there are unreadable sectors, extraction 
can take some time.</p>
<p><img src="/images/guide/isobuster2-error1.PNG" alt="" style="width: 264px; height: 327px;" /></p>
<p>If you get errors on other places than at the end (99%-100% extraction), this 
propably means that the disc is damaged/scratched and not suitable for dumping!<br />When extraction is ready, if it asks you if you want to delete the file. Choose 'No'.</p>
<p><img src="/images/guide/isobuster2-error2.PNG" alt="" style="width: 238px; height: 204px;" /></p>
</div>

<h3>Method 2 - Using Cdrwin</h3>
<div class="textblock">

<ul>
	<li>Start Cdrwin;</li>
	<li>Click on the third icon from the left (Extract Disc/Tracks/Sectors);</li>
	<li>Select "Select Tracks";</li>
	<li>Select your drive in the "CD Reader" box;</li>
	<li>Enter a filename for the image (if you choose a different filename, be sure to use this filename where the guide says 'Track 01.bin')</li>
	<li>Make sure that "RAW" is selected;</li>

	<li>Set error recovery to 'Ignore' (if the disc has no audio tracks, set this option to 'Abort'!);</li>
	<li>Click on "START" to begin the ripping process.</li>
</ul>
<p><img src="/images/guide/cdrwin2-settings.PNG" alt="" style="width: 582px; height: 475px;" /></p>
</div>

<h3>After ripping the data track</h3>
<div class="textblock">

<p>When the extraction is complete using one of the methods, you will have an image file of the data track.</p>
<p>If the disc that you are ripping contains audio tracks, go to the next part called 'Fixing the pregap'.</p>
<p>If the disc has a data track only, you are ready to check the image for errors. Skip to the step called 'Checking and repairing the data track'.</p>
</div>

<h3><b>Fixing the pregap</b></h3>
<div class="textblock">
<p>If the disc has audio tracks, we will have to do some more steps to get a proper dump of the data track.</p>
<p>IsoBuster and Cdrwin add a part from the 2nd track at the end of the Track 01 image file. This is not good.</p>
<p>We can fix this by removing an amount of bytes at the end of the track with a special tool. To determine how many bytes have to be removed, it is neccesary to determine the pregap length. We will do this by generating a cuesheet with Cdrwin. 
This action is similar to the previous step if you used Cdrwin to extract the 
data track. Follow the next steps:</p>

</div>

<h3>Determining the pregap length</h3>
<div class="textblock">
<ul>
	<li>Start Cdrwin;</li>
	<li>Click on the third icon from the left (Extract Disc/Tracks/Sectors);
	</li>
	<li>Make sure you select the correct CD Reader;</li>
	<li>For Extract Mode, choose Disc Image/Cuesheet (this should be the default setting);</li>

	<li>Choose a destination filename and location in the 'Image Filename' box;</li>
	<li>Press START.</li>
</ul>
<p>After analysing the disc layout, it will start reading the sectors. It is safe to cancel this process, because we only need the .cue file. The .cue file can be found in the destination folder.</p>
<p>Close Cdrwin and open up the .cue with Notepad. You will see a segment of text similar to this one:</p>
<blockquote><p>  TRACK 02 AUDIO
    FLAGS DCP
    PREGAP 00:02:00
    INDEX 01 39:35:69
</p></blockquote>
<p>The PREGAP line is what we need, because this line tells us how many bytes we need to remove from the Track 01.bin image that we ripped earlier.</p>
<p>A pregap of 00:02:00 means 2 seconds. 2 seconds stands for 352800 bytes. This 
is a common value for PSX discs with audio tracks.</p>

<p>To remove the required bytes (for 2 second pregap it's 352800 bytes) from the image, we will use the 'Resize' tool (see start of guide for download link). It is also possible to use other tools like hex editors for this operation, as long as you make sure that the correct amount of bytes is removed from the end of the image.</p>
</div>

<h3>Removing the pregap using Resize</h3>
<div class="textblock">
<p>To use the Resize tool, extract it in the same folder as the 'Track 01.bin' file. Resize.com requires you to work in Command Prompt.</p>
<p>If the pregap in the cuesheet was 2 seconds = 352800 bytes and the filename of the image is 'Track 01.bin', the command would be as follows:</p>
<blockquote><p>RESIZE -r -352800 "Track 01.bin"</p>
<p><img src="/images/guide/resize1.PNG" alt="" style="width: 356px; height: 91px;" /></p></blockquote>
<p>When the correct amount of bytes is removed from the end of the image, the data track image is ready for error checking and repairing. Go to the next step called 'Checking and repairing the data track'.</p>

</div>

<h3>Checking and repairing the data track</h3>
<div class="textblock">
<p>We now have an image of the data track that is ready for error checking and repairing. For this step we use the tool 'psxt001z' by Dremora. This program requires you to work in Command Prompt.</p>
<p>Make sure that the psxt001z.exe file is in the same folder as the Data track image, and use the following command:</p>
<blockquote><p>psxt001z.exe --fix "Track 01.bin"</p></blockquote>
<p>If the data track was ripped from a disc with audio tracks, you will receive a message similar to this 
(it is also possible that some sectors are fixed, this is normal for discs with 
audio tracks):</p>
<p><img src="/images/guide/psxt001z1.PNG" alt="" style="width: 340px; height: 407px;" /></p>
<p>If the disc that you are trying to dump has no audio tracks, we are almost ready. You can go to the part 'Final Steps'.</p>
<p>If the disc contains audio tracks, you can start ripping the audio tracks. So 
if you are ready, continue to the next part 'Ripping the audio tracks'.</p>
</div>

<h2>Ripping the audio tracks</h2>

<h3>Setting up EAC the first time</h3>
<div class="textblock">
<ul>
	<li>Start EAC (with no disc in drive, otherwise it might crash);</li>
	<li>Cancel the configuration wizard (if there is one);</li>

	<li>Select the drive you want to use for ripping in the combobox;</li>
	<li>Open the EAC Options (EAC -&gt; EAC Options or press F9);</li>
	<li>Select the "Tools" tab and deselect beginner mode;</li>
	<li>Select the "Extraction" tab. For "Error recovery quality" choose High;</li>
	<li>Open the compression options (EAC -&gt; Compression Options or press F11);</li>

	<li>Select the "Waveform" tab. For "Wave format" choose "Microsoft PCM Convertor";</li>
	<li>For "Sample format", make sure that "44,100 kHz; 16 Bit; Stereo" is selected;</li>
	<li>In the same tab, make sure that "Do not write WAV header to file" is checked;</li>
	<li>Also make sure that "High quality (slow) is selected";</li>
	<li>In the "File extension for headerless files" box, enter ".bin";</li>
	<li>Open the drive options (EAC -&gt; Drive Options or press F10);</li>

	<li>Select the first tab ("Extraction Method") and click on "Detect Read Features";</li>
	<li>EAC should now start detecting the features of your drive;</li>
	<li>Select "Secure Mode" (on the same tab);</li>
	<li>Click on the tab "Gap Detection" and set the detection accuracy to "Secure";</li>
	<li>Set the Gap/Index retrival method to "Method A". If you have problems detecting the gap, try changing to B or C (see "Ripping the audio tracks");</li>
	<li>Go to http://www.exactaudiocopy.de/eac3.html and scroll down to the list 
	of audio cds. Search some you have and insert one into your drive.</li>

	<li>Change to the "Offset / Speed" tab and enable "overread into lead-in and lead-out".</li>
	<li>Click on "Detect read sample offset correction". EAC tries to recognize the cd now according to its database. If it says "CD 
	not found in database", try another until EAC finds one. <br />
	This Step is very important, you cannot create exact audio rips without 
	offset correction!</li>
	<li>If you cannot set your offset correction using the above method, go to 
	http://www.accuraterip.com/driveoffsets.htm and use the offset 
	given there for your drive (if listed). PLEASE only use this list if the 
	above fails for you however.</li>
	<li>The offset value that is correct for your drive has to be corrected with 
	-30, so for example: +48 will become +18!</li>
</ul>
<p>The above steps have to be done only once!</p>

<p>Before we can start ripping the audio tracks, we first need to go to the next 
part 'Determining the disc's offset'</p>
</div>

<h2>Determining the disc's offset</h2>
<div class="textblock">
<p>If your disc has audio tracks, this is a very important step!<br />
<br />
First if you have EAC still running, it's best to shut it down first.<br />
<br />
We now need a tool that allows us to read the sectors on a cd.
In this guide we will use Cdrwin for this purpose, but other applications like 
IsoBuster 
should work just as good, as long as you are sure that the right sector is 
checked!</p>
</div>

<h3>Finding the right sector with Cdrwin</h3>
<div class="textblock">
<ul>
	<li>Insert the CD you want to rip (make sure the disc is clean); </li>
	<li>Start Cdrwin;</li>
	<li>Click on the first icon from the left of the bottom row (Table of Contents);</li>
	<li>Make sure you select the correct CD Reader;</li>
	<li>Write down the number that is displayed in the LBA column for Track 02.<br /><img src="/images/guide/cdrwin3-toc.PNG" alt="" style="width: 384px; height: 200px;" /></li>
	<li>This number has to be lowered with the amount of sectors of the pregap. We already determined the pregap length in a previous step. If the pregap was 2 seconds or 352800 bytes, the amount of sectors is 352800/2352 = 150. So for example, if Track 02 starts at LBA 088745, and the pregap is 150 sectors (2 seconds), the first sector of Track02 pregap would be at 088745 - 150 = 088595. </li>
</ul>
<p>Remember the number that you calculated, as this is the location of the sector that we want to watch.</p>
</div>

<h3>Determining offset with Cdrwin</h3>

<div class="textblock">
<ul>
	<li>Now go back to Cdrwin main window and click on the third icon from the left on the bottom row (Sector Viewer);</li>
	<li>The correct CD Reader should still be selected;</li>
	<li>Fill in the number in the sector field (in the example it was 088595) and press 'Read Sector'. If everything is alright, you should get a number of rows that show Binary (garbage) data, followed by rows of zeroes. It is also possible that the last row of data is not filled completely, but is partly zeroed.</li>
</ul>
<p><img src="/images/guide/cdrwin3-garbage.PNG" alt="" style="width: 578px; height: 483px;" /></p>
<p>Each row is 16 bytes, so if we have 8 full rows (like in the screenshot above) that are filled with data (and not just zeroes) this means the offset is 8*16 = 128 bytes. </p>

<p>Another way to determine the amount of data in bytes is by looking at the 
offset number in the left column. In the example screenshot the row with offset 
0070 is completely filled with data, so we pick the offset number from the next 
row = 0080. Now we can use windows calculator (in scientific mode) to convert 
this value (0080) from HEX to DEC. This also gives us a value of 128 bytes.</p>
<p>The amount of data in bytes has to be divided by 4 to get the amount of samples 
(that we use as EAC value), so in our screenshot it's 128 bytes of data /4 = 32 samples. This is the offset value that we will use 
for ripping in EAC.</p>
<blockquote style="white-space: normal;">
	<p><b>Note 1:</b> It can happen that the sector shows no data at all, but only zeroes. If you are 100% sure that you are reading the correct sector and it shows no rows of data, it's best to retry the guide (starting at the section 'Ripping the audio tracks') but with a different drive. If you DON'T get any data in the sector, try using a drive with a bigger offset. A list of offsets for each drive, as mentioned before:&nbsp; http://www.accuraterip.com/driveoffsets.htm. If you don't have any other drives with larger offset, or this drive also gives you only zeroes in the sector, skip to the next step 'Ripping audio tracks with EAC' and make sure <br />that the offset in EAC is set to the original value that we determined in the step 'Setting up EAC'. If you DO get data in the sector, please ignore this note and read on to the next sentence.<br /><br /><b>Note 2:</b> It is also possible that the sector is full of data. A full sector contains 2352 bytes of data. If the first sector is full of data, browse on to the next sector(s) and make sure all data is counted until you reach the end of the data (start of zeroes).</p>
</blockquote>

<p>If you got data in the sector, we will change the offset value in EAC to the 
one that we just determined. Before we do that, we will 
first determine the disc's offset value. This value is not required for dumping, 
but it's used for documenting purposes. We would like to get this value, because it shows us the offset value that 
was added when manufacturing the PSX disc.<br /><br />To get the disc's offset value, we need the following values that we determined 
earlier:</p>
<ul>
	<li>Amount of sector data in samples (was 32 in our example)</li>
	<li>Drive offset (value detected in EAC or taken from the accuraterip drive list, 
CORRECTED WITH -30)</li>
</ul>
<p>Now we can determine the Disc offset:<br /><br /><b>!! Amount of sector data = (Drive offset + Disc offset) !!</b></p>
<p>For example, reading the sector gives you +513 samples (or 2052 bytes 
(=128,25 rows) of data). 
The drive offset is +1160 -30 = +1130.<br />
Then if we add these values into the formula, we get:&nbsp;+513 = 
(+1130 + Disc offset) -&gt; Disc offset = -617</p>

<blockquote>
	<p><b>Note 3:</b> So far the PSXDB dumpers have encountered 4 unique disc 
	offsets: +32, +18, -542 and -617.
	If you get one of these 3 values, this most likely means that the step was 
	done correctly.
	Unfortunately discs with -542 and -617 disc offset only show data in the 
	sector if the Drive offset is very large.</p>
</blockquote>
<p>Write down the disc offset value, so that you include this value when 
submitting information about the dump.</p>
<p>Now we are finally ready to change the offset value in EAC and start dumping 
the audio tracks.
Please keep in mind that the offset value that we are entering in EAC equals the 
amount of data in the sector and not the Disc offset!</p>
</div>

<h3>Changing the offset in EAC to proper value</h3>
<div class="textblock">
<p>Start EAC and change the offset to the new value:</p>

<ul>
	<li>Open the drive options (EAC -&gt; Drive Options or press F10)</li>
	<li>Change to the "Offset / Speed" tab and change the value in the 'Read sample offset correction value' to the new value. A value of 32 samples (amount of data in the sector) earlier means entering '+32' in EAC.</li>
	<li>The value should always be positive, else EAC will cut off at the start of Track02 or at the end of the last track. <b>Therefore tracks should always be dumped on the drives that show garbage data in the previous step!</b></li>
</ul>
<p><img src="/images/guide/eac2-offset.PNG" alt="" style="width: 418px; height: 183px;" /></p>
<p>Now we are ready to continue with ripping the audio tracks.</p>
<h2>Ripping the audio tracks (continued)</h2>

</div>

<h3>Ripping audio tracks with EAC</h3>
<div class="textblock">
<ul>
	<li>Insert the CD you want to rip (make sure the disc has a clean surface)</li>
	<li>Press F4 (or select Action -&gt; Detect Gaps) </li>
	<li>EAC will now detect gaps between the tracks (if there are any)</li>
	<li>If you get weird gaps (normal gaps for PSX discs are 0.00, 2.00 or 4.00 seconds), try to clean the disc and try again.</li>
	<li>Select Action -&gt; Append gaps to next track</li>
	<li>Select the tracks you want to rip</li>
	<li>Press Shift + F6 (or select Action -&gt; Test &amp; Copy selected tracks -&gt; compressed from the menu) </li>
	<li>Select a directory to rip the files to and press ok </li>
	<li>EAC will now start ripping the selected tracks. When the extraction is complete, a 'Status and Error Messages' window will appear.</li>
	<li>Be sure to click the 'Create Log', as this .log file is mandatory information!</li>
	<li>When ripping is done, the Read and Write CRC columns should contain equal CRC numbers!</li>
	<li>After extracting, create a cuesheet. Do this by selecting Action -&gt; Create CUESheet -&gt; Current Gap Settings.</li>
</ul>
<p>Now go to the next step called 'Including track02 pregap'</p>
</div>

<h3>Including Track 02 pregap</h3>
<div class="textblock">
<p>In the step 'fixing the pregap' we removed the track02 pregap from the end of the data track.</p>
<p>We already determined length of the pregap. For instance, 2 seconds pregap was 352800 bytes. The amount of bytes that was determined now has to be added to the beginning of Track02.</p>
<p>We can do this by inserting the required amount of (empty) bytes with a hex editor or by using the psxt001z tool:</p>
<p>Open up command prompt, go to the folder of the track images. Make sure a recent version of psxt001z.exe (see the beginning of this guide for a link) is in the same folder. Use the following command:</p>
<blockquote><p>psxt001z --gen pregap.bin SIZE</p></blockquote>
<p>'SIZE' has to be replaced with the amount of bytes that was determined in the 
step 'fixing the pregap'. 
In most cases this amount will be 352800 (for a 2 sec gap), so this would give 
us the following command:</p>
<p><img src="/images/guide/psxt001z2.PNG" alt="" style="width: 588px; height: 119px;" /></p>
<p>To add the pregap to Track02, use the following command:</p>
<blockquote><p>copy /b pregap.bin+track02.bin newtrack02.bin</p>
<p><img src="/images/guide/copypregap.PNG" alt="" style="width: 460px; height: 122px;" /></p></blockquote>
<p>The file newtrack02.bin should now consist of pregap and audio track.</p>
<p>This is the Track02 file that we want to use for preservation.</p>
<p>Now we are almost ready, head on to the 'Final steps'.</p>
</div>

<h3>LibCrypt</h3>
<div class="textblock">
<p>For LibCrypt protection checking step please see <a href="/guide/libcrypt/">this guide</a>.</p>
</div>

<h3>Final steps</h3>
<div class="textblock">
<p>If you make it to this step, we congratulate you, as you have preserved a PSX game in the best (and there is only 1 best) way.</p>
<p>After dumping several games with audio tracks, it is likely that you are able to make the right decisions without always having to use this guide for each step.</p>
<p>After going through this guide, we will end up with the following files:</p>
<ul>
	<li>Data track and audio track binaries, properly dumped using the guide</li>
	<li>Cuesheets created by Cdrwin and EAC (only for games with audio tracks)</li>
	<li>EAC .log file(s) (only for games with audio tracks)</li>
</ul>
<p>You can now help the PSXDB project by supplying us with the information about the dump.</p>
<p>The information that we need is specified on top of this page. Also, for discs with audio tracks, don't forget to include Disc offset value!</p>
<p>Your contributions to the PSXDB project are much appreciated!</p>
<p>If you need any additional help, contact details can be found on this site in the 'The site' menu.</p>
<p>Feel free to idle or ask for any help in our IRC channels on ForeverChat #psxdb for English, NewNet #emu-russia for Russian users.</p>
</div>
end_delimiter;

?>