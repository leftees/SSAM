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
    exit;
}
         
    $decrypt = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($encryption_key), base64_decode($db_pass), MCRYPT_MODE_CBC, md5(md5($encryption_key))), "\0");
    $db_pass = trim($decrypt);
    
    $con = new PDO('mysql:host='.$db_server.';dbname='.$db_name.';charset=utf8', $db_user, $db_pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    // $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $site_table = 'ssa_'.stripslashes(str_replace('-','$',str_replace('.','_',$ftp_server))).'_newlist';
    
    $result2 = $con->prepare("SELECT COUNT(*) FROM :site_table");
    $result2->bindParam(':site_table', $site_table);
    $result2->execute();
    $total_rows = $result2->fetch(PDO::FETCH_BOTH);
    $con = null;

if($stop != 'Y'){
   
    if($total_rows[0] == 0){
         $number_files = 4000;
    }else{
         $number_files = $total_rows[0];
    }
    require_once 'class.ProgressBar.php';
    $p = new ProgressBar();

}else{
    $con = new PDO('mysql:host='.$db_server.';dbname='.$db_name.';charset=utf8', $db_user, $db_pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    // $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $site_table = 'ssa_'.stripslashes(str_replace('-','$',str_replace('.','_',$ftp_server))).'_newlist';

    
    $result2 = $con->prepare("SELECT COUNT(*) FROM :site_table");
    $result2->bindParam(':site_table', $site_table);
    $result2->execute();
    $total_rows = $result2->fetch(PDO::FETCH_BOTH);

    $con = null;
    echo 'Scan complete<br>Total files scanned: '.$total_rows[0].'<br>';
    //exit;

}

?>
