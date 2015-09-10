<?php
$url = $_POST['url'];
$api = $_POST['api'];
include '../version.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<title>Scan results</title>
<link href="../css/simplesiteaudit.css" rel="stylesheet" type="text/css">
</head>
<body>
<p class="sub2" style="text-align:left;margin:15px"><font color="maroon">SimpleSiteAudit<label> Multisite v<?php echo $ssa_ver?></label></font><br>Malware Scan results
<input type="button" id="close" onclick="window.close()" style="width: auto; background-color: #ffffff;" name="close" title="Close this window" alt="Close this window" value="Close">
</p>
<?php

require_once('VirusTotalApiV2.php');

/* Initialize the VirusTotalApi class. */
$api = new VirusTotalAPIV2($api);

/* Get an URL report. */
$report = $api->getURLReport($url);
$scan_result = object_2_array($report);
/*echo '<pre>';
print_r($scan_result);
echo '</pre>';*/
$exclude = array();
$exclude = array(permalink,response_code,scan_id,verbose_msg,filescan_id,positives,total);
?><table class="tab6"><tr><?php
foreach($scan_result as $key=>$value){
    
    if(is_array($value)){
      echo '<td></td><td></td></tr>';
      echo '<td><b>Name of database</b></td><td><b>Value returned by database</b></td></tr>';
       foreach($value as $k=>$val){
        if($val[detected] != ""){
          echo '<td>'. $k.'</td><td><img border="0" src="../images/red_light.png" width="10" height="10">Record shows '.$val[detected].' <b>Black-listed</b></td></tr>';
        }else{
          If($val[result] == "clean site"){
              echo '<td>'. $k.'</td><td><img border="0" src="../images/green_light.png" width="10" height="10">Record shows '.$val[result].'. Not black-listed.</td></tr>';
          }else{
              echo '<td>'. $k.'</td><td><img border="0" src="../images/grey_light.png" width="10" height="10">Record shows '.$val[result].'. Not yet rated, or rated as suspicious.</td></tr>';
          }
        }
       }
    }else{
      if(!in_array($key,$exclude)){
          print '<td>'. $key.'</td><td>'.$value.'</td></tr>';
      }
    }
}
?></tr></table></body></html><?php

function object_2_array($result) 
{ 
    $array = array(); 
    foreach ($result as $key=>$value) 
    { 
        if (is_object($value) || is_array($value)) 
        { 
            $array[$key]=object_2_array($value); 
        } 
        else 
        { 
            $array[$key]=$value; 
        } 
    } 
    return $array; 
} 
?>
