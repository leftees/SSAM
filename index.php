<?php

session_start();

// Start page-load timer    
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;

// Clear history of POST variables, so that browser doesn't object to a refresh.
if (isset($_GET['start_prg'])) {
    simple_prg(true, 'PHP_SELF');
} else {
    simple_prg(null, 'PHP_SELF');
}


$u = simple_prg(); // get the current transaction id 
setcookie("uid", $u, time() + 1200); // record the current transaction id, with 20 minute time-out

$file = "/settings.txt";

$logs_dir = '../../logs'; // Comment out this line, if using the line below
//$logs_dir = 'logs'; // Uncomment this line if you don't have access to an offline directory
                      // This will point to a writeable 'logs' directory which has already 
                      // been created within the SSA directory.

$domain = $_SERVER['HTTP_HOST'];

if (file_exists($logs_dir)) {
    if (!is_removeable($logs_dir)) { // Check if 'logs' directory is writeable. Files within the directory will also be checked.
        echo '<p class="sub1" style="text-align: left"><img border="0" src="images/cross.jpg" align="left" width="16" height="16"> Error!</p>
        <p class="sub1" style="text-align: left">Your \'logs\' directory doesn\'t exist or is not writeable or files within it are not writeable.</p>
        <p class="sub1" style="text-align: left">Please read the readme file.</p>
        <p class="sub1" style="text-align: left">Setup is unable to continue.</p>';
        exit();
    }
} else {
    echo '<p class="sub1" style="text-align: left"><img border="0" src="images/cross.jpg" align="left" width="16" height="16"> Error!</p>
       <p class="sub1" style="text-align: left">Your \'logs\' directory doesn\'t exist. Please create it and run this set up page again.</p>
       <p class="sub1" style="text-align: left">Setup is unable to continue.</p>';
    exit();
}
if (!file_exists($logs_dir . $file)) {
    $handle2 = fopen($logs_dir . $file, "w"); // If settings.txt doesn't exist, create it
    fclose($handle2);
}

$settings = array();
if (file_exists($logs_dir . $file)) {
    $settings = file($logs_dir . $file); // Parse the contents of settings.txt into an array
}

$function = "";
$monitor = trim($settings[0]);
$directoryToMonitor = trim($_POST[directoryToMonitor]) . "\r\n";
$dtm = trim($_POST[directoryToMonitor]);
$routeDirectory = trim($_POST[routeDirectory]) . "\r\n";
$rte = trim($_POST[routeDirectory]);
$subject = trim($_POST[subject]) . "\r\n";
$subj = trim($_POST[subject]);
$skipfile = trim($_POST[skipfile]) . "\r\n";
$skip = trim($_POST[skipfile]);
$alert = trim($_POST[alertAddress]) . "\r\n";
$alrt = trim($_POST[alertAddress]);
$from = trim($_POST[fromAddress]) . "\r\n";
$frm = trim($_POST[fromAddress]);
$message = trim($_POST[message]) . "\r\n";
$msg = trim($_POST[message]);
$createLog = trim($_POST[createLog]);
$clear = trim($_GET[clear]);
$id = $_GET[uniqid];
$outputfile = $logs_dir . '/ssa_output.txt';
$fileEmptied = $_GET[fileEmptied];
$createLog = $_POST[createLog];

if ($_POST[submit] == 'Submit settings') {
    $cronlogpath = trim($_POST[cronlogpath]);
}else{
    $cronlogpath = trim($settings[9]);
}
if ($createLog == 'Y' || $clear == 'Y') { // Show checkbox checked or not
    $log = 'checked';
} elseif(trim($settings[8]) == 'Y' && $uid == $id) {
    $log = 'checked';
}else{
    $log = "";
}
// Generate directoryToBeMonitored drop-down menu    
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('../'), RecursiveIteratorIterator::SELF_FIRST);
$html = Select($dtm, $monitor, 'dropdown', $iterator);

// Start html     
echo '
  <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
  <html>
  <head>
  <link href="css/simplesiteaudit.css" rel="stylesheet" type="text/css">
  <script language="JavaScript" src="validation/validate.js"
    type="text/javascript" xml:space="preserve"></script>
  </head>';

echo '
  <table class="tab1"><tr><td width="800px">
  <table class="tab0">
  <tr><td colspan="2">
  <p class="sub2"><font color="brown">SimpleSiteAudit</font> Admin<a class="ToolText" onMouseOver="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'"> 
  <img border="0" src="images/info.png" width="16" height="16">';
echo '
  <span><b>SimpleSiteAudit requirements:</b><br /><br />Hopefully, your site will never be hacked, but if it is, and you have SimpleSiteAudit installed, 
  you will be notified of any file changes shortly afterwards<br /><br /> Primarily, SimpleSiteAudit script is designed to be used as a scheduled task or cron job, 
  run maybe once an hour.<br /><br />It can also be run from your browser via the settings page, click on the \'Run the script\' button - if changes have been made, a 
  list of changed files will be displayed.<br /><br />
  
  <b>JAVASCRIPT:</b><br />
  Javascript is required for some of the form actions on the settings page and therefore, must be allowed in your browser settings.<br /><br />
  
  <b>FILE PLACEMENT:</b><br />
  1. Make sure that an OFFLINE directory, named \'logs\' exists, just ABOVE route level, where it is not web-accessible. If it doesn\'t exist, please create it via your FTP 
  client. This directory must be writeable.<br /><br /> 
  2. Create another directory, just BELOW route level, where you will upload the SimpleSiteAudit files. Name this directory what ever you like, e.g. \'ssa\'. 
  This directory should be password protected.<br />
  <br /><b>If you don\'t have access to offline directories</b>, create the \'logs\' directory on your password protected, ssa, directory
  and amend to the two ssa php files as follows:<br />
  File index.php - change the line (~22) <font color="blue">$logs_dir = \'../../logs\';</font> to <font color="blue">$logs_dir = \'logs\';</font><br />
  File simplesiteaudit.php - change the line (~12)<font color="blue">$file = \'../../logs/settings.txt\';</font> to <font color="blue">$file = \'logs/settings.txt\';</font> and run the settings file.<br /><br /> 
  
  <b>RUN THE SETTINGS PAGE:</b><br />
  1. Upload the files to your new directory and run index.php in your browser.<br /> 
  2. Complete and submit the settings form and then click \'Run the script\'. <br /><br />
  
  <b>CREATE A LOG FILE:</b><br />
  The settings form allows the optional creation of a log file, containing all the SimpleSiteAudit script outputs. The file (ssa_output.txt) will be created in your \'logs\' 
  directory. This was introduced mainly for those who don\'t have access to offline directories. However, it can be used instead of your scheduled task log.<br /><br />
  This file can grow quite large, periodic clearing of the file is recommended.<br /><br />First run will list all files in your nominated directory and it\'s 
  sub-directories. Subsequent runs will only list changes.<br /><br />  
  That\'s it.<br /><br />  
  Hope you enjoy using SimpleSiteAudit.</span></a></p>';

if ($settings[0] != "" || $dtm != "") { // check if settings already established      
    echo '<img border="0" src="images/spacer.gif" width="15" height="5"><label>Your current settings</label><img border="0" src="images/spacer.gif" width="250" height="5">
        <small>Fields marked with <a class="asterisk">*</a> are required</small><label>';
} else {
    echo '<img border="0" src="images/spacer.gif" width="15" height="5">
        <label>Please adjust the auto-fill below<img border="0" src="images/spacer.gif" width="250" height="5">
        <small>Fields marked with <a class="asterisk">*</a> are required</small><label>';
}

echo '</tr>
  </tr></td><tr><td>';
echo '<form name="myform" method="POST" action="'.$function.'">'; // display the form
echo '<a class="ToolText" onMouseOver="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'">
      <img border="0" src="images/info.png" width="16" height="16">
';
echo '<span><b>\'Path to directory being monitored\'</b><br /><br />If the SimpleSitAudit files are correctly placed, this drop-down menu will contain all directories in your web. Simply select the one you wish to monitor. 
  All of its sub-directories will also be monitored.<br />
  <br />For example: SimpleSiteAudit files must be placed in a sub-directory, just off the site route. The path to monitor the route and all its sub-directories will be <font color="blue">../</font><br /><br />
   That is, 1 level up from the SimpleSiteAudit files\' location.<br /><br />If you wish to monitor only a sub-directory of the route, the path will be 
   <font color="blue">../subDirName</font></span></a>
        <img border="0" src="images/spacer.gif" width="10" height="5">
      <small><font color="red">*</font></small><label>Directory to be monitored:</label>
      <!--[if IE]><br /><![endif]-->';

echo $html; // Will contain a list of all directories in directoryToBeMonitored
echo '<a class="ToolText" onMouseOver="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'">';
echo '  <a class="ToolText" onMouseOver="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'"> 
  <br /><br /><img border="0" src="images/info.png" width="16" height="16">
  <img border="0" src="images/spacer.gif" width="10" height="5">
  <span><b>\'Route directory URL\'</b><br /><br />This is the URL for your site route. E.g. http://domain.com</span></a> 
  <small><font color="red">*</font></small><label>Route directory URL:</label><br /> 
  <input type="text" name="routeDirectory" value="';
if(!file_exists($outputfile) && $uid == $id){
    echo "http://".$domain;
}
if ($_POST[submit] == 'Submit settings') {
    echo $rte;
} else {
    echo $settings[1];
}
echo '" id="routeDirectory" />
<br />
   <a class="ToolText" onMouseOver="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'"> 
  <br /><img border="0" src="images/info.png" width="16" height="16">
  <img border="0" src="images/spacer.gif" width="10" height="5">
  <span><b>\'Subject for message\'</b><br /><br />This will be used as the \'Subject\' line in the email that is transmitted when changes are found.</span></a>
  <small><small><font color="red">*</font></small></small><label>Subject for message:</label><br />
  <input type="text" name="subject" value="';
if(!file_exists($outputfile) && $uid == $id){
    echo "[SSA] Files on your web site have changed";
}
if ($_POST[submit] == 'Submit settings') {
    echo $subj;
} else {
    echo $settings[2];
}
echo '" /><br />
  <a class="ToolText" onMouseOver="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'"> 
  <br /><img border="0" src="images/info.png" width="16" height="16">
  <img border="0" src="images/spacer.gif" width="10" height="5">
  <span><b>\'List of files to ignore\'</b><br /><br />This should consist of a list of comma separated file names, including the file extension. (This field is optional)</span></a>
 <font color="#fbfbfb">-</font><label>List of files to ignore:</label> 
  <input type="text" name="skipfile" value="';
if(!file_exists($outputfile) && $uid == $id){
    echo "simplesiteaudit.php,simplesiteaudit.css";
}
if ($_POST[submit] == 'Submit settings') {
    echo $skip;
} else {
    echo $settings[3];
}
echo '" id="skipfile" />
<td>
  <a class="ToolText" onMouseOver="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'"> 
  <img border="0" src="images/info.png" width="16" height="16">
  <img border="0" src="images/spacer.gif" width="10" height="5">
  <span><b>\'Email address to alert\'</b><br /><br />This address will be used as the recipient\'s address in the email alert. A short, comma seperated list of addresses is allowed if required (Max characters: 300)</span></a>
  <small><font color="red">*</font></small><label>Email address to alert:</label><br /> 
  <input type="text" name="alertAddress" value="';
if(!file_exists($outputfile) && !$_POST[submit]){
    echo "persontonotify@".$domain;
}
if ($_POST[submit] == 'Submit settings') {
    echo $alrt;
} else {
    echo $settings[4];
}
echo '" id="alertAddress" /><br />
  <a class="ToolText" onMouseOver="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'"> 
  <br /><img border="0" src="images/info.png" width="16" height="16">
  <img border="0" src="images/spacer.gif" width="10" height="5">
  <span><b>\'The \'From\' email address\'</b><br /><br />This address will be used as the sender\'s address in the email alert.</span></a>
  <small><font color="red">*</font></small><label>The \'From\' email address:</label><br /> 
  <input type="text" name="fromAddress" value="';
if(!file_exists($outputfile) && $uid == $id ){
    echo "simplesiteaudit@".$domain;
}
if ($_POST[submit] == 'Submit settings') {
    echo $frm;
} else {
    echo $settings[5];
}
echo '" id="fromAddress" />
<br />
  <a class="ToolText" onMouseOver="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'"> 
  <br /><img border="0" src="images/info.png" width="16" height="16">
  <img border="0" src="images/spacer.gif" width="10" height="5">
  <span><b>\'Message body opening line\'</b><br /><br />This text will be used as the header line in the email body.</span></a>
  <small><font color="red">*</font></small><label>Message body opening line:</label><br /> 
  <input type="text" name="message" value="';
if(!file_exists($outputfile) && $uid == $id){
    echo "IMPORTANT! The following changes have been detected:";
}      
if ($_POST[submit] == 'Submit settings') {
    echo $msg;
} else {
    echo $settings[6];
}
echo '" id="message" />
<br />
  <a class="ToolText" onMouseOver="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'"> 
  <br /><img border="0" src="images/info.png" width="16" height="16">
  <img border="0" src="images/spacer.gif" width="10" height="5">
  <span><b>\'Path to and name of your Cron log\'</b><br /><br />Relative to your SSA files location. e.g. <font color="blue">../../cronfilename.txt</font> The \'view file\' button will not appear if this file does not exist.<br />
  Recommend leaving this field empty if using the SSA log file and vice versa.<br /><br />
  The log contents will appear in a read only textarea. Further formatting is not possible due to unknown input by the Cron process itself. There is no \'Clear log\' button for this feature.</span></a>
  <small><font color="#fbfbfb">-</font></small><label>Path to, and name of your Cron log:</label><br /> 
  <input type="text" name="cronlogpath" value="';
if ($_POST[submit] == 'Submit settings') {
    echo $cronlogpath;
} else {
    echo $settings[9];
}
echo '" id="cronlogpath" />
<br />
  ';

echo '
  <br />
  <a class="ToolText" onMouseOver="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'"> 
  <img border="0" src="images/info.png" width="16" height="16">
  <img border="0" src="images/spacer.gif" width="10" height="5">
  <span><b>\'Create a log file\'</b><br /><br />When the SimpleSiteAudit script is first run , it will create a file named \'ssa_output.txt\', in your \'logs\' directory.<br />
  <br />This file will be used to store all outputs generated by the script.<br /><br />The file can grow quite large. 
  Therefore, it is advisable to empty it periodically. Use the red question mark to activate the button.</span></a>  
  <label>Create an SSA log file: </label>
  <input style="width: auto;" type="checkbox" name="createLog" value="Y" ';
echo $log;
echo '/><br /><small>(File will be created when script is next run.)</small>';

echo '<tr><td colspan="2"><input style=" width: auto;" title="Submit to update the settings file." type="submit" name="submit" value="Submit settings"/>
     ';

if (file_exists($outputfile) && $log == 'checked') {
    echo '
    <input  onclick="toggle_visibility(\'activate\');"  title="Show/hide \'Clear SSA log file\' button" style="width: auto;background-color: #ffffcc; color: red; font-weight:bold;" type="button" name="" value="?">
    <input style="width: auto;background-color: #ffffcc;" type="button" name="view_log" value="View/hide SSA log file" onclick="toggle_visibility(\'view\');">';
}

if (file_exists($cronlogpath)) {
    echo '
    <input onclick="toggle_visibility(\'cronlogcontents\');" style="width: auto;background-color: #FFD3DA;" type="button" name="view_cronlog" value="View/hide Cron log file">';
}

if ($_POST[submit] == 'Submit settings' || $settings[1] != "") {
    echo '
<!--<img border="0" src="images/spacer.gif" width="5" height="5">-->
    <input style="width: auto;" title="Run the SimpleSiteAudit main script" class="button" type="button" name="runScript" value="Run the script" onClick="window.open(\'simplesiteaudit.php\',\'_blank\');window.location.href=\'index.php\'"/>
<!--<img border="0" src="images/spacer.gif" width="145" height="5">-->
    <input id="activate" onclick="location.href=\'clear_log.php?dir='.$logs_dir.'&pass=letmeinnow&outputfile='.$logs_dir.'/ssa_output.txt\'" style="display: none;width: auto;background-color: #ffffcc"" type="button" name="clear" value="Clear SSA log file">
    <input id="cronlog" onclick="location.href=\'clearcronlog.php?dir='.$logs_dir.'&pass=letmeinnow&outputfile='.$cronlogpath.'" style="display: none;width: auto;background-color: #FFD3DA"" type="button" name="clear" value="Clear Cron log file">
    </form></td>
    </td></tr>';
}

$uid = $_COOKIE["uid"]; // get unique transaction id from stored cookie.

echo '
    <script language="JavaScript" type="text/javascript"
        xml:space="preserve">//<![CDATA[
    var frmvalidator  = new Validator("myform"); 
    frmvalidator.EnableMsgsTogether();
    frmvalidator.addValidation("directoryToMonitor","req","Directory to monitor"); 
    frmvalidator.addValidation("routeDirectory","req","URL of your site route");      
    frmvalidator.addValidation("subject","req","Subject for the alert email");                 
    frmvalidator.addValidation("alertAddress","maxlen=300");                               
    frmvalidator.addValidation("alertAddress","req","email address to alert");                                 
    frmvalidator.addValidation("fromAddress","req","Sender\'s email address");                                       
    frmvalidator.addValidation("message","req","Message body first line");
    //]]></script>
    ';

// Store the form contents into settings.txt 
if ($_POST[submit] == "Submit settings") {
    $function = writeToFile($directoryToMonitor, $routeDirectory, $subject, $skipfile, $alert, $from, $message, $createLog, $cronlogpath, $logs_dir, $file);
}

echo '</td></tr></table>
      </td><td><table rowspan="2" border="0" width="400"><tr><td>';

echo '<div id="fadeBlock">';// Responses

echo '<table class="tab3"><label>Responses:</label><tr><td>'; // responses table
// Confirmation response - settings saved
if ($id != $uid && $id != "" && $fileEmptied != 1) {
    include 'includes/confirm.html';
}

// Confirmation response - cleared log file   && $id != $uid           
if ($fileEmptied == 1) {
    include 'includes/empty_file.html';
}

if (!file_exists($logs_dir . '/previousList.txt')) { // create empty file
    $handle2 = fopen($logs_dir . '/previousList.txt', "w");
    fclose($handle2);
    if (file_exists($logs_dir . '/previousList.txt')) {
        echo '<p class="sub1" style="text-align: left"><img border="0" src="images/tick.jpg" align="left" width="16" height="16">File: previousList.txt created.</p>';
    }
}

if (!file_exists($logs_dir . '/lastRun.txt')) { // create empty file 
    $handle3 = fopen($logs_dir . '/lastRun.txt', "w");
    fclose($handle3);
    if (file_exists($logs_dir . '/lastRun.txt')) {
        echo '<p class="sub1" style="text-align: left"><img border="0" src="images/tick.jpg" align="left" width="16" height="16">File: lastRun.txt created.</p>';
    }
}

if (file_exists($logs_dir . '/settings.txt') && !file_exists($logs_dir.'/ssa_output.txt')&& !$_POST[submit]) {
    echo '<p class="sub1" style="text-align: left"><img border="0" src="images/tick.jpg" align="left" width="16" height="16">File: settings.txt created.</p>';
}
echo '</tr></td></table>'; // end responses table
echo '</div>';

if($_POST[submit]){
    echo '<br /><label><font color="red">ERRORS!</font></label><table class="tab3"><tr><td>';
    if(!file_exists($cronlogpath)&& $_POST[submit] && $cronlogpath != ""){
        echo '<p class="sub1" style="text-align: left"><img border="0" src="images/cross.jpg" align="left" width="16" height="16">Cron log file:<br />\''.$cronlogpath.'\'<br />not found</p>';
    }else{
        echo 'None.';
    }
    echo '</tr></td></table>';
}

echo '</td></tr></table>
      </td></tr></table>
      </td></tr></table>
      <div class="view" id="view">';//Log file display.
$ssa_log_contents = array();
$ssa_log_contents = file($outputfile);

if($ssa_log_contents){
   $contents_header = '<p class="sub1" ><label>SSA Log file contents</label><br />
       <small>(You might need to refresh the page to show the latest updates)</small></p>';
}else{
   $contents_header = '<p class="sub1" style="text-align: center;"><label>SSA Log file is empty</label><br />
       <small>(You might need to refresh the page to show the latest updates)</small></p>'; 
}
echo '<table class="tab2" border="1" bordercolor="#ccc" style="width: 762;margin-left: 15px;" >
      <tr><td colspan="4">'.$contents_header.'</td></tr>';

foreach($ssa_log_contents as $value){
    $len = strlen($value);
    $bg_color = "";
    $status = str_replace("File ","",trim(substr($value,0,14)));
    $dateofchange = trim(substr($value,15,11));
    $timeofchange = trim(substr($value,27,10));
    $relpathtofile = trim(substr($value,39,$len-39));
    
    if($status == "ADDED"){
        $bgcolor = "#F0F8FF";
    }
    
    if($status == "MODIFIED"){
        $bgcolor = "#FFE4E1";
    }
    
    if($status == "MISSING"){
        $bgcolor = "#DEFADE";
    }

    if(trim(substr($value,5,4)) != "run:" && trim(substr($value,5,4)) != ""){
       echo '<tr style="background-color:'.$bgcolor.';">
             <td style="padding: 3px;">'.$status.'</td>';       
       echo '<td style="padding: 3px; width: auto;">'.$dateofchange.'</td>';
       echo '<td style="padding: 3px; width: 110px;">'.$timeofchange.'</td>';
       echo '<td style="padding: 3px; width: auto;">'.$relpathtofile.'</td></tr>';
    }elseif(trim(substr($value,5,4)) != "" && trim(strstr($value,'No')) != 'No changes found'){
       echo '<tr><td colspan="4" style="padding: 3px;"><b>'.$value.'</b></td></tr>
             <tr><td style="padding: 3px;"><font color="brown">File status</font></td>
             <td style="padding: 3px; width: 110px;"><font color="brown">Date of change</font></td>
             <td style="padding: 3px; width: 110px;"><font color="brown">Time of change</font></td>
             <td style="padding: 3px"><font color="brown">Path to file (relative to SSA files)</font></td></tr></font>';
    }elseif(trim(strstr($value,'No')) == 'No changes found'){
        echo '<tr><td colspan="4" style="padding: 3px;"><b>'.$value.'</b></td></tr>';
    }
} 
echo '</table>';
echo '</div>';//----------------------------------------------------------------

  echo '<div style="display: none;" class="cronlogcontents" id="cronlogcontents">';
    $log_contents = array();    
    if(file_exists($cronlogpath)){
      $log_contents = file($cronlogpath); // Parse the contents of cron log file into an array
    } 
    $view_log = displayString($log_contents); // Parse the array values into a string
if($view_log == ""){
      echo '<p class="sub2"><label>Cron log file is empty</label></p>';
}else{
      echo '<p class="sub2"><label>Cron log file contents</label></p>';
}
  echo '<textarea readonly rows="40" cols="30">';
  echo $view_log;
  echo '</textarea>';
  echo '</div>';

// Show page-load time.
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round(($finish - $start), 4);

echo '<br /><small style="margin-left:15">SSA v1.4 | Page loaded in ' . $total_time . ' seconds. | Check for the <a target="_blank" href="http://sourceforge.net/projects/simplesiteaudit/">latest</a> version</small></body></html>';

// Functions start here  
function writeToFile($directoryToMonitor, $routeDirectory, $subject, $skipfile, $alert, $from, $message, $createLog, $cronlogpath, $logs_dir, $file) {
    
    $handle = fopen($logs_dir . $file, "w");
    $logsdir = $logs_dir . "\r\n";
    $cronlogpath = "\r\n".$cronlogpath;
    fwrite($handle, $directoryToMonitor);
    fwrite($handle, $routeDirectory);
    fwrite($handle, $subject);
    fwrite($handle, $skipfile);
    fwrite($handle, $alert);
    fwrite($handle, $from);
    fwrite($handle, $message);
    fwrite($handle, $logsdir);
    fwrite($handle, $createLog);
    fwrite($handle, $cronlogpath);
    fclose($handle);

    echo '</div>';
}

function simple_prg($start_prg = false, $request_uri = null) {
    // check to see if we should start prg
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $uniqid = uniqid();
        $_SESSION['post'][$uniqid] = $_POST;
        if (!$request_uri) {
            $request_uri = 'REQUEST_URI';
        }

        header("HTTP/1.1 303 See Other");

        $header = "Location: " . $_SERVER[$request_uri] . '?prg=1&uniqid=' . $uniqid;
        header($header);
        //header('Expires: ' . gmdate('D, d M Y H:i:s', time()+1000) . ' GMT');        
        die;
    }

    if ($start_prg) {
        // on start we clean all session posts
        @$_SESSION['post'] = '';
    } else {
        if (isset($_GET['prg'])) {
            $uniqid = $_GET['uniqid'];
            $_POST = @$_SESSION['post'][$uniqid];
        }
    }
    return $uniqid;
}


function Select($dtm, $monitor, $name = '', $options = array() ) {

    $html = '<select class="dropdown" name="directoryToMonitor">';
    $html .= '<option value=\'../\'>../</option>';
    if ($_POST[submit] == 'Submit settings') {
        $html .= '<option selected value=\'' . $dtm . '\'>' . $dtm . '</option>';
    } else {
        $html .= '<option selected value=\'' . $monitor . '\'>' . $monitor . '</option>';
    }

    foreach ($options as $option => $value) {
        if ($value->isDir()) {
            $html .= '<option value=' . $value . '>' . $option . '</option>';
            $html .= $value->getRealpath() . '<br />';
        }
    }
    $html .= '</select>';
    return $html;
}


function is_removeable($dir) {
    $folder = opendir($dir);

    while ($file = readdir($folder))
        if ($file != '.' && $file != '..' &&
                (!is_writable($dir . "/" . $file) ||
                ( is_dir($dir . "/" . $file) && !is_removeable($dir . "/" . $file) ) )) {
            closedir($folder);
            return false;
        }
    closedir($folder);
    return true;
}

function displayString($log) {
    $string = '';
    foreach ($log as $val) {
        $string .= $val . "\r\n";
    }
    return $string;
}

function refresh_logview() {
    $log_contents = array();
    if (file_exists($logs_dir . '/ssa_output.txt')) {
        $log_contents = file($logs_dir . '/ssa_output.txt'); // Parse the contents of ssa_output.txt into an array
    }
    $view_log = displayString($log_contents);
}

?>