<?php
//include '../version.php'; //not needed as version.php is included before index1_functions.php
// Functions start here 
function checkSSL($domain){
	$ch = curl_init("http://".$domain);
	curl_setopt($ch, CURLOPT_NOBODY, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_exec($ch);
	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$port = curl_getinfo($ch, CURLINFO_PRIMARY_PORT);
	$domain = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
	if($port == 443 && $code == 200){
		$status = true;
	}else{
		$status = false;
	}
	curl_close($ch);
	return $status;
}

function store_details($db_server, $db_user, $db_pass, $db_name, $dbsettings, $ftp_server, $ftp_user, $ftp_pass, $logs_dir, $root_dir, $encryption_key) {

    if(substr($root_dir, 0, 1)!=="/")$root_dir="/".$root_dir;
    $date = date ("dMy");
    $time = date("H:i");

  if(!file_exists($logs_dir.'/'.$ftp_server)){
     mkdir($logs_dir.'/'.$ftp_server,0777,TRUE);
  }

    $db_settings = fopen($dbsettings, 'w');  
        
        $string = ' '.$db_pass.' '; // note the spaces    
        $db_pass = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($encryption_key), $string, MCRYPT_MODE_CBC, md5(md5($encryption_key))));
        
    $db_setts = trim($db_server)."\r\n".trim($db_user)."\r\n".trim($db_pass)."\r\n".trim($db_name);
    fwrite($db_settings, $db_setts);
    fclose($db_settings);
    
        $decrypt = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($encryption_key), base64_decode($db_pass), MCRYPT_MODE_CBC, md5(md5($encryption_key))), "\0");
        $db_pass = trim($decrypt);    
        $settings_table = 'ssa_'.str_replace('-','$',str_replace('.','_',$ftp_server)).'_settings';

    
    $con = mysql_connect($db_server,$db_user,$db_pass)or exit(mysql_error());
    mysql_select_db($db_name, $con)or exit(mysql_error());
    $query = "TRUNCATE TABLE $settings_table";
    mysql_query($query)or exit('MySql ERROR3! '.mysql_error());

        $string = ' '.$ftp_pass.' '; // note the spaces    
        $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($encryption_key), $string, MCRYPT_MODE_CBC, md5(md5($encryption_key))));

    $query ="INSERT INTO $settings_table (
        site_URL,
        FTP_user,
        FTP_pass,
        root_dir,
        root_URL,
        date,
        time
       )
    VALUES (
        '$ftp_server',
        '$ftp_user',
        '$encrypted',
        '$root_dir',
        '$ftp_server',
        '$date',
        '$time')";

    mysql_query($query)or exit('Query failed:<br />'.mysql_error());
    mysql_close($con)or exit(mysql_error());
}

function is_table_empty($table_name,$db_server,$db_user,$db_pass,$db_name){
    $con = new PDO('mysql:host='.$db_server.';dbname='.$db_name.';charset=utf8', $db_user, $db_pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    // $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $result = $con->prepare("SELECT COUNT(*) FROM :table_name");
    $result->bindParam(':table_name', $table_name);
    $result->execute();
    $total_rows = $result->fetch(PDO::FETCH_BOTH);
    $con = null;
    return $total_rows[0];
}

function create_db($db_user,$db_server,$db_pass,$db_name,$ftp_server){

    $ftp_svr  = str_replace('-','$',str_replace('.','_',$ftp_server));
    $newlist_table  = 'ssa_'.$ftp_svr.'_newlist';
    $settings_table = 'ssa_'.$ftp_svr.'_settings';
    $log_table      = 'ssa_'.$ftp_svr.'_log';
    $site_table     = 'ssa_'.$ftp_svr.'_site';

    $con = @mysql_connect($db_server,$db_user,$db_pass)or exit('Unable to connect to MySQL server: '.$db_server.'<br>Please check that the following details are correct:<br>
        db server name<br>
        db user name<br>
        db password<br>
        <a href="index1.php">Click to reload form</a>');
    mysql_query("CREATE DATABASE IF NOT EXISTS $db_name",$con)or exit(mysql_error());
    mysql_select_db($db_name, $con)or exit(mysql_error());

    // Create table
    // the path and filename fields should be much longer
    $newlist_sql = "CREATE TABLE IF NOT EXISTS $newlist_table
    (
    id int NOT NULL AUTO_INCREMENT,
    path varchar(150),
    filename varchar(150),
    size varchar(50),
    date varchar(10),
    time varchar(5),
    perms varchar(10),
    PRIMARY KEY (id)
    )";
    // Execute query
    mysql_query($newlist_sql,$con)or exit('create_db query2: '.mysql_error());

    $settings_sql = "CREATE TABLE IF NOT EXISTS $settings_table
    (
    id int NOT NULL AUTO_INCREMENT,
    site_URL varchar(150),
    FTP_user varchar(50),
    FTP_pass varchar(50),
    root_dir varchar(150),
    root_URL varchar(150),
    date varchar(7),
    time varchar(5),
    PRIMARY KEY (id)
    )";
    // Execute query
    mysql_query($settings_sql,$con)or exit('Failed to create settings table<br>'.mysql_error());
    // some fields should be longer
    $site_sql = "CREATE TABLE IF NOT EXISTS $site_table
    (
    id int NOT NULL AUTO_INCREMENT,
    email_subj varchar(100),
    email_alert varchar(1024),
    skip_files varchar(1024),
    skip_dir varchar(1024),
    from_addr varchar(150),
    email_header varchar(100),
    cron_path varchar(150),
    rename_file varchar(1024),
    SSA_log varchar(1),
    date varchar(7),
    time varchar(5),
    PRIMARY KEY (id)
    )";
    // Execute query
    mysql_query($site_sql,$con)or exit('Failed to create site table<br>'.mysql_error());
    //the file field should be mouch longer
    $log_sql = "CREATE TABLE IF NOT EXISTS $log_table
    (
    id int NOT NULL AUTO_INCREMENT,
    status varchar(30),
    file varchar(150),
    date varchar(10),
    time varchar(5),
    old_perms int(4),
    new_perms int(4),
    old_size int(20),
    new_size int(20),
    last_run varchar(20),
    PRIMARY KEY (id)
    )";
    // Execute query
    mysql_query($log_sql,$con)or exit('Failed to create log table<br>'.mysql_error());

    mysql_close($con)or exit(mysql_error());
}
