<?php

/***********************/
/*                     */
/*  AJAX class         */
/*  Author: Dremora    */
/*  version 1          */
/*                     */
/***********************/

class Ajax {
	public $status;
	public $data;
	
	function  __construct($status = 1, $data = '') {
		$this->status = $status;
		$this->data = $data;
	}
	
	function display() {
		$xml = new DOMDocument();
		$xml->loadXML('<response><status>'.$this->status.'</status><data>'.$this->data.'</data></response>');
		echo $xml->saveXML();
		header('Content-Type: application/xml');
		exit();
	}
}

?>