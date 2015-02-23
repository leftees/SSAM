<?php
/*
  _, _ _, _ __, _,  __,    _, _ ___ __,    _, _,_ __, _ ___  
 (_  | |\/| |_) |   |_    (_  |  |  |_    / \ | | | \ |  |   
 , ) | |  | |   | , |     , ) |  |  |     |~| | | | / |  |   
  ~  ~ ~  ~ ~   ~~~ ~~~    ~  ~  ~  ~~~   ~ ~ `~' ~~  ~  ~    Multisite
 * 
 * Copyright (C) 2012 Terry Heffernan. All rights reserved.
 * Copyright (C) 2014 Daniel Ruf. All rights reserved.
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

    $decrypt = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($encryption_key), base64_decode($db_pass), MCRYPT_MODE_CBC, md5(md5($encryption_key))), "\0");
    $db_pass = trim($decrypt);

$con = new PDO('mysql:host='.$db_server.';dbname='.$db_name.';charset=utf8', $db_user, $db_pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
$dom = strstr ($ftp_server,'.'); // seems to be unused

$log_table = $con->quote('ssa_'.str_replace('-','_',str_replace('.','_',$ftp_server.'_log')));
$site_table = $con->quote('ssa_'.str_replace('-','_',str_replace('.','_',$ftp_server.'_site')));
$settings_table = $con->quote('ssa_'.str_replace('-','_',str_replace('.','_',$ftp_server.'_settings')));
$newlist_table = $con->quote('ssa_'.str_replace('-','_',str_replace('.','_',$ftp_server.'_newlist')));

if(strstr($ftp_server,'-')){
    $log_table = $con->quote('ssa_'.str_replace('-','$',str_replace('.','_',$ftp_server.'_log')));
    $site_table = $con->quote('ssa_'.str_replace('-','$',str_replace('.','_',$ftp_server.'_site')));
    $settings_table = $con->quote('ssa_'.str_replace('-','$',str_replace('.','_',$ftp_server.'_settings')));
    $newlist_table = $con->quote('ssa_'.str_replace('-','$',str_replace('.','_',$ftp_server.'_newlist')));
}

$sth_log = $con->prepare("DROP TABLE IF EXISTS `$db_name`.`$log_table`");
$sth_site = $con->prepare("DROP TABLE IF EXISTS `$db_name`.`$site_table`");
$sth_settings = $con->prepare("DROP TABLE IF EXISTS `$db_name`.`$settings_table`");
$sth_newlist = $con->prepare("DROP TABLE IF EXISTS `$db_name`.`$newlist_table`");

if (!$sth_log->execute()) {
    echo('Unable to clear the table - ERROR1!<br>');
    print_r($sth_log->errorInfo());
    exit;
}

if (!$sth_site->execute()) {
    echo('Unable to clear the table - ERROR2!<br>');
    print_r($sth_site->errorInfo());
    exit;
}

if (!$sth_settings->execute()) {
    echo('Unable to clear the table - ERROR3!<br>');
    print_r($sth_settings->errorInfo());
    exit;
}

if (!$sth_newlist->execute()) {
    echo('Unable to clear the table - ERROR4!<br>');
    print_r($sth_newlist->errorInfo());
    exit;
}

$con = null;
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