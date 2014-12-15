<?php
header('Content-type: text/html');

echo '<html><head>
<link href="css/simplesiteaudit.css" rel="stylesheet" type="text/css">

</head><body>
<p class="sub2"><font color="#A52A2A">SimpleSiteAudit</font><label> Multisite</label>
<input type="button" id="close" onclick="self.close()" style="width: auto; background-color: #ffffff; text-align: right;"
 name="close" alt="Close this window" value="CLOSE">
 <script type="text/javascript" language="JavaScript" src="find2.js"></script>
</body></html>';
echo phpinfo();
//find2.js not needed?
?>
