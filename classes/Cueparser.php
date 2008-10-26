<?php

/*********************/
/*                   */
/*  Cueparser class  */
/*  Author: Dremora  */
/*  version 1        */
/*                   */
/*********************/

define('CUEPARSER_NO_ERRORS'           , 0 );
define('CUEPARSER_ERROR_TRACKNOTINROW' , 1 );
define('CUEPARSER_ERROR_FILENOTOPENED' , 2 );
define('CUEPARSER_ERROR_FILENOTCLOSED' , 3 );
define('CUEPARSER_ERROR_TRACKNOTOPENED', 4 );
define('CUEPARSER_ERROR_TRACKNOTCLOSED', 5 );
define('CUEPARSER_ERROR_GAPNOTOPENED'  , 6 );
define('CUEPARSER_ERROR_GAPNOTCLOSED'  , 7 );
define('CUEPARSER_ERROR_SECVAL'        , 8 );
define('CUEPARSER_ERROR_FRVAL'         , 9 );
define('CUEPARSER_ERROR_FLAGS'         , 10);
define('CUEPARSER_ERROR_SYNTAX'        , 11);
define('CUEPARSER_TRACK_MODE1'         , 1 );
define('CUEPARSER_TRACK_MODE2'         , 2 );
define('CUEPARSER_TRACK_AUDIO'         , 3 );

class Cueparser {
	public $trackscount;
	public $row;
	public $tracks;
	
	function  __construct() {
		$this->trackscount = 0;
		$this->row = 0;
		$this->tracks = array();
	}
	
	function loadCuesheet($cue) {
		$cue = trim(strtoupper(str_replace(array("\r\n", "\r"), "\n", $cue)));
		$cue = explode("\n", $cue);
		$file_opened = false;
		$track_opened = false;
		$gap_opened = false;
		$flags = false;
		foreach ($cue as $v) {
			$this->row++;
			$v = trim($v);
			if (preg_match('@^(FILE "[^"]+" (BINARY|WAVE))?$@', $v)) {
				if ($file_opened) {
					return CUEPARSER_ERROR_FILENOTCLOSED;
				}
				$file_opened = true;
				continue;
			} else if (preg_match('@^TRACK ([0-9]{2}) (MODE1/2352|MODE2/2352|AUDIO)$@', $v, $matches)) {
				if (($this->trackscount + 1) != intval($matches[1])) {
					return CUEPARSER_ERROR_TRACKNOTINROW;
				}
				if (!$file_opened) {
					return CUEPARSER_ERROR_FILENOTOPENED;
				}
				if ($track_opened) {
					return CUEPARSER_ERROR_TRACKNOTCLOSED;
				}
				$track_opened = true;
				$this->trackscount++;
				$this->tracks[$this->trackscount]['number'] = $this->trackscount;
				$this->tracks[$this->trackscount]['flags'] = 0;
				switch ($matches[2]) {
					case 'MODE1/2352':
						$this->tracks[$this->trackscount]['type'] = CUEPARSER_TRACK_MODE1;
						break;
					case 'MODE2/2352':
						$this->tracks[$this->trackscount]['type'] = CUEPARSER_TRACK_MODE2;
						break;
					case 'AUDIO':
						$this->tracks[$this->trackscount]['type'] = CUEPARSER_TRACK_AUDIO;
						break;
				}
			} else if (preg_match('@^FLAGS (.*)$@', $v, $matches)) {
				if (!$file_opened) {
					return CUEPARSER_ERROR_FILENOTOPENED;
				}
				if (!$track_opened) {
					return CUEPARSER_ERROR_TRACKNOTOPENED;
				}
				if ($gap_opened) {
					return CUEPARSER_ERROR_GAPNOTCLOSED;
				}
				foreach (explode(' ', $matches[1]) as $flag) {
					switch ($flag) {
						case 'PRE':
							$this->tracks[$this->trackscount]['flags'] |= 1;
							break;
						case 'DCP':
							$this->tracks[$this->trackscount]['flags'] |= 2;
							break;
						default:
							return CUEPARSER_ERROR_FLAGS;
					}
				}
				$flags = true;
			} else if (preg_match('@^INDEX 00 00:00:00$@', $v)) {
				if (!$file_opened) {
					return CUEPARSER_ERROR_FILENOTOPENED;
				}
				if (!$track_opened) {
					return CUEPARSER_ERROR_TRACKNOTOPENED;
				}
				if ($gap_opened) {
					return CUEPARSER_ERROR_GAPNOTCLOSED;
				}
				$gap_opened = true;
			} else if (preg_match('@^INDEX 01 00:00:00$@', $v)) {
				if (!$file_opened) {
					return CUEPARSER_ERROR_FILENOTOPENED;
				}
				if (!$track_opened) {
					return CUEPARSER_ERROR_TRACKNOTOPENED;
				}
				if ($gap_opened) {
					return CUEPARSER_ERROR_GAPNOTCLOSED;
				}
				$this->tracks[$this->trackscount]['pregap'] = 0;
				$file_opened = false;
				$track_opened = false;
			} else if (preg_match('@^INDEX 01 00:([0-9]{2}):([0-9]{2})$@', $v, $matches)) {
				if (!$file_opened) {
					return CUEPARSER_ERROR_FILENOTOPENED;
				}
				if (!$track_opened) {
					return CUEPARSER_ERROR_TRACKNOTOPENED;
				}
				if (!$gap_opened) {
					return CUEPARSER_ERROR_GAPNOTOPENED;
				}
				$file_opened  = false;
				$track_opened = false;
				$gap_opened   = false;
				$sec = intval($matches[1]);
				$fr  = intval($matches[2]);
				if ($sec >= 60) {
					return CUEPARSER_ERROR_SECVAL;
				}
				if ($fr >= 75) {
					return CUEPARSER_ERROR_FRVAL;
				}
				$this->tracks[$this->trackscount]['pregap'] = $sec * 75 + $fr;
			} else if (preg_match('@^REM@', $v) || preg_match('@^PERFORMER@', $v) || preg_match('@^TITLE@', $v)) {
			} else {
				return CUEPARSER_ERROR_SYNTAX;
			}
		}
	}
	
	function addTrack($track) {
		if (!isset($track['type']) || !preg_match('@^[123]$@', $track['type'])) {
			return;
		}
		if (!isset($track['number']) || !$track['number']) {
			if ($this->trackscount < 99) {
				$this->trackscount++;
				$track['number'] = $this->trackscount;
			} else {
				return;
			}
		} else if (!preg_match('@^[1-9]?[0-9]$@', $track['number'])) {
			return;
		}
		$this->tracks[$track['number']]['number']  = $track['number'];
		$this->tracks[$track['number']]['type']    = $track['type'];
		$this->tracks[$track['number']]['flags']   = $track['flags'];
		$this->tracks[$track['number']]['pregap']  = isset($track['pregap']) ? intval($track['pregap']) : 0;
			
	}
	
	function returnCuesheet() {
		$cue = '';
		foreach ($this->tracks as $track) {
			$cue .= 'FILE "';
			if (isset($track['filename'])) $cue .= $track['filename'];
			else $cue .= 'Track '.str_pad($track['number'], 2, '0', STR_PAD_LEFT);
			$cue .= '.bin" BINARY'."\r\n".'  TRACK '.str_pad($track['number'], 2, '0', STR_PAD_LEFT).' '.$this->trackType($track['type'])."\r\n";
			if ($track['flags']) {
				switch ($track['flags']) {
					case 1: $cue .= '    FLAGS PRE'."\r\n"; break;
					case 2: $cue .= '    FLAGS DCP'."\r\n"; break;
					case 3: $cue .= '    FLAGS PRE DCP'."\r\n"; break;
				}
				
			}
			if (!$track['pregap']) {
				$cue .= '    INDEX 01 00:00:00'."\r\n";
			} else {
				$cue .= '    INDEX 00 00:00:00'."\r\n";
				$gapsec = str_pad(floor($track['pregap'] / 75), 2, '0', STR_PAD_LEFT);
				$gapframe = str_pad($track['pregap'] - ($gapsec * 75), 2, '0', STR_PAD_LEFT);
				$cue .= "    INDEX 01 00:".$gapsec.":".$gapframe."\r\n";			
			}
		}
		return $cue;
	}
	
	function trackType($integer) {
		switch ($integer) {
			case CUEPARSER_TRACK_MODE1:
				return 'MODE1/2352';
				break;
			case CUEPARSER_TRACK_MODE2:
				return 'MODE2/2352';
				break;
			case CUEPARSER_TRACK_AUDIO:
				return 'AUDIO';
				break;
		}
	}
}

?>