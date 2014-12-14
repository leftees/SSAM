<?php 

$file = $_GET['file'];
$local_file = stripslashes($file);
$ftp_server = $_GET['server'];

header('Content-Type: application/octetstream');  
header("Content-Disposition: attachment; filename=$local_file"); 
header('Pragma: public');

$includeFile = file_get_contents("http://".$ftp_server.$file);
echo $includeFile;

?>
