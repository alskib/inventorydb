<?
require 'includes/util.php';
session_start();

// Connect to database
$connection=mysql_connect("localhost", "franklin", "P@ssw0rd") or die("Can't connect to database");
mysql_select_db("inventory");

// On form submit
if (isset($_POST['submitLogin'])) {
	$inputUser=($_POST['username']);
	$_SESSION['username'] = $inputUser;
	$inputPass=($_POST['password']);
	
	//SQL Query
	$query=sprintf("SELECT userID FROM users WHERE username='%s' AND password=SHA1('%s')", mysql_real_escape_string($inputUser), mysql_real_escape_string($inputPass));
	$result=mysql_query($query, $connection);
	
	// Check if valid username/password combination
	if(mysql_num_rows($result)==1){
		$_SESSION['users']=1;
		header("location:inventory_show.php");
	} else {
		$_SESSION['users']=0;
		//header("location:login.php");
	}
}

//Begin HTML
echo "<!DOCTYPE HTML>";
echo "<html>";
echo "<meta charset=\"utf-8\">";
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"styles.css\" />";
echo "<title>CS Inventory Log In</title>";

echo "<body>";
echo "<body onload=\"document.loginform.username.focus()\">";

echo "<div class=\"container\">";

	echo "<div class=\"header\">";
	echo "</div>";
	
	echo "<div class=\"content\" align=\"center\">";
	echo "<img src=\"images/cslogo.jpg\">";
	echo "<h3>Inventory System Login</h3>";
		
		echo "<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\" name=\"loginform\">";
			echo "Username: <input type=\"text\" name=\"username\" /> <br />";
			echo "Password: <input type=\"password\" name=\"password\" /> <br />";
		echo "<input type=\"submit\" value=\"Login\" name=\"submitLogin\" /></form>";
		
		if (isset($_POST['submitLogin']))
			echo "<br />Invalid login.";


	echo "</div>";
	echo "<div class=\"sidebar_right\">";
	echo "</div>";
	
	echo "<div class=\"footer\">";
	echo "</div>";
	
echo "</div>";

echo "</body>";
echo "</html>";
?>