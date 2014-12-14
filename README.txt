  _, _ _, _ __, _,  __,    _, _ ___ __,    _, _,_ __, _ ___  
 (_  | |\/| |_) |   |_    (_  |  |  |_    / \ | | | \ |  |   
 , ) | |  | |   | , |     , ) |  |  |     |~| | | |_/ |  |   
  ~  ~ ~  ~ ~   ~~~ ~~~    ~  ~  ~  ~~~   ~ ~ `~' ~   ~  ~  

Hopefully, your site will never be hacked, but if it is, and you have SimpleSiteAudit installed, you will be notified of any file changes shortly afterwards.

Primarily, SimpleSiteAudit script is designed to be used as a scheduled task or cron job, run maybe once an hour. You will be able to automatically monitor any directory within your web site, and all of its sub-directories.

It can also be run from your browser via the settings page (index.php), click on the 'Run the script' button - if changes have been made, a list of changed files will open in a new tab/window.

FILE AND DIRECTORY NAMES
All file names must remain as is. The SSA directory can be named anything.

JAVASCRIPT:
Javascript is required for some of the form actions and therefore, must be allowed in your browser settings.

FILE PLACEMENT:
1. Make sure that an OFFLINE directory, named 'logs' exists, just ABOVE route level, where it is not web-accessible. If it doesn't exist, please create it via your FTP client. If you don't have access to offline directories, create the 'logs' directory in your password protected, ssa, directory and amend the two ssa php files as follows:
File index.php - change the line (~22) $logs_dir = '../../logs'; to $logs_dir = 'logs';
File simplesiteaudit.php - change the line (~12) $file = '../../logs/settings.txt'; to $file = 'logs/settings.txt';

2. Create another directory, just BELOW route level, where you will upload the SimpleSiteAudit files. Name this directory what ever you like, e.g. 'ssa'. This directory should be password protected.

RUN THE SETTINGS PAGE:
1. Upload the files to your new directory and run index.php in your browser. 
2. Complete and submit the settings form and then click 'Run the script'. The settings form will auto-fill, simply change the details to your requirements. 

CREATE AN SSA LOG FILE:
The settings form allows the optional creation of a log file, containing all the SimpleSiteAudit script outputs. The file (ssa_output.txt) will be created in your 'logs' directory. This file can grow quite large, periodic clearing of the file is recommended. The log is viewable via a third checkbox.

DISPLAYING YOUR CRON LOG FILE (Field: 'Path to, and name of your Cron log')
This must point to your Cron log file, i.e. the relative path from your SSA installation files. e.g. ../../cronfilename.txt 
The 'view file' button will not appear if this file does not exist. Recommend leaving this field empty if using the SSA log file and vice versa. The log contents will appear in a read only textarea. Further formatting is not possible due to unknown input by the Cron process itself. There is no 'Clear log' button for this feature.

First run will list all files in your nominated directory and it's sub-directories. Subsequent runs will only list changes.

That's it. 

Hope you enjoy using SimpleSiteAudit.