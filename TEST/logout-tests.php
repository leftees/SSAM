<?php
/*function logout() {
    header('WWW-Authenticate: Basic realm="'.time().'"');
    header('HTTP/1.0 401 Unauthorized');
    echo "You need to enter a valid username and password.";
    exit;
}*/
logout2();

function logout2() {
	$path=str_replace("logout.php","",$_SERVER['HTTP_REFERER']);
	echo '<script>var request = new XMLHttpRequest();                                        
    request.open("get", "welcome", false, "false", "false");                                                                                                                               
    request.send();
	window.location.replace("'.$path.'");</script>';
}

echo "Welcome <a href=\"javascript:void(0)\" onclick=\"logout()\">logout</a>";
echo <<<CODE
<script>
function logout(){                                                                                                      
    var request = new XMLHttpRequest();                                        
    request.open("get", "welcome", false, "false", "false");                                                                                                                               
    request.send();                                                            
    window.location.replace("http://google.de");                                              
  };
</script>
CODE;
?>
