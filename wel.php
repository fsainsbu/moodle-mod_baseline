<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en" xml:lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="https://www.apok.org/cf4/theme/standard/styles.php" />
<link rel="stylesheet" type="text/css" href="https://www.apok.org/cf4/theme/mycf/styles.php" />
<script type="text/javascript" src="https://www.apok.org/cf4/mod/baseline/js.php?d=5"></script>
<!--[if IE 7]>
    <link rel="stylesheet" type="text/css" href="https://www.apok.org/cf4/theme/standard/styles_ie7.css" />
<![endif]-->
<!--[if IE 6]>
    <link rel="stylesheet" type="text/css" href="https://www.apok.org/cf4/theme/standard/styles_ie6.css" />
<![endif]-->


    <meta name="keywords" content="moodle, TCF: Diary " />
    <title>TCF: Diary</title>
    <link rel="shortcut icon" href="https://www.apok.org/cf4/theme/mycf/favi.png" />
    <!--<style type="text/css">/*<![CDATA[*/ body{behavior:url(https://www.apok.org/cf4/lib/csshover.htc);} /*]]>*/</style>-->

<script type="text/javascript" src="https://www.apok.org/cf4/lib/javascript-static.js"></script>
<script type="text/javascript" src="https://www.apok.org/cf4/lib/javascript-mod.php"></script>
<script type="text/javascript" src="https://www.apok.org/cf4/lib/overlib/overlib.js"></script>
<script type="text/javascript" src="https://www.apok.org/cf4/lib/overlib/overlib_cssstyle.js"></script>
<script type="text/javascript" src="https://www.apok.org/cf4/lib/cookies.js"></script>
<script type="text/javascript" src="https://www.apok.org/cf4/lib/ufo.js"></script>
<script type="text/javascript" src="https://www.apok.org/cf4/lib/dropdown.js"></script>  

<script type="text/javascript" defer="defer">
//<![CDATA[
setTimeout('fix_column_widths()', 20);
//]]>
</script>
<script type="text/javascript">
//<![CDATA[
function openpopup(url, name, options, fullscreen) {
    var fullurl = "https://www.apok.org/cf4" + url;
    var windowobj = window.open(fullurl, name, options);
    if (!windowobj) {
        return true;
    }
    if (fullscreen) {
        windowobj.moveTo(0, 0);
        windowobj.resizeTo(screen.availWidth, screen.availHeight);
    }
    windowobj.focus();
    return false;
}

function uncheckall() {
    var inputs = document.getElementsByTagName('input');
    for(var i = 0; i < inputs.length; i++) {
        inputs[i].checked = false;
    }
}

function checkall() {
    var inputs = document.getElementsByTagName('input');
    for(var i = 0; i < inputs.length; i++) {
        inputs[i].checked = true;
    }
}

function inserttext(text) {
  text = ' ' + text + ' ';
  if ( opener.document.forms['theform'].message.createTextRange && opener.document.forms['theform'].message.caretPos) {
    var caretPos = opener.document.forms['theform'].message.caretPos;
    caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
  } else {
    opener.document.forms['theform'].message.value  += text;
  }
  opener.document.forms['theform'].message.focus();
}

function getElementsByClassName(oElm, strTagName, oClassNames){
	var arrElements = (strTagName == "*" && oElm.all)? oElm.all : oElm.getElementsByTagName(strTagName);
	var arrReturnElements = new Array();
	var arrRegExpClassNames = new Array();
	if(typeof oClassNames == "object"){
		for(var i=0; i<oClassNames.length; i++){
			arrRegExpClassNames.push(new RegExp("(^|\\s)" + oClassNames[i].replace(/\-/g, "\\-") + "(\\s|$)"));
		}
	}
	else{
		arrRegExpClassNames.push(new RegExp("(^|\\s)" + oClassNames.replace(/\-/g, "\\-") + "(\\s|$)"));
	}
	var oElement;
	var bMatchesAll;
	for(var j=0; j<arrElements.length; j++){
		oElement = arrElements[j];
		bMatchesAll = true;
		for(var k=0; k<arrRegExpClassNames.length; k++){
			if(!arrRegExpClassNames[k].test(oElement.className)){
				bMatchesAll = false;
				break;
			}
		}
		if(bMatchesAll){
			arrReturnElements.push(oElement);
		}
	}
	return (arrReturnElements)
}
//]]>
</script>
</head>

<body  class="mod-baseline course-7 dir-ltr lang-en_utf8" id="mod-baseline-bsummary">

<div id="page">

	
	    	    <div class="navbar clearfix">
		<div id="nav">
		<ul>
								<li><a href="https://www.apok.org/cf4/index.php"><img src="https://www.apok.org/cf4/theme/mycf/pix/icons/icon8.png" /> home</a></li>
		<li><a href="https://www.apok.org/cf4/iphone/underconstruction.html"><img src="https://www.apok.org/cf4/theme/mycf/pix/icons/icon14.png" /> education</a></li>
		<li><a href="https://www.apok.org/cf4/mod/baseline/edit.php?id=29"><img src="https://www.apok.org/cf4/theme/mycf/pix/icons/icon10.png" /> diary</a></li>
		<li><a href="https://www.apok.org/cf4/mod/book/index.php?id=1"><img src="https://www.apok.org/cf4/theme/mycf/pix/icons/icon13.png" /> library</a></li>
		<li><a href="https://www.apok.org/cf4/blog/index.php?filtertype=course&filterselect=7"><img src="https://www.apok.org/cf4/theme/mycf/pix/icons/icon15.png" /> blog</a></li>	
		<li><a href="https://www.apok.org/cf4/mod/forum/index.php?id=1"><img src="https://www.apok.org/cf4/theme/mycf/pix/icons/icon16.png" /> forum</a></li>
	<li><a href="https://www.apok.org/cf4/iphone/underconstruction.html"><img src="https://www.apok.org/cf4/theme/mycf/pix/icons/icon17.png" /> clinics</a></li>
	<li><a href="https://www.apok.org/cf4/iphone/underconstruction.html"><img src="https://www.apok.org/cf4/theme/mycf/pix/icons/icon18.png" /> mentors</a></li>
				</ul>
		</div>
	    </div>
		
	<div id="header" class=" clearfix">	<div id="header-block" >
        <div class="headermain"><h1>myCF</h1></div>
		<div class="slogan"><h2>self management site</h2></div>
		<div class="headermenu"><div class="navbutton">&nbsp;</div></div>
	</div>
    </div>
    <!-- END OF HEADER -->
	<div id="content-block">
    <div id="content"><h2   class="main"></h2><form enctype="multipart/form-baseline" action="bsummary.php" method="post"><div><input name="d" value="5" type="hidden" /><input name="rid" value="0" type="hidden" /><input name="base" value="0" type="hidden" /><input name="sesskey" value="8QKmSPrb84" type="hidden" /><input name="gmode" value="" type="hidden" /><input name="begdate" value="begdate" type="hidden" /><input name="enddate" value="enddate" type="hidden" />

<div class="tabtree">
<ul class="tabrow0">
<li class="first"><a href="https://www.apok.org/cf4/mod/baseline/bedit.php?d=5&amp;base=1" title="Do Baseline"><span>Do Baseline</span></a> </li>
<li class="onerow here selected"><a class="nolink"><span>Daily Diary</span></a><div class="tabrow1 empty">&nbsp;</div>
 </li>
<li><a href="https://www.apok.org/cf4/mod/baseline/summary.php?d=5" title="Summary"><span>Summary</span></a> </li>
<li><a href="https://www.apok.org/cf4/mod/baseline/view.php?d=5&amp;base=1" title="View Baseline"><span>View Baseline</span></a> </li>
<li class="last"><a href="https://www.apok.org/cf4/mod/baseline/choice.php?d=5&amp;mode=choice" title="Settings"><span>Settings</span></a> </li>
</ul>
</div><div class="clearer"> </div>
<h3>Welcome!</h3><br>  Regularly recording your symptoms will help you be more aware of what your body is doing. Symptoms to notice may include - cough, sputum, joint pain, or your bowel motions. By recording your symptoms you will be able to compare from day to day and see changes that may alert you to review your current health care plan. This may help you to recognise at an earlier stage when your health is deteriorating and allow you to act upon this to keep yourself at peak wellness.  <br><h2> DISCLAIMER</h2><h4> Your health care team do not view your diary routinely. The purpose of the diary is to allow you to track your own progress and act quickly on any changes. It is your responsibility to notice any changes in your symptoms and we advise you to contact your health care team if your condition is worsening.  </h4>
<div id="footer"><img alt="[ Supporters DHHS , UTAS,  CF, TCF ]" src="https://www.apok.org/cf4/theme/mycf/images/logo.jpg" height="64" width="457"></div>
