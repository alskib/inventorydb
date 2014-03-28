<?
require 'includes/util.php';
// Check authorized user
session_start();
if($_SESSION['users']!=1){
	$_SESSION['users']=-1;
	header("location:index.php");
}

echo "<!DOCTYPE HTML>";
echo "<html>";
echo "<head>";
echo "<meta charset=\"utf-8\">";
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"styles.css\" />";
echo "<title>CS Inventory Main</title>";
echo "</head>";
echo "<body>";

echo "<div class=\"container\">";
	echo "<div class=\"header\">";
	echo "</div>";
	
	menu();
	
	echo "<div class=\"content\"></div>";
		echo "<h3>";
			echo "Welcome to the CS Inventory site.";
		echo "</h3>";
		
	echo "<div class=\"sidebar_right\">";
	echo "</div>";
	
	echo "<div class=\"footer\">";
	echo "</div>";
	
echo "</div>";
echo "</body>";
echo "</html>";
?>