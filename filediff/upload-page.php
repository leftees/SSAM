<?php

$filesToUpload1 = $_FILES['filesToUpload1'];
$filesToUpload2 = $_FILES['filesToUpload2'];

$filesToUpload = array_merge_recursive($filesToUpload2,$filesToUpload1);

$i = 0;
foreach ($filesToUpload['name'] as $file) {
$img = "temp/".$file;
if($i == 0){
$file1 = $img;
}else{
$file2 = $img;
}
move_uploaded_file($filesToUpload['tmp_name'][$i], $img);
chmod( $img , 0777 );
$i++;
}
header("Location: diffs.php?file1=".$file1."&file2=".$file2."");
//echo '<a href="diffs.php?file1='.$file1.'&file2='.$file2.'">Compare</a>'
?>
