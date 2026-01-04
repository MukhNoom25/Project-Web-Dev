<?php
require_once("connection.php");
//If the shout is not empty (validation)
if($_POST['shout'] != ""){
//Add to database
$result=mysqli_query($conn, "INSERT INTO shouts(shout_text) VALUES ('".$_POST['shout']."')")
or die(mysqli_error($conn));

}
?>


<html>
<head>
<meta http-equiv="refresh" content="0;
url=index.php">
<title>Redirecting...</title>
</head>


<body>
<p>Please wait.. (^-^)</p>
</body>
</html> 
