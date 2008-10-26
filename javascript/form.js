function makeRequest(form, mode) {
	
	var http_request = false;
	var formstatus = document.getElementById("status");
	var formstatusicon = document.getElementById("status-icon");
	
	if (window.XMLHttpRequest) http_request = new XMLHttpRequest();
	else if (window.ActiveXObject) {
		try {
			http_request = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				http_request = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e) {}
		}
	}
	
	if (!http_request) {
		formstatus.className = 'status-error';
		formstatus.innerHTML = 'Can\'t create an XMLHTTP instance';
		return false;
	}
	
	form.submit.setAttribute('disabled', 'disabled');
	form.submit.blur();
	http_request.onreadystatechange = function() {
		if (http_request.readyState == 4) {
			if (http_request.status == 200) {
				var xmldoc = http_request.responseXML;
				formstatus.className = null;
				formstatus.innerHTML = null;
				formstatusicon.className = null;
				if (xmldoc.getElementsByTagName('response')[0].getElementsByTagName('status')[0].lastChild.nodeValue == 1)
					formstatus.className = 'status-ok';
				else {
					form.submit.removeAttribute('disabled');
					formstatus.className = 'status-error';
				}
				formstatus.innerHTML = xmldoc.getElementsByTagName('response')[0].getElementsByTagName('message')[0].lastChild.nodeValue;
				if (xmldoc.getElementsByTagName('response')[0].getElementsByTagName('alert')[0])
					alert(xmldoc.getElementsByTagName('response')[0].getElementsByTagName('alert')[0].lastChild.nodeValue);
			} else {
				form.submit.removeAttribute('disabled');
				formstatus.className = 'status-error';
				formstatus.innerHTML = 'Error ' + http_request.status;
			}
		}
	};
	
	var send = '';
	var formelements = form.elements;
	for (var i = 0; i < formelements.length; i++) {
		if (formelements[i].tagName.toLowerCase() == 'input' && formelements[i].type == 'checkbox' || formelements[i].type == 'radio') {
			if (formelements[i].checked) send += formelements[i].name + '=' + encodeURIComponent(formelements[i].value) + '&';
		} else if (formelements[i].tagName.toLowerCase() == 'select') {
			for (a = 0; a < formelements[i].length; a++) if (formelements[i].options[a].selected) send += formelements[i].name + '=' + encodeURIComponent(formelements[i].options[a].value) + '&';
		} else if (formelements[i].tagName.toLowerCase() == 'fieldset') {
			continue;
		} else if (formelements[i].name != '' && formelements[i].value != '') {
			send += formelements[i].name + '=' + encodeURIComponent(formelements[i].value) + '&';
		}
	}
	
	http_request.open('POST', 'http://' + site_domain + '/?module=' + mode + '&action=1', true);
	http_request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	http_request.send(send);
	formstatus.className = 'status-notice';
	formstatus.innerHTML = 'Sending data...';
	formstatusicon.className = 'status-icon-loading';
}

function checkform(mode) {
	makeRequest(document.getElementById(mode), mode);
}

function resetForm(mode) {
	document.getElementById("status").innerHTML = '';
	form = document.getElementById(mode);
	form.reset();
	form.submit.removeAttribute('disabled');
	window.scroll(0, 0);
}

function resizeElement(element, amount, llimit, hlimit) {
	if (((element.clientHeight + amount) > llimit) && ((element.clientHeight + amount) < hlimit))
		element.style.height = (element.clientHeight + amount + 'px');
}