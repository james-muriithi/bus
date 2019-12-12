<?php
/* Local Database*/

$servername = "localhost";
$username = "bcqjswpz_james";
$password = "31*66D9o";
$dbname = "bcqjswpz_pwaniuniversity";


// Create connection
@$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (!$conn) {
	?>
	<script type="text/javascript">
		alert("It seems like you aint connected to the database")
	</script>
	<?php
    die("Connection to the database failed: " . mysqli_connect_error());
}

?> 