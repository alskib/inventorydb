<?php
require 'includes/util.php';
// Check authorized user
session_start();
if($_SESSION['users']!=1){
	$_SESSION['users']=-1;
	header("location:index.php");
}

// Connect to database
$connection=mysql_connect("localhost", "franklin", "P@ssw0rd") or die("Can't connect to database");
mysql_select_db("inventory");

if (isset($_POST['asset']))
{
	// Delete item
	$deletedb = sprintf("DELETE FROM items WHERE asset='%d'", $_POST['asset']);
	if (isset($_POST['deleteItem'])) {
		if (mysql_query($deletedb, $connection) == false) {
			echo "<p style=\"text-align:center\">";
			echo "Delete item failed.";
			echo mysql_error();
			echo "</p>";
		}
	}
	// Update item
	if (isset($_POST['updateItem'])) {
		$inputManufacturer = mysql_real_escape_string($_POST['manufacturer']);
		$inputModel = mysql_real_escape_string($_POST['model']);
		$inputSerial = mysql_real_escape_string($_POST['serial']);
		$inputItemCond = mysql_real_escape_string($_POST['itemCond']);
		$inputAcquisitionDate = mysql_real_escape_string($_POST['acquisitionDate']);
		// if (isset($_POST['price'])) {
			// if (is_numeric($_POST['price'])) {
				// $price = $_POST['price'];
				// echo "Is numeric.";
			// }
			// else {
				// $invalidPrice = true;
				// echo "Price must be a decimal value.";
				// header("location:details.php?asset=" . $_GET['asset']);
			// }
		// }
		if ($_POST['price'] != "") {
			if (!filter_var($_POST['price'], FILTER_VALIDATE_FLOAT))
				$priceinvalid = true;
			else
				$price = $_POST['price'];
		}
		
		$updatedb = sprintf("UPDATE items SET manufacturer='%s', model='%s', serial='%s', itemCond='%s', acquisitionDate='%s', price='%f' WHERE asset='%d'", $inputManufacturer, $inputModel, $inputSerial, $inputItemCond, $inputAcquisitionDate, $price, $_POST['asset']);
		
		// If price is blank or is a proper float, update item
		if (!$priceinvalid) {
			if (mysql_query($updatedb, $connection) == false)
				$echonotupdated = true;
			else
				$echoupdated = true;
		}
	}
	// Add item to current inventory
	if (isset($_POST['addToInventory'])) {
		$insertdb = sprintf("INSERT INTO inventory (asset) VALUES ('%d')", $_POST['asset']);
		if (mysql_query($insertdb, $connection) == false)
			if (mysql_errno() == 1062)
				$echonotinserted = true;
			else
				echo mysql_error();
		else
			$echoinserted = true;
	}
	// Remove item from current inventory
	if (isset($_POST['removeFromInventory'])) {
		$deletedb = sprintf("DELETE FROM inventory where asset='%d'", $_POST['asset']);
		if (mysql_query($deletedb, $connection) == false) {
			echo "<p style=\"text-align:center\">";
			echo "Item not removed.";
			echo mysql_error();
			echo "</p>";
		} else
			$echoremoved = true;
	}
}
// If asset is set, use it to query the database
if (isset($_GET['asset'])) {
	$fetch=sprintf("SELECT asset, manufacturer, model, serial, itemCond, acquisitionDate, price FROM items WHERE asset='%d'", $_GET['asset']);
	$result=mysql_query($fetch, $connection);
	
	$checkInventorydb = sprintf("SELECT asset FROM inventory WHERE asset='%d'", $_GET['asset']);
	$result2 = mysql_query($checkInventorydb, $connection);
	if (mysql_num_rows($result2) == 1)
		$yesInInventory = true;
	else
		$yesInInventory = false;
} else {
	header("location:index.php");
}
	
// Retrieved fields
$row=mysql_fetch_array($result);

echo "<!DOCTYPE HTML>";
echo "<html>";
echo "<head>";
echo "<meta charset=\"utf-8\">";
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"styles.css\" />";
echo "<title>Item Details</title>";
echo "</head>";
echo "<body>";

echo "<div class=\"container\">";
	echo "<div class=\"header\">";
	echo "</div>";
	menu();
	echo "<div class=\"content\">";
		// if ($invalidPrice)
			// echo "Price must be a decimal value.";
		if ($priceinvalid)
				echo "<p style=\"text-align:center\">Price must be an integer or decimal value.</p>";

		if (mysql_num_rows($result) == 0) {
			echo "<h3>Item " . $_GET['asset'] . " does not exist.</h3>";
		} else {
			echo "<form action=\"details.php?asset=". $_GET['asset']. "\" method=\"post\">";
			echo "<table width=\"auto\" style=\"margin-left:auto; margin-right:auto;\" border=\"1\" bgcolor=\"#F0F0F0\" cellpadding=\"4\">";
			
			echo "<tr><td>Asset Number:</td>";
			echo "<td><input type=\"hidden\" name=\"asset\" value=\"". $row['asset']. "\">". $row['asset']. "</input></td>";
			
			echo "</tr><tr><td>Manufacturer:</td>";
			echo "<td><input type=\"text\" name=\"manufacturer\" value=\"". $row['manufacturer']. "\" /></td>";
			
			echo "</tr><tr><td>Model:</td>";
			echo "<td><input type=\"text\" class=\"modelBox\" name=\"model\" value=\"". $row['model']. "\" /></td>";
			
			echo "</tr><tr><td>Serial Number:</td>";
			echo "<td><input type=\"text\" name=\"serial\" value=\"". $row['serial']. "\" /></td>";
			
			echo "</td></tr><tr><td>Condition:</td>";
			echo "<td>";
				echo "<select name=\"itemCond\">";
					
					echo "<option value=\"N\" ";
					if ($row['itemCond'] == "N")
						echo "selected=\"selected\"";
					echo ">New (N)</option>";
					//echo "<option value=\"N\">New (N)</option>";
					
					echo "<option value=\"G\" ";
					if ($row['itemCond'] == "G")
						echo "selected=\"selected\"";
					echo ">Good (G)</option>";
					
					echo "<option value=\"M\" ";
					if ($row['itemCond'] == "M")
						echo "selected=\"selected\"";
					echo ">Mediocre (M)</option>";
					
					echo "<option value=\"B\" ";
					if ($row['itemCond'] == "B")
						echo "selected=\"selected\"";
					echo ">Bad (B)</option>";
					
					echo "<option value=\"D\" ";
					if ($row['itemCond'] == "D")
						echo "selected=\"selected\"";
					echo ">Destroyed (D)</option>";
						
					echo "</select>";
				echo "</td>";
			//echo "<td><input type=\"text\" name=\"itemCond\" value=\"". $row['itemCond']. "\" /></td>";
			
			echo "</tr><tr><td>Acquisition Date:</td>";
			echo "<td><input type=\"text\" name=\"acquisitionDate\" value=\"". $row['acquisitionDate']. "\" /><b style=\"font-size:12px;\">YYYY-MM-DD</b></td>";
			
			echo "</td></tr><tr><td>Price:</td>";
			echo "<td><input type=\"text\" name=\"price\" value=\"". $row['price']. "\" /></td>";
			
			echo "</td></tr><tr><td>In current inventory?</td>";
			
			// Checkbox is checked if item is in current inventory
			if ($yesInInventory)
				echo "<td><input type=\"checkbox\" name=\"inInventory\" checked=\"checked\" disabled=\"disabled\" /></td>";
			else
				echo "<td><input type=\"checkbox\" name=\"inInventory\" disabled=\"disabled\" /></td>";
				
			echo "</tr></table>";
			
			// echo "</td></tr><tr><td>Destruction Status:</td>";
			// echo "<td><select name=\"destructionStatus\">";
			// echo "<option value=\"\">Select a status...</option>";
			
			// if ($row['status']=='MarkedToDestroy') {
				// echo "<option selected value=\"pendingDestruction\">Marked for Destruction (paperwork pending)</option>";
				// echo "<option value=\"destroyed\">Destroyed (paperwork complete)</option>";
			// } else if($row['status']=='Destroyed') {
				// echo "<option value=\"pendingDestruction\">Marked for Destruction (paperwork pending)</option>";
				// echo "<option selected value=\"destroyed\">Destroyed (paperwork complete)</option>";
			// } else {
				// echo "<option value=\"pendingDestruction\">Marked for Destruction (paperwork pending)</option>";
				// echo "<option value=\"destroyed\">Destroyed (paperwork complete)</option>";
			// }

			// echo "</select></td></tr></table>";
			echo "<p style=\"text-align:center\">";
			echo "<input type=\"submit\" value=\"Update Item\" name=\"updateItem\" />";
			echo "<input type=\"submit\" value=\"Delete Item\" name=\"deleteItem\" />";
			
			// Show "Add to Inventory" button only if not in inventory, or vice versa.
			if ($yesInInventory)
				echo "<input type=\"submit\" value=\"Remove from Inventory\" name=\"removeFromInventory\" />";
			else
				echo "<input type=\"submit\" value=\"Add to Inventory\" name=\"addToInventory\" />";
				
			echo "</p>";
			
			if ($echoupdated) {
				echo "<p style=\"text-align:center\">";
				echo "Item updated.</p>";
			}
			if ($echonotupdated) {
				echo "<p style=\"text-align:center\">";
				echo "Update failed.";
				echo mysql_error();
				echo "</p>";
			}
			if ($echoinserted) {
				echo "<p style=\"text-align:center\">";
				echo "Added to inventory.</p>";
			}
			if ($echonotinserted) {
				echo "<p style=\"text-align:center\">";
				echo "Item not added: already exists in inventory.";
				echo "</p>";
			}
			if ($echoremoved) {
				echo "<p style=\"text-align:center\">";
				echo "Removed from inventory.</p>";
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