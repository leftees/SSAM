<?php
//make this the config file?

//SSA version
$ssa_ver = '1.5.8';

$debug = false;
$debug_level = 0;

switch ($debug_level) {
    case 0:
		error_reporting(0);
		break;
	case 1:
		error_reporting(E_ERROR | E_WARNING | E_PARSE);
		break;
	case 2:
		error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
		break;
	case 3:
		error_reporting(E_ALL ^ E_NOTICE);
		break;
	case 4:
		error_reporting(E_ALL);
		break;
	case 5:
		error_reporting(-1);
        break;
}

//prepare methods for backtracing and printing the stacktrace
function show_backtrace($debug){
if($debug) debug_backtrace();
}

function show_backtrace($debug){
if($debug) debug_print_backtrace();
}

// $logs_dir. This folder will contain your db access details. Therefore, it should either be in an off-line folder
//  (e.g. '../../logs') or in a password protected folder if you want it web accessible.
$logs_dir = '../../ssam_logs';

// $remote_sys_type can be a single entry (as it is here), or a comma separated list of Linux SYST responses,
// in which case it will be treated as an array 
// http://cr.yp.to/ftp/syst.html
$remote_sys_type = '215 UNIX Type: L8';

// $time_limit holds the time-out value for 'ftp_scan.php'.
// Adjust this time-out value as required - measured in seconds
$time_limit = 60;

// If you run in to memory problems, $memory_limit allows the adjustment of maximum allowed memory usage
// for the duration of the script - measured in megabytes
$memory_limit = 512;
?>