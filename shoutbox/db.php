<?php
// Enter your Host, username, password, database below.
// the password is empty because no settings for the password were made on localhost.
$con =mysqli_connect("localhost","root","","register");
// Check connection
if (mysqli_connect_errno())
{
echo "Failed to connect to MySQL: " .
mysqli_connect_error();
}
?>
