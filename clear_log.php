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

    /* try { */
    $con = new PDO('mysql:host='.$db_server.';dbname='.$db_name.';charset=utf8', $db_user, $db_pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'")); //the database type can be later configured (mysql, oracle, mssql, sqlite, ...)
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $dom = strstr ($ftp_server,'.'); //this is not used
    $log_table = 'ssa_'.str_replace('-','$',str_replace('.','_',$ftp_server)).'_log';

    $sth = $con->prepare("TRUNCATE TABLE :log_table");
    $sth->bindParam(':log_table', $log_table);
    if (!$sth->execute()) {
    /*  $sth->execute() */
        echo('Query failed! Please check your database settings and user permissions.<br>');
        print_r($sth->errorInfo());
        exit;
    }
    
    /*
 
     $con = null;
     }

     catch( PDOException $ex ) { 
     exit('Query failed! Please check your database settings and user permissions.<br>'.$ex->getMessage());
     }

    */

    $con = null;
}

if (is_table_empty($log_table,$db_server,$db_user,$db_pass,$db_name) == 0) {
    header("Location: index.php?server=$ftp_server&fileEmptied=1");
	exit;
}else{
    echo 'Unable to clear log. Please check your database settings and user permissions.';  
}

function is_table_empty($table_name,$db_server,$db_user,$db_pass,$db_name){
    $con = new PDO('mysql:host='.$db_server.';dbname='.$db_name.';charset=utf8', $db_user, $db_pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    // $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sth = $con->prepare("SELECT COUNT(id) FROM :table_name");
    $sth->bindParam(':table_name', $table_name);
    // id (pk) is in PostgreSQL much faster as it uses index only scans, see https://wiki.postgresql.org/wiki/Index-only_scans for more info
    // always use prepared statements and execute for better security, see http://stackoverflow.com/a/4700740/753676 for more info
    $sth->execute();
    $total_rows=$sth->fetchColumn(0);
    $con = null;
    return $total_rows;    
}
?>