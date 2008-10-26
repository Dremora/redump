<?php

if (!defined('PSXDB')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

// параметры указаны:
// 1) проверить параметры
// 2) записать в профиль
// 3) сгенерировать датник
if (isset($_GET['system'])) {
}
// если не указаны параметры генерации датника, отображать форму

// Form

$systems = $mysqli->query('SELECT * FROM `systems` AS `s` GROUP BY `s`.`s_short` ORDER BY `s`.`s_order`');

$psxdb['script'][] = "<script type=\"text/javascript\">
<!--//--><![CDATA[//><!--
var form;
function UpdateLinks() {
	var url = 0;
	for (var i = ".($systems->num_rows + 1)."; i < (form.length - 1); i++) {
		if (form.elements[i].value < 0 && !form.elements[i].checked)
			url |= -form.elements[i].value;
		if (form.elements[i].value > 0 && form.elements[i].checked)
			url |= form.elements[i].value;
	}
	
	if (url != 0)
		url = url + '/';
	else url = '';

	// X. System
	for (i = 0; i < form.system.length; i++) {
		if (form.system[i].checked) {
			var system = form.system[i].value + '/';
			break;
		}
	}
	document.getElementById('cmplink').value = 'http://".$_SERVER['HTTP_HOST']."/datfile2/' + url + 'xmldats.zip';
	document.getElementById('datfilelink').href = 'http://".$_SERVER['HTTP_HOST']."/datfile2/' + url + system;
	document.getElementById('datfilelink').firstChild.nodeValue = 'Download ' + form.system[i].nextSibling.nextSibling.firstChild.nodeValue + ' datfile';
}
//--><!]]>
</script>";

$psxdb['onload'][] = "	form = document.getElementById('datfile2');
	for (i = 1; i < form.length; i++) {
			form.elements[i].onchange = UpdateLinks;
			form.elements[i].onclick = UpdateLinks;
	}
	document.getElementById('cmplink').onclick = function () {this.focus(); this.select();}
	UpdateLinks();";
	

$form = new Form('Download custom datfile');

// 1. Systems
while ($system = $systems->fetch_array())
	$systems_array[] = array(htmlspecialchars($system['s_full']), strtolower($system['s_short']));
$form->radio(array('name' => 'system', 'caption' => 'System', 'radio' => $systems_array, 'check' => 'psx'));

// 2. Status this.focus(); this.select();
$status = array(
	array(status(1).' '.statustext(1), -0x1),
	array(status(2).' '.statustext(2), -0x2),
	array(status(4).' '.statustext(4), -0x4),
	array(status(5).' '.statustext(5), -0x8)
);
$form->checkbox(array('name' => 'status', 'caption' => 'Status', 'checkbox' => $status, 'nobrackets' => 1, 'check' => array(-0x1, -0x2, -0x4, -0x8)));

// 3. Region
$regions = array(
	array(region('E').' '.$psxdb['regions']['E'], -0x10),
	array(region('U').' '.$psxdb['regions']['U'], -0x20),
	array(region('A').' '.$psxdb['regions']['A'], -0x40)
);
$form->checkbox(array('name' => 'region', 'caption' => 'Regions', 'checkbox' => $regions, 'nobrackets' => 1, 'check' => array(-0x10, -0x20, -0x40)));

// 4. Region format
$regformat = array(
	array('Don\'t display regions', 0x80),
	array('GoodTools (U, E, J)', 0x0),
	array('ISO 3166-1 alpha-2 (US, Eu, Jp)', 0x100),
	array('Full names', 0x200)
);
$form->radio(array('name' => 'regformat', 'caption' => 'Region format', 'radio' => $regformat, 'check' => 0));

// 5. Languages format
$languages = array(
	array('Don\'t display languages', 0x0),
	array('ISO 639-1, coma-separated', 0x400),
	array('(Mx)', 0x800)
);
$form->radio(array('name' => 'languages', 'caption' => 'Languages', 'radio' => $languages, 'check' => 0));

// 6. Options
$options = array(
	array('Move articles to the end of the title', -0x1000),
	array('Add spaces between brackets', -0x2000),
	array('Display serials', -0x4000)
);
$form->checkbox(array('name' => 'options', 'caption' => 'Options', 'checkbox' => $options, 'nobrackets' => 1, 'check' => array(-0x1000, -0x2000, -0x4000)));

// 6a. Options
$optionsa = array(
	array('Version', 0),
	array('EXE date', 0x8000)
);
$form->radio(array('name' => 'optionsa', 'caption' => 'Version/date', 'radio' => $optionsa, 'check' => 0));


// 5. Links
$form->text(array('name' => 'cmplink', 'caption' => 'Link for clrmamepro www profiler', 'readonly' => 1, 'value' => 'http://psxdb.com/datfile2/xmldats.zip'));
$form->statictext(array('caption' => 'Download datfile', 'contents' => '<b><a id="datfilelink" href="http://psxdb.com/datfile2/psx/">...</a></b>'));


$psxdb['title'] = 'Download custom datfile';
$psxdb['script'][] = $form->script();
echo $form->contents();
display();



?>