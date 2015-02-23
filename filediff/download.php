<?php 
include '../version.php';

$file = $_GET['file'];
//$local_file = stripslashes($file);
$local_file = str_replace("/","_",stripslashes($file));
$ftp_server = $_GET['server'];


$db_file = '../'.$logs_dir.'/'.$ftp_server.'/db_settings.txt';
   
if(file_exists($db_file)){
  $db_settings = file($db_file);
}else{
  echo 'Before you run this file, please save the database settings. Run the file, index1.php';
  exit;
}

$db_server = trim($db_settings[0]); // database Server 
$db_user = trim($db_settings[1]);  // mysql user name
$dbpass = trim($db_settings[2]);  // mysql password
$db_name = trim($db_settings[3]);   // Name of database
                
    $decrypt = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($encryption_key), base64_decode($dbpass), MCRYPT_MODE_CBC, md5(md5($encryption_key))), "\0");
    $db_pass = trim($decrypt);

if($ftp_server != "" && $ftp_server != null && $db_server != ""/* && $is_table_empty() > 0*/){
    $con = mysql_connect($db_server,$db_user,$db_pass)or exit(mysql_error());
    mysql_select_db($db_name, $con)or exit(mysql_error());
    
    $settings_table = 'ssa_'.str_replace('-','$',str_replace('.','_',$ftp_server)).'_settings';
    $result = mysql_query("SELECT FTP_user,FTP_pass,root_dir FROM $settings_table") or exit(mysql_error());

    while($row = mysql_fetch_array($result)) 
    {
       $ftp_user = $row['FTP_user'];
       $ftp_pw = $row['FTP_pass'];
       $root_dir = $row['root_dir'];
	   
    }
    mysql_close($con)or exit(mysql_error());
}

if(is_table_empty($settings_table,$db_server,$db_user,$db_pass,$db_name) > 0){
     $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($encryption_key), base64_decode($ftp_pw), MCRYPT_MODE_CBC, md5(md5($encryption_key))), "\0");
     $ftp_pw = trim($decrypted);
}else{
     'Wrong FTP username or password';
     exit;
}

function is_table_empty($table_name,$db_server,$db_user,$db_pass,$db_name){
    
    $con = mysql_connect($db_server,$db_user,$db_pass)or exit('no conn: '.mysql_error());
    mysql_select_db($db_name, $con)or exit(mysql_error());
    
    $x = "SELECT COUNT(*) FROM $table_name"; 
    $result = mysql_query($x) or exit(mysql_error()); 
    $total_rows = mysql_fetch_row($result);
    return $total_rows[0];    
}

header('Content-Type: application/octetstream');  
header('Content-Disposition: attachment; filename="'.$file.'"'); 
header('Pragma: public');
try{
if (empty($root_dir)){
$includeFile   = file_get_contents("ftp://".$ftp_user.":".$ftp_pw."@".$ftp_server.$file);
} else {
$includeFile   = file_get_contents("ftp://".$ftp_user.":".$ftp_pw."@".$ftp_server.'/'.$root_dir.$file);
}
}
catch(Exception $e){
if (empty($root_dir)){
$includeFile = file_get_contents("http://".$ftp_server.$file);
} else {
$includeFile = file_get_contents("http://".$ftp_server.'/'.$root_dir.$file);
}
}
/* Read remote file from ftp.example.com using FTP */
//$ftpfile   = file_get_contents("ftp://user:pass@ftp.example.com/foo.txt");

/* Read remote file from ftp.example.com using FTPS */
//$ftpsfile  = file_get_contents("ftps://user:pass@ftp.example.com/foo.txt");

echo $includeFile;

?>
