<?php
$ftp_server = '';
$ftp_user = '';
$ftp_pw = '';
$directory = '';

    $conn_id = ftp_connect($ftp_server) OR exit("Unable to establish an FTP connection");
    @ftp_login($conn_id, $ftp_user, $ftp_pw) OR exit("ftp-login failed - User name or password not correct");
    @ftp_pasv ( $conn_id, true ) or exit("Unable to set FTP passive mode."); //Use passive mode for client-side action
    //ftp_delete($conn_id,"ssa/filediff/temp/2.htm");
    //ftp_delete($conn_id,'ssa/filediff/temp/1.htm');
    $file_list = array_filter(ftp_rawlist($conn_id, $directory, true));
echo '<pre>';
print_r($file_list);
echo '</pre>';
ftp_close($conn_id); 

$l = traverseDirectoryStructure('includes', '', '');

$count = 0;
foreach($l as $val){

    if(is_array($val)){
          $val = array_map('trim',$val);
echo '<pre>';
print_r($val);
echo '</pre>';
        foreach($val as $v){

          if(!is_dir($v) && $v != '.' && $v != '..'){
            $count++;
          }
        } 
    }
}

/*
echo '<pre>';
print_r(array_keys($l));
echo '</pre>';
 * */
$f = count($l) -1;// decrement the count for [.] directory
print 'Folders: '.$f.'<br>';
print 'Files: '.$count.'<br>';
    
function traverseDirectoryStructure($dir, $excludeFileList, $excludeExtensionList) {
    
	if (!is_dir($dir) || !is_readable($dir)) {
		print "The directory you have chosen to monitor - $dir - does not exist or is not readable.<br />Please close this window and re-enter this field.";
                exit(0);
	}
        $fldr_count = 0;
        $file_count = 0;
	$fileList = array ();
	$d = dir($dir);

	while (false !== ($entry = $d->read())) {
		if ($entry != '.' && $entry != '..') {
			if (is_dir($dir . '/' . $entry)) {
				$newFiles = traverseDirectoryStructure($dir . '/' . $entry, $excludeFileList, $excludeExtensionList);
				$fileList = array_merge($fileList,$newFiles);
                                $fldr_count++;
			} else {
				//dont scan files in exclude list
				if (stripos($excludeFileList, $entry) === false) {
				//print	$extension = strpos('.', $entry); //get the file extension

					//dont scan extensions in exclude list
					if (stripos($excludeExtensionList, $extension) === false) {
						$fileList[str_replace('./','',$dir)][$file_count] = str_replace('./','',$entry);
                                                $file_count++;
					}
				}
			}
		}
	}

        
	$d->close();
	return $fileList;
}
?>