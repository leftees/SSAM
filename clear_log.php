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
if(file_exists($dbsettings)){
    $file = file($dbsettings);
    $db_server = trim($file[0]); 
    $db_user = trim($file[1]);
    $db_pass = trim($file[2]);
    $db_name = trim($file[3]);

    $decrypt = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($encryption_key), base64_decode($db_pass), MCRYPT_MODE_CBC, md5(md5($encryption_key))), "\0");
    $db_pass = trim($decrypt);
	
	$con = new PDO('mysql:host='.$db_server.';dbname='.$db_name, $db_user, $db_pass); //the database type can be later configured (mysql, oracle, mssql, sqlite, ...)
	//$con = mysql_connect($db_server,$db_user,$db_pass)or exit('no conn: '.mysql_error());
    //mysql_select_db($db_name, $con)or exit(mysql_error()); //add try - catch block for printing the exception - only in debug mode?

    $dom = strstr ($ftp_server,'.');
    $log_table = 'ssa_'.str_replace('-','$',str_replace('.','_',$ftp_server)).'_log';

    $query = "TRUNCATE TABLE $log_table";
    mysql_query($query)or exit('MySql query failed! Please check your database settings and user permissions.<br>'.mysql_error());

    $con = null;
}

if (is_table_empty($log_table,$db_server,$db_user,$db_pass,$db_name) == 0) {
  header("Location: index.php?server=$ftp_server&fileEmptied=1");
}else{
  echo 'Unable to clear log. Please check your database settings and user permissions.';  
}


function is_table_empty($table_name,$db_server,$db_user,$db_pass,$db_name){
    
	$con = new PDO('mysql:host='.$db_server.';dbname='.$db_name, $db_user, $db_pass);
    //$con = mysql_connect($db_server,$db_user,$db_pass)or exit('no conn: '.mysql_error());
    //mysql_select_db($db_name, $con)or exit(mysql_error());
    
    $x = "SELECT COUNT(*) FROM $table_name"; 
    $result = mysql_query($x) or exit(mysql_error()); 
    $total_rows = mysql_fetch_row($result);
    return $total_rows[0];    
}
?>
