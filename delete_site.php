<?php
/*
  _, _ _, _ __, _,  __,    _, _ ___ __,    _, _,_ __, _ ___  
 (_  | |\/| |_) |   |_    (_  |  |  |_    / \ | | | \ |  |   
 , ) | |  | |   | , |     , ) |  |  |     |~| | | | / |  |   
  ~  ~ ~  ~ ~   ~~~ ~~~    ~  ~  ~  ~~~   ~ ~ `~' ~~  ~  ~   v1.5.4 Multisite
 * 
 * Copyright (C) 2012 Terry Heffernan. All rights reserved.
 * Technical support: http://simplesiteaudit.terryheffernan.net
 */
include 'version.php';
//session_start();
$ftp_server = $_GET[server];
$dbsettings = $logs_dir.'/'.$ftp_server.'/db_settings.txt';
$dbsettings_dir = $logs_dir.'/'.$ftp_server;
$special_file = '_'.$ftp_server.'.php';

if(file_exists($dbsettings)){
    $file = file($dbsettings);
    $db_server = trim($file[0]); 
    $db_user = trim($file[1]);
    $db_pass = trim($file[2]);
    $db_name = trim($file[3]);

    $key = 'let@me@in@NOW';
    $decrypt = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($db_pass), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
    $db_pass = trim($decrypt);
    
$con = mysql_connect($db_server,$db_user,$db_pass)or die(mysql_error());
mysql_select_db($db_name, $con)or die(mysql_error());

    
$dom = strstr ($ftp_server,'.');
$log_table = 'ssa_'.str_replace('-','_',str_replace('.','_',$ftp_server.'_log'));
$site_table = 'ssa_'.str_replace('-','_',str_replace('.','_',$ftp_server.'_site'));
$settings_table = 'ssa_'.str_replace('-','_',str_replace('.','_',$ftp_server.'_settings'));
$newlist_table = 'ssa_'.str_replace('-','_',str_replace('.','_',$ftp_server.'_newlist'));

if(strstr($ftp_server,'-')){
  $log_table = 'ssa_'.str_replace('-','$',str_replace('.','_',$ftp_server.'_log'));
  $site_table = 'ssa_'.str_replace('-','$',str_replace('.','_',$ftp_server.'_site'));
  $settings_table = 'ssa_'.str_replace('-','$',str_replace('.','_',$ftp_server.'_settings'));
  $newlist_table = 'ssa_'.str_replace('-','$',str_replace('.','_',$ftp_server.'_newlist'));
}

@mysql_query("DROP TABLE IF EXISTS `$db_name`.`$log_table`")or die('Unable to clear the table - ERROR1! '.mysql_error());
@mysql_query("DROP TABLE IF EXISTS `$db_name`.`$site_table`")or die('Unable to clear the table - ERROR2! '.mysql_error());
@mysql_query("DROP TABLE IF EXISTS `$db_name`.`$settings_table`")or die('Unable to clear the table - ERROR3! '.mysql_error());
@mysql_query("DROP TABLE IF EXISTS `$db_name`.`$newlist_table`")or die('Unable to clear the table - ERROR4! '.mysql_error());

mysql_close($con)or die(mysql_error());
}

if(file_exists($dbsettings)){
   chmod($dbsettings, 0666);
   unlink($dbsettings);
   chmod($dbsettings_dir, 0666);
   rmdir($dbsettings_dir);
   chmod($special_file, 0666);
   unlink($special_file);
}

if (!file_exists($dbsettings) && !file_exists($dbsettings_dir)) {
  header("Location: index1.php?site_deleted=1");// server=$ftp_server&
}else{
  echo 'Unable to delete. Please delete manually';  
}
?>
