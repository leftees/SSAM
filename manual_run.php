<?php
header('Content-type: text/html');
$date = date ("dMy@H:i:s");
//include 'version.php';
//error_reporting (E_ALL ^ E_NOTICE);


if($_GET['server']){
$ftp_server = trim($_GET['server']); // Leave
}
if($_GET['errno']){
$errno = trim($_GET['errno']); // Leave
}

?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<title>SSAM Manual run</title>
<script>
function refresh_iframe(){
document.getElementById('I1').contentWindow.location='ftp_scan_progress_bar.php?stop=Y&server=<?php echo $ftp_server ?>';
//window.location ="ftp_scan_progress_bar.php?stop=Y";
}
</script>
</head>

<body>

<p><iframe id="I1" name="I1" width="100%" height="100" scrolling="yes" frameborder="0" src="ftp_scan_progress_bar.php?server=<?php echo $ftp_server ?>">
Your browser does not support inline frames or is currently configured not to display inline frames.
</iframe><br>
<iframe onload="javascript: refresh_iframe();" name="I2" scrolling="yes" width="100%" height="650" frameborder="0" src="ftp_scan.php?server=<?php echo $ftp_server ?>">
Your browser does not support inline frames or is currently configured not to display inline frames.
</iframe></p>

</body>

</html>