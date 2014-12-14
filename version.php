<?php
//SSA version
$ssa_ver = '1.5.8';

// $logs_dir. This folder will contain your db access details. Therefore, it should either be in an off-line folder
//  (e.g. '../../logs') or in a password protected folder if you want it web accessible.
$logs_dir = '../../logs';

// $remote_sys_type can be a single entry (as it is here), or a comma separated list of Linux SYST responses,
// in which case it will be treated as an array 
$remote_sys_type = '215 UNIX Type: L8';

// $time_limit holds the time-out value for 'ftp_scan.php'.
// Adjust this time-out value as required - measured in seconds
$time_limit = 60;

// If you run in to memory problems, $memory_limit allows the adjustment of maximum allowed memory usage
// for the duration of the script - measured in megabytes
$memory_limit = 512;
?>