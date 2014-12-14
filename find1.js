/* Cool Javascript Find on this Page 
v. 2.2
Written by Jeff Baker on September, 8, 2007.
Copyright 2007 by Jeff Baker - 
updated 10/16/2012
www.seabreezecomputers.com
Paste the following javascript call in your HTML web page where
you want a button called "Find on this Page...":

<script type="text/javascript" language="JavaScript" 
src="find2.js">
</script>

When you click on the button a floating DIV will pop up
that will have a text box for users to enter in the text they
want to find on the page.  

WARNING: If you want to place a second "Find on this page..."
button somewhere on the same page then use the code below for
the second button, otherwise firefox and netscape will not
display the text that users type in and it will not find
text correctly because there will be two different text input
boxes with the same name:

<input type="button" value="Find on this page..." 
onclick="show();">
	
*/

/* You may edit the following variables */
var window_background = "#F5F5DC"; // the color of the pop-up window
var window_border = "#BDB76B";// the border color of pop-up window
var text_color = "black"; // the color of the text in window
var title_color = "#ffffff"; // color of window title text
var window_width = 200; // width of window
var window_height = 150; // height of window
var mozilla_opt = 1; // change to 0 to use Netscape and Firefox built-in search window
var start_at = 100; // Change to which character you want to start with on the page if IE gives an error because of searching in menus
// Example: start_at = 300, makes the find start at the 300th character on the page
/* Do not edit anything below this line */

//var ie = ((navigator.appVersion.indexOf("MSIE")!= -1)&&!window.opera)? true : false; // to detect if IE
var ie = (document.all)
//if (document.getElementById && !document.all)
if (window.find)
	var nav = 1; // to detect if netscape or firefox
else 
	var nav = 0;
var t = 0;  // used for timer to move window in IE when scrolling

var sel; // Selection object needed for Firefox
var range; // range object needed for Firefox

// The following is to capture mouse movement
// If Netscape or Mozilla -- then set up for mouse capture

if (!ie) 
{
	document.captureEvents(Event.MOUSEDOWN | Event.MOUSEMOVE | Event.MOUSEUP);
}

document.onmousedown = MouseDown;
document.onmousemove = MouseMove;
document.onmouseup = MouseUp;

// Temporary variables to hold mouse x-y pos's
var mousex = 120;
var mousey = 30;

if (ie)
{
	// this variable will hold all the text on the page for IE
	// We are creating a text range from the whole document body
	var txt = document.body.createTextRange();

	// this variable will bookmark the last find position
	// in an array of bookmarks so we can keep going to previous finds
	var bookmark = new Array();
	// bookmark the beginning of the text body
	//bookmark[0] = txt.getBookmark();
}

// variable to record number fo finds
var finds = 0;

function findit() 
{
	// put the value of the textbox in string - 
	var string = document.getElementById('fwtext').value;

	// 8-9-2010 Turn DIV to hidden just while searching so doesn't find the text in the window
	findwindow.style.visibility = 'hidden';
        document.getElementById('tab0').style.visibility = 'hidden';

	if (ie)
	{
	// Bookmark this position in bookmark array at finds variable
	bookmark[finds] = txt.getBookmark();
		
	// findText is IE's javascript function to find text in
	// a text range
	if (string)  // only call findText if there is a string or IE will have error
		if (txt.findText(string)) // if found
		{	
			// select() not only highlights the string but
			// it also moves the view to that location
			txt.select();
			// scrollIntoView() is just a duplicate of what
			// select does by jumping to the selection, so
			// we don't realy need it.
			txt.scrollIntoView();
			// moveStart('character') moves the position in the
			// text one character forward so that we can search
			// for the next case of string in the body
			//txt.moveStart('character');
			
			// collapse moves the insertion point to the end
			// of the range so we can search for the next value
			txt.collapse(false);
		
			test.innerHTML = "<center><input style='width: 100; background-color:lightgreen' id='button' type='button' value='Found'></center>";
		}
		else
		{
			test.innerHTML = "<center><input style='width: 100;background-color:pink' id='button' type='button' value='Not found'></center>";
			finds--;
		}
	}
	else // Netscape or firefox
	{
		/*if (finds == 0)
		{
			range = document.createRange();
			range.selectNodeContents(document.body);
			//sel = window.getSelection();
    		//sel.addRange(range);

			//range.setStart(document.body, 0);
			//range.setEnd(endNode, endOffset);

		}*/
		if (finds > 0)
		{

                        sel = window.getSelection(); // get selection
			// remove all ranges
			if(sel.rangeCount > 0) sel.removeAllRanges();
			// add last highlighted range
			sel.addRange(range);		
		}	
		// window.find(string, caseSensitive, searchBackwards)
		//for (i=0;i <= finds; i++)


		if (string != "")	
		{
			if (window.find(string, false, false))
			{
				// In Firefox (not Netscape) when we press the
				// Next or Prev buttons in the DIV it looses the
				// selection, so we have to grab the selection
				// locations so we don't keep searching for only
				// the first found search over and over
				sel = window.getSelection(); // get selection
				if(sel.rangeCount > 0) // 10/16/2012
					range = sel.getRangeAt(0); // get object
				test.innerHTML = "<center><input style='width: 100;background-color:lightgreen' id='button' type='button' value='Found'></center>";
			}
			else
			{
				test.innerHTML = "<center><input style='width: 100;background-color:pink' id='button' type='button' value='Not found'></center>";
				finds--;
			}
		}
				
	}
	finds++;

	findwindow.style.visibility = 'visible';
        document.getElementById('tab0').style.visibility = 'visible';
       
}  // end function findit()


// This function is to find backwards by pressing the Prev button
function findprev()
{
	// put the value of the textbox in string
	var string = document.getElementById('fwtext').value;
	
	// 8-9-2010 Turn DIV to hidden just while searching so doesn't find the text in the window
	findwindow.style.visibility = 'hidden';
        document.getElementById('tab0').style.visibility = 'hidden';
	
	if (ie)
	{
	// if they found only 0 or 1 occurance then don't do anything
	// because they haven't found enough to go backwards
	if (finds < 2)
	{
		findwindow.style.visibility = 'visible';
                document.getElementById('tab0').style.visibility = 'visible';                       
               
		return;  
	}
	// Make finds variable go back to previous find
	finds = finds - 2;  // I don't know why I have to go back 2
	
	// move back to previously bookmarked position
	txt.moveToBookmark(bookmark[finds]);
	
	// select it
	findit();
	}
	else // if netscape or firefox
	{
			if (finds > 0)
			{
				sel = window.getSelection(); // get selection
				// remove all ranges
				if(sel.rangeCount > 0) sel.removeAllRanges();
				// add last highlighted range
				sel.addRange(range);		
			}
		// window.find(string, caseSensitive, searchBackwards)
		if (string != "")
                    if(window.find(string, false, true) == true){
			//test.innerHTML = window.find(string, false, true);
                        test.innerHTML = "<center><input style='width: 100;background-color:lightgreen' id='button' type='button' value='Found'></center>";
                    }else{
                        test.innerHTML = "<center><input style='width: 100;background-color:pink' id='button' type='button' value='Not found'></center>";
                    }
		// In Firefox (not Netscape) when we press the
			// Next or Prev buttons in the DIV it looses the
			// selection, so we have to grab the selection
			// locations so we don't keep searching for only
			// the first found search over and over
			sel = window.getSelection(); // get selection
			if(sel.rangeCount > 0) // 10/16/2012
				range = sel.getRangeAt(0); // get object
	}
	
	findwindow.style.visibility = 'visible';
        document.getElementById('tab0').style.visibility = 'visible';

} // end findprev()


// This function looks for the ENTER key (13) 
// while the find window is open, so that if they user
// press ENTER it will do the find next
function checkkey(e)
{	
	var keycode;
	if (window.event)  // if ie
		keycode = window.event.keyCode;
	else // if Firefox or Netscape
		keycode = e.which;
	
	//test.innerHTML = keycode;
	
	if (keycode == 13) // if ENTER key
	{	
		// For some reason in IE, 
		// I have to focus on the 'NEXT' button
		// or finding by the Enter key does not always work.
		if (ie)
			document.getElementById('btn').focus();
		findit(); // call findit() function (like pressing NEXT)	
	}
} // end function checkkey()


// This function makes the findwindow DIV visible
// so they can type in what they want to search for
function show()
{
	if (ie || mozilla_opt == 1)
	{
	//var findwindow = document.getElementById('findwindow');
	
	// Object to hold textbox so we can focus on it
	// so user can just start typing after "find" button
	// is clicked
	var textbox = document.getElementById('fwtext');
	
	// Make the find window visible
	findwindow.style.visibility = 'visible';
	//fwtext.style.visibility = 'visible';
	
	// Put cursor focus in the text box
	textbox.focus();
	
	// Call timer to move textbox in case they scroll the window
	t = setInterval('move_window();', 50); 	
	
	// Setup to look for keypresses while window is open
	document.onkeydown = checkkey;
	
	}
	else  // if netscape or firefox
		window.find();
	// Note: Netscape and Firefox have a built in find window
	// that can be called with window.find()
	// They also have a find like IE's with self.find(string, 0, 1)
	// But I can't find any instructions on how to use it to
	// keep searching forward on "Next" button presses
	
} // end function show()


// This function makes the findwindow DIV hidden
// for when they click on close
function hide()
{
	//var findwindow = document.getElementById('findwindow');
	
	findwindow.style.visibility = 'hidden';
	
	// turn off timer to move window on scrolling
	clearTimeout(t);
	
	// Make document no longer look for enter key
	document.onkeydown = null;
	
} // end function hide()


// This function resets the txt selection pointer to the
// beginning of the body so that we can search from the
// beginning for the new search string when somebody
// enters new text in the find box
function resettext()
{
	if (ie)
	{
		txt = document.body.createTextRange();

		//txt.select();
	}
	finds = 0;
} // end function reset()


// This function makes the find window jump back into view
// if they scroll while it is open or if the page automatically
// scrolls when it is hightlighting the next found text
function move_window()
{

	//var findwindow = document.getElementById('findwindow');	
	
	// get current left, top and height of find_window
	fwtop = parseFloat(findwindow.style.top);
	fwleft = parseFloat(findwindow.style.left);
	fwheight = parseFloat(findwindow.style.height);
	
	// get current top and bottom position of browser screen
	if (document.documentElement.scrollTop) // Needed if you use doctype loose.htm
		current_top = document.documentElement.scrollTop;
	else 
		current_top = document.body.scrollTop;
	if (document.documentElement.clientHeight)
	{
		if (document.documentElement.clientHeight > document.body.clientHeight)
			current_bottom = document.body.clientHeight + current_top;
		else
			current_bottom = document.documentElement.clientHeight + current_top;
	}
	else
		current_bottom = document.body.clientHeight + current_top;
	
	// get current left and right position of browser
	if (document.documentElement.scrollLeft) // Needed if you use doctype loose.htm
		current_left = document.documentElement.scrollLeft;
	else 
		current_left = document.body.scrollLeft;
	if (document.documentElement.clientWidth)
	{
		if (document.documentElement.clientWidth > document.body.clientWidth)
			current_right = document.body.clientWidth + current_left;
		else
			current_right = document.documentElement.clientWidth + current_left;
	}
	else
		current_right = document.body.clientWidth + current_left;
	
	//test.innerHTML = current_right + ',' + current_left;
	
	// Only move window if it is out of the view
	if (fwtop < current_top)
	{	
		// move window to current_top
		findwindow.style.top = current_top + 'px';	 
	}
	else if (fwtop > current_bottom - fwheight)
	{
		// move to current_bottom
		findwindow.style.top = current_top + 'px'; //current_bottom - fwheight + 'px';	 
	}
	
	// Only move left position if out of view
	if (fwleft < current_left ||
		fwleft > current_right)
	{
	findwindow.style.left = current_left + 'px';
	}
	
	/* var test = document.getElementById('test');
	test.innerHTML = 'find window: ' + fwtop
		+ ' curr_bottom: ' + current_bottom; */

} // end function move_window()

function MouseDown(e) 
{
    if (over == 1)
    	DivID = 'findwindow';
	
	if (over)
    {    
		if (ie) 
		{
            objDiv = document.getElementById(DivID);
            objDiv = objDiv.style;
            mousex=event.offsetX;
            mousey=event.offsetY;
        }
        else // if Mozilla or Netscape 
		{
            objDiv = document.getElementById(DivID);
            mousex=e.layerX;
            mousey=e.layerY;
            return false;
        }
    }
}



function MouseMove(e) 
{
    
	// get current top 
	if (document.documentElement.scrollTop) // Needed if you use doctype loose.htm
		current_top = document.documentElement.scrollTop;
	else 
		current_top = document.body.scrollTop;
	
	
	// get current left
	if (document.documentElement.scrollLeft) // Needed if you use doctype loose.htm
		current_top = document.documentElement.scrollLeft;
	else 
		current_left = document.body.scrollLeft;
	
	
	if (objDiv) 
	{
        if (ie) 
        {
            objDiv.pixelLeft = event.clientX-mousex + current_left;
            objDiv.pixelTop = event.clientY-mousey + current_top;
            return false;
        }
		else // if Mozilla or Netscape
		{
            objDiv.style.left = (e.pageX-mousex) + 'px';
            objDiv.style.top = (e.pageY-mousey) + 'px';
            return false;
        }
    }
}  // end function MouseMove(e)

//
//
//
function MouseUp() 
{
    objDiv = null;
}



// Create findwindow DIV but make it invisible
// It will be turned visible when user clicks on
// the "Find on this page..." button
function create_div(dleft, dtop, dwidth, dheight)
{

    if (document.documentElement.scrollTop) // Needed if you use doctype loose.htm
	current_top = document.documentElement.scrollTop;
	else 
	current_top = document.body.scrollTop;
	
    if (document.getElementById('findwindow'))
    {
        findwindow = document.getElementById('findwindow');
        //win_iframe = document.getElementById('win_iframe');
    }
    else
    { 
	findwindow.id = "findwindow";
	findwindow.style.position = 'absolute';

        //document.body.appendChild(findwindow);
        document.body.insertBefore(findwindow, document.body.firstChild);
        findwindow.className = 'findwindow';
        findwindow.style.visibility = 'hidden';
	}
    
        findwindow.style.backgroundColor = window_background;
        findwindow.style.border = '2px solid ' + window_border;
        findwindow.style.color = text_color;
	findwindow.style.width = window_width + 'px';
	findwindow.style.height = + window_height + 'px';
        findwindow.style.top = 10;
	findwindow.style.left = 1000;
	findwindow.style.padding = '1px'; 
	findwindow.style.zIndex = 2000;
	findwindow.style.fontSize = '14px';
	findwindow.style.overflowX = 'hidden';
	//findwindow.style.display = "block";
	
	// This part creates the title bar
	findwindow.innerHTML = '<div style="text-align: center'
	+ ';width: ' + (window_width-20) + 'px'
    	+ ';height: ' + (16) + 'px'
	+ ';cursor: move'  // turn mouse arrow to move icon
	+ ';border: 1px solid grey' //+ window_border + text_color
	+ ';background-color: ' + window_border
    	+ ';color: '  + title_color
	+ ';float: left' 
	+ ';" onmouseover="over=1;" onmouseout="over=0;">'
	+ '<label>Search the SSA log</label></div>';
	// This part creates the closing X
	findwindow.innerHTML += '<div onclick="hide();" style="text-align: center'
	+ ';width: ' + (16) + 'px'
	+ ';height: ' + (16) + 'px'
	+ ';cursor: hand' // make mouse arrow stay an arrow instead of turning to text arrow
	+ ';font-weight: bold'
	+ ';font-family: Tahoma'
    	+ ';color: #B22222'
	+ ';background-color: pink'
	+ ';border: 1px solid grey' //+ window_border
	+ ';float: right'
	+ ';">'
	+ 'X' // write the letter X
	+ '</div><br />\n';
// This part creates the instructions and the "find" button
	findwindow.innerHTML += '<div align="center" id="window_body" style="padding: 5px;">'
	+ '<br><label>Enter search string:</label>'
	+ '<input style="width: 160px" type="text" size="25" maxlength="25" id="fwtext"'
	+ ' onchange="resettext();">'
	+ '<br><input style="width: auto" type="button" value="Find Prev" onclick="findprev();">'
	+ '<input style="width: auto" id="btn" type="button" value="Find Next" onclick="findit();">'
	+ '</div>\n'
	+ '<div id="test"><br /></div>';
	
} // end function create_div()


// This part creates a visible button on the HTML page to
// where the script is pasted in the HTML code
document.write('<input style="width: 120px" type="button" value="Search SSA log..."'
	+ ' onclick="show();document.getElementById(\'view\').style.display=\'block\';">');
	
// Create the DIV
var findwindow = document.createElement("div");
create_div();

// over variable is whether mouse pointer is over the DIV to move
var over = 0;

// Object to hold findwindow for MouseMove
var objDiv = null;

// ID of DIV for MouseMove functions
var DivID = null;

var test = document.getElementById('test');

