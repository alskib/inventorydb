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

if (isset($_POST['asset'])) {
	if (!filter_var($_POST['asset'], FILTER_VALIDATE_INT))
		$assetinvalid = true;
	else
		$assetnum = $_POST['asset'];
		
	if (isset($_POST['manufacturer']))
		$manufacturer=mysql_real_escape_string($_POST['manufacturer']);
	// else
		// $manufacturer=null;
	if (isset($_POST['model']))
		$model=mysql_real_escape_string($_POST['model']);
	// else
		// $model=null;	
	if (isset($_POST['serial']))
		$serial=mysql_real_escape_string($_POST['serial']);
	// else
		// $serial=null;	
	if (isset($_POST['itemCond']))
		$itemCond = $_POST['itemCond'];
	// else
		// $cond=null;
	if (isset($_POST['acquisitionDate']))
		$acquisitionDate=mysql_real_escape_string($_POST['acquisitionDate']);
	
	if (isset($_POST['price']) && $_POST['price'] != "") {
		if (!filter_var($_POST['price'], FILTER_VALIDATE_FLOAT)) {
			$priceinvalid = true;
			echo "Price invalid.";
		} else {
			$price = $_POST['price'];
			echo "Price good.";
		}
	}
		
	// else
		// $price=null;
	
	//echo $assetnum, $manufacturer, $model, $serial, $cond, $_POST['acquisitionDate'], $price;
	
	if ($assetinvalid || $priceinvalid)
		$formerror =  true;
		
	$insertdb1=sprintf("INSERT INTO items (asset, manufacturer, model, serial, itemCond, acquisitionDate, price) VALUES ('%d', '%s', '%s', '%s', '%s', '%s', '%f')", $assetnum, $manufacturer, $model, $serial, $itemCond, $acquisitionDate, $price);
	
	if (!$formerror) {
		if (mysql_query($insertdb1, $connection) == false) {
			echo "<p style=\"text-align:center\">";
			echo "Insert into items failed. ";
			echo mysql_error();
			echo "</p>";
		} else
			$redirect = true;
	
		if (isset($_POST['addtoinventory'])) {
			$insertdb2=sprintf("INSERT INTO inventory (asset) VALUES ('%d')", $_POST['asset']);
			
			if (mysql_query($insertdb2, $connection) == false) {
				echo "<p style=\"text-align:center\">";
				echo "Insert into inventory failed. ";
				echo mysql_error();
				echo "</p>";
			} else
				$redirect = true;
		}
	}
	else
		$redirect = false;

	if ($redirect) {
		$url="details.php?asset=$assetnum";
		header("location:$url");
	}

	//$result=mysql_query($updatedb, $connection);
	
}


echo "<!DOCTYPE HTML>";
echo "<html>";
echo "<head>";
echo "<meta charset=\"utf-8\">";
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"styles.css\" />";
echo "<title>Add Item</title>";
echo "</head>";

echo "<body>";
echo "<body onload=\"document.additem.asset.focus()\">";

echo "<div class=\"container\">";
	echo "<div class=\"header\"></div>";
	menu();
	echo "<div class=\"content\">";
		echo "<table width=\"auto\" style=\"margin-left:auto;margin-right:auto;\" border=\"1\" bgcolor=\"#F0F0F0\" cellpadding=\"4\">";

		echo "<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\" name=\"additem\">";
			if ($assetinvalid)
				echo "<p style=\"text-align:center\">Asset number must be an integer.</p>";
			else if ($priceinvalid)
				echo "<p style=\"text-align:center\">Price must be an integer or decimal value.</p>";

			echo "<tr>";
			
			echo "<td>Asset Number:</td>";
//				if (isset($_POST['asset']))
//					echo "<td><input type=\"text\" name=\"asset\" value=\""; if (isset($_REQUEST['asset'])) { echo $_REQUEST['asset']; } echo "/></td>";
//				else
					echo "<td><input type=\"text\" name=\"asset\" /></td>";
			echo "</tr><tr>";
			
				echo "<td>Manufacturer:</td>";
				echo "<td><input type=\"text\" name=\"manufacturer\" /></td>";
			echo "</tr><tr>";
				echo "<td>Model:</td>";
				echo "<td><input type=\"text\" name=\"model\" /></td>";
			echo "</tr><tr>";
				echo "<td>Serial Number:</td>";
				echo "<td><input type=\"text\" name=\"serial\" /></td>";
			echo "</tr><tr>";
				echo "<td>Condition:</td>";
				echo "<td>";
				echo "<select name=\"itemCond\">";
						echo "<option value=\"N\">New (N)</option>";
						echo "<option value=\"G\">Good (G)</option>";
						echo "<option value=\"M\">Mediocre (M)</option>";
						echo "<option value=\"B\">Bad (B)</option>";
						echo "<option value=\"D\">Destroyed (D)</option>";
					echo "</select>";
				echo "</td>";
			echo "</tr><tr>";
				echo "<td>Acquisition Date:</td>";
				echo "<td><input type=\"text\" name=\"acquisitionDate\" /></td>";
			echo "</tr><tr>";
				echo "<td>Price:</td>";
				echo "<td><input type=\"text\" name=\"price\" /></td>";
			echo "</tr><tr>";
				echo "<td>Add to current inventory?</td>";
				echo "<td><input type=\"checkbox\" name=\"addtoinventory\" checked=\"checked\" /></td>";
			echo "</tr>";
		echo "</table>";
		
		echo "<p style=\"text-align:center\">";
			echo "<input type=\"submit\" value=\"Add Item\" />";
		echo "</p>";
		
		echo "</form>";
	echo "</div>";
	
	echo "<div class=\"sidebar_right\">";
	echo "</div>";
	
	echo "<div class=\"footer\">";
	echo "</div>";
	
echo "</div>";

echo "</body>";
echo "</html>";
?>