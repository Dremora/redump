<?php

if (!defined('PSXDB')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

global $psxdb;
$psxdb['script'][] = '<script type="text/javascript"><!--//--><![CDATA[//><!--
var site_domain=\''.$_SERVER['HTTP_HOST'].'\';
//--><!]]></script>';
$psxdb['script'][] = '<script type="text/javascript" src="/javascript/form.js"></script>';
$psxdb['css'][]  = '<style type="text/css" media="screen">@import url(\'/styles/form.css\');</style>';

function okXML($string) {
	header('Content-type: application/xml');
	ob_clean();
	echo '<?xml version="1.0" encoding="utf-8"?>
<response>
	<status>1</status>
	<message><![CDATA['.$string.']]></message>
</response>';
	exit();
}

function errorXML($string, $alert = '') {
	header('Content-type: application/xml');
	ob_clean();
	echo '<?xml version="1.0" encoding="utf-8"?>
<response>
	<status>0</status>
	<message><![CDATA['.$string.']]></message>
';
if ($alert != '')
	echo '	<alert><![CDATA['.$alert.']]></alert>
';
echo '
</response>';
	exit();
}

class Form {
	private $contents;
	
	function  __construct($legend) {
		$this->addContents('<form action="javascript:checkform(\''.$_GET['module'].'\')" id="'.$_GET['module'].'" class="form"><fieldset><legend>'.$legend.'</legend><table>');
	}
	
	function addContents($contents) {
		$this->contents .= $contents;
	}
	
	/* Elements */
	
	function text($element) {
		if ($element['name'] == '') return;
		$this->addContents('<tr id="tr_'.$element['name'].'"><th><label for="'.$element['name'].'">'.$element['caption'].'</label></th><td><input class="input" type="text" id="'.$element['name'].'" name="'.$element['name'].'"');
		if (isset($element['value']))
			$this->addContents(' value="'.htmlspecialchars($element['value']).'"');
		if (isset($element['readonly']))
			$this->addContents(' readonly="readonly"');
		$this->addContents(' /></td></tr>');
	}
	
	function textarea($element) {
		global $psxdb;
		if ($element['caption'] == '' || $element['name'] == '') return;
		if (isset($element['width']))
			$width = $element['width'];
		else
			$width = 600;
		if (isset($element['height']))
			$height = $element['height'];
		else
			$height = 150;
		$this->addContents('<tr><th><label for="'.$element['name'].'">'.$element['caption'].'</label></th><td><span class="small"><a id="'.$element['name'].'_expand_">expand +</a>&nbsp;&nbsp;&nbsp;<a id="'.$element['name'].'_collapse_">collapse -</a></span><br /><textarea class="input" rows="" cols="" id="'.$element['name'].'" style="width: '.$width.'px; height: '.$height.'px;" name="'.$element['name'].'">'.htmlspecialchars($element['value']).'</textarea></td></tr>');
		$psxdb['onload'][] = 'document.getElementById(\''.$element['name'].'_expand_\').onclick = function () {resizeElement(document.getElementById(\''.$element['name'].'\'), 30, 20, 1000);}';
		$psxdb['onload'][] = 'document.getElementById(\''.$element['name'].'_collapse_\').onclick = function () {resizeElement(document.getElementById(\''.$element['name'].'\'), -30, 20, 1000);}';
	}
	
	function radio($element) {
		if ($element['name'] == '' || $element['radio'] == '') return;
		$this->addContents('<tr id="tr_'.$element['name'].'"><th>'.$element['caption'].'</th><td>');
		for ($i = 0; $i < count($element['radio']); $i++) {
			if ($i != 0)
				$this->addContents('<br />');
			$this->addContents('<input class="check" type="radio" name="'.$element['name'].'" id="'.$element['name'].'_'.$i.'" value="'.$element['radio'][$i][1].'"');
			if (isset($element['check']) && $element['check'] == $element['radio'][$i][1]) $this->addContents(' checked="checked"');
			if ($element['radio'][$i][2]) $this->addContents(' onclick="'.$element['radio'][$i][2].'"');
			$this->addContents(' /> <label class="labelright" for="'.$element['name'].'_'.$i.'">'.$element['radio'][$i][0].'</label>');
		}
		$this->addContents('</td></tr>');
	}
	
	function checkbox($element) {
		if ($element['name'] == '' || $element['checkbox'] == '') return;
		$this->addContents('<tr><th>'.$element['caption'].'</th><td>');
		for ($i = 0; $i < count($element['checkbox']); $i++) {
			if ($i != 0) $this->addContents('<br />');
			$this->addContents('<input class="check" type="checkbox" name="'.$element['name']);
			if (count($element['checkbox']) > 1 && !$element['nobrackets']) $this->addContents('[]');
			$this->addContents('" id="'.$element['name'].'_'.$i.'" value="'.$element['checkbox'][$i][1].'"');
			if (isset($element['check']) && in_array($element['checkbox'][$i][1], $element['check'])) $this->addContents(' checked="checked"');
			$this->addContents(' /> <label class="labelright" for="'.$element['name'].'_'.$i.'">'.$element['checkbox'][$i][0].'</label>');
		}
		$this->addContents('</td></tr>');
	}
	
	function select($element) {
		if ($element['caption'] == '' || $element['name'] == '' || $element['option'] == '') return;
		$this->addContents('<tr><th>'.$element['caption'].'</th><td><select class="input" id="'.$element['name'].'" name="'.$element['name'].'');
		if (isset($element['multiple'])) $this->addContents('[]" multiple="multiple" size="10');
		$this->addContents('">');
		for ($i = 0; $i < count($element['option']); $i++) {
			$this->addContents('<option value="'.$element['option'][$i][1].'"');
			if (isset($element['select']) && in_array($element['option'][$i][1], $element['select'])) $this->addContents(' selected="selected"');
			$this->addContents('>'.$element['option'][$i][0].'</option>');
		}
		$this->addContents('</select></td></tr>');
	}
	
	function hidden($element) {	
		if ($element['name'] == '' || $element['value'] == '') return;
		$this->addContents('<tr style="display: none;"><td><input type="hidden" name="'.$element['name'].'" value="'.htmlspecialchars($element['value']).'" /></td></tr>');
	}
	
	function file($element) {
		if ($element['caption'] == '' || $element['name'] == '') return;
		$this->addContents('<label for="'.$element['name'].'">'.$element['caption'].'</label><input class="file" type="file" id="'.$element['name'].'" name="'.$element['name'].'" /><br />');
	}
	
	function statictext($element) {
		if ($element['contents'] == '') return;
		$this->addContents('<tr'.(isset($element['name']) ? ' id="tr_'.$element['name'].'"' : '').'><th>'.$element['caption'].'</th><td class="static"'.(isset($element['name']) ? ' id="'.$element['name'].'"' : '').'>'.$element['contents'].'</td></tr>');
	}
	
	function submit($element) {
		if ($element['caption'] == '') return;
		$this->addContents('<tr><th><span></span></th><td><input id="submit" type="submit" name="submit" value="'.$element['caption'].'" /> <span id="status-icon"></span> <span id="status"></span></td></tr>');
	}
	
	/* End of elements /*/
	
	function fieldset($title) {
		$this->contents .= '</table></fieldset><fieldset><legend>'.$title.'</legend><table>';
	}
	
	function contents() {
		$this->contents .= '</table></fieldset></form>';
		return $this->contents;
	}
}

?>