<?php
header('Content-type: text/html');
$date = date ("dMy@H:i:s");
include 'version.php';
$stop = '';


if( !ini_get('safe_mode') ){
  set_time_limit($time_limit); // Adjust this value in the file 'version.php'
}

if($_GET['server']){
$ftp_server = trim($_GET['server']); // Leave
}

if($_GET['stop']){
$stop = trim($_GET['stop']); // Leave
}

$db_file = $logs_dir.'/'.$ftp_server.'/db_settings.txt';

if(file_exists($db_file)){
  $db_settings = file($db_file);
  $db_server = trim($db_settings[0]); // database Server 
  $db_user = trim($db_settings[1]);  // mysql user name
  $db_pass = trim($db_settings[2]);  // mysql password
  $db_name = trim($db_settings[3]);   // Name of database
}else{
    echo 'db_settings file not found!';
    exit();
}
         
    $decrypt = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($encryption_key), base64_decode($db_pass), MCRYPT_MODE_CBC, md5(md5($encryption_key))), "\0");
    $db_pass = trim($decrypt);
    
    $con = mysql_connect($db_server,$db_user,$db_pass)or exit(mysql_error());
    mysql_select_db($db_name, $con)or exit(mysql_error());
    
    $site_table = 'ssa_'.stripslashes(str_replace('-','$',str_replace('.','_',$ftp_server))).'_newlist';
    $query = "SELECT COUNT(*) FROM $site_table"; 
    $result2 = mysql_query($query) or exit(mysql_error()); 
    $total_rows = mysql_fetch_row($result2);

    mysql_close($con)or exit(mysql_error());

if($stop != 'Y'){
   
    if($total_rows[0] == 0){
         $number_files = 4000;
    }else{
         $number_files = $total_rows[0];
    }
    require_once 'class.ProgressBar.php';
    $p = new ProgressBar();

}else{
    $con = mysql_connect($db_server,$db_user,$db_pass)or exit(mysql_error());
    mysql_select_db($db_name, $con)or exit(mysql_error());
    $site_table = 'ssa_'.stripslashes(str_replace('-','$',str_replace('.','_',$ftp_server))).'_newlist';
    $query = "SELECT COUNT(*) FROM $site_table"; 
    $result2 = mysql_query($query) or exit(mysql_error()); 
    $total_rows = mysql_fetch_row($result2);

    mysql_close($con)or exit(mysql_error());
    echo 'Scan complete<br>Total files scanned: '.$total_rows[0].'<br>';
    //exit();

}

?>
