<?php
/*
  _, _ _, _ __, _,  __,    _, _ ___ __,    _, _,_ __, _ ___  
 (_  | |\/| |_) |   |_    (_  |  |  |_    / \ | | | \ |  |   
 , ) | |  | |   | , |     , ) |  |  |     |~| | | | / |  |   
  ~  ~ ~  ~ ~   ~~~ ~~~    ~  ~  ~  ~~~   ~ ~ `~' ~~  ~  ~    Multisite
 * 
 * Copyright (C) 2012 Terry Heffernan. All rights reserved.
 * Technical support: http://simplesiteaudit.terryheffernan.net
 */
 

/*
   if( !ini_get('safe_mode') ){
      set_time_limit(30); // 30 secs should cover most sites. This may be your default already.
   }
*/

session_start();
error_reporting (E_ALL ^ E_NOTICE);
include 'version.php';
include 'functions/index1_functions.php';
//$logs_dir = '../../logs';
$ftp_user = "";
$ftp_pass = "";
$site_deleted = $_GET['site_deleted'];
$root_dir = "";

if($_GET['server']){
    $ftp_server = $_GET['server'];
}elseif(isit_dir($logs_dir) > 0 && $ftp_server == ""){
    $scan = scandir($logs_dir);
    $i = 0;
    foreach($scan as $value){
      if($i == 0){
        if($value != '.' && $value != '..'){
            $ftp_server = trim($value);
            $i++;
        }
      }
    }
 } 
     
if ($_POST['submit']){
  if(!file_exists($logs_dir)){
    mkdir($logs_dir,0777,TRUE) or die('Unable to create the logs directory. Program halted.<br />Try creating dir: '.$logs_dir.' manually.' );
  }
  
$ftp_server = $_POST['ftp_server']; 
$cron_file = '_'.$ftp_server.'.php';
    
    If(!file_exists($cron_file) && $ftp_server != ""){  //$ftp_server = "'.$ftp_server.'";
        $string = 
        '<?php 
          $ftp_server = \''.$ftp_server.'\'; 
          include \'ftp_scan.php\'; 
         ?>';   
        $fp = fopen($cron_file, "w") or die('Unable to create cron file');
        fwrite($fp, $string);
        fclose($fp);
    }
    
    $dbsettings = $logs_dir.'/'.$ftp_server.'/db_settings.txt';
    $db_server = $_POST['db_server']; 
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'];
    $db_name = $_POST['db_name'];    
    $ftp_user  = $_POST['ftp_user'];
    $ftp_pass   = $_POST['ftp_pass'];
    $root_dir   = $_POST['root_dir'];
    create_db($db_user,$db_server,$db_pass,$db_name,$ftp_server);
 }
if($_GET['check_db_details'] != 'Y'){
 if(!$_POST['submit'] && isit_dir($logs_dir) > 0){
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
    }
 }
 
 if(file_exists($dbsettings)){
    $con = @mysql_connect($db_server,$db_user,$db_pass)or die('Unable to connect to MySQL server: '.$db_server.'<br>Please check that the following details are correct:<br>
        db server name<br>
        db user name<br>
        db password<br>
        <a href="index1.php?check_db_details=Y">Click to reload form</a>');
    mysql_select_db($db_name, $con)or die(mysql_error());    
    $settings_table = 'ssa_'.str_replace('-','$',str_replace('.','_',$ftp_server)).'_settings';
    $result = @mysql_query("SELECT site_URL,FTP_user,FTP_pass,root_dir FROM $settings_table");
}
    
if(is_resource ($result) && !$_POST['submit']){

    while($row = mysql_fetch_array($result))
    {
       $ftp_server = stripslashes($row[site_URL]);
       $ftp_user = $row[FTP_user];
       $ftp_pass = $row[FTP_pass];
       $root_dir = $row[root_dir];
    }    
     mysql_close($con)or die(mysql_error());
   
     if(is_table_empty($settings_table,$db_server,$db_user,$db_pass,$db_name) > 0){
         $key = 'let@me@in@NOW';
         $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($ftp_pass), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
         $ftp_pass = trim($decrypted);
     }
}

     if($_POST['submit']){
       store_details($db_server, $db_user, $db_pass, $db_name, $dbsettings, $ftp_server, $ftp_user, $ftp_pass, $logs_dir, $root_dir);
     }
 } 
 
echo '
  <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
  <html>
  <head>
  <link href="css/simplesiteaudit.css" rel="stylesheet" type="text/css">
  <script language="JavaScript" src="validation/validate.js" type="text/javascript" 
  xml:space="preserve"></script>';

echo ' 
<script type="text/javascript">
function depopulate(){
    for (i=0;i<document.form.elements.length;i++)
    {
        if (document.form.elements[i].type == "text" || document.form.elements[i].name == "site_list"){
          if (document.form.elements[i].name == "ftp_server" || 
              document.form.elements[i].name == "ftp_user" || 
              document.form.elements[i].name == "ftp_pass" || 
              document.form.elements[i].name == "root_dir" || 
              document.form.elements[i].name == "site_list"){
              document.form.elements[i].value="";
          }
        }
        if (document.form.elements[i].type == "password" || document.form.elements[i].name == "site_list"){
          if (document.form.elements[i].name == "ftp_pass"){
              document.form.elements[i].value="";
          }
        }
    }
}

</script>

<SCRIPT LANGUAGE="JavaScript">
function respConfirm () {
     var response = confirm(\'Confirm deletion:\');
     if (response) location.href=\'delete_site.php?server='.$ftp_server.'\';
}

</SCRIPT>

<!--[if IE]>
<style>
th, td {
    padding: 15;
    width: 50%;
}
input {
margin: 0;
padding: 1;
width: auto;
overflow: visible;
align: absmiddle;
}
input.text{
width:350px;
}
</style>
<![endif]-->
</head><body>
';

echo '<table class="tab1"><tr><td>
    <table class="tab0">
    <tr><td colspan="2">
    <p class="sub2"><font color="brown">SimpleSiteAudit</font> Admin<label> Multisite v'.$ssa_ver.' </label>';
echo '<img border="0" src="images/spacer.gif" width="200" height="5">
    <input type="button" id="readme" onclick="window.open(\'phpinfo.php\',\'_blank\',\'width=850,height=600\');" style="width: auto; background-color: #ffffff;" name="phpinfo" alt="View the PHP configuration" value="PHP Info">
    <input type="button" id="readme" onclick="window.open(\'readme.html\',\'_blank\',\'width=850,height=600\');" style="width: auto; background-color: #ffffff;" name="readme" alt="View the README file" value="README">
    <br />';
echo '<label>STEP 1: Please provide FTP and database details for the sites to be monitored</label>
      <img border="0" src="images/spacer.gif" width="15" height="5"><small> All fields are required</small><label><br />';
echo '<form name="form" method="POST" action="index1.php">';

if(isit_dir($logs_dir) > 0){    
    echo '<br /><label>Number of sites being monitored: '.isit_dir($logs_dir).'  </label>';

    $html = Select($logs_dir, 'site_list');
    echo $html; // Will contain a drop-down list of all sites being monitored
}
echo '</td></tr></td></tr><tr><td>';

echo '<![if !IE]><br /><![endif]><label><b>FTP details </b></label><small>(Remote site)</small>
      <a class="ToolText" onclick="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'"><br /><br />     
      <img alt="Click for info" title="Click for info" border="0" src="images/info.png" width="16" height="16">';
echo '<span><b>\'URL of site to be monitored\'</b><br /><br />E.g.  
      <font color="blue">sitetobemonitored.com</font> without http://<br /><br />
      On Windows/IIS servers, you may need to add the prefix: \'ftp.\'</span></a>   
      <img border="0" src="images/spacer.gif" width="10" height="5">        
      <small><font color="red">*</font></small><label>URL of site to be monitored:</label>
      <!--[if IE]>
        <br />
      <![endif]-->
      ';

echo   '<input size="55" type="text" name="ftp_server" id="ftp_server" value="';
echo $ftp_server;
echo '" /><br />';

echo '  <a class="ToolText" onclick="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'"> 
  <br /><img alt="Click for info" title="Click for info" border="0" src="images/info.png" width="16" height="16">  
  <span><b>\'FTP User Name\'</b><br /><br />This is the user name required for FTP access to the site being monitored.<br /><br />
  On Windows/IIS servers, you may need to experiment with the point at which the ftp user accesses the site, especially where sub-domains are concerned.</span></a>
  <img border="0" src="images/spacer.gif" width="10" height="5">
  <small><font color="red">*</font></small><label>FTP User Name:</label><br />  
  <input size="55" type="text" name="ftp_user" value="';
echo $ftp_user;
echo '" id="ftp_user" /><br />
    
   <a class="ToolText" onclick="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'"> 
  <br /><img alt="Click for info" title="Click for info" border="0" src="images/info.png" width="16" height="16">  
  <span><b>\'FTP password\'</b><br /><br />This is the password required for FTP access to the site being monitored.</span></a>
  <img border="0" src="images/spacer.gif" width="10" height="5">
  <small><small><font color="red">*</font></small></small><label>FTP password:</label><br />
  <input size="55" type="password" name="ftp_pass" id="ftp_pass" value="';
echo $ftp_pass;
echo '" /><br />
  <a class="ToolText" onclick="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'"> 
  <br /><img alt="Click for info" title="Click for info" border="0" src="images/info.png" width="16" height="16">  
  <span><b>\'Directory or sub-domain to be monitored\'</b><br /><br />To monitor a whole web site, enter the name of the site\'s root directory, e.g. <font color="blue">htdocs</font> or <font color="blue">wwwroot</font> etc.
  <br /><br />
  To monitor a sub-domain or sub-directory, this field should be entered as, e.g. <font color="blue">htdocs/sub-dir-name</font> or the <font color="blue">sub-domain-name</font>
  - a sub-directory can be added to the sub-domain-name if required, e.g. <font color="blue">sub-domain-name/sub-dir-name</font>.<br /><br />
  On Windows/IIS servers, this will depend on how your hosting provider sets up your account. Check it out first using your ftp client.</span></a>
  <img border="0" src="images/spacer.gif" width="10" height="5">
  <small><font color="red">*</font></small><label>Directory or sub-domain to be monitored:</label><br /> 
  <input size="55" type="text" name="root_dir" value="';
echo $root_dir;
echo '" id="root_dir" />

<td><![if !IE]><img border="0" src="images/spacer.gif" width="1" height="26"><br /><![endif]>
<!--[if IE]><img border="0" src="images/spacer.gif" width="0" height="6"><br /><![endif]-->
<label><b>Database details </b></label><small>(Master site)</small><br /><br /> 
  <a class="ToolText" onclick="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'">   
  <img alt="Click for info" title="Click for info" border="0" src="images/info.png" width="16" height="16">
  
  <span><b>Mysql database server</b><br /><br />This is the name of your database server, usually <font color="blue">localhost</font></span></a>
  <img border="0" src="images/spacer.gif" width="10" height="5">
  <small><font color="red">*</font></small><label>Mysql database server:</label><br /> 
  <input size="55" type="text" name="db_server" id="db_server" value="';
    echo $db_server;

echo '" id="db_server" /><br />

  <a class="ToolText" onclick="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'"> 
  <br /><img alt="Click for info" title="Click for info" border="0" src="images/info.png" width="16" height="16">  
  <span><b>Database user name</b><br /><br />Enter the username for database access. This user must have \'Drop\' privileges</span></a>
  <img border="0" src="images/spacer.gif" width="10" height="5">
  <small><font color="red">*</font></small><label>Database user name:</label><br /> 
  <input size="55" type="text" name="db_user" id="db_user" value="';
    echo $db_user;

echo '" id="db_user" /><br />

  <a class="ToolText" onclick="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'"> 
  <br /><img alt="Click for info" title="Click for info" border="0" src="images/info.png" width="16" height="16">  
  <span><b>Database password</b><br /><br />Enter the password for database access</span></a>
  <img border="0" src="images/spacer.gif" width="10" height="5">
  <small><font color="red">*</font></small><label>Database password:</label><br /> 
  <input size="55" type="password" name="db_pass" id="db_pass" value="';
   echo $db_pass;

echo '" id="db_pass" /><br />
  <a class="ToolText" onclick="javascript:this.className=\'ToolTextHover\'" onMouseOut="javascript:this.className=\'ToolText\'"> 
  <br /><img alt="Click for info" title="Click for info" border="0" src="images/info.png" width="16" height="16">  
  <span><b>Database name</b><br /><br />Enter the database name you would like to use for SSAM. If you only have 1 database available, don\'t worry. 
  SSAM will create tables with a prefix consisting of \'ssa_\' and the domain name i.e. \'ssa_domainname_com\' or \'ssa_subdomain_domainname_com\', so there won\'t be any conflicts with existing tables. 
  Just enter the name of the database here.</span></a>
  <img border="0" src="images/spacer.gif" width="10" height="5">
  <small><font color="red">*</font></small><label>Database name:</label><br /> 
  <input size="55" type="text" name="db_name" id="db_name" value="';
echo $db_name;

echo '" id="db_name" />
<br /></td><![if !IE]><br /><![endif]>';

echo '<tr><td colspan="2" style="text-align: center;"><![if !IE]><br /><![endif]>
    <input style=" width: auto;" title="Submit to update settings." type="submit" name="submit" value="Submit settings"/>';

if(file_exists($dbsettings)){
  echo '<input style=" width: auto;background-color: #ffffcc" title="Clear the relevent fields to add another site." type="button" name="clear_form" value="Add another site" onclick="depopulate()"/>';
  echo '<input style=" width: auto;background-color: #FFD3DA" title="Click to proceed." type="button" name="step2" value="Finished" onclick="location.href=\'index.php?load_start_file=N&server='.$ftp_server.'\'"/>';
  echo '<img border="0" src="images/spacer.gif" width="270" height="5">';
  echo '<input style=" width: auto;background-color: #B0C4DE" title="Remove this site from the database." type="button" name="delete_site" value="Delete this site" onclick="respConfirm ();"/>';
}
    
echo '</form>';
    
echo '<![if !IE]><br /><br /><![endif]><small style="text-align: left">SSAM v'.$ssa_ver.' | Check for the <a target="_blank" href="http://simplesiteaudit.terryheffernan.net/">latest version</a></small>

<!--Paypal form-->
<form target="_blank" action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="item_name" value="In support of SimpleSiteAudit Multisite project">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
<center><small>If you think this software is worthy of support, please <a href="#">
<input type="submit" style=" width: auto;" value="Donate" border="0" name="submit" title="PayPal - The safer, easier way to pay online." alt="PayPal - The safer, easier way to pay online."></a></center>
<img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
</form>
<center><small><a target="_blank" href="http://simplesiteaudit.terryheffernan.net/forum?mingleforumaction=vforum&g=5.0">Feedback</a> would be appreciated. Thank you.</small></center>

</td></td></tr>';

echo '
    <script language="JavaScript" type="text/javascript"
        xml:space="preserve">//<![CDATA[
    var frmvalidator  = new Validator("form");
    frmvalidator.EnableMsgsTogether();
    //frmvalidator.addValidation("ftp_server","alphanumeric","Subdirectories not handled    ");
    frmvalidator.addValidation("ftp_server","req","Need URL of site to be monitored    "); 
    frmvalidator.addValidation("ftp_user","req","Need FTP User Name    ");      
    frmvalidator.addValidation("ftp_pass","req","Need FTP password    ");                 
    frmvalidator.addValidation("root_dir","req","Need document root of site being monitored     ");
    frmvalidator.addValidation("db_server","req","Need Mysql database server    "); 
    frmvalidator.addValidation("db_user","req","Need database user name    ");      
    frmvalidator.addValidation("db_pass","req","Need database password    ");                 
    frmvalidator.addValidation("db_name","req","Need database name    "); 
    //]]></script>';

echo '</td></tr></table>';

echo '<div id="fadeBlock">'; // Responses

echo '<table class="tab3" style="margin-left: 190px;"><tr><td>'; // responses table

if($site_deleted == 1){
    include 'includes/site_deleted.html';
}
// Confirmation response - cleared log file           
if ($_POST['submit'] && file_exists($dbsettings)) {
    include 'includes/confirm.html';
}

echo '</tr></td></table>'; // end responses table
echo '</div>';

echo '</tr></td></table>';

echo '</td></tr></table>
      </td></tr></table>';
echo '</body></html>';


?>