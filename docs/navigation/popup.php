
<!-- TWO STEPS TO INSTALL POPUP MENU:

  1.  Copy the coding into the HEAD of your HTML document
  2.  Add the last code into the BODY of your HTML document  -->

<!-- STEP ONE: Paste this code into the HEAD of your HTML document  -->

<HEAD>

<script type="text/javascript">
<!-- This script and many more are available free online at -->
<!-- The JavaScript Source!! http://javascript.internet.com -->
<!-- Original script at: http://elouai.com/scripts.php -->

var ie  = document.all
var ns6 = document.getElementById&&!document.all
var isMenu  = false ;
var menuSelObj = null ;
var overpopupmenu = false;
function mouseSelect(e)
{
  var obj = ns6 ? e.target.parentNode : event.srcElement.parentElement;
  if( isMenu )
  {
    if( overpopupmenu == false )
    {
      isMenu = false ;
      overpopupmenu = false;
      document.getElementById('menudiv').style.display = "none" ;
      return true ;
    }
    return true ;
  }
  return false;
}
// POP UP MENU
function  ItemSelMenu(e)
{
  var obj = ns6 ? e.target.parentNode : event.srcElement.parentElement; 
      menuSelObj = obj ;
  if (ns6)
  {
    document.getElementById('menudiv').style.left = e.clientX+document.body.scrollLeft;
    document.getElementById('menudiv').style.top = e.clientY+document.body.scrollTop;
  } else
  {
    document.getElementById('menudiv').style.pixelLeft = event.clientX+document.body.scrollLeft;
    document.getElementById('menudiv').style.pixelTop = event.clientY+document.body.scrollTop;
  }
  document.getElementById('menudiv').style.display = "";
  document.getElementById('item1').style.backgroundColor='#FFFFFF';
  document.getElementById('item2').style.backgroundColor='#FFFFFF';
  document.getElementById('item3').style.backgroundColor='#FFFFFF';
  document.getElementById('item4').style.backgroundColor='#FFFFFF';
  isMenu = true;
  return false ;
}
document.onmousedown  = mouseSelect;
document.oncontextmenu  = ItemSelMenu;
</script>
</HEAD>

<!-- STEP TWO: Copy this code into the BODY of your HTML document  -->

<BODY>

<!--------------------- BEGIN POPUP MENU -------------------------->
-->
<h2>Here</h2>
<div id="menudiv" style="position:absolute; display:none; top:0px; left:0px;z-index:10000;" onmouseover="javascript:overpopupmenu=true;" onmouseout="javascript:overpopupmenu=false;">
<table width=82 cellspacing=1 cellpadding=0 bgcolor=lightgray>
  <tr><td>
    <table width=80 cellspacing=0 cellpadding=0>
      <tr>
        <td id="item1" bgcolor="#FFFFFF" width="80" height="16" onMouseOver="this.style.backgroundColor='#EFEFEF'" onMouseOut="this.style.backgroundColor='#FFFFFF'">  <a href="#">Item 1</a></td>
      </tr>
      <tr>
        <td id="item2" bgcolor="#FFFFFF" width="80" height="16" onMouseOver="this.style.backgroundColor='#EFEFEF'" onMouseOut="this.style.backgroundColor='#FFFFFF'">  <a href="#">Item 2</a></td>
      </tr>
      <tr>
        <td id="item3" bgcolor="#FFFFFF" width="80" height="16" onMouseOver="this.style.backgroundColor='#EFEFEF'" onMouseOut="this.style.backgroundColor='#FFFFFF'">  <a href="#">Item 3</a></td>
      </tr>
      <tr>
        <td id="item4" bgcolor="#ffffff" width="80" height="16" onMouseOver="this.style.backgroundColor='#EFEFEF'" onMouseOut="this.style.backgroundColor='#FFFFFF'">  <a href="#">Item 4</a></td>
      </tr>
    </table>
  </td></tr>
</table>
</div>
<!--------------------- END POPUP MENU -------------------------->

-->
<p><center>
<font face="arial, helvetica" size"-2">Free JavaScripts provided<br>
by <a href="http://javascriptsource.com">The JavaScript Source</a></font>
</center><p>
