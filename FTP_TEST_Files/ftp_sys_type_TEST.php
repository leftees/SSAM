<?php

//FTP access details
$ftp_server = 'ftp.yourSiteURL'; // might not need ftp.
$ftp_user = 'ftpUserName';
$ftp_pw = 'ftpPassword';

    // make FTP connection
    $conn_id = ftp_connect($ftp_server) OR exit("Unable to establish an FTP connection");
    ftp_login($conn_id, $ftp_user, $ftp_pw) OR exit("ftp-login failed - User name or password not correct");
    ftp_pasv ( $conn_id, true ) or exit("Unable to set FTP passive mode.");
    $system = ftp_raw($conn_id,'SYST') OR exit("ftp_raw failed.");

    $OS = $system[0];
    echo $OS;
    // close the connection
    ftp_close($conn_id);
?>
