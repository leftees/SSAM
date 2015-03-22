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
    $con = new PDO('mysql:host='.$db_server.';dbname='.$db_name.';charset=utf8', $db_user, $db_pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    // $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $result = $con->prepare("TRUNCATE TABLE :site_table");
    $result->bindParam(':site_table', $site_table);
    $result->execute();

    $result = $con->prepare("INSERT INTO :site_table (
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
        ':subject',
        ':alert',        
        ':skipfile',
        ':skipdir',
        ':from',        
        ':message',
        ':cronlogpath',
        ':rename_file',
        ':createLog',
        ':date',
        ':time')");
    $result->bindParam(':site_table', $site_table);
    $result->bindParam(':subject', $subject);
    $result->bindParam(':alert', $alert);
    $result->bindParam(':skipfile', $skipfile);
    $result->bindParam(':skipdir', $skipdir);
    $result->bindParam(':from', $from);
    $result->bindParam(':message', $message);
    $result->bindParam(':cronlogpath', $cronlogpath);
    $result->bindParam(':rename_file', $rename_file);
    $result->bindParam(':createLog', $createLog);
    $result->bindParam(':date', $date);
    $result->bindParam(':time', $time);
    $result->execute();

    $con = null;
}

function is_table_empty($table_name,$db_server,$db_user,$db_pass,$db_name){
    $con = new PDO('mysql:host='.$db_server.';dbname='.$db_name.';charset=utf8', $db_user, $db_pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    // $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $result = $con->prepare("SELECT COUNT(*) FROM :table_name");
    $result->bindParam(':table_name', $table_name);
    $result->execute();
    $total_rows = $result->fetch(PDO::FETCH_BOTH);
    $con = null;
    
    // $con = @mysql_connect($db_server,$db_user,$db_pass)or exit('Unable to connect to MySQL server: '.$db_server.'<br>Please check that the following details are correct:<br>
        // db server name<br>
        // db user name<br>
        // db password<br>
        // <a href="index1.php?check_db_details=Y">Click to reload form</a>');
    
    return $total_rows[0];    
}
?>
