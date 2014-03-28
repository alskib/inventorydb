<?php
function logout()
{
	session_start();
	// Unset all session variables
	$_SESSION = array();
	// Delete session data
	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
				  $params["path"], $params["domain"],
				  $params["secure"], $params["httponly"]
				  );
	}
	// Destroy session
	session_destroy();
}

function menu()
{
	echo "<div class=\"sidebar_left\">";
		echo "<ul class=\"nav\">";
			echo "<li><a href=\"inventory_show.php\">List Current Inventory</a></li>";
			echo "<li><a href=\"items_show.php\">List All Items</a></li>";
			echo "<li><a href=\"item_add.php\">Add Item</a></li>";
			echo "<li><a href=\"profile.php\">Profile</a></li>";
			echo "<li><a href=\"logout.php\">Logout</a></li>";
		echo "</ul>";
		echo "<br>";
		echo "<b style=\"text-align:left\">Search by asset number</b>";
		echo "<form action=\"details.php\" method=\"get\">";
			echo "<input type=\"text\" name=\"asset\" />";
			echo "<input type=\"submit\" value=\"Search\" />";
		echo "</form>";
	echo "</div>";
}
?>