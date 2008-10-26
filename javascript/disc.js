function ChangeDiscStatus(id) {
	ajaxRequest('http://redump.org/disc/'+id+'/mydisc/', '', ChangeDiscStatusCallback);
}

function ChangeDiscStatusCallback(ajax) {
	if (ajax.responseXML.getElementsByTagName('response')[0].getElementsByTagName('status')[0].lastChild.nodeValue == 0) {
		var data = ajax.responseXML.getElementsByTagName('response')[0].getElementsByTagName('data')[0];
		var text = data.getElementsByTagName('text')[0].lastChild.nodeValue;
		var id = data.getElementsByTagName('id')[0].lastChild.nodeValue;
		if (data.getElementsByTagName('userid').length) var userid = data.getElementsByTagName('userid')[0].lastChild.nodeValue;
		if (text == 'have') {
			if (userid !== undefined) {
				var el = document.createElement("a")
				el.innerHTML =  data.getElementsByTagName('username')[0].lastChild.nodeValue;
				el.setAttribute('id', 'owner'+userid);
				el.setAttribute('href', 'http://forum.redump.org/profile.php?id='+userid);
				document.getElementById('owners').appendChild(el);
			}
			document.getElementById('mydisctext').innerHTML = 'Remove from my discs';
		} else if (text == 'miss') {
			if (userid !== undefined) document.getElementById('owners').removeChild(document.getElementById('owner'+userid));
			document.getElementById('mydisctext').innerHTML = 'Add to my discs';
		}
	}
}