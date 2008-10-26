function getposOffset(element, type) {
	var totaloffset=(type=='left')? element.offsetLeft : element.offsetTop;
	var parent = element.offsetParent;
	while (parent != null){
		totaloffset = (type == 'left') ? totaloffset + parent.offsetLeft : totaloffset + parent.offsetTop;
		parent = parent.offsetParent;
	}
	return totaloffset;
}

function toggleElement(element) {
	if (element.style.display == 'none')
		element.style.display = '';
	else
		element.style.display = 'none';
}

function showelement(id) {
	document.getElementById('submenu' + id).style.display = 'block';
	document.getElementById('menu' + id).className = 'menu-selected';
	document.getElementById('submenu' + id).style.left = getposOffset(document.getElementById('menu' + id), 'left') + 'px';
	document.getElementById('submenu' + id).style.top = getposOffset(document.getElementById('menu' + id), 'right') + 18 + 'px';
}

function hideelement(id) {
	document.getElementById('submenu' + id).style.display = 'none';
	document.getElementById('menu' + id).className = '';
}

function setopacity(object, value) {
	object.style.opacity = value/10;
}

function hidenmenu(menu) {
	el = document.getElementById(menu);
	switch (menu) {
		case 'viewbymedia':
			toggleElement(el);
			document.getElementById('viewbyregion').style.display = 'none';
			break;
		case 'viewbyregion':
			toggleElement(el);
			document.getElementById('viewbymedia').style.display = 'none';
			break;
	}
}

function ShowOverlay() {
	var y;
	if (self.innerHeight)
		y = self.innerHeight;
	else if (document.documentElement && document.documentElement.clientHeight)
		y = document.documentElement.clientHeight;
	else if (document.body)
		y = document.body.clientHeight;
	document.getElementById('overlay').style.height = y + 'px';
	document.getElementById('overlay').style.display = 'block';
	for (i = 1; i < 9; i++)
		setTimeout('document.getElementById(\'overlay\').style.opacity = 0.1 * '+i+'; if ('+i+' == 8) document.getElementById(\'overlay-contents\').style.display = \'block\';', i * 20 );
}

function HideOverlay() {
	document.getElementById('overlay-contents').style.display = '';
	for (i = 7; i >= 0; i--)
		setTimeout('document.getElementById(\'overlay\').style.opacity = 0.1 * '+i+'; if ('+i+' == 0) document.getElementById(\'overlay\').style.display = \'\';', (8 - i) * 20 );
}