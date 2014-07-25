<?php
session_start();
# for testing only 
# unset ($_SESSION["session"]);
if (! isset($_SESSION["session"])){$_SESSION["session"]=rand();}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <!-- costumize and minimize Javascript libraries in order to reduce initial load time -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="language" content="en" />
    <title>Safe Routes to School - Davis, California</title>
    <link rel="stylesheet" type="text/css" href="lib/css/layout-default-latest.css" />
    <link rel="stylesheet" type="text/css" href="jquery-ui.css?rand=<?php echo rand();?><" />
    <!-- CUSTOMIZE/OVERRIDE THE DEFAULT CSS -->
    <link rel="stylesheet" type="text/css" href="custom.css?rand=<?php echo rand();?>" />
</head>


<div id = "north" class="ui-layout-north" style="display: none;">
    <div class="hl">Welcome to the City of Davis Safe Routes Google Map Tool</div>
    <div class="di">Please follow the steps below to find, use and/or comment on Suggested Walking Routes to School
    <a class = "emptylink" href="#">[Help]</a></div>
</div>

<div class="ui-layout-center" id="map_canvas" style="display: none;"> 
    <p class="ui-layout-content ui-widget-content ui-resizable"></p>
</div>

<div class="ui-layout-west" id="menus" style="display: none;">
  <div id="accordion1" class="basic">  

    <h3><a href="#a" id = "schoolname"><table border="0" cellpadding="0"><tr><td valign="top" width="50">Step 1:</td><td valign="top">Type in your address and select your school</td></tr></table></a></h3>
    <div class="acc_cont">
        <p>First, type in your address or closest intersection (start of your route):</p>
        <!-- address field code see http://code.google.com/apis/maps/documentation/javascript/geocoding.html--> 
        <input id="address" type="textbox" value="" size="23">
        <input id="submit_address_button" type="button" value="Find">
        <p>Second, pick your school from the drop-down menu (end of your route):</p>
        <p><select id = "select_school"><option value="0,0#0">SELECT SCHOOL</option></select></p>
    </div>
    
    <h3><a href="#b"><table border="0" cellpadding="0"><tr><td valign="top" width="50">Step 2:</td><td valign="top">Find routes</td></tr></table></a></h3>
    <div id = "route_text" class="acc_cont">
	<table>
	    <tr>
		<td colspan="2">
		    <p>The Safe Routes program has identified the best, most direct walking routes to school.
		    These routes are highlighted in purple on the map. <a href="#" class="emptylink">[Learn More]</a></p>
		</td>
	    </tr>
	  <!--  <tr>
		<td colspan="2">
		   <button id="screenprint" disabled="disabled">Print Map on Screen</button>
		    <button id="pdfmap">View PDF Map with More Info</button><br>&nbsp;
		</td>
	    </tr> -->
	    <tr>
		<td>
		    <input type="checkbox" id="allschools"></input>
		</td>
		<td>
		    <p class="marker_menu">Show suggested routes<br>for all schools</p>
		</td>
	    </tr>
	    <tr style="display: none">
		<td>
		    <input type="checkbox" id="wsb" checked="checked"></input>
		</td>
		<td>
		    <p class="marker_menu">Show Walking School Bus Routes and Meet Up Spots<br><a href="#" class="emptylink">[Learn More]</a></p>
		</td>
	    </tr>
	    <tr>
		<td>
		    <input type="checkbox" id="wsb2"></input>
		</td>
		<td>
		    <p class="marker_menu">Show Walking School Bus Routes and Meet Up Spots for all Schools<br><a href="#" class="emptylink">[Learn More]</a></p>
		</td>
	    </tr>
	    <tr>
		<td colspan="2">
		    <p>Don't see a good route or want to suggest changes/additions?</p>
		</td>
	    </tr>
	    <tr>
		<td colspan="2">
		    <button id="routebutton">Suggest new route</button>
		</td>
	    </tr>
	    <tr>
		<td colspan="2">
		    <button id="commentbutton">Comment on routes</button>
		</td>
	    </tr>
	    <tr>
		<td colspan="2">
		    <p>&nbsp;<br>Click "Done" when you are finished</p>
		</td>
	    </tr>
	    <tr>
		<td colspan="2">
		    <button class="done2">Done</button>
		</td>
	    </tr>
	</table>
    </div>

    <h3><a href="#b"><table border="0" cellpadding="0"><tr><td valign="top" width="50">Step 3:</td><td valign="top">Suggest (draw) new routes</td></tr></table></a></h3>
    
    <div id = "route_text" class="acc_cont">
        <p>You will now draw the route you walk, bike or drive from your house to your school.</p>
        <p id = "dir-ed" style="display: none">To edit your route, click the &quot;Review &amp; Edit&quot; button</p>
        <p id = "dir-do" style="display: none">Click on the &quot;Done&quot; button once you finished your route.</p>
        <span><input type="button" id="another" value ="Draw route"></span>
        <span><input type="button" id="done1" value ="Done"></span>
        <p>Click "Draw Route" and use the mouse to single-click at the beginning and for every turn of your route. To finish your route&#8212;double-click the last point in the route. Last, leave a comment about the route you have drawn.</p>
        <p>Click "Done" when you are finished.</p>
    </div>
    
    <h3><a href="#c"><table border="0" cellpadding="0"><tr><td valign="top" width="50">Step 4:</td><td valign="top">Comment on locations along a route</td></tr></table></a></h3>
    <div id = "markers_menu" class="acc_cont">
	<p>Please zoom in to locate and comment on the features below.</p>
        <p>To comment on a location, click an icon below then click on the map.</p>
        <p id="m1" class="marker_menu"><img src="img/walk.png" width="24" />&nbsp;Sidewalk</p>
        <div id="in1" class="marker_in" style="display:none">Click on a sidewalk to comment on the<br><i>condition, obstructions</i> and/or <i>safety</i> of<br>an entire sidewalk block or specific<br>sidewalk location.</div>
	<p id="m2" class="marker_menu"><img src="img/road.png" width="24" />&nbsp;Street</p>
        <div id="in2" class="marker_in" style="display:none">Click on a specific street to comment on the: <i>traffic</i> and/or <i>safety</i> of that street block.</div>
	<p id="m3" class="marker_menu"><img src="img/traffic_lights_green.png" width="24" />&nbsp;Intersection</p> 
        <div id="in3" class="marker_in" style="display:none">Click within an intersection to comment on the: <i>safety</i> and <i>condition</i> of the crosswalks, curb ramps,  and traffic control devices (signals and stop signs) at that intersection.</div>
	<p id="m4" class="marker_menu"><img src="img/bike.png" width="24" />&nbsp;Bicycle Path</p>
        <div id="in4" class="marker_in" style="display:none">Click a street block or bicycle path to comment on the: <i>condition</i>, <i>obstructions</i> and/or <i>safety</i> of that block.</div>
	<!--<p id="m5" class="marker_menu"><img src="img/crash.png" width="24">&nbsp;Crash location</p>
        <div id="in5" class="marker_in" style="display:none">Click on the location where you know there has been a <i>bicycle</i> and/or <i>pedestrian</i> crash.</div>-->
	<p id="m6" class="marker_menu"><img src="img/comment.png" width="24" />&nbsp;General comment</p>
	<div id="in6" class="marker_in" style="display:none">Click on any location to make any type of comment regarding your route for walking or biking to school.</div>
        <p></p>
        <div><button class="done2">Done</button></div>
    </div>

    <h3><a href="#d"><table border="0" cellpadding="0"><tr><td valign="top" width="50">Step 5:</td><td valign="top">Review and submit comments</td></tr></table></a></h3>
    <div class="acc_cont">
    <div id = "submit2">
            <p>Review the map to ensure all your comments are identified. To edit comments, click-on the symbol or route. To add comments, go back to Step 2 or 3.</p>
	    <p>Provide your email or contact information below if you would like to be contacted about your comment or when a Walking School Bus is identified in your neighborhood:</p>
            <p>Email:&nbsp;<input id="email_field" type="textbox" value="" size="23"></p>
            <p>Click here to submit your comments.</p>
            <div><button id="submit11">Submit</button></div>
    </div>
    <div id="submit2b" style="display:none">
	    <p>Thank you for submitting comments for the Safe Routes to School Mapping Project - your input is invaluable to promoting safer walking and biking routes to schools in Solano County.</p>
    </div>
    </div>
        
</div>
  
<!-- other divs -->
<!--<div id="traffic" style="display:none"></div>-->
<div id="info_template_1" style="display:none">
    <form class="map-form"><input type="hidden" id="dbid" value=""><input type="hidden" id="mnr" value="">
        <table><tr><td><b>Sidewalk</b></td></tr>
        <tr><td>How would you rate the condition/safety of this sidewalk or trail for<br>walking and biking?</td></tr>
        <tr><td><select id="v11">
        <option value="empty">...</option>
        <option value="poor">poor</option>
        <option value="fair">fair</option>
        <option value="good">good</option>
        </select></td></tr>
        <tr><td>Would you be more likely to walk or bike if this location were improved?</td></tr>
        <tr><td><select id="v12">
        <option value="empty">...</option>
        <option value="yes">yes</option>
        <option value="no">no</option>
        </select></td></tr>
        <tr><td>Additional comments:</td></tr>
        <tr><td><textarea id = "comment_here"></textarea></td></tr>
        <tr><td><input type="button" class="map-go" value="Save &amp; Close" onclick = "s2s.save_recenter()" /><input type="button" class="map-delete" value="Remove" onclick="s2s.removemarker()" /></td></tr>
        </table>
    </form>
</div>
<div id="info_template_2" style="display:none">
    <form class="map-form"><input type="hidden" id="dbid" value=""><input type="hidden" id="mnr" value="">
        <table><tr><td><b>Street</b></td></tr>
        <tr><td>How would you rate the condition/safety of this street<br>(for walking and biking)?</td></tr>
        <tr><td><select id ="v21">
        <option value="empty">...</option>
        <option value="poor">poor</option>
        <option value="fair">fair</option>
        <option value="good">good</option>
        </select></td></tr>
        <tr><td>How would you rate the volume of traffic on this street?</td></tr>
        <tr><td><select id ="v22">
        <option value="empty">...</option>
        <option value="low">low</option>
        <option value="medium">medium</option>
        <option value="high">high</option>
        </select></td></tr>
        <tr><td>In your opinion, is speeding traffic a concern on this street?</td></tr>
        <tr><td><select id ="v23">
        <option value="empty">...</option>
        <option value="yes">yes</option>
        <option value="no">no</option>
        <option value="do not know">I don\'t know</option>
        </select></td></tr>
        <tr><td>Additional comments:</td></tr>
        <tr><td><textarea id = "comment_here"></textarea></td></tr>
        <tr><td><input type="button" class="map-go" value="Save &amp; Close" onclick = "s2s.save_recenter()" /><input type="button" class="map-delete" value="Remove" onclick="s2s.removemarker()"/></td></tr>
        </table>
    </form>
</div>
<div id="info_template_3" style="display:none">
    <form class="map-form"><input type="hidden" id="dbid" value="" /><input type="hidden" id="mnr" value="" />
        <table><tr><td colspan="2"><b>Intersection</b></td></tr>
        <tr><td colspan="4">How would you rate the condition/safety of this intersection (for walking or biking)?</td></tr>
        <tr><td colspan="4"><select id ="v31">
        <option value="empty">...</option>
        <option value="poor">poor</option>
        <option value="fair">fair</option>
        <option value="good">good</option>
        </select></td></tr>
        <tr><td colspan="4">How would you rate the condition/safety of the <i>crosswalk</i> at this intersection?</td></tr>
        <tr><td colspan="4"><select id ="v32">
        <option value="empty">...</option>
        <option value="poor">poor</option>
        <option value="fair">fair</option>
        <option value="good">good</option>
        </select></td></tr>
        <tr><td colspan="4">How would you rate the condition/safety of the <i>curb ramps</i> at this intersection<br>(for walking or biking)?</td></tr>
        <tr><td colspan="4"><select id ="v33">
        <option value="empty">...</option>
        <option value="poor">poor</option>
        <option value="fair">fair</option>
        <option value="good">good</option>
        </select></td></tr>
        <tr><td colspan="4">Do you believe this intersection is a high priority for any of the following<br>improvements/features?</td></tr>
        <tr><td>Crossing guard</td><td>
        <select id ="v34">
        <option value="empty">...</option>
        <option value="yes">yes</option>
        <option value="no">no</option>
        </select>
        </td>
        <td>Better traffic control</td><td>
        <select id ="v35">
        <option value="empty">...</option>
        <option value="yes">yes</option>
        <option value="no">no</option>
        </select>
        </td></tr>
        <tr><td>Better (or new) curb ramps</td><td>
        <select id ="v36">
        <option value="empty">...</option>
        <option value="yes">yes</option>
        <option value="no">no</option>
        </select>
        </td>
        <td>Better (or new) crosswalks</td><td>
        <select id ="v37">
        <option value="empty">...</option>
        <option value="yes">yes</option>
        <option value="no">no</option>
        </select>
        </td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr><td colspan="4">Additional comments:</td></tr>
        <tr><td colspan="4"><textarea id = "comment_here" rows="2" cols="40"></textarea></td></tr>
        <tr><td colspan="4"><input type="button" id="map-go" value="Save &amp; Close" onclick = "s2s.save_recenter()" /><input type="button" class="map-delete" value="Remove" onclick="s2s.removemarker()"/></td></tr>
        </table>
    </form>
</div>
<div id="info_template_4" style="display:none">
    <form class="map-form"><input type="hidden" id="dbid" value=""><input type="hidden" id="mnr" value="">
        <table><tr><td><b>Bicycle Path</b></td></tr>
        <tr><td>How would you rate the condition/safety of this bike route?</td></tr>
        <tr><td><select id ="v41">
        <option value="empty">...</option>
        <option value="poor">poor</option>
        <option value="fair">fair</option>
        <option value="good">good</option>
        </select></td></tr>
        <tr><td>Would you be more likely to bike if this location were improved?</td></tr>
        <tr><td><select id ="v42">
        <option value="empty">...</option>
        <option value="yes">yes</option>
        <option value="no">no</option>
        </select></td></tr>
        <tr><td>Additional comments:</td></tr>
        <tr><td><textarea id = "comment_here"></textarea></td></tr>
        <tr><td><input type="button" class="map-go" value="Save &amp; Close" onclick = "s2s.save_recenter()" /><input type="button" class="map-delete" value="Remove" onclick="s2s.removemarker()"/></td></tr>
        </table>
    </form>
</div>
<div id="info_template_5" style="display:none">
    <form class="map-form"><input type="hidden" id="dbid" value=""><input type="hidden" id="mnr" value="">
        <table><tr><td><b>Crash Location</b></td></tr>
        <tr><td>Type of crash:</td></tr>
        <tr><td><select id ="v51">
        <option value="empty">...</option>
        <option value="bycicle">bicycle</option>
        <option value="pedestrian">pedestrian</option>
        <option value="both">both</option>
        </select></td></tr>
        <tr><td>Additional comments:<tr></td>
        <tr><td><textarea id = "comment_here"></textarea></td></tr>
        <tr><td><input type="button" class="map-go" value="Save &amp; Close" onclick ="s2s.save_recenter()" /><input type="button" class="map-delete" value="Remove" onclick="s2s.removemarker()"/></td></tr>
        </table>
    </form>
</div>
<div id="info_template_6" style="display:none">
    <form class="map-form"><input type="hidden" id="dbid" value=""><input type="hidden" id="mnr" value="">
        <table><tr><td><b>General comment</b></td></tr>
        <tr><td><textarea id = "comment_here"></textarea></td></tr>
        <tr><td><input type="button" class="map-go" value="Save &amp; Close" onclick ="s2s.save_recenter()" /><input type="button" class="map-delete" value="Remove" onclick="s2s.removemarker()"/></td></tr>
        </table>
    </form>
</div>
<div id="infoline_template" style="display:none">
    <form class="map-form-2"><input type="hidden" id="dbid" value="">
        <table>
        <tr><td><b>Route</b></td></tr>
        <tr><td>First, choose the Type of Route and Mode (walking or biking):</td></tr>
        <tr><td><input id="radio1" type="radio" name="group1" value="actual" checked>Typical route my child (and/or I) currently take to school</input></td></tr>
        <tr><td><input id="radio2" type="radio" name="group1" value="potential">Route my child (and/or I) would take to school if we did not drive</input></input></td></tr>
        <tr><td>Mode:</td></tr>
        <tr><td><input id="radio3" type="radio" name="group2" value="walking" checked>Walking</input></td></tr>
        <tr><td><input id="radio4" type="radio" name="group2" value="biking">Biking</input></td></tr>
        <tr><td><input id="radio5" type="radio" name="group2" value="biking">Vehicle</input></td></tr>
        <tr><td>How would you rate the condition and/or safety of this route (for walking or biking)?</td></tr>
        <tr><td><select id ="v100">
        <option value="empty">...</option>
        <option value="poor">poor</option>
        <option value="fair">fair</option>
        <option value="good">good</option>
        </select></td></tr>
        <tr><td>Additional Comments:</td></tr>
        <tr><td><textarea id = "comment_here_2"></textarea></td></tr>
        <tr><td><input type="button" class="map-go-2" value="Save &amp; Close" onclick = "s2s.saveline()" /><input type="button" class="map-delete-2" value="Remove" onclick="s2s.removeline()"/></td></tr>
        </table>
    </form>
</div>

<div id="parkloctext" style="display:none">
    <div class="ai">
	Consider parking at or near this or other &ldquo;park and walk&ldquo;
	locations and walking to/from school with your child - to avoid and
	reduce congestion, and improve safety, around the school &hellip; and to fit in
	a little exercise.
    </div>
</div>

</body>
<!-- loading js at the end and from remote cloud repos might improve speed ==> test -->
<script src="http://code.jquery.com/jquery-1.8.2.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/jquery-ui.min.js"></script>
<script src="lib/js/jquery.layout-1.3.0.min.js"></script>
<script type="text/javascript" src="lib/js/jquery.layout.resizePaneAccordions-1.0.js"></script>
<script type="text/javascript" src="lib/js/jquery.ui.map.full.min.js"></script>
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false&libraries=drawing"></script>
<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript" src="http://google-maps-utility-library-v3.googlecode.com/svn/trunk/infobubble/src/infobubble-compiled.js"></script>
<script type="text/javascript" src="s2s.js?<?php echo rand(1,9999); ?>"></script>
</html> 