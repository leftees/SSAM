<?php
//session_start();
$pass = $_GET[pass];

$outputFile = trim($_GET[outputfile]);

$logs_dir =  trim($_GET[dir]);

$settings = array();
if (file_exists($logs_dir.'/settings.txt')) {
    $settings = file($logs_dir.'/settings.txt'); // Parse the contents of settings.txt into an array
}
$clear = trim($settings[8]);

if($pass == 'letmeinnow'){
  clear_log($outputFile);
  header("Location: index.php?clear=$clear&fileEmptied=1");
}else{
  header("Location: index.php?clear=$clear");
}

function clear_log($outputFile){
      $output = fopen($outputFile, 'w');
      fclose($output);
}
?>
