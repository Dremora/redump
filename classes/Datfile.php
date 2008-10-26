<?php

/***********************/
/*                     */
/*  ClrMamePro datfile */
/*  class              */
/*  Author: Dremora    */
/*  version 0          */
/*                     */
/***********************/

class Datfile {
	public $trackscount;
	public $row;
	public $tracks;
	
	function  __construct() {
		$this->trackscount = 0;
		$this->row = 0;
		$this->tracks = array();
	}

?>