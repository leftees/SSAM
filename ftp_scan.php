<?php
/*
  _, _ _, _ __, _,  __,    _, _ ___ __,    _, _,_ __, _ ___  
 (_  | |\/| |_) |   |_    (_  |  |  |_    / \ | | | \ |  |   
 , ) | |  | |   | , |     , ) |  |  |     |~| | | | / |  |   
  ~  ~ ~  ~ ~   ~~~ ~~~    ~  ~  ~  ~~~   ~ ~ `~' ~~  ~  ~    Multisite
 * 
 * Copyright (C) 2012 Terry Heffernan. All rights reserved.
 * Copyright (C) 2014 Daniel Ruf. All rights reserved.
 * Technical support: http://simplesiteaudit.terryheffernan.net
 */

// the only file which should not be protected with the new login system
header('Content-type: text/html');
$date = date ("dMy@H:i:s");
$files = array();
include 'version.php';

if( !ini_get('safe_mode') ){
  set_time_limit($time_limit); // Adjust this value in the file 'version.php'
}
ini_set('memory_limit',$memory_limit.'M'); // Adjust this value in the file 'version.php'

// Start page-load timer    
$t0 = microtime();
$t3 = explode(' ', $t0);
$time1 = $t3[1] + $t3[0];
$start = $time1;

if($_GET['server']){
$ftp_server = trim($_GET['server']);
}

// For CLI support
/*
if (php_sapi_name() == 'cli') {
    $argv = $_SERVER['argv'];
    $ftp_server = $argv[1];
}
 */

$db_file = $logs_dir.'/'.$ftp_server.'/db_settings.txt';
   
if(file_exists($db_file)){
  $db_settings = file($db_file);
}else{
  echo 'Before you run this file, please save the database settings. Run the file, index1.php';
  exit;
}

//encrypt / decrypt all these values?
$db_server = trim($db_settings[0]); // database Server 
$db_user = trim($db_settings[1]);  // mysql user name
$dbpass = trim($db_settings[2]);  // mysql password
$db_name = trim($db_settings[3]);   // Name of database
                
    $decrypt = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($encryption_key), base64_decode($dbpass), MCRYPT_MODE_CBC, md5(md5($encryption_key))), "\0");
    $db_pass = trim($decrypt);

if($ftp_server !== "" && $ftp_server !== null && $db_server !== ""/* && $is_table_empty() > 0*/){
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

build_lists($logs_dir, $ftp_server, $ftp_user, $ftp_pw,$db_server,$db_user,$db_pass,$db_name,$date,$root_dir);
/*
$finish = timer();
$total_time = round(($finish - $start), 4);
echo '. Page loaded in ' . $total_time . ' seconds.'."\r\n";
 */

/*
 *------------------------------------------------------------------------------
 *--------------------------------- Functions ----------------------------------
 *------------------------------------------------------------------------------
 */

function build_lists($logs_dir, $ftp_server, $ftp_user, $ftp_pw ,$db_server,$db_user,$db_pass,$db_name,$date,$root_dir){

    global $ssa_ver;
    global $remote_sys_type;
    global $start;
    $remote_sys = explode(',',$remote_sys_type);

    $con = mysql_connect($db_server,$db_user,$db_pass)or exit(mysql_error());
    mysql_select_db($db_name, $con)or exit(mysql_error());
    
    $site_table = 'ssa_'.stripslashes(str_replace('-','$',str_replace('.','_',$ftp_server))).'_site';
    $result = mysql_query("SELECT * FROM $site_table") or exit(mysql_error());

    while($row = mysql_fetch_array($result)) 
    {
       $email_subject = $row['email_subj'];
       $skipfiles = $row['skip_files'];
       $skipdir = $row['skip_dir'];
       $rename = $row['rename_file'];
       $email_alert_addr = $row['email_alert'];
       $email_header = $row['email_header'];
       $email_from_addr = $row['from_addr'];
     
       $excludes = preg_split('/[\n\r,]+/', $skipfiles);
       $skip_dir = preg_split('/[\n\r,]+/', $skipdir);
       $rename_file = explode(',',$rename);
    }

    mysql_close($con)or exit(mysql_error());
    
    $skip_dir = array_filter(array_map('trim', $skip_dir));
    $excludes = array_filter(array_map('trim', $excludes));
    $email_subject = $email_subject.' - '.$ftp_server; //email subject text
    $email_text = $email_header.' - '.$ftp_server."\r\n\n";

    // make FTP connection
    $conn_id = ftp_connect($ftp_server);
    
    if(!$conn_id){
        exit("Unable to establish an FTP connection");
    }
    if(@ftp_login($conn_id, $ftp_user, $ftp_pw)){
    }
    else {
        //send mail if login fails
        /*
        //set priority to high, there are 1 = High, 3 = Normal and 5 = Low
        //use this for different priorities for the mails
        $headers = "MIME-Version: 1.0\r\n" ;
        $headers .= "Content-Type: text/html; charset=\"iso-8859-1\"\r\n"; //html
        $headers .= "Content-Type: text/plain; charset=\"utf-8\"\r\n"; //or plaintext
        $header  .= "Content-Type: text/plain; charset=utf-8\r\n"; // \r\n or \n?
        $headers .= "X-Priority: 1 (Highest)\r\n";
        $headers .= "X-MSMail-Priority: High\r\n";
        $headers .= "Importance: High\r\n";
        
        $headers  = "From: Some Person <person@website.com>\r\n";
        $headers .= "Reply-To: person@website.com\r\n";
        $headers .= "Return-Path: person@website.com\r\n";

        $headers = 'From: '.$email_from_addr . "\r\n" . 'X-Mailer: PHP/' . phpversion();
        
        $status   = mail($to, $subject, $message,$headers);
        mail($email_alert_addr, $email_subject, "ftp-login failed - User name or password not correct", $headers); //Simple mail function for alert.
        */
        exit("ftp-login failed - User name or password not correct");
    }
        
    @ftp_pasv ( $conn_id, true ) or exit("Unable to set FTP passive mode."); //Use passive mode for client-side action
    
    $system = ftp_raw($conn_id,'syst');
    
    $OS = $system[0];
    echo 'Remote system: ('.$OS.') - ';

    if(in_array($OS,$remote_sys)){
      $file_list = raw_list_linux($conn_id,$root_dir,$skip_dir,$excludes);
    }else{
      //$file_list = raw_list_windows($root_dir, $conn_id, $db_server, $db_user, $db_name, $db_pass, $ftp_server);
      //unsupported operating system
      exit;
    }
   
    ftp_close($conn_id); 
    
    $newlist_prefix = 'ssa_'.str_replace('-','$',str_replace('.','_',$ftp_server)).'_newlist';
    $log_prefix = 'ssa_'.str_replace('-','$',str_replace('.','_',$ftp_server)).'_log';
    $conn = mysql_connect($db_server,$db_user,$db_pass)or exit(mysql_error());
    mysql_select_db($db_name, $conn)or exit(mysql_error());

    $oldlist = oldlist($newlist_prefix);

    if(!empty($oldlist)){
        $first_run = 'N';
    }else{
        $first_run = 'Y';
    }
   
    mysql_query("TRUNCATE TABLE  `$newlist_prefix`") or exit('Unable to empty the newlist table:<br> '.mysql_error()); 

        echo 'SSA v'.$ssa_ver.' Multisite - Script run on '.$ftp_server.' on '.$date."\r\n";

        foreach ($file_list as $value) {
          if(in_array($OS,$remote_sys)){
            $perms = $value['perms'];
            $size  = $value['size'];
            $month = $value['month'];
            $day   = $value['day'];
            $year  = $value['year'];
            $file_name  = $value['filename'];
            $path  = $value['path'];
          }else{
            $perms = $value[0];
            $size  = $value[4];
            $month = $value[5];
            $day   = $value[6];
            $year  = $value[7];
            $file_name  = $value[8];
            $path  = $value[9];
          }
 
         if($file_name != ""){

                if(strpos($year, ':')){
                    $time = $year;
                    $y = ""; //then it is the current year, anyway, we should use a timestamp
                }else{
                    $y = $year;
                    $time = "00:00"; // should read 00:00:00
                }

          mysql_query("INSERT INTO $newlist_prefix
                  (path,
                  filename,
                  size,
                  date,
                  time,
                  perms) 
                     VALUES ('$path',
                  '$file_name',
                  '$size',
                  '$day$month$y',
                  '$time',
                  '$perms')")or exit(mysql_error()); 
          }
        }// End foreach

        $newlist = newlist($newlist_prefix);
        $missing = 0;
        if(!empty($oldlist) && is_array($newlist)){
            
            $diff = array_diff_key($oldlist,$newlist);
            if(!empty($diff)){
            $i = 0;
            
            foreach($diff as $key=>$value){

                $p = convert_perms($value['perms']);
                print 'File missing: '.$key.' - Last seen: '.$value['date'].' at '.$value['time']."\r\n";
                $email_text .= 'File missing: '.$key."\r\n".'Last seen: '.$value['date'].' at '.$value['time']."\r\n\n";
                mysql_query("INSERT INTO $log_prefix
                    (status,
                        file,
                        date,
                        time,
                        old_perms,
                        new_perms,
                        old_size,
                        new_size,
                        last_run) 
                        VALUES ('Missing',
                            '$key',
                            '$value[date]',
                            '$value[time]',
                            '$p',
                            '',
                            '$value[size]',
                            '',
                            '$date')")or exit(mysql_error());
                $i++;
                $missing++;
              }
            }
        }
        
        $conn_id = @ftp_connect($ftp_server);
        if(!$conn_id){
            exit("Unable to establish an FTP connection");
        }
        if(@ftp_login($conn_id, $ftp_user, $ftp_pw)){
        }
        else {
            //send mail if login fails
            $headers = 'From: '.$email_from_addr . "\r\n" . 'X-Mailer: PHP/' . phpversion();
            mail($email_alert_addr, $email_subject, "ftp-login failed - User name or password not correct", $headers); //Simple mail function for alert.
            exit("ftp-login failed - User name or password not correct");
        }
        @ftp_pasv ( $conn_id, true ) or exit("Unable to set FTP passive mode."); //Use passive mode for client-side action
       
        $added = 0;
        $modified = 0;
        $perm = 0;
        $renamed = 0;
        foreach ($file_list as $value) {
                   
          if(in_array($OS,$remote_sys)){
            $perms = $value['perms'];
            $size  = $value['size'];
            $month = $value['month'];
            $day   = $value['day'];
            $year  = $value['year'];
            $file_name  = $value['filename'];
            $path  = $value['path'];
          }else{
            $perms = $value[0];
            $size  = $value[4];
            $month = $value[5];
            $day   = $value[6];
            $year  = $value[7];
            $file_name  = $value[8];
            $path  = $value[9];
          }
                       
         if($file_name != ""){
                
            $resultB = mysql_query("SELECT * FROM $newlist_prefix WHERE path = '$path' AND filename = '$file_name' ")or exit(mysql_error());

            $row2 = mysql_fetch_row($resultB);
       
            $file = trim($path.'/'.$file_name);

            $date_newlist = $newlist[$file]['date'];
            $date_oldlist = $oldlist[$file]['date'];
            $time_newlist = $newlist[$file]['time'];
            $time_oldlist = $oldlist[$file]['time'];
            $size_newlist = $newlist[$file]['size'];
            $size_oldlist = $oldlist[$file]['size'];
            $new_perms = convert_perms($newlist[$file]['perms']);
            $old_perms = convert_perms($oldlist[$file]['perms']);
                
            if(in_array($file_name,$rename_file)){

                if(!@ftp_rename ( $conn_id , $path.'/'.$file_name , $path.'/'.$file_name.'_renamed.by.ssam' )){
                    @ftp_chmod ($conn_id, 755, $file_name) or exit(' Unable to change file permissions: '.$file_name);
                    @ftp_rename ( $conn_id , $path.'/'.$file_name , $path.'/'.$file_name.'_renamed.by.ssam' ) or exit(' Unable to rename file: '.$path.'/'.$file_name);
                }

                //ftp_close($conn_id);
                
                    print 'File renamed: '.$file.' - Date '.$row2[4].' Time: '.$row2[5]."\r\n";
                    $email_text .= 'File renamed: '.$file."\r\n".'Date '.$row2[4].' Time: '.$row2[5]."\r\n\n";
                    mysql_query("INSERT INTO $log_prefix
                        (status,
                            file,
                            date,
                            time,
                            old_perms,
                            new_perms,
                            old_size,
                            new_size,
                            last_run) 
                            VALUES ('Renamed',
                                '$file',
                                '$row2[4]',
                                '$row2[5]',
                                '$old_perms',
                                '$new_perms',
                                '$size_oldlist',
                                '$size_newlist',
                                '$date')")or exit(mysql_error());
 
               $i++;
               $renamed++;               
            }

                if($size_newlist != $size_oldlist && $newlist[$file]['path'] != "" && $oldlist[$file]['path'] != ""){
                    print 'File modified: '.$file.' - Date '.$row2[4].' Time: '.$row2[5].' Old file size = '.$size_oldlist.'bytes. New file size = '.$size_newlist.'bytes'."\r\n";
                    $email_text .= 'File modified: '.$file."\r\n".'Date '.$row2[4].' Time: '.$row2[5].' Old file size = '.$size_oldlist.'bytes. New file size = '.$size_newlist."bytes.\r\n\n";
                    mysql_query("INSERT INTO $log_prefix
                        (status,
                            file,
                            date,
                            time,
                            old_perms,
                            new_perms,
                            old_size,
                            new_size,
                            last_run) 
                            VALUES ('Modified',
                                '$file',
                                '$row2[4]',
                                '$row2[5]',
                                '$old_perms',
                                '$new_perms',
                                '$size_oldlist',
                                '$size_newlist',
                                '$date')")or exit(mysql_error()); 
                    $i++;
                    $modified++;
                }
                // optimize and create additional variable instead of using $modified
                if((($date_newlist != $date_oldlist) || ($time_newlist != $time_oldlist)) && $size_newlist == $size_oldlist && $newlist[$file]['path'] != "" && $oldlist[$file]['path'] != ""){
                    print 'File date or time changed: '.$file.' - Old date '.$date_oldlist.' New date '.$date_newlist.' Old time: '.$time_oldlist.' New time: '.$time_newlist."\r\n";
                    $email_text .= 'File date or time changed: '.$file."\r\n".'Old date '.$date_oldlist.' New date '.$date_newlist.' Old time: '.$time_oldlist.' New time: '.$time_newlist."\r\n\n";
                    mysql_query("INSERT INTO $log_prefix
                        (status,
                            file,
                            date,
                            time,
                            old_perms,
                            new_perms,
                            old_size,
                            new_size,
                            last_run) 
                            VALUES ('Modified',
                                '$file',
                                '$row2[4]',
                                '$row2[5]',
                                '$old_perms',
                                '$new_perms',
                                '$size_oldlist',
                                '$size_newlist',
                                '$date')")or exit(mysql_error()); 
                    $i++;
                    $modified++;
                }

                if(!empty($diff)){
                    $i++;
                }
               if(!empty($oldlist) && $newlist[$file]['path'] != "" && $oldlist[$file]['path'] == ""){
                    print 'File added: '.$file.' - Date added: '.$row2[4].' Time added: '.$row2[5]."\r\n";
                    $email_text .= 'File added: '.$file."\r\n".'Date: '.$row2[4].' Time: '.$row2[5]."\r\n\n";
                    mysql_query("INSERT INTO $log_prefix
                        (status,
                            file,
                            date,
                            time,
                            old_perms,
                            new_perms,
                            old_size,
                            new_size,
                            last_run) 
                            VALUES ('Added',
                                '$file',
                                '$row2[4]',
                                '$row2[5]',
                                '',
                                '$new_perms',
                                '$size_oldlist',
                                '$size_newlist',
                                '$date')")or exit(mysql_error()); 
                    $i++;
                    $added++;
                }
  
                if($newlist[$file]['perms'] != $oldlist[$file]['perms'] && $newlist[$file]['path'] != "" && $oldlist[$file]['path'] != ""){

                    print 'File permissions changed: '.$file.' - Old perms: '.$old_perms.' New perms: '.$new_perms."\r\n";
                    $email_text .= 'File permissions changed: '.$file."\r\n".'Old perms: '.$old_perms.' New perms: '.$new_perms."\r\n\n";
                    mysql_query("INSERT INTO $log_prefix
                        (status,
                            file,
                            date,
                            time,
                            old_perms,
                            new_perms,
                            old_size,
                            new_size,
                            last_run) 
                            VALUES ('Permissions',
                                '$file',
                                '$row2[4]',
                                '$row2[5]',
                                '$old_perms',
                                '$new_perms',
                                '$size_oldlist',
                                '$size_newlist',
                                '$date')")or exit(mysql_error()); 
                    $i++;
                    $perm++;
                }
            }
        }// end foreach loop
       
        if($i == 0 && $first_run == 'N'){
          echo 'NO CHANGES FOUND';
        }

        if($first_run == 'Y'){
          echo 'First run completed - All current website files have been added to the database';
        }

        if($i > 0){
            // Send email
            $headers = 'From: '.$email_from_addr . "\r\n" . 'X-Mailer: PHP/' . phpversion();
            mail($email_alert_addr, $email_subject, $email_text, $headers); //Simple mail function for alert. 
        }
        
$finish = timer();
$total_time = round(($finish - $start), 4);
echo '. Page loaded in ' . $total_time . ' seconds.'."\r\n";

//##################################################################################
    store_status($logs_dir, $ftp_server, $missing, $added, $perm, $modified, $renamed, $total_time);
//##################################################################################

        // Close mysql connection
        mysql_close($conn)or exit(mysql_error());
}

function oldlist($newlist_prefix){
    $oldlist = array();
    $old_list = mysql_query("SELECT * FROM $newlist_prefix") or exit(mysql_error());
    $a = 0;
    while($row = mysql_fetch_array($old_list)){
        $key = $row['path'].'/'.$row['filename'];
            $oldlist[$key]['id'] = $row['id'];
            $oldlist[$key]['path'] = $key;
            $oldlist[$key]['size'] = $row['size'];
            $oldlist[$key]['date'] = $row['date'];
            $oldlist[$key]['time'] = $row['time'];
            $oldlist[$key]['perms'] = $row['perms'];
            $a++;
    }
    return $oldlist;
}

function newlist($newlist_prefix){
    $newlist = array();
    $new_list = mysql_query("SELECT * FROM $newlist_prefix") or exit(mysql_error());
    $a = 0;
    while($row = mysql_fetch_array($new_list)){
        $key = $row['path'].'/'.$row['filename'];
            $newlist[$key]['id'] = $row['id'];
            $newlist[$key]['path'] = $key;
            $newlist[$key]['size'] = $row['size'];
            $newlist[$key]['date'] = $row['date'];
            $newlist[$key]['time'] = $row['time'];
            $newlist[$key]['perms'] = $row['perms'];
            $a++;
    }
    return $newlist;
}

function convert_perms($perms){
    $permissions = $perms;  // or whatever
      $mode = 0;

      if ($permissions[1] == 'r') $mode += 0400;
      if ($permissions[2] == 'w') $mode += 0200;
      if ($permissions[3] == 'x') $mode += 0100;
      else if ($permissions[3] == 's') $mode += 04100;
      else if ($permissions[3] == 'S') $mode += 04000;

      if ($permissions[4] == 'r') $mode += 040;
      if ($permissions[5] == 'w') $mode += 020;
      if ($permissions[6] == 'x') $mode += 010;
      else if ($permissions[6] == 's') $mode += 02010;
      else if ($permissions[6] == 'S') $mode += 02000;

      if ($permissions[7] == 'r') $mode += 04;
      if ($permissions[8] == 'w') $mode += 02;
      if ($permissions[9] == 'x') $mode += 01;
      else if ($permissions[9] == 't') $mode += 01001;
      else if ($permissions[9] == 'T') $mode += 01000;
      
      $octal = sprintf('%o', $mode, $mode);
      return $octal;
    
}

function is_table_empty($table_name,$db_server,$db_user,$db_pass,$db_name){
    
    $con = mysql_connect($db_server,$db_user,$db_pass)or exit('no connection to database: '.mysql_error());
    mysql_select_db($db_name, $con)or exit(mysql_error());
    
    $x = "SELECT COUNT(*) FROM $table_name"; 
    $result = mysql_query($x) or exit(mysql_error()); 
    $total_rows = mysql_fetch_row($result);
    return $total_rows[0];    
}

  function raw_list_linux($resource, $directory,$skipdir,$excludes) {
      global $items;

        if (is_array($file_list = array_filter(ftp_rawlist($resource, "-a ".$directory, true)))) {       

            $file = '';
           
            foreach ($file_list as $value) {
                  if(strpos($value,'/')){
                    $item['path'] = str_replace(':','',$value);
                  }
                  
                  if($item['path'] == ''){
                       $item['path'] = $directory;
                  }                
               
                  $parts = preg_split("/\s+/", $value);

                  if(count($parts) > 9){
                      $i = 9;
                      while($i < count($parts)){
                          $parts[8] = $parts[8].' '.$parts[$i];
                          $i++;
                      }
                  }
                    
                  if(isset($parts[8]) && isset($item)){

                     $extn = strrchr($parts[8],'.');
                     $dir_filetype = $item['path'].'/'.$extn;
                     $ign_directory = explode('/',$item['path']);

                     if(!in_array($item['path'],$skipdir) && is_ignored($skipdir,$item['path']) == 0 && !array_intersect($ign_directory,$skipdir) && 
                        !in_array($parts[8],$excludes) && !in_array($extn,$excludes) && !in_array($dir_filetype,$excludes) && 
                        !(in_array($parts[8],$skipdir) && $parts[0]{0}=='d')){

                          if(!strpos($value,'/') && $parts[8] != '.' && $parts[8] != '..'){
                            list($item['perms'],
                                $item['number'],
                                $item['user'],
                                //$item['user'], should be $item['owner'],
                                $item['group'],                      
                                $item['size'],
                                $item['month'],
                                $item['day'],
                                $item['year'], 
                                /*
                                    this might be a string for the time if the file was created in the last 12 months, 
                                    if it is older than 12 months, the year is used instead of the time string 
                                    see http://php.net/manual/en/function.ftp-rawlist.php
                                    
                                    rename to $item['year_time'] instead of $item['year']
                                */
                                $item['filename']) = $parts;
                                /*
                                    we just need the timestamp in the database instead of date and time
                                    
                                    calculate timestamp and calculate the numeric values of day, month and year
                                    $item['timestamp'] = strtotime(implode(' ', array($item['month'], $item['day'], $item['year_time'])));
                                    $item['year'] = date("Y",$item['timestamp']);
                                    $item['month'] = date("m",$item['timestamp']);
                                    $item['day'] = date("d",$item['timestamp']);
                                    
                                    see http://stackoverflow.com/a/10207358/753676 for more infos
                                */
                                /*
                                    optionally use filemtime(), filectime(), ftp_mdtm() for timestamp
                                    may increase needed time

                                    http://en.wikipedia.org/wiki/List_of_FTP_commands
                                    http://stackoverflow.com/questions/1310212/php-ftp-mdtm-not-support-by-server
                                */
                                $item['type'] = $parts[0]{0} === 'd' ? 'directory' : 'file';  // is 'type' a directory or a file?

                                if($file != $item['filename']){
                                    $items[] = $item;
                                }
                                $file = $item['filename'];
                         }
                   }
                }
          }
        }
    return $items;
 }
 /*
  * echo '<pre>';
    print_r($list);
    echo '</pre>';
  */

function is_ignored($skipdir,$folder){
    
    $match_found = 0;
    foreach($skipdir as $v){

      if(strpos($v,'*')){
       $needle = trim(str_replace('*','',$v));

       if($folder == $needle || strstr($folder,$needle)){
          $match_found++;
       }
      }
    }
   return $match_found;
}

function timer(){
$t = microtime();
$t2 = explode(' ', $t);
$time = $t2[1] + $t2[0];
$finish = $time;
$total_time = round(($finish - $start), 4);
return $total_time;
}

// improve or remove this and use the database for calculating the statistics and the new timestamp
function store_status($logs_dir, $ftp_server, $missing, $added, $perm, $modified, $renamed, $total_time) {

    $date = date ("dMy H:i:s");
    $time = date("Hi");
    $unix_time_now = time();
    $status_file1 = $logs_dir.'/'.$ftp_server.'/24hr_status.txt';
    $status_file2 = $logs_dir.'/'.$ftp_server.'/7day_status.txt';
    $status_file3 = $logs_dir.'/'.$ftp_server.'/30day_status.txt';
    $missing2 = $missing;
    $missing3 = $missing;
    $added2 = $added;
    $added3 = $added;
    $perm2 = $perm;
    $perm3 = $perm;
    $modified2 = $modified;
    $modified3 = $modified;
    $renamed2 = $renamed;
    $renamed3 = $renamed;
    
    if(file_exists($status_file1)){ 
        $time_arr = file($status_file1);
        $existing_times = $time_arr[8];
        $average_time = $existing_times.','.$total_time;
         $existing_status = file($status_file1); //same as line 667
         $first_run_date = trim($existing_status[1]);
         $last_run_time = trim($existing_status[2]);

        if($unix_time_now - $last_run_time < 86400){  
            $missing = trim($missing) + $existing_status[3];
            $added = trim($added) + $existing_status[4];
            $perm = trim($perm) + $existing_status[5];
            $modified = trim($modified) + $existing_status[6];
            $renamed = trim($renamed) + $existing_status[7];

            $status1 = fopen($status_file1, 'w');
            $status_data = $ftp_server."\r\n".$first_run_date."\r\n".$last_run_time."\r\n".$missing."\r\n".$added."\r\n".$perm."\r\n".$modified."\r\n".$renamed."\r\n".$average_time;
            fwrite($status1, $status_data);
        }else{
            $this_run_time = $unix_time_now;
            $this_run_date = $date;
            $status1 = fopen($status_file1, 'w');
            $status_data = $ftp_server."\r\n".$this_run_date."\r\n".$this_run_time."\r\n".$missing."\r\n".$added."\r\n".$perm."\r\n".$modified."\r\n".$renamed."\r\n".$average_time;
            fwrite($status1, $status_data);
        }
   }else{
        $first_run_time = $unix_time_now;
        $first_run_date = $date;
        $status1 = fopen($status_file1, 'w');
        $status_data = $ftp_server."\r\n".$first_run_date."\r\n".$first_run_time."\r\n".$missing."\r\n".$added."\r\n".$perm."\r\n".$modified."\r\n".$renamed."\r\n".$average_time;
        fwrite($status1, $status_data);
   }
 fclose($status1);
 

     if(file_exists($status_file2)){
        $time_arr2 = file($status_file2);
        $existing_times2 = $time_arr2[8];
        $average_time2 = $existing_times2.','.$total_time;
         $existing_status = file($status_file2);
         $first_run_date = trim($existing_status[1]);
         $last_run_time = trim($existing_status[2]);

        if($unix_time_now - $last_run_time < 604800){  
            $missing2 = trim($missing2) + $existing_status[3];
            $added2 = trim($added2) + $existing_status[4];
            $perm2 = trim($perm2) + $existing_status[5];
            $modified2 = trim($modified2) + $existing_status[6];
            $renamed2 = trim($renamed2) + $existing_status[7];

            $status2 = fopen($status_file2, 'w');
            $status_data = $ftp_server."\r\n".$first_run_date."\r\n".$last_run_time."\r\n".$missing2."\r\n".$added2."\r\n".$perm2."\r\n".$modified2."\r\n".$renamed2."\r\n".$average_time2;
            fwrite($status2, $status_data);
        }else{
            $this_run_time = $unix_time_now;
            $this_run_date = $date;
            $status2 = fopen($status_file2, 'w');
            $status_data = $ftp_server."\r\n".$this_run_date."\r\n".$this_run_time."\r\n".$missing2."\r\n".$added2."\r\n".$perm2."\r\n".$modified2."\r\n".$renamed2."\r\n".$average_time2;
            fwrite($status2, $status_data);
        }
   }else{
        $first_run_time = $unix_time_now;
        $first_run_date = $date;
        $status2 = fopen($status_file2, 'w');
        $status_data = $ftp_server."\r\n".$first_run_date."\r\n".$first_run_time."\r\n".$missing2."\r\n".$added2."\r\n".$perm2."\r\n".$modified2."\r\n".$renamed2."\r\n".$average_time2;
        fwrite($status2, $status_data);
   }
 fclose($status2);
 
  
  
     if(file_exists($status_file3)){ 
        $time_arr3 = file($status_file3);
        $existing_times3 = $time_arr3[8];
        $average_time3 = $existing_times3.','.$total_time;
         $existing_status = file($status_file3);
         $first_run_date = trim($existing_status[1]);
         $last_run_time = trim($existing_status[2]);

        if($unix_time_now - $last_run_time < 2592000){  
            $missing3 = trim($missing3) + $existing_status[3];
            $added3 = trim($added3) + $existing_status[4];
            $perm3 = trim($perm3) + $existing_status[5];
            $modified3 = trim($modified3) + $existing_status[6];
            $renamed3 = trim($renamed3) + $existing_status[7];

            $status3 = fopen($status_file3, 'w');
            $status_data = $ftp_server."\r\n".$first_run_date."\r\n".$last_run_time."\r\n".$missing3."\r\n".$added3."\r\n".$perm3."\r\n".$modified3."\r\n".$renamed3."\r\n".$average_time3;
            fwrite($status3, $status_data);
        }else{
            $this_run_time = $unix_time_now;
            $this_run_date = $date;
            $status3 = fopen($status_file3, 'w');
            $status_data = $ftp_server."\r\n".$this_run_date."\r\n".$this_run_time."\r\n".$missing3."\r\n".$added3."\r\n".$perm3."\r\n".$modified3."\r\n".$renamed3."\r\n".$average_time3;
            fwrite($status3, $status_data);
        }
   }else{
        $first_run_time = $unix_time_now;
        $first_run_date = $date;
        $status3 = fopen($status_file3, 'w');
        $status_data = $ftp_server."\r\n".$first_run_date."\r\n".$first_run_time."\r\n".$missing3."\r\n".$added3."\r\n".$perm3."\r\n".$modified3."\r\n".$renamed3."\r\n".$average_time3;
        fwrite($status3, $status_data);
   }
 fclose($status3);
}
?>
