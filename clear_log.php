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
include 'version.php';
//session_start();
$ftp_server = $_GET[server];
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

    $con = mysql_connect($db_server,$db_user,$db_pass)or die(mysql_error());
    mysql_select_db($db_name, $con)or die(mysql_error());

    $dom = strstr ($ftp_server,'.');
    $log_table = 'ssa_'.str_replace('-','$',str_replace('.','_',$ftp_server)).'_log';

    $query = "TRUNCATE TABLE $log_table";
    mysql_query($query)or die('MySql query failed! Please check your database settings and user permissions.<br>'.mysql_error());

    mysql_close($con)or die(mysql_error());
}

if (is_table_empty($log_table,$db_server,$db_user,$db_pass,$db_name) == 0) {
  header("Location: index.php?server=$ftp_server&fileEmptied=1");
}else{
  echo 'Unable to clear log. Please check your database settings and user permissions.';  
}


function is_table_empty($table_name,$db_server,$db_user,$db_pass,$db_name){
    
    $con = mysql_connect($db_server,$db_user,$db_pass)or die('no conn: '.mysql_error());
    mysql_select_db($db_name, $con)or die(mysql_error());
    
    $x = "SELECT COUNT(*) FROM $table_name"; 
    $result = mysql_query($x) or die(mysql_error()); 
    $total_rows = mysql_fetch_row($result);
    return $total_rows[0];    
}
?>
