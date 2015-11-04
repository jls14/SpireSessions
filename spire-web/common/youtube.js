(function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) {
			return;
		}
		js = d.createElement(s);
		js.id = id;
		js.src = "https://www.youtube.com/iframe_api";
	fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'youtube-api'));

