<?php

session_start();

    /** If your php.ini settings allow, increase php.ini time-out limit for big sites
     *  by uncommenting the following statement
     *           
     *  if( !ini_get('safe_mode') ){
     *     set_time_limit(60); // 60 secs should cover most sites
     *  }
     *        
     */

    $settings = array();
    $file = '../../logs/settings.txt'; // Comment out if uncommenting the following line
    //$file = 'logs/settings.txt'; // Use if no access to offline directory

    
// No need to change anything below this line
  if(file_exists($file)){
    $settings = file($file);
  }else{
  echo 'Before you run this file, please run the settings file.';
  echo '<p><a href="index.php">Click here</a></p>';
  exit(0);
  }  

    //collect values from the settings file
    $dir = trim($settings[0]); //Directory in which to start scanning.
    $url_route = trim($settings[1]); //Site's route directory
    $emailSubject = trim($settings[2]); //email subject text
    $excludeFileList = trim($settings[3]); //files to skip
    //$excludeExtensionList = "png,js";
    $emailAddressToAlert = trim($settings[4]); //email address to send the report
    $sendersEmail = trim($settings[5]); //"email sender's address
    $msg = trim($settings[6]); //"first line of email body text
    $logs_dir = trim($settings[7]); //location of your logs directory
    
    $pl = $logs_dir.'/previousList.txt'; //location of previousList.txt
    $lr = $logs_dir.'/lastRun.txt'; //location of lastRun.txt
    $outputFile = $logs_dir.'/ssa_output.txt'; //location of script output log
    $ssaLogHeader =  "\r\n\n".'File run: '.date('d-M-y @ H:i:s'); // log file sub-heading
    $createLog = trim($settings[8]);
    $clear = trim($settings[8]);

 //This array is used for finding duplicated 'Modified' file names  
   $duplicates = array();
   
 //Read last saved file-list into an array
   if(file_exists($pl)){
     $currentList = file($pl);
   }
   
 //Get last run time
   if(file_exists($lr)){
	   $lastRunTime = file_get_contents($lr);
	 }

 //Record this run time
  $thisRunTime = fopen($lr, 'w');
	  fwrite($thisRunTime,date('d-M-y @ H:i:s'));
 	fclose($thisRunTime);  
 
	if($emailAddressToAlert <> ""){   			
 
  $files = traverseDirectoryStructure($dir, $excludeFileList, $excludeExtensionList);
  //Write new list to file
  $previousList = fopen($pl, 'w');
 
  foreach($files as $key => $value){
   if($dir == '../'){
    $needle = "./";
    $url = "..".str_replace($needle ,"",stristr ($value,$needle ));
   }else{
    $url = $value;   
   }
    $timeStamp = filemtime ($url);
	  fwrite($previousList,(string)$url."|".(string)$timeStamp."\r\n"); 
  }
 	fclose($previousList);    	

 	$newList = file($pl);
 	
  $differences = array_merge(array_diff($newList,$currentList),array_diff($currentList,$newList));
  
  if($differences != null ){
       print "Run was successful. The following changes were found:"."\r\n";
  }          
  $i = 0;
  if($createLog == 'Y'){
     $output = fopen($outputFile, 'c');
  }
   
  foreach($differences as $key => $value){ 
     
        //Find URL
        $len = strlen($value);
        $u1 = substr($value,0,$len-13);
        $needle = "|";

            $mod = 0;
            foreach($newList as $value2){
              $u2 = substr($value2,0,$len-13);
              if($u2 == $u1){
                $mod = 1;
                $t1 = str_replace($needle,"",strstr($value2,$needle));
              }
            }
            
            $mod2 = 0;         
            foreach($currentList as $value3){
              $u3 = substr($value3,0,$len-13);
              if($u3 == $u1){
                $mod2 = 1;
                $t2 = str_replace($needle,"",strstr($value3,$needle));
              }
            }                           
                          
          //Find date
          if($t1 != ""){ 
          $dt1 = date ("d-M-y | H:i:s",$t1);
          }else{
          $dt1 = "Not available";
          }
          if($t2 != ""){         
          $dt2 = date ("d-M-y | H:i:s",$t2);
          }else{
          $dt2 = "Not available";
          }

      //Remove duplicate file names
      If(!array_value_in($duplicates,$u1)){
      $url  =  $url_route;
      $url .=  substr_replace(substr($value,0,$len-13),'',0,2);

            if ($mod2 == 1 && $mod == 0) {                 			  
                      $msg .= "\r\n\n"."File: '".substr($value,0,$len-13)."' is MISSING.\r\nLast seen: ".$dt2;
                      $ssaLog .= "\r\n"."File MISSING  | ".$dt2." | ".substr($value,0,$len-13);
                      echo "\r\n"."<br />File MISSING  |".$dt2."|".substr($value,0,$len-13);
                } elseif ($t1 > $t2 && $mod == 1 && $mod2 == 1) {
                          $msg .= "\r\n\nFile: ".$url." has been MODIFIED\r\n "."Date of change: ".$dt1;
                          $ssaLog .= "\r\n"."File MODIFIED | ".$dt1." | ".substr($value,0,$len-13); 
                          echo "\r\n"."<br />File MODIFIED |".$dt1."|".substr($value,0,$len-13);
                    } elseif ($mod == 1 && $mod2 == 0) { 
                              $msg .= "\r\n\nFile: ".$url." has been ADDED\r\n "."Date of change: ".$dt1;
                              $ssaLog .= "\r\n"."File ADDED    | ".$dt1." | ".substr($value,0,$len-13);
                              echo ("\r\n"."<br />File ADDED    |".$dt1."|".substr($value,0,$len-13));
            }
      
         $i++;      
      }
      array_push($duplicates,$u1);

  }//foreach loop end
  
    
  if($ssaLog == ""){
     $ssaLog = "\r\n".trim($ssaLogHeader).' No changes found';
  }else{
     $ssaLog = "\r\n".trim($ssaLogHeader)." The following changes were found:".$ssaLog;
  }
// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>    
  if($createLog == 'Y'){
     $output = fopen($logs_dir.'/ssa_output.txt', "a");
     fwrite($output,$ssaLog);
     fclose($output);
  }
 }

  //If changes exist, send mail  
	if($differences != null ){
	   $headers = 'From: '.$sendersEmail . "\r\n" . 'X-Mailer: PHP/' . phpversion();
		 mail($emailAddressToAlert, $emailSubject, $msg, $headers); //Simple mail function for alert.         
  }else{    
     echo "Run was successful. No changes found. "."\r\n";
  }   

                           
// FUNCTIONS FOLLOW

//Check for duplicate 'Modified' file names
function array_value_in($array,$string){
if(in_array($string,$array)){
return true;
}else{
return false;
}
}


//List all files in website, other than exclusions 
function traverseDirectoryStructure($dir, $excludeFileList, $excludeExtensionList) {
	if (!is_dir($dir) || !is_readable($dir)) {
		print "The directory you have chosen to monitor - $dir - does not exist or is not readable.<br />Please close this window and re-enter this field.";
    exit(0);
    return false;
	}

	$fileList = array ();
	$d = dir($dir);

	while (false !== ($entry = $d->read())) {
		if ($entry != '.' && $entry != '..') {
			if (is_dir($dir . '/' . $entry)) {
				$newFiles = traverseDirectoryStructure($dir . '/' . $entry, $excludeFileList, $excludeExtensionList);
				$fileList = array_merge($fileList,$newFiles);
			} else {
				//dont scan files in exclude list
				if (stripos($excludeFileList, $entry) === false) {
					$extension = end(explode('.', $entry)); //get the file extension

					//dont scan extensions in exclude list
					if (stripos($excludeExtensionList, $extension) === false) {
						$fileList[] = $dir . '/' . $entry; 
					}
				}
			}
		}
	}

	$d->close();

	return $fileList;
}

//For use with PHP4, I found I had to comment out the following 'if' statement - used by traverseDirectoryStructure()

//On earlier than PHP5 will use code below to declare stripos function
if (!function_exists("stripos")) {
  function stripos($str,$needle,$offset=0){
      return strpos(strtolower($str),strtolower($needle),$offset);
  }
}
?>