<?php

function logout() {
    $path=str_replace("logout.php","",$_SERVER['HTTP_REFERER']);
    echo '<script>var request = new XMLHttpRequest();
    request.open("get", "welcome", false, "false", "false");
    request.send();
    window.location.replace("'.$path.'");</script>';
}

logout();

$path2=str_replace("logout.php","",$_SERVER['HTTP_REFERER']);
echo "Welcome <a href=\"javascript:void(0)\" onclick=\"logout()\">logout</a>";
echo '
<script>
function logout(){
    var request = new XMLHttpRequest();
    request.open("get", "welcome", false, "false", "false");
    request.send();
    window.location.replace("'.$path.'");
  };
</script>';
?>
