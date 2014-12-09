<?php
/*
  _, _ _, _ __, _,  __,    _, _ ___ __,    _, _,_ __, _ ___  
 (_  | |\/| |_) |   |_    (_  |  |  |_    / \ | | | \ |  |   
 , ) | |  | |   | , |     , ) |  |  |     |~| | | | / |  |   
  ~  ~ ~  ~ ~   ~~~ ~~~    ~  ~  ~  ~~~   ~ ~ `~' ~~  ~  ~   Multisite
 * 
 * Copyright (C) 2012 Terry Heffernan. All rights reserved.
 * Technical support: http://simplesiteaudit.terryheffernan.net
 */


session_start();
error_reporting (E_ALL ^ E_NOTICE);
$ssa_ver = '';
$logs_dir = '';
$date = date('dMy H:i:s');

include 'version.php';
include 'functions/index_functions.php';

if($_GET['server']){
   $ftp_server = trim($_GET['server']); // Leave
}else{
   $ftp_server = "";
}

if (isset($_GET['fileEmptied'])){
$fileEmptied = $_GET['fileEmptied'];
}

if(isset($_GET['server'])){
    $ftp_server = stripslashes($_GET['server']);
}elseif(isit_dir($logs_dir) > 0 && $ftp_server == ""){
    $scan = scandir($logs_dir);
    $i = 0;
    foreach($scan as $value){
      if($i == 0){
        if($value != '.' && $value != '..' && $value != '.htaccess' && $value != '.htpasswd'){
            $ftp_server = stripslashes(trim($value));
            $i++;
        }
      }
    }
 }

$dbsettings = $logs_dir.'/'.$ftp_server.'/db_settings.txt';
if(file_exists($dbsettings)){
    $file = file($dbsettings);
    $db_server = trim($file[0]); 
    $db_user = trim($file[1]);
    $db_pass = trim($file[2]);
    $db_name = trim($file[3]);
        
    $key = 'let@me@in@NOW';         
    $decrypt = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($db_pass), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
    $db_pass = trim($decrypt);
}else{
    header("Location: index1.php");
}

// Clear history of POST variables, so that browser doesn't object to a refresh.
if (isset($_GET['start_prg'])) {
    simple_prg(true, 'PHP_SELF');
} else {
    simple_prg(null, 'PHP_SELF');
}

$u = simple_prg(); // get the current transaction id 
setcookie("uid", $u, time() + 1200); // record the current transaction id, with 20 minute time-out
   
$site_table = 'ssa_'.str_replace('-','$',str_replace('.','_',$ftp_server)).'_site';

if($db_server != ""){
$is_table_empty = is_table_empty($site_table,$db_server,$db_user,$db_pass,$db_name);
}

if($ftp_server != "" && $ftp_server != null && $db_server != "" && $is_table_empty > 0){
    $con = @mysql_connect($db_server,$db_user,$db_pass)or die(mysql_error())or die('Unable to connect to MySQL server: '.$db_server.'<br>Please check that the following details are correct:<br>
        db server name<br>
        db user name<br>
        db password<br>
        <a href="index1.php?check_db_details=Y">Click to reload form</a>');
    mysql_select_db($db_name, $con)or die(mysql_error());
    
    $site_table = 'ssa_'.stripslashes(str_replace('-','$',str_replace('.','_',$ftp_server))).'_site';
    $log_table = 'ssa_'.stripslashes(str_replace('-','$',str_replace('.','_',$ftp_server))).'_log';
    $result = mysql_query("SELECT * FROM $site_table") or die(mysql_error());
    $r = mysql_query("SELECT COUNT(*) FROM $log_table ");
    
    while($rows = mysql_fetch_array($r)) 
    {
    $show_buttons = $rows[0];
    }
    
    while($row = mysql_fetch_array($result)) 
    {
       $id = $row['id'];
       $subject = $row['email_subj'];
       $skipfile = $row['skip_files'];
       $skipdir = $row['skip_dir'];
       $alert = $row['email_alert'];
       $message = $row['email_header'];
       $from = $row['from_addr'];
       $createLog = $row['SSA_log'];
       $cronlogpath = $row['cron_path'];
       $rename_file = $row['rename_file'];
       //$dte = $row['date'];
       //$tme = $row['time'];
    }
    mysql_close($con)or die(mysql_error()); 
}

if($_POST['submit']){
    $ftp_server = trim($_POST['site_list']);
    $subject = trim($_POST['subject']);
    $skipfile = trim($_POST['skipfile']);
    $skipdir = trim($_POST['skipdir']);
    $alert = trim($_POST['alertAddress']);
    $from = trim($_POST['fromAddress']);
    $message = trim($_POST['message']);
    $createLog = trim($_POST['createLog']);
    $cronlogpath = trim($_POST['cronlogpath']);
    $rename_file = trim($_POST['rename_file']);
}

$clear = trim($_GET['clear']);
$fileEmptied = $_GET['fileEmptied'];

if (file_exists($logs_dir)) {
    if (!is_removeable($logs_dir)) { // Check if 'logs' directory is writeable. Files within the directory will also be checked.
        echo '<p class="sub1" style="text-align: left"><img border="0" src="images/cross.jpg" align="left" width="16" height="16"> Error!</p>
        <p class="sub1" style="text-align: left">Your \'logs\' directory doesn\'t exist or is not writeable or files within it are not writeable.</p>
        <p class="sub1" style="text-align: left">Please read the <a href="readme.html">README file.</a></p>
        <p class="sub1" style="text-align: left">Setup is unable to continue.</p>';
        exit();
    }
}

$id = $_GET['uniqid'];

if ($createLog == 'Y' || $clear == 'Y') { // Show checkbox checked or not
    $log = 'checked';
} elseif($createLog == 'Y' && $uid == $id) {
    $log = 'checked';
}else{
    $log = "";
}

// Generate sites list drop-down menu    
$html = Select($logs_dir, 'site_list'/*, $site_list*/);

if($_POST['submit'] && $ftp_server != ""){
    store_details($db_server, $db_user, $db_pass, $db_name, $ftp_server, $subject, $skipfile, $skipdir, $rename_file, $alert, $from, $message, $createLog, $cronlogpath);
}

// Start html     
echo '
  <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
  <html>
  <head>
  <link href="css/simplesiteaudit.css" rel="stylesheet" type="text/css">
  <script language="JavaScript" src="validation/validate.js"
    type="text/javascript" xml:space="preserve"></script>

  <script type="text/javascript">
  function populate(server, default_subject, default_ignore_files, TO_address, FROM_address, default_message,cronlogpath){
     document.myform.site_list.value=server; 

     document.myform.routeDirectory.value=server;
     document.myform.subject.value=default_subject;
     document.myform.skipfile.value=default_ignore_files;
     document.myform.alertAddress.value=TO_address;
     document.myform.fromAddress.value=FROM_address;
     document.myform.message.value=default_message;
     document.myform.cronlogpath.value=cronlogpath;
     document.myform.rename_file.value=rename_file;
  }
  
 function open_win(){
    window.open("manual_run.php?server='.$ftp_server.'");
    //window.open("ftp_scan.php?server='.$ftp_server.'");
 }
 
function pageScroll2() {
    	window.scrollBy(0,50); // horizontal and vertical scroll increments
      //alert( getPosition2() ); 
       if( getPosition2() <= 700 &&  getPosition2() >= 165){
    	scrolldelay = setTimeout(\'pageScroll2()\',50); // scroll increments every 50 milliseconds
       }
}

function getPosition2(){
    var e = document.getElementById(\'cronlogcontents\');
    var offset = {x:0,y:0};
    while (e)
    {
        offset.x += e.offsetLeft;
        offset.y += e.offsetTop;
        e = e.offsetParent;
    }

    if (document.documentElement && (document.documentElement.scrollTop || document.documentElement.scrollLeft))
    {
        offset.x -= document.documentElement.scrollLeft;
        offset.y -= document.documentElement.scrollTop;
    }
    else if (document.body && (document.body.scrollTop || document.body.scrollLeft))
    {
        offset.x -= document.body.scrollLeft;
        offset.y -= document.body.scrollTop;
    }
    else if (window.pageXOffset || window.pageYOffset)
    {
        offset.x -= window.pageXOffset;
        offset.y -= window.pageYOffset;
    }

    return offset.y;
}

</script>

<script type="text/javascript">
function selected()
{
document.getElementById("selected").bgColor=#ccc;
}
</script>
        
 <script type="text/javascript">// <![CDATA[
 function printDiv(divName) {
     var printContents = document.getElementById(divName).innerHTML;
     var originalContents = document.body.innerHTML;

     document.body.innerHTML = printContents;

     window.print();

     document.body.innerHTML = originalContents;
}
// ]]></script>

<SCRIPT LANGUAGE="JavaScript">
function respConfirm () {
     var response = confirm(\'Click OK to empty the SSA log:\');
     if (response) window.location.href=\'clear_log.php?server='.$ftp_server.'\';
}
function ScrollUp() {
var h = window.innerHeight-16;
window.scrollBy(0,-h);
}

function ScrollDown() {
var h = window.innerHeight-16;
window.scrollBy(0,h);
}
</SCRIPT>
<style>
div.floating-menu {position:fixed;width:60px;}
div.floating-menu a, div.floating-menu h3 {display:block;margin:10 53.0em;}
</style>

<!--[if IE]>
<style>
#tab6{
border: 1px solid gray
}
th, td {
    padding: 15;
}
input {
margin: 0;
padding: 1;
width: auto;
overflow: visible;
}
input.text{
width:350px;
}
image{
border: 0px;
}
</style>
<![endif]-->
</head><body>';

if($show_buttons > 0){
echo '<![if !IE]>
<div id="floating-menu" class="floating-menu">';
echo '<a><input onclick="window.location.href=\'#top\'" style="background-color:#F5F5DC; width: 120px;" title="Top of page" type="button" name="top" value="Top" />
<input onclick="ScrollUp();" style="background-color:#E6E6FA; width: 120px;" title="Page Up" type="button" name="page_up" value="Page Up" />
<input onclick="document.getElementById(\'view\').style.display=\'block\';ScrollDown();" style="background-color:#E6E6FA; width: 120px;" title="Page Down" type="button" name="page_down" value="Page Down" />
<input onclick="document.getElementById(\'view\').style.display=\'block\';window.location.href=\'#bottom\'" style="background-color:#F5F5DC; width: 120px;" title="Bottom of page" type="button" name="bottom" value="Bottom" />
<input type="button" onclick="document.location.href=\'index.php?refresh=Y&server='.$ftp_server.'\';" style="width:120;background-color: lightgreen;" name="Refresh" value="Refresh" />
<script type="text/javascript" language="JavaScript" src="find1.js"></script>
</a></div><![endif]>';
};  // SimpleSiteAudit</font> Admin<label> Multisite <img  align="top" title="Site logo" alt="Site logo" border="0" src="images/ssa-logo.png" width="412" height="88">
echo '
  <a name="top"></a>
  <table class="tab1"><tr><td>
  <table padding="15px" id="tab0" class="tab0">
  <tr><td colspan="2">
  <p class="sub2"><font color="brown">SimpleSiteAudit</font> Admin<label> Multisite v'.$ssa_ver.'</label>
  <img border="0" src="images/spacer.gif" width="90" height="0">
  <input type="button" id="back" onclick="window.location.href=\'index1.php?server='.$ftp_server.'\'" style="width: auto; background-color: #ffffff;" name="back" alt="Back to FTP/DB setup form" value="Back to FTP/DB setup">
  <input type="button" id="utilities" onclick="window.location.href=\'filediff/utilities.html\'" style="width: auto; background-color: #ADFF2F;" name="compare" alt="Compare 2 text-based files" value="Utilities">
  <br /><br />
    <label>Step 2: Enter your preferences for each site you wish to monitor.<label>
    <img border="0" src="images/spacer.gif" width="100" height="5">
    <small>Fields marked with <a class="asterisk">*</a> are required</small><br /><br />';
echo '<div id="form"><form name="myform" method="POST" action="index.php">
    <label>Select site: </label>';
echo $html; // Will contain a drop-down list of all sites being monitored

echo '<img border="0" src="images/spacer.gif" width="150" height="2">';
echo '<br />';

echo '<tr><td>';
echo '<div id="myform">';

echo '<a class="ToolText" onclick="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'"> 
  <![if !IE]><img border="0" src="images/spacer.gif" width="0" height="22"><br /><![endif]>
  <!--[if IE]><img border="0" src="images/spacer.gif" width="0" height="19"><br /><![endif]--> 
  <img alt="Click for info" title="Click for info" border="0" src="images/info.png" width="16" height="16">  
  <span><b>\'Email address to alert\'</b><br /><br />This address will be used as the recipient\'s address in the email alert. A short, comma seperated list of addresses is allowed if required (Max characters: 300)</span></a>
  <img border="0" src="images/spacer.gif" width="10" height="5">
  <small><font color="red">*</font></small><label>Email address to alert:</label><br /> 
  <input id="input1" size="50" type="text" name="alertAddress" value="';
    echo $alert;
echo '" id="alertAddress" />
<br />
   <a class="ToolText" onclick="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'"> 
  <br><img alt="Click for info" title="Click for info" border="0" src="images/info.png" width="16" height="16">  
  <span><b>\'Subject for message\'</b><br /><br />This will be used as the \'Subject\' line in the email that is transmitted when changes are found.</span></a>
  <img border="0" src="images/spacer.gif" width="10" height="5">
  <small><small><font color="red">*</font></small></small><label>Subject for message:</label><br />
  <input id="input2" size="50" type="text" name="subject" value="';
    echo $subject;
echo '" />
<br />
  <a class="ToolText" onclick="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'"> 
  <br /><img alt="Click for info" title="Click for info" border="0" src="images/info.png" width="16" height="16">  
  <span><b>\'List of files and/or file extensions to ignore\'</b><br /><br />This should consist of a list of comma or linefeed separated file names, including the file extension.<br />
  Be careful with the selection of file names. If, for example, index.php is entered, all files with that name, throughout the site, will be ignored. (This field is optional).<br><br>
  If you want to exculde catagories of files, you can mix in some file extensions (including the dot), e.g. your list might look something like this:
  robots.txt,.gif,.jpg,filename.doc,.pdf<p>If you wish to ignore certain file types in a specific folder only, you can add the path to the folder and the extension to ignore. 
  The path must include the site\'s root directory name, e.g. htdocs - your entry to this field might look like this:<br>htdocs/folder1/folder2/folder3/.jpg<br> 
  only file type .jpg will be ignored and only in folder3. All other .jpg files will be monitored. </p><p>It is possible to mix in all of the above options.</p></span></a>
 <img border="0" src="images/spacer.gif" width="10" height="5">
 <font color="#fbfbfb">-</font><label>List of files and/or file extensions to ignore:</label> 
  <textarea id="textarea1" rows="4" name="skipfile" id="skipfile" style="width:350px;">';echo $skipfile.'</textarea>';

echo '
<br />
  <a class="ToolText" onclick="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'"> 
  <br /><img alt="Click for info" title="Click for info" border="0" src="images/info.png" width="16" height="16">  
  <span><b>\'List of directories to ignore\'</b><br /><br />This should consist of a list of comma or linefeed separated directory names.<br />
  Be careful with the selection of directory names. If, for example, the \'images\' directory is entered, all directories with that name, 
  throughout the site, will be ignored.<p>If you wish to ignore a specific folder only, you can add the path to the 
  folder. The path must include the site\'s root directory name, e.g. htdocs - your entry to this field might look like this:
htdocs/folder1/folder2/folder3<br>All files in folder3 will be ignored. Any other directory that is named \'folder3\' and all sub-folders
in folder3 will be monitored.</p>To ignore all files AND folders in a specified directory, enter the path to the directory with an asterisk at the end. 
e.g. htdocs/folder1/folder2* everything in folder2 will be ignored.</p>  (This field is optional)</span></a>
 <img border="0" src="images/spacer.gif" width="10" height="5">
 <font color="#fbfbfb">-</font><label>List of directories to ignore:</label>   
  <textarea id="textarea2" rows="4" name="skipdir" id="skipdir" style="width:350px;">';echo $skipdir.'</textarea>';
echo '</div>';

echo '<td><![if !IE]><img border="0" src="images/spacer.gif" width="0" height="2"><![endif]>
<div id="myform2">

  <a class="ToolText" onclick="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'"> 
  <br /><img alt="Click for info" title="Click for info" border="0" src="images/info.png" width="16" height="16">  
  <span><b>\'The \'From\' email address\'</b><br /><br />This address will be used as the sender\'s address in the email alert.</span></a>
  <img border="0" src="images/spacer.gif" width="10" height="5">
  <small><font color="red">*</font></small><label>The \'From\' email address:</label><br /> 
  <input id="input3" size="50" type="text" name="fromAddress" value="';
    echo $from;
echo '" id="fromAddress" />
<br />
  <a class="ToolText" onclick="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'"> 
  <br /><img alt="Click for info" title="Click for info" border="0" src="images/info.png" width="16" height="16">  
  <span><b>\'Message body opening line\'</b><br /><br />This text will be used as the header line in the email body.</span></a>
  <img border="0" src="images/spacer.gif" width="10" height="5">
  <small><font color="red">*</font></small><label>Message body opening line:</label><br /> 
  <input id="input4" size="50" type="text" name="message" id="message" value="';
    echo $message;
echo '" id="message" />
<br />
  <a class="ToolText" onclick="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'"> 
  <br /><img alt="Click for info" title="Click for info" border="0" src="images/info.png" width="16" height="16">  
  <span><b>\'Path to and name of your Cron log\'</b><br /><br />Relative to your SSA files location. e.g. <font color="blue">../../cronfilename.txt</font> The \'view file\' button will not appear if this file does not exist.<br />
  Recommend leaving this field empty if using the SSA log file and vice versa.<br /><br />
  The log contents will appear in a read only textarea. Further formatting is not possible due to unknown input by the Cron process itself. There is no \'Clear log\' button for this feature.</span></a>
  <img border="0" src="images/spacer.gif" width="10" height="5">
  <small><font color="#fbfbfb">-</font></small><label>Path to, and name of your Cron log:</label><br /> 
  <input id="input5" size="50" type="text" name="cronlogpath" value="';
    echo $cronlogpath;
echo '" id="cronlogpath" /><br />';
if($cronlogpath != "" && !file_exists($cronlogpath)){
    echo '<img border="0" src="images/spacer.gif" width="220" height="0">
        <label><font color="brown">Cron log not found</font></label>'; 
}
echo '
  <a class="ToolText" onclick="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'"> 
  <br /><img alt="Click for info" title="Click for info" border="0" src="images/info.png" width="16" height="16">  
  <span><b>\'Files to rename\'</b><br /><br />List of comma separated file names to be re-named on discovery. 
  If found, these files will be re-named by giving them a \'_renamed.by.ssam\' extension. This feature might be useful where you know of certain file names that have caused you problems in the past. 
  Re-naming the file as soon as it is detected, will prevent it being used for malicious purposes.<br><br>An email alert will be sent on detection.<br><br>
  Take care when using this feature.</span></a>
  <img border="0" src="images/spacer.gif" width="10" height="5">
  <small><font color="#fbfbfb">-</font></small><label>List of files to rename:</label><br /> 
  <input id="input6" size="50" type="text" name="rename_file" value="';
    echo $rename_file;
echo '" id="rename_file" /><br />';
echo '
  <br />
  <a class="ToolText" onclick="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'"> 
  <img alt="Click for info" title="Click for info" border="0" src="images/info.png" width="16" height="16">  
  <span><b>\'Create an SSA log\'</b><br /><br />Choose this option if you would prefer to set up a log in the database, as opposed to the file created by the Cron process.<br />
  <br />The advantages in choosing this option is to allow a nicely formatted view of the log. Also, the SSA log will only record the script output if changes are detected. As opposed to The Cron log, which will record the output for every run.<br /><br />The log can grow quite large. 
  Therefore, it is advisable to empty it periodically via the \'Clear SSA log\' button. The \'View/hide SSA log\' and the \'Clear SSA log\' buttons will appear after the first changes have been detected. They will not be visible if the log is empty.</span></a>  
  <img border="0" src="images/spacer.gif" width="10" height="5">
  <label>Create an SSA log: </label>
  <input style="width: auto;" type="checkbox" name="createLog" value="Y" ';
echo $log;
echo '/><br /><small>(Log will only be updated when changes are detected.)</small>';
echo '</td></tr><tr><td colspan="2">';
//Submit button
echo '<![if !IE]><br /><![endif]><input style=" width: auto;" title="Submit to update database." type="submit" name="submit" value="Submit settings" onclick="location.href=\'index1.php?server='.$ftp_server.'\'"/>';

    $log_table = 'ssa_'.str_replace('-','$',str_replace('.','_',$ftp_server)).'_log';
   
if (is_table_empty($log_table,$db_server,$db_user,$db_pass,$db_name) > 0 && $log == 'checked') {
    echo '
    <input  onclick="respConfirm ();"  title="Clear the SSA log" style="width: auto;background-color: #ffffcc;" type="button" name="" value="Clear SSA log">
    <input onclick="toggle_visibility(\'view\');window.scroll(0,500);" style="width: auto;background-color: #ffffcc;" type="button" name="view_log" value="View/hide SSA log" >';
}//

if (is_table_empty($site_table,$db_server,$db_user,$db_pass,$db_name) > 0) {
    echo '
    <input style="width: auto;" title="Run the script on selected site" class="button" type="button" name="runScript" value="Run the SSA script" onClick="open_win()"/>';
}

if ($cronlogpath != "") {
   if(file_exists($cronlogpath)){
    echo '<input onclick="toggle_visibility(\'cronlogcontents\');pageScroll2()" style="width: auto;background-color: #FFD3DA;" type="button" name="view_cronlog" value="View/hide Cron log"><!--[if IE]></div><![endif]-->';
   }
}
//echo '</div>';
echo '<div id="fadeBlock" style="border:1px;align:top;height:25px;float:right;">';// Responses

//echo '<table style="border-collapse:collapse;border: 0px solid brown;background-color:#87CEEB;height:0px;width:250px;"><td>'; // responses table
// Confirmation response - settings saved
if ($id != $uid && $id != "" && $fileEmptied != 1) {
    include 'includes/confirm.html';
}

// Confirmation response - cleared log file   && $id != $uid           
if ($fileEmptied == 1) {
    include 'includes/empty_log.html';
}

if (is_table_empty($log_table,$db_server,$db_user,$db_pass,$db_name) == 0 && $_GET['refresh'] == 'Y') {
    include 'includes/log_is_empty.html';
}

echo '</div>';

     echo '</form>';
echo '<![if !IE]><br /><br /><center><![endif]><br><small>SSA v'.$ssa_ver.' | Check for the <a target="_blank" href="http://simplesiteaudit.terryheffernan.net/">latest version</a><![if !IE]></center><br /><![endif]>
      <!--Paypal form-->
<form target="_blank" action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="item_name" value="In support of SimpleSiteAudit Multisite project">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
<![if !IE]><center><![endif]>If you think this software is worthy of support, please 
<input type="submit" style=" width: auto;" value="Donate" border="0" name="submit" title="PayPal - The safer, easier way to pay online." alt="PayPal - The safer, easier way to pay online."><![if !IE]></center><![endif]>

<img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
</form>
<![if !IE]><center><![endif]><small><a target="_blank" href="http://simplesiteaudit.terryheffernan.net/forum?mingleforumaction=vforum&g=5.0">Feedback</a> would be appreciated. Thank you.</small><![if !IE]></center></div><![endif]>
';
echo '</td></tr>';
$uid = $_COOKIE["uid"]; // get unique transaction id from stored cookie.

echo '
    <script language="JavaScript" type="text/javascript"
        xml:space="preserve">//<![CDATA[
    var frmvalidator  = new Validator("myform"); 
    frmvalidator.EnableMsgsTogether();    
    frmvalidator.addValidation("alertAddress","req","Need Email address to alert    ");
    frmvalidator.addValidation("alertAddress","maxlen=1024");
    frmvalidator.addValidation("subject","req","Need subject for message    ");                                     
    frmvalidator.addValidation("fromAddress","req","Need the \'From\' email address    ");                                       
    frmvalidator.addValidation("message","req","Need message body opening line    ");
    //]]></script>
    ';

echo '
    </table>
    <table rowspan="2" border="0" width="400">
    <tr><td>';


if(file_exists($logs_dir .'/'.$ftp_server.'/'. 'db_settings.txt') && !$_POST['submit']){
  echo '<script>
    populate(
        \''.$ftp_server.'\',
        \''.$subject.'\', 
        \''.$skipfile.'\',
        \''.$alert.'\',
        \''.$from.'\',
        \''.$message.'\',
        \''.$cronlogpath.'\',
        \''.$rename_file.'\'
            )
     </script>';
}elseif($_POST['submit']){
  echo '<script>
    populate(
        \''.trim($_POST['site_list']).'\',         
        \''.trim($_POST['subject']).'\',
        \''.trim($_POST['alertAddress']).'\', 
        \''.trim($_POST['skipfile']).'\',
        \''.trim($_POST['fromAddress']).'\',
        \''.trim($_POST['message']).'\',
        \''.trim($_POST['cronlogpath']).'\',
        \''.trim($_POST['rename_file']).'\'
            )
     </script>';
}

echo '<div style="display: none;" class="cronlogcontents" id="cronlogcontents">';
    $log_contents = array();    
    if(file_exists($cronlogpath)){
      $log_contents = file($cronlogpath); // Parse the contents of cron log file into an array
    }
    
    $view_log = displayString($log_contents); // Parse the array values into a string

  echo '<p class="sub2"><br /><label>Cron log file contents - '.$ftp_server.' <input type="button" onclick="toggle_visibility(\'cronlogcontents\');" style="width:auto;background-color: #FFD3DA;" name="Hide_cronlog" value="Hide Cron log" /a></label></p>';
  echo '<textarea readonly rows="40" cols="30">';
  echo $view_log;
  echo '</textarea><br /><img border="0" src="images/spacer.gif" width="0" height="20">';
  echo '</div>'; 

// SSA log display
echo '<script>bookmark[0] = txt.getBookmark();</script>';    
echo '<div class="view" id="view">';
if($_GET['refresh'] == 'Y'){
    echo '<script>
        document.getElementById("view").style.display="block";
        </script>';
}
echo '<input class="noPrint" type="button" onclick="toggle_visibility(\'view\');" style="width:auto;background-color: #FFffcc;" name="Hide_ssa_log" value="Hide SSA log" /a>
<input class="noPrint" type="button" onclick="document.location.href=\'index.php?refresh=Y&server='.$ftp_server.'\';" style="width:auto;background-color: lightgreen;" name="Refresh" value="Refresh" /a>
';
if (is_table_empty($log_table,$db_server,$db_user,$db_pass,$db_name) > 0 && $log == 'checked') {
   $contents_header = '<p class="sub1" ><label>SSA Log contents</label><br />
       <small class="noPrint">(You might need to refresh the page to show the latest updates)</small></p>';
}else{
   $contents_header = '<p class="sub1" style="text-align: center;"><label>SSA Log is empty</label><br />
       <small class="noPrint">(You might need to refresh the page to show the latest updates)</small></p>'; 
}
echo '<a name="1"></a>';// bookmark for files that can't be downloaded
echo '<table class="tab2" border="1" bordercolor="#ccc">
      <tr><td colspan="9">'.$contents_header.'</td></tr>';

if($ftp_server != "" && $ftp_server != null && $db_server != "" && $is_table_empty > 0){
    $con = mysql_connect($db_server,$db_user,$db_pass)or die(mysql_error());
    mysql_select_db($db_name, $con)or die(mysql_error());
    
    $log_table = 'ssa_'.str_replace('-','$',str_replace('.','_',$ftp_server)).'_log';
    $settings_table = 'ssa_'.str_replace('-','$',str_replace('.','_',$ftp_server)).'_settings';
    $result = mysql_query("SELECT * FROM $log_table") or die(mysql_error());
    $dir_to_monitor = mysql_query("SELECT * FROM $settings_table") or die(mysql_error());

    while($row = mysql_fetch_array($result)) 
    {
       $log_lines[] = $row;
    }
    
    $dir_to_mon = mysql_fetch_array($dir_to_monitor);

    mysql_close($con)or die(mysql_error()); 
}

echo '<tr><td colspan="8" style="padding: 3px;font-size:12px;"><b>Web site:</b> '.$ftp_server.'<br /><b>Start Dir:</b> '.$dir_to_mon[root_dir].'</td>
    <td><input class="noPrint" type="button" id="print" onclick="printDiv(\'view\')" style="margin-top:5;margin-left:30; width: auto; background-color: #ffffff;" name="print" alt="Print" title="Print the log" value="PRINT"></td></tr>
        <tr><td style="padding: 3px;"><font color="brown">Status</font></td>
        <td style="padding: 3px; width: 110px;"><font color="brown">Path to file (relative to \'Start Dir\')</font></td>
        <td style="padding: 3px; width: 110px;"><font color="brown">File date</font></td>
        <td style="padding: 3px; width: 110px;"><font color="brown">File time</font></td>
        <td style="padding: 3px; width: 110px;"><font color="brown">Old perms</font></td>
        <td style="padding: 3px; width: 110px;"><font color="brown">New perms</font></td>
        <td style="padding: 3px; width: 110px;"><font color="brown">Old size</font></td>
        <td style="padding: 3px; width: 110px;"><font color="brown">New size</font></td>
        <td style="padding: 3px"><font color="brown">Script run time </font></td></tr></font>';

  if(is_array($log_lines)){
  
      foreach($log_lines as $value){
             $id = $value['id'];
             $status = $value['status'];
             $file_name = $value['file'];
             $file_date = $value['date'];
             $file_time = $value['time'];
             $old_perms = $value['old_perms'];
             $new_perms = $value['new_perms'];
             $old_size = $value['old_size'];
             $new_size = $value['new_size'];
             $last_run = $value['last_run'];            
          
          if($status == "Added"){$bgcolor = "#F0F8FF";}          
          if($status == "Modified"){$bgcolor = "#FFE4E1";}          
          if($status == "Missing"){$bgcolor = "#DEFADE";}              
          if($status == "Permissions"){$bgcolor = "#ffffcc";}
          if($status == "Renamed"){$bgcolor = "#FFCCFF";}
      
             echo '<tr onclick="selected()" id="selected" style="background-color:'.$bgcolor.';">';
      
             $file_name = trim(stristr ($file_name,'/'));
        
      
             if($status == "Modified" || $status == "Added"){
               $img = 'images/arrow_down_blue.gif';
               $alt = 'Download this file for comparison with backup file.';
               $ttl = 'Download this file for comparison with backup file.';
               $href = "filediff/download.php?file=$file_name&server=$ftp_server";
               echo '<td style="padding: 1px;">'.$status.'<a href="'.$href.'" /><img class="noPrint" title="'.$ttl.'" alt="'.$alt.'"src="'.$img.'" /></td>';
               echo '<td style="padding: 1px; width: auto;"><img border="0" src="images/spacer.gif" width="3" height="0">'.$file_name.'</td>';
             }else{
               echo '<td style="padding: 4px;">'.$status.'</td>';
               echo '<td style="padding: 4px; width: auto;">'.$file_name.'</td>';
             }
             echo '<td style="padding: 4px; width: auto;">'.$file_date.'</td>';
             echo '<td style="padding: 4px; width: 110px;">'.$file_time.'</td>';
             echo '<td style="padding: 4px; width: auto;">'.$old_perms.'</td>';
             echo '<td style="padding: 4px; width: auto;">'.$new_perms.'</td>';
             echo '<td style="padding: 4px; width: 110px;">'.$old_size.'</td>';
             echo '<td style="padding: 4px; width: auto;">'.$new_size.'</td>';
             echo '<td style="padding: 4px; width: auto;">'.$last_run.'</td></tr>';
      }
  }
echo '</table>';

echo '</div>';

// ############################################ STATUS START ########################################################

if($_GET['refresh'] === 'Y' || $_GET['reload_status'] === 'Y'){
    echo '<script>
        window.scroll(0,500);
        </script>';
}
//echo '<a id="status_bookmark"></a>';
echo '<div class="status" id="status">';
echo '<script>document.getElementById("status").style.display="block";</script>';

echo '<table class="tab2" border="1" bordercolor="#ccc">
      <tr><td colspan="8" style="text-align:center;"><label><b>Overall status </b><br>(<a  href="index.php?server='.$ftp_server.'&reload_status=Y" id="status">Refresh</a>)</label></td></tr></table>';

$site_list = array_filter(scandir($logs_dir));

echo '<table class="tab2" border="1" bordercolor="#ccc">
    <tr><td colspan="8" style="text-align:center;padding: 4px; width: auto;"><b>Last 24 hours</b></td></tr>';

    echo '<td style="padding: 3px; width: auto;"><font color="brown">Site name</font></td>
            <td style="padding: 3px; width: auto;"><font color="brown">Started</font></td>
            <!--<td style="padding: 3px; width: auto;"><font color="brown">Last run time</font></td>-->
            <td style="padding: 3px; width: auto;"><font color="brown">Missing</font></td>
            <td style="padding: 3px; width: auto;"><font color="brown">Added</font></td>
            <td style="padding: 3px; width: auto;"><font color="brown">Perms</font></td>
            <td style="padding: 3px; width: auto;"><font color="brown">Modified</font></td>
            <td style="padding: 3px; width: auto;"><font color="brown">Renamed</font></td>
            <td style="padding: 3px; width: auto;"><font color="brown">Average duration</font></td>
            </tr>';
foreach($site_list as $site_name){
  if($site_name != '.' && $site_name != '..' && $site_name != '.htaccess' && $site_name != '.htpasswd'){
    if(file_exists($logs_dir.'/'.$site_name.'/24hr_status.txt')){
        $file_24hr = $logs_dir.'/'.$site_name.'/24hr_status.txt';
        $status_24hr = file($file_24hr);
        $unixtime_to_date = date('dMy H:i:s', $status_24hr[2]);
        $times = explode(',',$status_24hr[8]);
        $average_duration = array_sum($times) / count($times);
             echo '<tr><td style="padding: 4px; width: auto;"><a href="index.php?server='.$site_name.'">'.$site_name.'</a></td>';
             echo '<td style="padding: 4px; width: auto;">'.$status_24hr[1].'</td>';
             //echo '<td style="padding: 4px; width: auto;">'.$date.'</td>';
             echo '<td style="padding: 4px; width: auto;">'.$status_24hr[3].'</td>';
             echo '<td style="padding: 4px; width: auto;">'.$status_24hr[4].'</td>';
             echo '<td style="padding: 4px; width: auto;">'.$status_24hr[5].'</td>';
             echo '<td style="padding: 4px; width: auto;">'.$status_24hr[6].'</td>
                   <td style="padding: 4px; width: auto;">'.$status_24hr[7].'</td>
                   <td style="padding: 4px; width: auto;">'.round($average_duration,2).' Seconds.</td></tr>';                   
    }
  }
}
echo '<tr><td colspan="9"><input class="noPrint" type="button" onclick="toggle_visibility(\'week\');window.scroll(0, document.body.scrollHeight);" style="width:auto;background-color: lightgreen;" name="display_week" value="Show/Hide Last 7 days" /a></td></tr>';
echo '</table>';
  echo '<div class="week" id="week">';
  echo '<table class="tab2" border="1" bordercolor="#ccc">';
  echo '<tr><td colspan="8" style="text-align:center;padding: 4px; width: auto;"><b>Last 7 days</b></td></tr>';
  
    echo '<td style="padding: 3px; width: auto;"><font color="brown">Site name</font></td>
            <td style="padding: 3px; width: auto;"><font color="brown">Started</font></td>
            <!--<td style="padding: 3px; width: auto;"><font color="brown">Last run time</font></td>-->
            <td style="padding: 3px; width: auto;"><font color="brown">Missing</font></td>
            <td style="padding: 3px; width: auto;"><font color="brown">Added</font></td>
            <td style="padding: 3px; width: auto;"><font color="brown">Perms</font></td>
            <td style="padding: 3px; width: auto;"><font color="brown">Modified</font></td>
            <td style="padding: 3px; width: auto;"><font color="brown">Renamed</font></td>
            <td style="padding: 3px; width: auto;"><font color="brown">Average duration</font></td>
            </tr>';
  foreach($site_list as $site_name){
  if($site_name != '.' && $site_name != '..' && $site_name != '.htaccess' && $site_name != '.htpasswd'){
    if(file_exists($logs_dir.'/'.$site_name.'/7day_status.txt')){
        $file_7day = $logs_dir.'/'.$site_name.'/7day_status.txt';
        $status_7day = file($file_7day);
        $unixtime_to_date = date('dMy H:i:s', $status_7day[2]);
        $times2 = explode(',',$status_7day[8]);
        $average_duration2 = array_sum($times2) / count($times2);
             echo '<tr><td style="padding: 4px; width: auto;"><a href="index.php?server='.$site_name.'">'.$site_name.'</a></td>';
             echo '<td style="padding: 4px; width: auto;">'.$status_7day[1].'</td>';
             //echo '<td style="padding: 4px; width: auto;">'.$date.'</td>';
             echo '<td style="padding: 4px; width: auto;">'.$status_7day[3].'</td>';
             echo '<td style="padding: 4px; width: auto;">'.$status_7day[4].'</td>';
             echo '<td style="padding: 4px; width: auto;">'.$status_7day[5].'</td>';
             echo '<td style="padding: 4px; width: auto;">'.$status_7day[6].'</td>
                   <td style="padding: 4px; width: auto;">'.$status_7day[7].'</td>
                   <td style="padding: 4px; width: auto;">'.round($average_duration2,2).' Seconds.</td></tr>';
    }
    }
  }
  
  echo '<tr><td colspan="9"><input class="noPrint" type="button" onclick="toggle_visibility(\'month\');window.scroll(0, document.body.scrollHeight);" style="width:auto;background-color: lightgreen;" name="display_month" value="Show/Hide Last 30 days" /a></td></tr>';
  echo '</table>';
  echo '</div>';
  echo '<script>document.getElementById("month").style.display="none";</script>';
  echo '<div class="month" id="month">';
  echo '<table class="tab2" border="1" bordercolor="#ccc">';
  echo '<tr><td colspan="8" style="text-align:center;padding: 4px; width: auto;"><b>Last 30 days</b></td></tr>';
  
    echo '<td style="padding: 3px; width: auto;"><font color="brown">Site name</font></td>
            <td style="padding: 3px; width: auto;"><font color="brown">Started</font></td>
            <!--<td style="padding: 3px; width: auto;"><font color="brown">Last run time</font></td>-->
            <td style="padding: 3px; width: auto;"><font color="brown">Missing</font></td>
            <td style="padding: 3px; width: auto;"><font color="brown">Added</font></td>
            <td style="padding: 3px; width: auto;"><font color="brown">Perms</font></td>
            <td style="padding: 3px; width: auto;"><font color="brown">Modified</font></td>
            <td style="padding: 3px; width: auto;"><font color="brown">Renamed</font></td>
            <td style="padding: 3px; width: auto;"><font color="brown">Average duration</font></td>
            </tr>';
  foreach($site_list as $site_name){
  if($site_name != '.' && $site_name != '..' && $site_name != '.htaccess' && $site_name != '.htpasswd'){
    if(file_exists($logs_dir.'/'.$site_name.'/30day_status.txt')){
        $file_30day = $logs_dir.'/'.$site_name.'/30day_status.txt';
        $status_30day = file($file_30day);
        $unixtime_to_date = date('dMy H:i:s', $status_30day[2]);
        $times3 = explode(',',$status_30day[8]);
        $average_duration3 = array_sum($times3) / count($times3);
             echo '<tr><td style="padding: 4px; width: auto;"><a href="index.php?server='.$site_name.'">'.$site_name.'</a></td>';
             echo '<td style="padding: 4px; width: auto;">'.$status_30day[1].'</td>';
             //echo '<td style="padding: 4px; width: auto;">'.$date.'</td>';
             echo '<td style="padding: 4px; width: auto;">'.$status_30day[3].'</td>';
             echo '<td style="padding: 4px; width: auto;">'.$status_30day[4].'</td>';
             echo '<td style="padding: 4px; width: auto;">'.$status_30day[5].'</td>';
             echo '<td style="padding: 4px; width: auto;">'.$status_30day[6].'</td>
                   <td style="padding: 4px; width: auto;">'.$status_30day[7].'</td>
                   <td style="padding: 4px; width: auto;">'.round($average_duration3,2).' Seconds.</td></tr>';
    }
    }
 }
echo '</table>';
echo '</div>';
//echo '</div>';
echo '<a name="bottom"></a>';
?>