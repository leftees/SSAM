<?php

function simple_prg($start_prg = false, $request_uri = null) {
    // check to see if we should start prg
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $uniqid = uniqid();
        $_SESSION['post'][$uniqid] = $_POST;
        if (!$request_uri) {
            $request_uri = 'REQUEST_URI';
        }

        header("HTTP/1.1 303 See Other");

        $header = "Location: " . $_SERVER[$request_uri] . '?prg=1&uniqid=' . $uniqid;
        header($header);
        die;
    }

    if ($start_prg) {
        // on start we clean all session posts
        @$_SESSION['post'] = '';
    } else {
        if (isset($_GET['prg'])) {
            $uniqid = $_GET['uniqid'];
            $_POST = @$_SESSION['post'][$uniqid];
        }
    }
    return $uniqid;
}

function Select($logs_dir, $name) {
global $ftp_server;

    $html = '<select class="dropdown" name="'.$name.'" onchange="location.href=\'index.php?load_start_file=N&server=\'+this.value">';
    $html .= '<option selected>'.$ftp_server.'</option>';

    foreach(glob($logs_dir.'/*', GLOB_ONLYDIR) as $dir){ 
        $dir = basename($dir); 
        if(stripos($dir,".") != FALSE){
           $html .= '<option value='.$dir. '>' .$dir. '</option>';
        }
    }
    $html .= '</select>';
    return  $html;
}


function is_removeable($dir) {
    $folder = opendir($dir);

    while ($file = readdir($folder))
        if ($file != '.' && $file != '..' &&
                (!is_writable($dir . "/" . $file) ||
                ( is_dir($dir . "/" . $file) && !is_removeable($dir . "/" . $file) ) )) {
            closedir($folder);
            return false;
        }
    closedir($folder);
    return true;
}

function displayString($log) {
    $string = '';
    foreach ($log as $val) {
        $string .= $val . "\r\n";
    }
    return $string;
}

function refresh_logview() {
    $log_contents = array();
    if (file_exists($logs_dir . '/ssa_output.txt')) {
        $log_contents = file($logs_dir . '/ssa_output.txt'); // Parse the contents of ssa_output.txt into an array
    }
    $view_log = displayString($log_contents);
}

function isit_dir($dir){
 if($dir != '.' && $dir != '..'){
    $count = (count(glob("$dir/*",GLOB_ONLYDIR)));
    return $count;
 }
}

function store_details($db_server, $db_user, $db_pass, $db_name, $ftp_server, $subject, $skipfile, $skipdir, $rename_file, $alert, $from, $message, $createLog, $cronlogpath){

$date = date ("dMy");
$time = date("H:i");

    $site_table = 'ssa_'.str_replace('-','$',str_replace('.','_',$ftp_server)).'_site';
    $con = mysql_connect($db_server,$db_user,$db_pass)or exit('MySql ERROR1! '.mysql_error());
    mysql_select_db($db_name, $con)or exit('MySql ERROR2! '.mysql_error());
    $query = "TRUNCATE TABLE $site_table";
    mysql_query($query)or exit('Failed to empty site table<br>'.mysql_error());
    $query ="INSERT INTO $site_table (
        email_subj,
        email_alert,        
        skip_files,
        skip_dir,
        from_addr,
        email_header,
        cron_path,
        rename_file,
        SSA_log,
        date,
        time
       )
    VALUES (
        '$subject',
        '$alert',        
        '$skipfile',
        '$skipdir',
        '$from',        
        '$message',
        '$cronlogpath',
        '$rename_file',
        '$createLog',
        '$date',
        '$time')";

    mysql_query($query)or exit('Failed to update site table<br>'.mysql_error());
    mysql_close($con)or exit(mysql_error());
}

function is_table_empty($table_name,$db_server,$db_user,$db_pass,$db_name){
    
    $con = @mysql_connect($db_server,$db_user,$db_pass)or exit('Unable to connect to MySQL server: '.$db_server.'<br>Please check that the following details are correct:<br>
        db server name<br>
        db user name<br>
        db password<br>
        <a href="index1.php?check_db_details=Y">Click to reload form</a>');
    mysql_select_db($db_name, $con)or exit(mysql_error());
    
    $x = "SELECT COUNT(*) FROM $table_name"; 
    $result = mysql_query($x) or exit(mysql_error()); 
    $total_rows = mysql_fetch_row($result);
    return $total_rows[0];    
}
?>
