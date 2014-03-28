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

// Get total number of rows
$sql = sprintf("SELECT COUNT(*) FROM items");
$result=mysql_query($sql,$connection);
$r=mysql_fetch_row($result);
$numrows=$r[0];
// Number of rows per page
$rowsperpage=50;
// Calculate total pages
$totalpages=ceil($numrows/$rowsperpage);

// Get current page or set default page
if (isset($_GET['currentpage']) && is_numeric($_GET['currentpage'])){
	// Cast var to int
	$currentpage=(int)$_GET['currentpage'];
}
else{
	// Default page
	$currentpage=1;
}

// Prevent underflow/overflow
if ($currentpage > $totalpages){$currentpage=$totalpages;}
if ($currentpage < 1){$currentpage=1;}

// List offset based on current page
$offset=($currentpage-1)*$rowsperpage;

// Determine which filters are being used
$priceInvalid = FALSE;
if (!empty($_POST['filterManufacturer']))
	$filterManufacturer = TRUE;
else
	$filterManufacturer = FALSE;

if (!empty($_POST['filterItemCond']))
	$filterItemCond = TRUE;
else
	$filterItemCond = FALSE;

if (!empty($_POST['filterModel'])) {
	$filterModel = TRUE;
	$inputModel = mysql_real_escape_string($_POST['filterModel']);
	} else
		$filterModel = FALSE;

if (!empty($_POST['filterPriceMin'])) {
	if (filter_var($_POST['filterPriceMin'], FILTER_VALIDATE_FLOAT))
		$filterPriceMin = TRUE;
	else {
		$priceInvalid = TRUE;
		$filterPriceMin = FALSE;
	}
}

if (!empty($_POST['filterPriceMax'])) {
	if (filter_var($_POST['filterPriceMax'], FILTER_VALIDATE_FLOAT))
		$filterPriceMax = TRUE;
	else {
		$priceInvalid = TRUE;
		$filterPriceMax = FALSE;
	}
}

// Get filtered items from database
if (!$priceInvalid) {
	$filterDBBegin = sprintf("SELECT asset, manufacturer, model, serial, itemCond, acquisitionDate, price FROM items ");
	
	if ($filterManufacturer || $filterItemCond || $filterModel || $filterPriceMin || $filterPriceMax)
		$filterDBBegin = $filterDBBegin . sprintf("WHERE ");
		
	$filterdbManufacturer = sprintf("manufacturer='%s' ", $_POST['filterManufacturer']);
	$filterdbItemCond = sprintf("AND itemCond='%s' ", $_POST['filterItemCond']);
	$filterdbModel = sprintf("AND model LIKE '%%%s%%' ", $inputModel);
	$filterdbPriceMin = sprintf("AND price>'%f' ", $inputPriceMin);
	$filterdbPriceMax = sprintf("AND price<'%f' ", $inputPriceMax);
	$filterExists = FALSE;
	
	if ($filterManufacturer) {
		$filterDBBegin = $filterDBBegin . $filterdbManufacturer;
		$filterExists = TRUE;
	}
	
	if ($filterItemCond) {
		if (!$filterExists) {
			$filterdbItemCond = str_replace("AND ", "", $filterdbItemCond);
			$filterDBBegin = $filterDBBegin . $filterdbItemCond;
		} else
			$filterDBBegin = $filterDBBegin . $filterdbItemCond;
		$filterExists = TRUE;
	}
	
	if ($filterModel) {
		if (!$filterExists) {
			$filterdbModel = str_replace("AND ", "", $filterdbModel);
			$filterDBBegin = $filterDBBegin . $filterdbModel;
		} else		
			$filterDBBegin = $filterDBBegin . $filterdbModel;
		$filterExists = TRUE;
	}
	
	if ($filterPriceMin) {
		if (!$filterExists) {
			$filterdbPriceMin = str_replace("AND ", "", $filterdbPriceMin);
			$filterDBBegin .= $filterdbPriceMin;
		} else
			$filterDBBegin .= $filterdbPriceMin;
		$filterExists = TRUE;
	}
	
	if ($filterPriceMax) {
		if (!$filterExists) {
			$filterdbPriceMax = str_replace("AND ", "", $filterdbPriceMax);
			$filterDBBegin .= $filterdbPriceMax;
		} else
			$filterDBBegin .= $filterdbPriceMax;
		$filterExists = TRUE;
	}
	
	$filterDBEnd = sprintf("ORDER BY asset LIMIT $offset,$rowsperpage");
	$filterDBAll = $filterDBBegin . $filterDBEnd; 
	//echo $filterDBAll;
	$result = mysql_query($filterDBAll, $connection);
}

$manufacturerFetch = sprintf("SELECT DISTINCT manufacturer FROM items ORDER BY manufacturer");
$result2 = mysql_query($manufacturerFetch, $connection);

// Begin HTML
echo "<!DOCTYPE HTML>";
echo "<html>";
echo "<meta charset=\"utf-8\">";
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"styles.css\" />";
echo "<title>Items List</title>";

echo "<body>";

echo "<div class=\"container\">";
	echo "<div class=\"header\">";
	echo "</div>";
	menu();
	echo "<div class=\"content\">";
		if ($priceInvalid)
			echo "<p style=\"text-align:center\">Price must be a valid float.</p>";
		
		echo "<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\">";
		
		// if ($filterManufacturer)
			// echo "filterManufacturer";
		// if ($filterItemCond)
			// echo "filterItemCond";
		// if ($filterModel)
			// echo "filterModel";
		// if ($filterPriceMin)
			// echo "filterPriceMin";
		// if ($filterPriceMax)
			// echo "filterPriceMax";

		
		// Table for filtering
		echo "<table class=\"filters\">";
			echo "<tr>";
				echo "<th scope=\"col\" align=\"center\">Manufacturer</th>";
				echo "<th scope=\"col\" align=\"center\">Item Condition</th>";
				echo "<th scope=\"col\" align=\"center\">Model</th>";
				echo "<th scope=\"col\" align=\"center\">Price</th>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>";
					// Dropdown box for filtering manufacturers
					echo "<select name=\"filterManufacturer\">";
						echo "<option value=\"\">--NONE--</option>";
						while($row = mysql_fetch_array($result2))
							echo "<option value=\"" . $row['manufacturer'] . "\">" . $row['manufacturer'] . "</option>";
					echo "</select>";
				echo "</td>";
				echo "<td>";
					// Dropdown box for filtering item condition
					echo "<select name=\"filterItemCond\">";
						echo "<option value=\"\" select=\"selected\">--NONE--</option>";
						echo "<option value=\"N\">New (N)</option>";
						echo "<option value=\"G\">Good (G)</option>";
						echo "<option value=\"M\">Mediocre (M)</option>";
						echo "<option value=\"B\">Bad (B)</option>";
						echo "<option value=\"D\">Destroyed (D)</option>";
					echo "</select>";
				echo "</td>";
				echo "<td>";
					// Search box for searching in model attribute
					echo "<input type=\"text\" name=\"filterModel\" />";
				echo "</td>";
				echo "<td>";
					// Search boxes for filtering price
					echo "<input type=\"text\" class=\"priceBoxMin\" name=\"filterPriceMin\" /> to <input type=\"text\" class=\"priceBoxMax\" name=\"filterPriceMax\" />";
				echo "</td>";
			echo "</tr>";
			echo "<tr>";
				echo "<th colspan=\"4\"><input type=\"submit\" value=\"Filter\" name=\"filter\" />";
			echo "</tr>";
		echo "</table>";
		echo "</form>";
		echo "<br />";
		
			/*** Build pagination links ***/
			// Range of links to show
			$range=3;
			
			
			echo "<p style=\"text-align:center\">";
			
			// Show link to first page if not already there
			if ($currentpage > 1) {
				// Link to first page
				echo "<a href='{$_SERVER['PHP_SELF']}?currentpage=1'>First</a> ";
			}
			
			for ($i=($currentpage-$range); $i<(($currentpage+$range)+1); $i++) {
				// If valid page number
				if (($i>0) && ($i<=$totalpages)) {
					// If on current page
					if ($i==$currentpage){
						// Bold current page number but do not make a link
						echo " [<b>$i</b>] ";
					} else {
						// Make it a link
						echo " <a href='{$_SERVER['PHP_SELF']}?currentpage=$i'>$i</a> ";
					}
				}
			}
			
			// Show link to last page if not already there
			if ($currentpage!=$totalpages) {
				// Link to last page
				echo "<a href='{$_SERVER['PHP_SELF']}?currentpage=$totalpages'>Last</a>";
			}
			
			echo "<br>";
			
			// Don't show back link if on page 1
			if ($currentpage>1) {
				// Get previous page number
				$prevpage=$currentpage-1;
				// Link to previous page
				echo " <a href='{$_SERVER['PHP_SELF']}?currentpage=$prevpage'>Prev</a> ";
			}
			
			// If not on last page, show link to next page
			if ($currentpage!=$totalpages){
				// Get next page number
				$nextpage=$currentpage+1;
				// Link to next page
				echo " <a href='{$_SERVER['PHP_SELF']}?currentpage=$nextpage'>Next</a>  ";
			}
			
			echo "</p>";
	
		echo "<table class=\"items\">";
			echo "<tr>";
				echo "<th scope=\"col\" align=\"center\">Asset</th>";
				echo "<th scope=\"col\" align=\"center\">Manufacturer</th>";
				echo "<th scope=\"col\" align=\"center\">Model</th>";
				echo "<th scope=\"col\" align=\"center\">Serial</th>";
				echo "<th scope=\"col\" align=\"center\">Condition</th>";
				echo "<th scope=\"col\" align=\"center\">Acquired</th>";
				echo "<th scope=\"col\" align=\"center\">Price</th>";
			echo "</tr>";

			$id=0;
			while($row=mysql_fetch_array($result)){
				$id++;
				echo "</td><td align=\"right\">";
				echo "<a href=\"details.php?asset={$row['asset']}\">";
				echo $row['asset'];
				echo "</a>";

				echo "</td><td align=\"left\">";
				echo $row['manufacturer'];

				echo "</td><td align=\"left\">";
				echo $row['model'];
				
				echo "</td><td align=\"left\">";
				echo $row['serial'];
				
				echo "</td><td align=\"center\">";
				echo $row['itemCond'];

				echo "</td><td align=\"center\">";
				echo $row['acquisitionDate'];
				
				echo "</td><td align=\"right\">";
				echo $row['price'];

				echo "</td></tr>";
			}

		echo "</table>";

/*** Build pagination links ***/
// Range of links to show
$range=3;

echo "<p style=\"text-align:center\">";

// Show link to first page if not already there
if ($currentpage > 1) {
	// Link to first page
	echo "<a href='{$_SERVER['PHP_SELF']}?currentpage=1'>First</a> ";
}

for ($i=($currentpage-$range); $i<(($currentpage+$range)+1); $i++) {
	// If valid page number
	if (($i>0) && ($i<=$totalpages)) {
		// If on current page
		if ($i==$currentpage){
			// Bold current page number but do not make a link
			echo " [<b>$i</b>] ";
		} else {
			// Make it a link
			echo " <a href='{$_SERVER['PHP_SELF']}?currentpage=$i'>$i</a> ";
		}
	}
}

// Show link to last page if not already there
if ($currentpage!=$totalpages) {
	// Link to last page
	echo "<a href='{$_SERVER['PHP_SELF']}?currentpage=$totalpages'>Last</a>";
}

echo "<br>";

// Don't show back link if on page 1
if ($currentpage>1) {
	// Get previous page number
	$prevpage=$currentpage-1;
	// Link to previous page
	echo " <a href='{$_SERVER['PHP_SELF']}?currentpage=$prevpage'>Prev</a> ";
}

// If not on last page, show link to next page
if ($currentpage!=$totalpages){
	// Get next page number
	$nextpage=$currentpage+1;
	// Link to next page
	echo " <a href='{$_SERVER['PHP_SELF']}?currentpage=$nextpage'>Next</a>  ";
}

echo "</p>";

	echo "</div>";
	echo "<div class=\"sidebar_right\">";
	echo "</div>";
	
	echo "<div class=\"footer\">";
	echo "</div>";
	
echo "</div>";

echo "</body>";
echo "</html>";
?>