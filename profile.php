<?
require 'includes/util.php';
session_start();
if($_SESSION['users']==-1){
	echo "<p style=\"text-align:center\">";
	echo "You must be logged in first.";
	echo "</p>";
}

// Connect to database
$connection=mysql_connect("localhost", "franklin", "P@ssw0rd") or die("Can't connect to database");
mysql_select_db("inventory");

// Get info from database
$username = $_SESSION['username'];

if (isset($_POST['updateInfo'])) {
		$inputFirstName = mysql_real_escape_string($_POST['firstname']);
		$inputLastName = mysql_real_escape_string($_POST['lastname']);
		$inputEmail = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
		$inputPhone = mysql_real_escape_string($_POST['phone']);
		$inputPassword = mysql_real_escape_string($_POST['password']);
		
		if (isset($_POST['password']) && $_POST['password'] != "") {
			$updateUser = sprintf("UPDATE users SET firstName='%s', lastName='%s', email='%s', phone='%s', password=SHA1('%s') WHERE username='%s'", $inputFirstName, $inputLastName, $inputEmail, $inputPhone, $inputPassword, $username);
		} else 
			$updateUser = sprintf("UPDATE users SET firstName='%s', lastName='%s', email='%s', phone='%s' WHERE username='%s'", $inputFirstName, $inputLastName, $inputEmail, $inputPhone, $username);
		
		if (mysql_query($updateUser, $connection) == false)
			$echonotupdated = true;
		else
			$echoupdated = true;

}

// Retrieve fields
$query=sprintf("SELECT firstName, lastName, email, phone, username FROM users WHERE username='%s'", $username);
$result=mysql_query($query, $connection);
$row=mysql_fetch_array($result);
	
echo "<!DOCTYPE HTML>";
echo "<html>";
echo "<head>";
echo "<meta charset=\"utf-8\">";
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"styles.css\" />";
echo "<title>Update Profile</title>";
echo "</head>";

echo "<body>";
//echo "<body onload=\"document.additem.asset.focus()\">";

echo "<div class=\"container\">";
	echo "<div class=\"header\"></div>";
	menu();
	echo "<div class=\"content\">";
	
		if (mysql_num_rows($result) == 0)
			echo "<h3>User does not exist.</h3>";
		else {
	
			echo "<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\" >";
			echo "<table width=\"auto\" style=\"margin-left:auto; margin-right:auto;\" border=\"1\" bgcolor=\"#F0F0F0\" cellpadding=\"4\">";
			
			echo "<tr>";
				echo "<td>Username</td>";
				echo "<td><input type=\"hidden\" name=\"username\" value=\"". $row['username'] . "\">". $row['username'] . "<//input></td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>Password</td>";
				echo "<td><input type=\"password\" name=\"password\" ></input></td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>First Name</td>";
				echo "<td><input type=\"text\" name=\"firstname\" value=\"". $row['firstName']. "\"></input></td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>Last Name</td>";
				echo "<td><input type=\"text\" name=\"lastname\" value=\"". $row['lastName']. "\"></input></td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>Email</td>";
				echo "<td><input type=\"text\" name=\"email\" value=\"". $row['email']. "\"></input></td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>Phone</td>";
				echo "<td><input type=\"text\" name=\"phone\" value=\"". $row['phone']. "\"></input></td>";
			echo "</tr>";
			echo "</table>";
			echo "<p style=\"text-align:center\"><input type=\"submit\" value=\"Update Info\" name=\"updateInfo\" /></p>";
			
			if ($echoupdated) {
				echo "<p style=\"text-align:center\">";
				echo "Profile updated.</p>";
			}
			if ($echonotupdated) {
				echo "<p style=\"text-align:center\">";
				echo "Update failed. ";
				echo mysql_error();
				echo "</p>";
			}
			
			echo "</form>";
			}
		
	
	echo "</div>";
	
	echo "<div class=\"sidebar_right\">";
	echo "</div>";
	
	echo "<div class=\"footer\">";
	echo "</div>";
	
echo "</div>";

echo "</body>";
echo "</html>";
?>