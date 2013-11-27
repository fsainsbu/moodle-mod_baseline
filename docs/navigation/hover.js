<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
	"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>

<head>
<title>Suckerfish Dropdowns - One Level Bones</title>

<style type="text/css">

body {
	font-family: arial, helvetica, serif;
}

#nav, #nav ul { /* all lists */
	padding: 0;
	margin: 0;
	list-style: none;
	line-height: 1;
}

#nav a {
	display: block;
	width: 10em;
}

#nav li { /* all list items */
	float: left;
	width: 10em; /* width needed or else Opera goes nuts */
}

#nav li ul { /* second-level lists */
	position: absolute;
	background: orange;
	width: 10em;
	left: -999em; /* using left instead of display to hide menus because display: none isn't read by screen readers */
}

#nav li:hover ul, #nav li.sfhover ul { /* lists nested under hovered list items */
	left: auto;
}

#content {
	clear: left;
	color: #ccc;
}

</style>

<script type="text/javascript"><!--//--><![CDATA[//><!--

sfHover = function() {
	var sfEls = document.getElementById("nav").getElementsByTagName("LI");
	for (var i=0; i<sfEls.length; i++) {
		sfEls[i].onmouseover=function() {
			this.className+=" sfhover";
		}
		sfEls[i].onmouseout=function() {
			this.className=this.className.replace(new RegExp(" sfhover\\b"), "");
		}
	}
}
if (window.attachEvent) window.attachEvent("onload", sfHover);

//--><!]]></script>


