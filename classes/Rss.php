<?php

if (!defined('PSXDB')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

class Rss {
	private $contents;
	private $title;
	private $url;
	private $id;
	
	function  __construct($title, $url, $id = 0) {
		$this->title = $title;
		$this->url = $url;
		$this->id = $id;
		$this->contents = '<table>';
	}
	
	function changes($title, $olddata, $newdata) {
		if ($olddata == '' && $newdata == '')
			return;
		else if ($olddata == $newdata)
			$this->contents .= '<tr><td style="vertical-align: top;"><b>'.$title.'</b></td><td>'.$newdata.'</td></tr>';
		else if ($olddata != '' && $newdata == '') {
			$this->contents .= '<tr><td style="vertical-align: top; color: #aa0000;"><b>'.$title.'</b></td><td style="color: #777777;">(removed)</td></tr>';
			$this->contents .= '<tr style="color: #777777;"><td style="vertical-align: top; text-align: right;">was</td><td>'.$olddata.'</td></tr>';
		} else if ($olddata != '' && $newdata != '') {
			$this->contents .= '<tr><td style="vertical-align: top; color: #0000aa;"><b>'.$title.'</b></td><td>'.$newdata.'</td></tr>';
			$this->contents .= '<tr style="color: #777777;"><td style="vertical-align: top; text-align: right;">was</td><td>'.$olddata.'</td></tr>';
		} else if ($olddata == '' && $newdata != '')
			$this->contents .= '<tr><td style="vertical-align: top; color: #00aa00;"><b>'.$title.'</b></td><td>'.$newdata.'</td></tr>';
	}
	
	function row($title, $data) {
		if ($data == '') return;
		else $this->contents .= '<tr><td><b>'.$title.'</b></td><td>'.$data.'</td></tr>';
	}
	
	function blankrow() {
		$this->contents .= '</table><br /><table>';
	}
	
	function query() {
		global $mysqli, $psxdb_user;
		$this->contents .= '</table>';
		return $mysqli->query('INSERT INTO `rss` (`r_contents`,`r_datetime`,`r_datetime_new`,`r_title`,`r_url`,`u_id`,`d_id`) VALUES ("'.addslashes($this->contents).'","'.gmdate("Y-m-d H:i:s").'",'.time().',"'.addslashes($this->title).'","'.addslashes($this->url).'",'.$psxdb_user['id'].','.$this->id.')');
	}
	
	function return_query() {
		return 'INSERT INTO `rss` (`r_contents`,`r_datetime`,`r_datetime_new`,`r_title`,`r_url`,`u_id`,`d_id`) VALUES ("'.addslashes($this->contents).'","'.gmdate("Y-m-d H:i:s").'",'.time().',"'.addslashes($this->title).'","'.addslashes($this->url).'",'.$psxdb_user['id'].','.$this->id.')';
	}
	
}

?>