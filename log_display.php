<?php
echo '<div class="view" id="view">
<input type="button" onclick="toggle_visibility(\'view\');" style="width:auto;background-color: #FFffcc;" name="Hide_ssa_log" value="Hide SSA log" /a>';
if (is_table_empty($log_table,$db_server,$db_user,$db_pass,$db_name) > 0 && $log == 'checked') {
   $contents_header = '<p class="sub1" ><label>SSA Log contents</label><br />
       <small class="noPrint">(You might need to refresh the page to show the latest updates)</small></p>';
}else{
   $contents_header = '<p class="sub1" style="text-align: center;"><label>SSA Log is empty</label><br />
       <small class="noPrint">(You might need to refresh the page to show the latest updates)</small></p>'; 
}
echo '<a name="1"></a>';// bookmark for files that can't be downloaded
echo '<table class="tab2" border="1" bordercolor="#ccc">
      <tr><td colspan="9">'.$contents_header.'</td></tr>';

if($ftp_server != "" && $ftp_server != null && $db_server != "" && $is_table_empty > 0){
    $con = new PDO('mysql:host='.$db_server.';dbname='.$db_name.';charset=utf8', $db_user, $db_pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    
    $log_table = 'ssa_'.str_replace('-','_',str_replace('.','_',$ftp_server)).'_log';
    $settings_table = 'ssa_'.str_replace('-','_',str_replace('.','_',$ftp_server)).'_settings';
    $result = $con->prepare("SELECT * FROM $log_table");
    $result->execute();
    
    while($row = $result->fetch(PDO::FETCH_BOTH)) // default fetch style
    {
       $log_lines[] = $row;
    }
    
    $dir_to_monitor = $con->prepare("SELECT * FROM $settings_table");
    $dir_to_monitor->execute();
    $dir_to_mon = $dir_to_monitor->fetchAll();

    $con = null;
}

echo '<tr><td colspan="8" style="padding: 3px;font-size:12px;"><b>Web site:</b> '.$ftp_server.'<br /><b>Start Dir:</b> '.$dir_to_mon[root_dir].'</td>
    <td><input class="noPrint" type="button" id="print" onclick="printDiv(\'view\')" style="margin-top:5;margin-left:30; width: auto; background-color: #ffffff;" name="print" alt="Print" title="Print the log" value="PRINT"></td></tr>
        <tr><td style="padding: 3px;"><font color="brown">Status</font></td>
        <td style="padding: 3px; width: 110px;"><font color="brown">Path to file (relative to \'Start Dir\')</font></td>
        <td style="padding: 3px; width: 110px;"><font color="brown">File date</font></td>
        <td style="padding: 3px; width: 110px;"><font color="brown">File time</font></td>
        <td style="padding: 3px; width: 110px;"><font color="brown">Old perms</font></td>
        <td style="padding: 3px; width: 110px;"><font color="brown">New perms</font></td>
        <td style="padding: 3px; width: 110px;"><font color="brown">Old size</font></td>
        <td style="padding: 3px; width: 110px;"><font color="brown">New size</font></td>
        <td style="padding: 3px"><font color="brown">Script run time </font></td></tr></font>';

  if(is_array($log_lines)){
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;
  
      foreach($log_lines as $value){
         
             $id = $value['id'];
             $status = $value['status'];
             $file_name = $value['file'];
             $file_date = $value['date'];
             $file_time = $value['time'];
             $old_perms = $value['old_perms'];
             $new_perms = $value['new_perms'];
             $old_size = $value['old_size'];
             $new_size = $value['new_size'];
             $last_run = $value['last_run']; 

          
          if($status == "Added"){$bgcolor = "#F0F8FF";}          
          if($status == "Modified"){$bgcolor = "#FFE4E1";}          
          if($status == "Missing"){$bgcolor = "#DEFADE";}              
          if($status == "Permissions"){$bgcolor = "#ffffcc";}
          if($status == "Renamed"){$bgcolor = "#FFCCFF";}
      
             echo '<tr id="selected" style="background-color:'.$bgcolor.';">';
      
             $file_name = trim(stristr ($file_name,'/'));
      
             if(@file_get_contents("http://".$ftp_server.$file_name, NULL, NULL, 0, 1)){// Test if file is downloadable, map client address to real address, download.php should always work
                       $img = 'images/arrow_down_blue.gif';
                       $alt = 'Download this file for comparison with backup file.';
                       $ttl = 'Download this file for comparison with backup file.';
                       $href = "filediff/download.php?file=$file_name&server=$ftp_server";
             }else{
                       $img = 'images/button_cancel.png';
                       $alt = 'Remote server returned an error. I am unable to download this file.';
                       $ttl = 'Remote server returned an error. I am unable to download this file.';
                       $href = "#1";
             }
             
             
      
             if($status == "Modified"){
               echo '<td style="padding: 1px;">'.$status.'<a href="'.$href.'" /><img title="'.$ttl.'" alt="'.$alt.'"src="'.$img.'" /></td>';
               echo '<td style="padding: 1px; width: auto;"><img border="0" src="images/spacer.gif" width="3" height="0">'.$file_name.'</td>';
             }else{
               echo '<td style="padding: 4px;">'.$status.'</td>';
               echo '<td style="padding: 4px; width: auto;">'.$file_name.'</td>';
             }
             echo '<td style="padding: 4px; width: auto;">'.$file_date.'</td>';
             echo '<td style="padding: 4px; width: 110px;">'.$file_time.'</td>';
             echo '<td style="padding: 4px; width: auto;">'.$old_perms.'</td>';
             echo '<td style="padding: 4px; width: auto;">'.$new_perms.'</td>';
             echo '<td style="padding: 4px; width: 110px;">'.$old_size.'</td>';
             echo '<td style="padding: 4px; width: auto;">'.$new_size.'</td>';
             echo '<td style="padding: 4px; width: auto;">'.$last_run.'</td></tr>';
      }
  }

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round(($finish - $start), 4);
echo 'foreach loop ' . $total_time . ' seconds.';
echo '</table></div>';

?>