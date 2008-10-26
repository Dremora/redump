function ajaxRequest(url, vars, callbackFunction) {
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
		alert('Unfortunately you browser doesn\'t support AJAX.');
		return false;
	}
	
	http_request.onreadystatechange = function() {
		if (http_request.readyState == 4) {
			if (http_request.status == 200) {
				callbackFunction(http_request);
			} else {
				// Error
			}
		}
	};
	
	http_request.open('POST', url, true);
	http_request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	http_request.send(vars);
}