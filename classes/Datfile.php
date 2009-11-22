<?php

/***********************/
/*                     */
/*  ClrMamePro datfile */
/*  class              */
/*  Author: Dremora    */
/*  version 0          */
/*                     */
/***********************/

class Datfile
{
	public $tracks = array();
	public $tracks_count = 0;
	
	public function  __construct()
	{
	}
	
	public function parse($tracks, $cue)
	{
		$tracks = explode("\n", trim(strtolower(str_replace(array("\r\n", "\r"), "\n", $tracks))));

		foreach ($tracks as $track) {
			$track = trim($track);
			if (!preg_match('@size="([0-9]{6,10})" crc="([0-9a-f]{8})" md5="([0-9a-f]{32})" sha1="([0-9a-f]{40})"@', $track, $matches)
			&& !preg_match('@size ([0-9]{6,10}) crc ([0-9a-f]{8}) md5 ([0-9a-f]{32}) sha1 ([0-9a-f]{40})@', $track, $matches))
			{
				continue;
			}
			$this->tracks_count++;
			$this->tracks[$this->tracks_count]['size']      = $matches[1];
			$this->tracks[$this->tracks_count]['crc32']     = $matches[2];
			$this->tracks[$this->tracks_count]['md5']       = $matches[3];
			$this->tracks[$this->tracks_count]['sha1']      = $matches[4];
			$this->tracks[$this->tracks_count]['pregap']    = $cue->tracks[$this->tracks_count]['pregap'];
			$this->tracks[$this->tracks_count]['type']      = $cue->tracks[$this->tracks_count]['type'];
			$this->tracks[$this->tracks_count]['flags']     = $cue->tracks[$this->tracks_count]['flags'];
		}
	}
}