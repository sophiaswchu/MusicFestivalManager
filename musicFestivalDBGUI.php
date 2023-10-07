<html>
	<head>
		<title>CPSC 304 Group 27 Project</title>
		<style>
			* {
				font-family: "Lucida Console";
				margin: 5px;
			}
			#intro {
				margin-bottom: 20px;
			}
			#container {
				display: flex;
				flex-wrap: wrap;
				justify-content: center;
			}
			.table {
				border: 1px black solid;
				border-radius: 8px;
			}
			th {
				text-align: start;
			}
			th > b {
				margin: 0px;
			}
			.table > *,
			form,
			table {
				display: flex;
				justify-content: center;
			}
			table {
				text-indent: 16px;
			}
			.buttons {
				display: flex;
			}
		</style>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
		<script>
			function change() {
				let customTableSelect = document.getElementById("custom-table")
				let optionsArray = [...customTableSelect.options]
				let selectedTables = optionsArray.filter(option => option.selected).map(htmlElement => htmlElement.value)
				$.ajax({
					type: "GET",
					url: "customQueryHelper.php",
					data: { tables: selectedTables },
					dataType: "json",
					success: function(data) {
						console.log(data) // Debug
						let div = document.getElementById("custom-query-data")
						
						// Delete any old entries
						while (div.firstChild) {
							div.removeChild(div.firstChild);
						}
						
						let possibleRestrictions = []

						// Populate div with new entries
						Object.entries(data).forEach(entry => {
							let label = document.createElement("label")
							let select = document.createElement("select")
							select.name = "custom-query-select-" + entry[0] + "[]"
							select.multiple = true
							label.for = "custom-query-select-" + entry[0]
							label.innerHTML = "Attribute(s) from " + entry[0] + " to select:"
							entry[1].forEach(attribute => {
								let option = document.createElement("option")
								possibleRestrictions.push({table: entry[0], attribute: attribute})
								option.value = attribute
								option.innerHTML = attribute
								select.appendChild(option)
							})
							div.appendChild(label)
							div.appendChild(select)
						})

						console.log(possibleRestrictions)
						let btn = document.createElement("button")
						btn.innerHTML = "Add restriction"
						btn.type = "button"
						count = 0
						btn.addEventListener("click", () => {
							let restrictionDiv = document.createElement("div")
							let restrictionLabel = document.createElement("label")
							restrictionLabel.for = `custom-query-restriction-${count}`
							restrictionLabel.innerHTML = "Restriction"
							restrictionDiv.appendChild(restrictionLabel)
							let restrictionSelect = document.createElement("select")
							restrictionSelect.name = `custom-query-restriction-attribute-${count}`
							let restrictionSelectNull = document.createElement("option")
							restrictionSelectNull.innerHTML = "No restriction"
							restrictionSelect.appendChild(restrictionSelectNull) // NULL
							possibleRestrictions.forEach(restriction => {
								let restrictionOption = document.createElement("option")
								restrictionOption.value = `${restriction.table}.${restriction.attribute}`
								restrictionOption.innerHTML = `${restriction.attribute} (${restriction.table})`
								restrictionSelect.appendChild(restrictionOption)
							})
							restrictionDiv.appendChild(restrictionSelect)
							let restrictionOpSelect = document.createElement("select")
							restrictionOpSelect.name = `custom-query-restriction-op-${count}`
							let restrictionOpG = document.createElement("option")
							restrictionOpG.value = ">"
							restrictionOpG.innerHTML = ">"
							restrictionOpSelect.appendChild(restrictionOpG)
							let restrictionOpGE = document.createElement("option")
							restrictionOpGE.value = ">="
							restrictionOpGE.innerHTML = ">="
							restrictionOpSelect.appendChild(restrictionOpGE)
							let restrictionOpE = document.createElement("option")
							restrictionOpE.value = "="
							restrictionOpE.innerHTML = "="
							restrictionOpSelect.appendChild(restrictionOpE)
							let restrictionOpNE = document.createElement("option")
							restrictionOpNE.value = "<>"
							restrictionOpNE.innerHTML = "<>"
							restrictionOpSelect.appendChild(restrictionOpNE)
							let restrictionOpLE = document.createElement("option")
							restrictionOpLE.value = "<="
							restrictionOpLE.innerHTML = "<="
							restrictionOpSelect.appendChild(restrictionOpLE)
							let restrictionOpL = document.createElement("option")
							restrictionOpL.value = "<"
							restrictionOpL.innerHTML = "<"
							restrictionOpSelect.appendChild(restrictionOpL)
							let restrictionOpLike = document.createElement("option")
							restrictionOpLike.value = "LIKE"
							restrictionOpLike.innerHTML = "LIKE"
							restrictionOpSelect.appendChild(restrictionOpLike)
							restrictionDiv.appendChild(restrictionOpSelect)
							let restrictionInput = document.createElement("input")
							restrictionInput.name = `custom-query-restriction-value-${count}`
							restrictionDiv.appendChild(restrictionInput)
							div.appendChild(restrictionDiv)
							count++
						})
						div.appendChild(btn)
					}
				})
			}

			window.onload = () => {
				// Hide all tables
				let sel = document.getElementById("table-select")
				document.querySelectorAll(".table").forEach(table => {
					let option = document.createElement("option")
					option.value = table.id
					option.innerHTML = table.firstElementChild.innerHTML
					sel.appendChild(option)
				})

				let urlChoices = window.location.search.split('=')
				let firstQueryKey = urlChoices[0].split('?')[1]
				let displayedChoice;
				if (firstQueryKey === "displayTable" || firstQueryKey === undefined) {
					displayedChoice = urlChoices[1]
				} else if (firstQueryKey === "custom-table%5B%5D") {
					displayedChocie = "custom-table"
				} else if (firstQueryKey === "attendee-division") {
					displayedChoice = "attendee"
				} else {
					console.error("Unexpected query order, firstQueryKey: " + firstQueryKey)
					return
				}
				let prev;
				if (displayedChoice === undefined) {
					prev = "custom-query"
				} else {
					prev = displayedChoice
					let i
					for (i = 0; i < sel.children.length; i++) {
						if (sel.children[i].value === displayedChoice) {
							break
						}
					}
					sel.selectedIndex = i
				}
				let p = document.getElementById(prev)
				if (p) {
					p.style = ""
				}

				// Add event listeners
				sel.addEventListener("change", event => {
					document.getElementById(prev).style = "display: none;"
					document.getElementById(event.target.value).style = ""
					console.log(document.getElementById(event.target.value))
					prev = event.target.value
				})
			}
		</script>
	</head>
	<body>
		<?php
		$success = True;
		$db_conn = NULL;
		$tables = array();

		/**
		 * This function was taken from the Oracle/PHP tutorial files.
		 */
		function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
			//echo "<br>running ".$cmdstr."<br>";
			global $db_conn, $success;

			$statement = OCIParse($db_conn, $cmdstr);
			//There are a set of comments at the end of the file that describe some of the OCI specific functions and how they work

			if (!$statement) {
				echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
				$e = OCI_Error($db_conn); // For OCIParse errors pass the connection handle
				echo htmlentities($e['message']);
				$success = False;
			}

			$r = OCIExecute($statement, OCI_DEFAULT);
			if (!$r) {
				echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
				$e = oci_error($statement); // For OCIExecute errors pass the statementhandle
				echo htmlentities($e['message']);
				$success = False;
			}

			return $statement;
		}
		
		/**
		 * This function was taken from the Oracle/PHP tutorial files.
		 */
		function connectToDB() {
			global $db_conn;

			// Your username is ora_(CWL_ID) and the password is a(student number). For example,
			// ora_platypus is the username and a12345678 is the password.
			$db_conn = OCILogon("ora_platypus", "a12345678", "dbhost.students.cs.ubc.ca:1234/stu");

			if ($db_conn) {
				return true;
			} else {
				$e = OCI_Error(); // For OCILogon errors pass no handle
				echo htmlentities($e['message']);
				return false;
			}
		}

		/**
		 * This function was taken from the Oracle/PHP tutorial files.
		 */
		function disconnectFromDB() {
			global $db_conn;

			OCILogoff($db_conn);
		}

		/**
		 * This function was adapted from the Oracle/PHP tutorial files.
		 */
		function generateTableStringFromResult($result) {
			$returnString = "<table border='0'>"; // make this border='1' for 1px border on the table
			$ncols = oci_num_fields($result);
			$returnString .= "<tr>\n";
			for ($i = 1; $i <= $ncols; ++$i) {
				$colname = oci_field_name($result, $i);
				$returnString .= "  <th><b>".htmlentities($colname, ENT_QUOTES)."</b></th>\n";
			}
			while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
				$returnString .= "<tr>";
				foreach($row as $value) {
					$returnString .= "<td>" . $value . "</td>";
				}
				$returnString .= "</tr>";
			}
			$returnString .= "</table>";
			return $returnString;
		}

		/**
		 * This function was adapted from the Oracle/PHP tutorial files.
		 * REQUIRES: $result has one column
		 */
		function generateOptionsFromResult($result) {
			$returnString = "";
			while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
				foreach($row as $value) {
					$returnString .= "<option value='" . $value . "'/>" . $value . "</option>";
				}
			}
			return $returnString;
		}

		function executeOracleSQLFile($filename) {
			global $db_conn;

			$contents = file_get_contents($filename);
			$arr = explode(";", $contents);
			foreach($arr as $cmd) {
				if (strlen($cmd) > 1) {
					executePlainSQL($cmd);
					OCICommit($db_conn);
				}
			}
		}

		function handleGETRequest() {
			global $tables;
			if (connectToDB()) {
				if (isset($_GET['displayTable'])) {
					$result = executePlainSQL("select * from " . $_GET['displayTable']);
					$tableString = generateTableStringFromResult($result);
					$tables[$_GET['displayTable']] = $tableString;
				} else if (isset($_GET['custom-table'])) {
					$customTables = "";
					$customAttributes = "";
					foreach($_GET['custom-table'] as $table) {
						$customTables .=  "," . $table . " " . $table;
						foreach($_GET['custom-query-select-' . $table] as $attribute)  {
							$customAttributes .= "," . $table . "." . $attribute;
						}
					}
					$customRestrictions = "";
					$i = 0;
					$rstr = 'custom-query-restriction-'; // For readability
					while (isset($_GET['custom-query-restriction-attribute-' . $i])) {
						$customRestrictions .= " AND " . $_GET[$rstr . "attribute-" . $i] . " " . $_GET[$rstr . "op-" . $i] . " " . $_GET[$rstr . "value-" . $i];
						$i++;
					}

					$customTables = substr($customTables, 1);
					$customAttributes = substr($customAttributes, 1);
					$customRestrictions = strlen($customRestrictions) > 0 ? " WHERE" . substr($customRestrictions, 4) : "";
					$queryString = "SELECT " . $customAttributes . " FROM " . $customTables . $customRestrictions;
					$result = executePlainSQL($queryString);
					$tableString = generateTableStringFromResult($result);
					$tables['custom-query'] = $tableString;
				} else if (isset($_GET['attendee-division'])) {
					$result = executePlainSQL("SELECT attendee_name FROM Attendee WHERE NOT EXISTS ((SELECT lot_number FROM Vendor) MINUS (SELECT vendor_lot FROM CustomerReceipt WHERE attendee_id = id))");
					$tableString = generateTableStringFromResult($result);
					$tables['attendee'] = $tableString;
				}
			}

			disconnectFromDB();
		}

		function handlePOSTRequest() {
			global $db_conn;
			
			// Handle Queries
			if (isset($_POST['delete_venue_name']) && connectToDB()) {
				executePlainSQL("DELETE FROM Venue WHERE venue_name = '{$_POST['delete_venue_name']}'");
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['update_value']) && connectToDB()) {
				$data = explode(",", $_POST['update_health_certification-data']); // [0] is lot#, [1] is year
				if ($_POST['update_what'] == "Cuisine") {
					executePlainSQL("UPDATE FoodVendor SET {$_POST['update_what']} = '{$_POST['update_value']}' WHERE lot_number = {$data[0]} AND festival_year = {$data[1]}");
				} else {
					executePlainSQL("UPDATE FoodVendor SET {$_POST['update_what']} = {$_POST['update_value']} WHERE lot_number = {$data[0]} AND festival_year = {$data[1]}");
				}
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['search_festival_year']) && connectToDB()) {
				$result = executePlainSQL("SELECT musician_name FROM Musician WHERE festival_year = {$_POST['search_festival_year']} AND stage_venue = '{$_POST['stage_venue']}'");
				$tableString = generateTableStringFromResult($result);
				echo $tableString;
				
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['From_Which_Table']) && connectToDB()) {
				$result = executePlainSQL("SELECT {$_POST['which_columns']} FROM {$_POST['From_Which_Table']} WHERE {$_POST['attr1']} = {$_POST['val1']} AND {$_POST['attr2']} = {$_POST['val2']} ");
				$tableString = generateTableStringFromResult($result);
				echo $tableString;
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['older_than']) && connectToDB()) {
				$result = executePlainSQL("SELECT attendee_name, ticket_to FROM Attendee INNER JOIN Ticket ON Attendee.id = Ticket.holder WHERE age > {$_POST['older_than']}");
				$tableString = generateTableStringFromResult($result);
				echo $tableString;
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['calculate_averages']) && connectToDB()) {
				$result = executePlainSQL("SELECT position, ABS(ROUND(AVG(quantity), 2)) AS Average_Salary FROM (Employee INNER JOIN EmployeePayment ON Employee.employee_id = EmployeePayment.employee_id) INNER JOIN CashFlow ON EmployeePayment.cash_flow_id = CashFlow.id GROUP BY position");
				$tableString = generateTableStringFromResult($result);
				echo $tableString;
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['venues_earning_over']) && connectToDB()) {
				$result = executePlainSQL("SELECT ticket_to AS venue_name, SUM(quantity) AS ticket_sales FROM Ticket INNER JOIN CashFlow ON Ticket.cash_flow_id = CashFlow.id GROUP BY ticket_to HAVING SUM(quantity) > {$_POST['venues_earning_over']}");
				$tableString = generateTableStringFromResult($result);
				echo $tableString;
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['avg_cuisine_earnings']) && connectToDB()) {
				$result = executePlainSQL("SELECT cuisine, AVG(sumval) AS avg_sumval FROM (SELECT FoodVendor.festival_year, cuisine, SUM(CashFlow.quantity) AS sumval FROM FoodVendor INNER JOIN VendorRevenue ON FoodVendor.lot_number = VendorRevenue.vendor_lot INNER JOIN CashFlow ON VendorRevenue.cash_flow_id = CashFlow.id GROUP BY FoodVendor.festival_year, cuisine) GROUP BY cuisine");
				$tableString = generateTableStringFromResult($result);
				echo $tableString;
				OCICommit($db_conn);
				disconnectFromDB();
			}


			// Handle reset
			if (isset($_POST['reset']) && connectToDB()) {
				executeOracleSQLFile(__DIR__ . "/dropTables.sql");
				executeOracleSQLFile(__DIR__ . "/createAndInsert.sql");
				// executeOracleSQLFile(__DIR__ . "/createTables.sql");
				// executeOracleSQLFile(__DIR__ . "/insertStatements.sql");
				OCICommit($db_conn);
				disconnectFromDB();
			}

			// Handle drops
			if (isset($_POST['dropTable']) && connectToDB()) {
				executePlainSQL("drop table {$_POST['dropTable']} cascade constraints");
				
				// Find and execute the create statement in createTables.sql that corresponds to the table that was just dropped
				$allCreateCommands = explode(";", file_get_contents(__DIR__ . "/createTables.sql"));
				foreach($allCreateCommands as $cmd) {
					if (strlen($cmd) > 1) {
						$parts = explode("(", $cmd);
						$words = explode(" ", $parts[0]);
						$tableName = strtolower($words[2]);
						if ($tableName == $_POST['dropTable']) {
							executePlainSQL($cmd);
						}
					}
				}

				disconnectFromDB();
			}

			// Handle inserts
			if (isset($_POST['musicianpopularity']) && connectToDB()) {
				executePlainSQL("INSERT INTO MusicianPopularity (popularity, expected_turnout) VALUES ({$_POST['popularity']}, {$_POST['expected_turnout']})");
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['stagesize']) && connectToDB()) {
				executePlainSQL("INSERT INTO StageSize (stage_size, capacity) VALUES ({$_POST['stage_size']}, {$_POST['capacity']})");
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['venue']) && connectToDB()) {
				executePlainSQL("INSERT INTO Venue (venue_name, city, capacity, accessibility) VALUES ('{$_POST['venue_name']}', '{$_POST['city']}', {$_POST['capacity']}, '{$_POST['accessibility']}')");
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['stage']) && connectToDB()) {
				executePlainSQL("INSERT INTO Stage (venue_name, stage_number, stage_size) VALUES ('{$_POST['venue_name']}', {$_POST['stage_number']}, {$_POST['stage_size']})");
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['musician']) && connectToDB()) {
				executePlainSQL("INSERT INTO Musician (musician_id, musician_name, festival_year, stage_venue, stage_number, popularity) VALUES ({$_POST['musician_id']}, '{$_POST['musician_name']}', {$_POST['festival_year']}, '{$_POST['stage_venue']}', {$_POST['stage_number']}, {$_POST['popularity']})");
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['marketingplatform']) && connectToDB()) {
				executePlainSQL("INSERT INTO MarketingPlatform (platform, content) VALUES ('{$_POST['platform']}', '{$_POST['content']}')");
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['marketing']) && connectToDB()) {
				executePlainSQL("INSERT INTO Marketing (platform, festival_year, releaseDate) VALUES ('{$_POST['platform']}', {$_POST['festival_year']}, '{$_POST['releaseDate']}')");
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['advertises']) && connectToDB()) {
				executePlainSQL("INSERT INTO Advertises (platform, festival_year, venue_name) VALUES ('{$_POST['platform']}', {$_POST['festival_year']}, '{$_POST['venue_name']}')");
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['cashflow']) && connectToDB()) {
				executePlainSQL("INSERT INTO CashFlow (id, quantity) VALUES ({$_POST['id']}, {$_POST['quantity']})");
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['sponsor']) && connectToDB()) {
				executePlainSQL("INSERT INTO Sponsor (sponsor_name, festival_year, contributes_to) VALUES ('{$_POST['sponsor_name']}', {$_POST['festival_year']}, {$_POST['contributes_to']})");
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['features']) && connectToDB()) {
				executePlainSQL("INSERT INTO Features (sponsor_name, marketing_platform, festival_year) VALUES ('{$_POST['sponsor_name']}', '{$_POST['marketing_platform']}', {$_POST['festival_year']})");
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['venuepayment']) && connectToDB()) {
				executePlainSQL("INSERT INTO VenuePayment (cash_flow_id, venue_name) VALUES ({$_POST['cash_flow_id']}, '{$_POST['venue_name']}')");
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['musicianpayment']) && connectToDB()) {
				executePlainSQL("INSERT INTO MusicianPayment (cash_flow_id, id, festival_year) VALUES ({$_POST['cash_flow_id']}, {$_POST['id']}, {$_POST['festival_year']})");
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['marketingpayment']) && connectToDB()) {
				executePlainSQL("INSERT INTO MarketingPayment (platform, festival_year, cash_flow_id) VALUES ('{$_POST['platform']}', {$_POST['festival_year']}, {$_POST['cash_flow_id']})");
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['employeeposition']) && connectToDB()) {
				executePlainSQL("INSERT INTO EmployeePosition (position, hourly_wage, hours_worked) VALUES ('{$_POST['position']}', {$_POST['hourly_wage']}, {$_POST['hours_worked']})");
				OCICommit($db_conn);
				disconnectFromDB();
			}
			// Employee inserts handled by Staff and SecurityStaff
			if (isset($_POST['employeepayment']) && connectToDB()) {
				executePlainSQL("INSERT INTO EmployeePayment (employee_id, cash_flow_id) VALUES ({$_POST['employee_id']}, {$_POST['cash_flow_id']})");
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['staff']) && connectToDB()) {
				executePlainSQL("INSERT INTO Employee (employee_id, position, employee_name) VALUES ({$_POST['id']}, '{$_POST['position']}', '{$_POST['employee_name']}')");
				executePlainSQL("INSERT INTO Staff (id, station) VALUES ({$_POST['id']}, '{$_POST['station']}')");
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['securitystaff']) && connectToDB()) {
				executePlainSQL("INSERT INTO Employee (employee_id, position, employee_name) VALUES ({$_POST['id']}, '{$_POST['position']}', '{$_POST['employee_name']}')");
				executePlainSQL("INSERT INTO SecurityStaff (id, license_number) VALUES ({$_POST['id']}, {$_POST['license_number']})");
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['attendee']) && connectToDB()) {
				executePlainSQL("INSERT INTO Attendee (id, age, attendee_name) VALUES ({$_POST['id']}, {$_POST['age']}, '{$_POST['attendee_name']}')");
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['ticket']) && connectToDB()) {
				executePlainSQL("INSERT INTO Ticket (ticket_number, holder, ticket_to, cash_flow_id) VALUES ({$_POST['ticket_number']}, {$_POST['holder']}, '{$_POST['ticket_to']}', {$_POST['cash_flow_id']})");
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['foodvendor']) && connectToDB()) {
				executePlainSQL("INSERT INTO Vendor (lot_number, festival_year) VALUES ({$_POST['lot_number']}, {$_POST['festival_year']})");
				executePlainSQL("INSERT INTO FoodVendor (lot_number, festival_year, health_certification, cuisine) VALUES ({$_POST['lot_number']}, {$_POST['festival_year']}, {$_POST['health_certification']}, '{$_POST['cuisine']}')");
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['drinkvendor']) && connectToDB()) {
				executePlainSQL("INSERT INTO Vendor (lot_number, festival_year) VALUES ({$_POST['lot_number']}, {$_POST['festival_year']})");
				executePlainSQL("INSERT INTO DrinkVendor (lot_number, festival_year, license_id, drinkType) VALUES ({$_POST['lot_number']}, {$_POST['festival_year']}, {$_POST['license_id']}, '{$_POST['drinkType']}')");
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['merchandisevendor']) && connectToDB()) {
				executePlainSQL("INSERT INTO Vendor (lot_number, festival_year) VALUES ({$_POST['lot_number']}, {$_POST['festival_year']})");
				executePlainSQL("INSERT INTO MerchandiseVendor (lot_number, festival_year, type_sold) VALUES ({$_POST['lot_number']}, {$_POST['festival_year']}, '{$_POST['type_sold']}')");
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['customerreceipt']) && connectToDB()) {
				executePlainSQL("INSERT INTO CustomerReceipt (attendee_id, vendor_lot, festival_year) VALUES ({$_POST['attendee_id']}, {$_POST['vendor_lot']}, {$_POST['festival_year']})");
				OCICommit($db_conn);
				disconnectFromDB();
			}
			if (isset($_POST['vendorrevenue']) && connectToDB()) {
				executePlainSQL("INSERT INTO VendorRevenue (vendor_lot, festival_year, cash_flow_id) VALUES ({$_POST['vendor_lot']}, {$_POST['festival_year']}, {$_POST['cash_flow_id']})");
				OCICommit($db_conn);
				disconnectFromDB();
			}
		}

		if (isset($_GET)) {
			handleGETRequest();
		}
		if (isset($_POST)) {
			handlePOSTRequest();
		}
		?>
		<div>
			<div id="intro">
				<h1>Music Festival Database GUI Application</h1>
				<h2>By CPSC 304 Group 27, 2023 Summer</h2>
			</div>
			<div id="table-choice">
				<label for="visible-table">Visible table</table>
				<select name="visible-table" id="table-select"></select>
			</div>
			<form method="POST" action="musicFestivalDBGUI.php">
				<input type="hidden" name="reset" />
				<input type="submit" value="Reset all tables" />
			</form>
			</div>

			
			<div id="container">
				<div class="table" style="display: none;" id="custom-query">
					<h3>Custom Query</h3>
					<form method="GET" action="musicFestivalDBGUI.php">
						<label for="custom-table">Select table(s) to query</label>
						<select name="custom-table[]" id="custom-table" onchange="change()" multiple required>
							<!-- <option value=""></option> -->
							<option value="advertises">advertises</option>
							<option value="attendee">attendee</option>
							<option value="cashflow">cashflow</option>
							<option value="customerreceipt">customerreceipt</option>
							<option value="drinkvendor">drinkvendor</option>
							<option value="employee">employee</option>
							<option value="employeepayment">employeepayment</option>
							<option value="employeeposition">employeeposition</option>
							<option value="features">features</option>
							<option value="foodvendor">foodvendor</option>
							<option value="marketing">marketing</option>
							<option value="marketingpayment">marketingpayment</option>
							<option value="marketingplatform">marketingplatform</option>
							<option value="merchandisevendor">merchandisevendor</option>
							<option value="musician">musician</option>
							<option value="musicianpopularity">musicianpopularity</option>
							<option value="musicianpayment">musicianpayment</option>
							<option value="securitystaff">securitystaff</option>
							<option value="sponsor">sponsor</option>
							<option value="staff">staff</option>
							<option value="stage">stage</option>
							<option value="stagesize">stagesize</option>
							<option value="ticket">ticket</option>
							<option value="vendor">vendor</option>
							<option value="vendorrevenue">vendorrevenue</option>
							<option value="venue">venue</option>
							<option value="venuepayment">venuepayment</option>
						</select>
						<div id="custom-query-data"></div>
						<input type="submit" value="Search!"/>
						<?php
						if (isset($tables['custom-query'])) {
							echo $tables['custom-query'];
						}
						?>
					</form>
				</div>
				<div class="table" style="display: none;" id="advertises">
					<h3>Advertises</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="advertises" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="advertises" />
							<input type="submit" value="Clear" />
						</form>
					</div>
					<form method="POST" action="musicFestivalDBGUI.php">
						<input type="hidden" name="advertises" />
						<label for="platform">Platform</label>
						<select name="platform">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT DISTINCT platform FROM Marketing"));
							}
							disconnectFromDB();
							?>
						</select>
						<label for="festival_year">Year</label>
						<select name="festival_year">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT DISTINCT festival_year FROM Marketing"));
							}
							disconnectFromDB();
							?>
						</select>
						<label for="venue_name">Venue name</label>
						<select name="venue_name">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT DISTINCT venue_name FROM Venue"));
							}
							disconnectFromDB();
							?>
						</select>
						<input type="submit" value="Insert" />
					</form>
					<?php
					if (isset($tables['advertises'])) {
						echo $tables['advertises'];
					}
					?>
				</div>
				
				<div class="table" style="display: none;" id="attendee">
					<h3>Attendee</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="attendee" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="attendee" />
							<input type="submit" value="Clear" />
						</form>
					</div>
					<form method="POST" action="musicFestivalDBGUI.php">
						<input type="hidden" name="attendee" />
						<label for="id">Attendee ID</label>
						<input type="number" name="id" />
						<label for="age">Age</label>
						<input type="number" name="age" />
						<label for="attendee_name">Name</label>
						<input type="text" name="attendee_name" />
						<input type="submit" value="Insert" />
					</form>
					<form method="GET" action="musicFestivalDBGUI.php?displayTable=attendee">
						<label for="attendee-division">Get names of attendees who have purchased something from every vendor lot at one point</label>
						<input type="submit" name="attendee-division" />
					</form>

					<form method="POST" action="musicFestivalDBGUI.php">
						<label for="older_than">Find ticket holders older than:</label>
						<input type="number" name="older_than" />
						<input type="submit" value="Search!" />
					</form>

					<?php
					if (isset($tables['attendee'])) {
						echo $tables['attendee'];
					}
					?>
				</div>
				
				<div class="table" style="display: none;" id="cashflow">
					<h3>CashFlow</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="cashflow" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="cashflow" />
							<input type="submit" value="Clear" />
						</form>
					</div>
					<form method="POST" action="musicFestivalDBGUI.php">
						<input type="hidden" name="cashflow" />
						<label for="id">Cash flow ID</label>
						<input type="number" name="id" />
						<label for="quantity">Quantity</label>
						<input type="number" name="quantity" />
						<input type="submit" value="Insert" />
					</form>
					<?php
					if (isset($tables['cashflow'])) {
						echo $tables['cashflow'];
					}
					?>
				</div>
				
				<div class="table" style="display: none;" id="customerreceipt">
					<h3>CustomerReceipt</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="customerreceipt" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="customerreceipt" />
							<input type="submit" value="Clear" />
						</form>
					</div>
					<form method="POST" action="musicFestivalDBGUI.php">
						<input type="hidden" name="customerreceipt" />
						<label for="attendee_id">Attendee ID</label>
						<select name="attendee_id">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT id FROM Attendee"));
							}
							disconnectFromDB();
							?>
						</select>
						<label for="vendor_lot">Vendor lot</label>
						<select name="vendor_lot">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT DISTINCT lot_number FROM Vendor"));
							}
							disconnectFromDB();
							?>
						</select>
						<label for="festival_year">Year</label>
						<select name="festival_year">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT DISTINCT festival_year FROM Vendor"));
							}
							disconnectFromDB();
							?>
						</select>
						<input type="submit" value="Insert" />
					</form>
					<?php
					if (isset($tables['customerreceipt'])) {
						echo $tables['customerreceipt'];
					}
					?>
				</div>
				
				<div class="table" style="display: none;" id="drinkvendor">
					<h3>DrinkVendor</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="drinkvendor" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="drinkvendor" />
							<input type="submit" value="Clear" />
						</form>
					</div>
					<form method="POST" action="musicFestivalDBGUI.php">
						<input type="hidden" name="drinkvendor" />
						<label for="lot_number">Lot number</label>
						<input type="number" name="lot_number" />
						<label for="festival_year">Year</label>
						<input type="number" name="festival_year" />
						<label for="license_id">License ID</label>
						<input type="number" name="license_id" />
						<label for="drinkType">Drink type</label>
						<input type="text" name="drinkType" />
						<input type="submit" value="Insert" />
					</form>
					<?php
					if (isset($tables['drinkvendor'])) {
						echo $tables['drinkvendor'];
					}
					?>
				</div>
				
				<div class="table" style="display: none;" id="employee">
					<h3>Employee</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="employee" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="employee" />
							<input type="submit" value="Clear" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="calculate_averages" value="calculate_averages" />
							<input type="submit" value="Calculate Averages" />
						</form>
					</div>
					<p>Insert not supported, please insert to a subclass.</p>
					<?php
					if (isset($tables['employee'])) {
						echo $tables['employee'];
					}
					?>
				</div>
				
				<div class="table" style="display: none;" id="employeepayment">
					<h3>EmployeePayment</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="employeepayment" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="employeepayment" />
							<input type="submit" value="Clear" />
						</form>
					</div>
					<form method="POST" action="musicFestivalDBGUI.php">
						<input type="hidden" name="employeepayment" />
						<label for="employee_id">Employee ID</label>
						<select name="employee_id">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT employee_id FROM Employee"));
							}
							disconnectFromDB();
							?>
						</select>
						<label for="cash_flow_id">Cash flow iD</label>
						<select name="cash_flow_id">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT DISTINCT id FROM CashFlow"));
							}
							disconnectFromDB();
							?>
						</select>
						<input type="submit" value="Insert" />
					</form>
					<?php
					if (isset($tables['employeepayment'])) {
						echo $tables['employeepayment'];
					}
					?>
				</div>
				
				<div class="table" style="display: none;" id="employeeposition">
					<h3>EmployeePosition</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="employeeposition" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="employeeposition" />
							<input type="submit" value="Clear" />
						</form>
					</div>
					<form method="POST" action="musicFestivalDBGUI.php">
						<input type="hidden" name="employeeposition" />
						<label for="position">Position</label>
						<input type="text" name="position" />
						<label for="hourly_wage">Hourly wage</label>
						<input type="number" name="hourly_wage" />
						<label for="hours_worked">Hours worked</label>
						<input type="number" name="hours_worked" />
						<input type="submit" value="Insert" />
					</form>
					<?php
					if (isset($tables['employeeposition'])) {
						echo $tables['employeeposition'];
					}
					?>
				</div>
				
				<div class="table" style="display: none;" id="features">
					<h3>Features</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="features" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="features" />
							<input type="submit" value="Clear" />
						</form>
					</div>
					<form method="POST" action="musicFestivalDBGUI.php">
						<input type="hidden" name="features" />
						<label for="sponsor_name">Sponsor name</label>
						<select name="sponsor_name">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT DISTINCT sponsor_name FROM Sponsor"));
							}
							disconnectFromDB();
							?>
						</select>
						<label for="marketing_platform">Marketing platform</label>
						<select name="marketing_platform">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT DISTINCT platform FROM Marketing"));
							}
							disconnectFromDB();
							?>
						</select>
						<label for="festival_year">Year</label>
						<select name="festival_year">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT DISTINCT M.festival_year FROM Marketing M, Sponsor S WHERE M.festival_year = S.festival_year"));
							}
							disconnectFromDB();
							?>
						</select>
						<input type="submit" value="Insert" />
					</form>
					<?php
					if (isset($tables['features'])) {
						echo $tables['features'];
					}
					?>
				</div>
				
				<div class="table" style="display: none;" id="foodvendor">
					<h3>FoodVendor</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="foodvendor" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="foodvendor" />
							<input type="submit" value="Clear" />
						</form>
					</div>
					<form method="POST" action="musicFestivalDBGUI.php">
						<input type="hidden" name="foodvendor" />
						<label for="lot_number">Lot number</label>
						<input type="number" name="lot_number" />
						<label for="festival_year">Year</label>
						<input type="number" name="festival_year" />
						<label for="health_certification">Health certification number</label>
						<input type="number" name="health_certification" />
						<label for="cuisine">Cuisine</label>
						<input type="text" name="cuisine" />
						<input type="submit" value="Insert" />
					</form>
					<form method="POST" action="musicFestivalDBGUI.php">
						<label for="update_what">Update</label>
						<select name="update_what">
							<option value="Cuisine">Cuisine</option>
							<option value="Health_Certification">Health_Certification</option>
						</select>
						<!-- <input type="text" name="update_what" /> -->
						<label for="update_value">to</label>
						<input type="text" name="update_value" />
						<label for="update_health_certification-data">For</label>
						<select name="update_health_certification-data" id="update_health_certification-data">
							<?php
							if (connectToDB()) {
								$result = executePlainSQL("SELECT lot_number, festival_year FROM FoodVendor");
								while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
									$dataStr = $row['LOT_NUMBER'] . "," . $row['FESTIVAL_YEAR'];
									$displayStr = "Lot " . $row['LOT_NUMBER'] . " in " . $row['FESTIVAL_YEAR'];
									echo "<option value='" . $dataStr . "'>" . $displayStr . "</option>";
								}
								disconnectFromDB();
							}
							?>
						</select>
						<input type="submit" value="update" />
					</form>
					<form method="POST" action="musicFestivalDBGUI.php">
						<label for="avg_cuisine_earnings">Calculate Average earnings per cuisine type</label>
						<input type="hidden" name="avg_cuisine_earnings" value="avg_cuisine_earnings" />
						<input type="submit" value="Search!" />
					</form>
					<?php
					if (isset($tables['foodvendor'])) {
						echo $tables['foodvendor'];
					}
					?>
				</div>
				
				<div class="table" style="display: none;" id="marketing">
					<h3>Marketing</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="marketing" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="marketing" />
							<input type="submit" value="Clear" />
						</form>
					</div>
					<form method="POST" action="musicFestivalDBGUI.php">
						<input type="hidden" name="marketing" />
						<label for="platform">Platform</label>
						<select name="platform">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT platform FROM MarketingPlatform"));
							}
							disconnectFromDB();
							?>
						</select>
						<label for="festival_year">Year</label>
						<input type="number" name="festival_year" />
						<label for="releaseDate">Release date</label>
						<input type="text" name="releaseDate" />
						<input type="submit" value="Insert" />
					</form>
					<?php
					if (isset($tables['marketing'])) {
						echo $tables['marketing'];
					}
					?>
				</div>
				
				<div class="table" style="display: none;" id="marketingpayment">
					<h3>MarketingPayment</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="marketingpayment" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="marketingpayment" />
							<input type="submit" value="Clear" />
						</form>
					</div>
					<form method="POST" action="musicFestivalDBGUI.php">
						<input type="hidden" name="marketingpayment" />
						<label for="platform">Platform</label>
						<select name="platform">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT DISTINCT platform FROM Marketing"));
							}
							disconnectFromDB();
							?>
						</select>
						<label for="festival_year">Year</label>
						<select name="festival_year">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT DISTINCT festival_year FROM Marketing"));
							}
							disconnectFromDB();
							?>
						</select>
						<label for="cash_flow_id">Cash flow ID</label>
						<select name="cash_flow_id">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT id FROM CashFlow"));
							}
							disconnectFromDB();
							?>
						</select>
						<input type="submit" value="Insert" />
					</form>
					<?php
					if (isset($tables['marketingpayment'])) {
						echo $tables['marketingpayment'];
					}
					?>
				</div>
				
				<div class="table" style="display: none;" id="marketingplatform">
					<h3>MarketingPlatform</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="marketingplatform" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="marketingplatform" />
							<input type="submit" value="Clear" />
						</form>
					</div>
					<form method="POST" action="musicFestivalDBGUI.php">
						<input type="hidden" name="marketingplatform" />
						<label for="platform">Platform</label>
						<input type="text" name="platform" />
						<label for="content">Content</label>
						<input type="text" name="content" />
						<input type="submit" value="Insert" />
					</form>
					<?php
					if (isset($tables['marketingplatform'])) {
						echo $tables['marketingplatform'];
					}
					?>
				</div>
				
				<div class="table" style="display: none;" id="merchandisevendor">
					<h3>MerchandiseVendor</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="merchandisevendor" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="merchandisevendor" />
							<input type="submit" value="Clear" />
						</form>
					</div>
					<form method="POST" action="musicFestivalDBGUI.php">
						<input type="hidden" name="merchandisevendor" />
						<label for="lot_number">Lot number</label>
						<input type="number" name="lot_number" />
						<label for="festival_year">Year</label>
						<input type="number" name="festival_year" />
						<label for="type_sold">Type sold</label>
						<input type="text" name="type_sold" />
						<input type="submit" value="Insert" />
					</form>
					<?php
					if (isset($tables['merchandisevendor'])) {
						echo $tables['merchandisevendor'];
					}
					?>
				</div>
				
				<div class="table" style="display: none;" id="musician">
					<h3>Musician</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="musician" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="musician" />
							<input type="submit" value="Clear" />
						</form>
					</div>
					<form method="POST" action="musicFestivalDBGUI.php">
						<input type="hidden" name="musician" />
						<label for="musician_id">Musician ID</label>
						<input type="number" name="musician_id" />
						<label for="musician_name">Name</label>
						<input type="text" name="musician_name" />
						<label for="festival_year">Year</label>
						<input type="number" name="festival_year" />
						<label for="stage_venue">Venue name</label>
						<select name="stage_venue">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT DISTINCT venue_name FROM Stage"));
							}
							disconnectFromDB();
							?>
						</select>
						<label for="stage_number">Stage number</label>
						<input type="number" name="stage_number" />
						<label for="popularity">Popularity</label>
						<select name="popularity">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT popularity FROM MusicianPopularity"));
							}
							disconnectFromDB();
							?>
						</select>
						<input type="submit" value="Insert" />
					</form>
					<form method="POST" action="musicFestivalDBGUI.php">
						<label for="musician_id">Find Musicians</label>
						<label for="search_festival_year">Performing in Year</label>
						<input type="number" name="search_festival_year" />
						<label for="stage_venue">At Venue</label>
						<select name="stage_venue">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT DISTINCT venue_name FROM Stage"));
							}
							disconnectFromDB();
							?>
						</select>
						<input type="submit" value="Search!"/>
					</form>
					<?php
					if (isset($tables['musician'])) {
						echo $tables['musician'];
					}
					?>
				</div>
				
				<div class="table" style="display: none;" id="musicianpopularity">
					<h3>MusicianPopularity</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="musicianpopularity" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="musicianpopularity" />
							<input type="submit" value="Clear" />
						</form>
					</div>
					<form method="POST" action="musicFestivalDBGUI.php">
						<input type="hidden" name="musicianpopularity" />
						<label for="popularity">Popularity</label>
						<input type="number" name="popularity" />
						<label for="expected_turnout">Expected turnout</label>
						<input type="number" name="expected_turnout" />
						<input type="submit" value="Insert" />
					</form>
					<?php
					if (isset($tables['musicianpopularity'])) {
						echo $tables['musicianpopularity'];
					}
					?>
				</div>
				
				<div class="table" style="display: none;" id="musicianpayment">
					<h3>MusicianPayment</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="musicianpayment" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="musicianpayment" />
							<input type="submit" value="Clear" />
						</form>
					</div>
					<form method="POST" action="musicFestivalDBGUI.php">
						<input type="hidden" name="musicianpayment" />
						<label for="id">Musician ID</label>
						<select name="id">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT musician_id FROM Musician"));
							}
							disconnectFromDB();
							?>
						</select>
						<label for="festival_year">Year</label>
						<select name="festival_year">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT DISTINCT festival_year FROM Musician"));
							}
							disconnectFromDB();
							?>
						</select>
						<label for="cash_flow_id">Cash flow ID</label>
						<select name="cash_flow_id">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT id FROM CashFlow"));
							}
							disconnectFromDB();
							?>
						</select>
						<input type="submit" value="Insert" />
					</form>
					<?php
					if (isset($tables['musicianpayment'])) {
						echo $tables['musicianpayment'];
					}
					?>
				</div>
				
				<div class="table" style="display: none;" id="securitystaff">
					<h3>SecurityStaff</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="securitystaff" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="securitystaff" />
							<input type="submit" value="Clear" />
						</form>
					</div>
					<form method="POST" action="musicFestivalDBGUI.php">
						<input type="hidden" name="securitystaff" />
						<label for="id">Employee ID</label>
						<input type="number" name="id" />
						<label for="license_number">License number</label>
						<input type="number" name="license_number" />
						<label for="position">Position type</label>
						<select name="position">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT position FROM EmployeePosition"));
							}
							disconnectFromDB();
							?>
						</select>
						<label for="employee_name">Name</label>
						<input type="text" name="employee_name" />
						<input type="submit" value="Insert" />
					</form>
					<?php
					if (isset($tables['securitystaff'])) {
						echo $tables['securitystaff'];
					}
					?>
				</div>
				
				<div class="table" style="display: none;" id="sponsor">
					<h3>Sponsor</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="sponsor" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="sponsor" />
							<input type="submit" value="Clear" />
						</form>
					</div>
					<form method="POST" action="musicFestivalDBGUI.php">
						<input type="hidden" name="sponsor" />
						<label for="sponsor_name">Name</label>
						<input type="text" name="sponsor_name" />
						<label for="festival_year">Year</label>
						<input type="number" name="festival_year" />
						<label for="contributes_to">Cash flow ID</label>
						<select name="contributes_to">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT id FROM CashFlow"));
							}
							disconnectFromDB();
							?>
						</select>
						<input type="submit" value="Insert" />
					</form>
					<?php
					if (isset($tables['sponsor'])) {
						echo $tables['sponsor'];
					}
					?>
				</div>
				
				<div class="table" style="display: none;" id="staff">
					<h3>Staff</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="staff" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="staff" />
							<input type="submit" value="Clear" />
						</form>
					</div>
					<form method="POST" action="musicFestivalDBGUI.php">
						<input type="hidden" name="staff" />
						<label for="id">Employee ID</label>
						<input type="number" name="id" />
						<label for="station">Station</label>
						<input type="text" name="station" />
						<label for="position">Position type</label>
						<select name="position">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT position FROM EmployeePosition"));
							}
							disconnectFromDB();
							?>
						</select>
						<label for="employee_name">Name</label>
						<input type="text" name="employee_name" />
						<input type="submit" value="Insert" />
					</form>
					<?php
					if (isset($tables['staff'])) {
						echo $tables['staff'];
					}
					?>
				</div>
				
				<div class="table" style="display: none;" id="stage">
					<h3>Stage</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="stage" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="stage" />
							<input type="submit" value="Clear" />
						</form>
					</div>
					<form method="POST" action="musicFestivalDBGUI.php">
						<input type="hidden" name="stage" />
						<label for="venue_name">Venue name</label>
						<select name="venue_name">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT venue_name FROM Venue"));
							}
							disconnectFromDB();
							?>
						</select>
						<label for="stage_number">Number</label>
						<input type="number" name="stage_number" />
						<label for="stage_size">Size</label>
						<select name="stage_size">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT stage_size FROM StageSize"));
							}
							disconnectFromDB();
							?>
						</select>
						<input type="submit" value="Insert" />
					</form>
					<?php
					if (isset($tables['stage'])) {
						echo $tables['stage'];
					}
					?>
				</div>
				
				<div class="table" style="display: none;" id="stagesize">
					<h3>StageSize</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="stagesize" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="stagesize" />
							<input type="submit" value="Clear" />
						</form>
					</div>
					<form method="POST" action="musicFestivalDBGUI.php">
						<input type="hidden" name="stagesize" />
						<label for="stage_size">Size</label>
						<input type="number" name="stage_size" />
						<label for="capacity">Capacity</label>
						<input type="number" name="capacity" />
						<input type="submit" value="Insert" />
					</form>
					<?php
					if (isset($tables['stagesize'])) {
						echo $tables['stagesize'];
					}
					?>
				</div>
				
				<div class="table" style="display: none;" id="ticket">
					<h3>Ticket</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="ticket" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="ticket" />
							<input type="submit" value="Clear" />
						</form>
					</div>
					<form method="POST" action="musicFestivalDBGUI.php">
						<input type="hidden" name="ticket" />
						<label for="ticket_number">Number</label>
						<input type="number" name="ticket_number" />
						<label for="holder">Ticket holder ID</label>
						<select name="holder">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT id FROM Attendee"));
							}
							disconnectFromDB();
							?>
						</select>
						<label for="ticket_to">Venue name</label>
						<select name="ticket_to">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT venue_name FROM Venue"));
							}
							disconnectFromDB();
							?>
						</select>
						<label for="cash_flow_id">Cash flow ID</label>
						<select name="cash_flow_id">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT id FROM CashFlow"));
							}
							disconnectFromDB();
							?>
						</select>
						<input type="submit" value="Insert" />
					</form>
					<?php
					if (isset($tables['ticket'])) {
						echo $tables['ticket'];
					}
					?>
				</div>
				
				<div class="table" style="display: none;" id="vendor">
					<h3>Vendor</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="vendor" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="vendor" />
							<input type="submit" value="Clear" />
						</form>
					</div>
					<p>Insert not supported, please insert to a subclass.</p>
					<?php
					if (isset($tables['vendor'])) {
						echo $tables['vendor'];
					}
					?>
				</div>
				
				<div class="table" style="display: none;" id="vendorrevenue">
					<h3>VendorRevenue</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="vendorrevenue" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="vendorrevenue" />
							<input type="submit" value="Clear" />
						</form>
					</div>
					<form method="POST" action="musicFestivalDBGUI.php">
						<input type="hidden" name="vendorrevenue" />
						<label for="vendor_lot">Vendor lot</label>
						<select name="vendor_lot">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT DISTINCT lot_number FROM Vendor"));
							}
							disconnectFromDB();
							?>
						</select>
						<label for="festival_year">Year</label>
						<select name="festival_year">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT DISTINCT festival_year FROM Vendor"));
							}
							disconnectFromDB();
							?>
						</select>
						<label for="cash_flow_id">Cash flow ID</label>
						<select name="cash_flow_id">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT id FROM CashFlow"));
							}
							disconnectFromDB();
							?>
						</select>
						<input type="submit" value="Insert" />
					</form>
					<?php
					if (isset($tables['vendorrevenue'])) {
						echo $tables['vendorrevenue'];
					}
					?>
				</div>
				
				<div class="table" style="display: none;" id="venue">
					<h3>Venue</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="venue" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="venue" />
							<input type="submit" value="Clear" />
						</form>
					</div>
					<form method="POST" action="musicFestivalDBGUI.php">
						<input type="hidden" name="venue" />
						<label for="venue_name">Name</label>
						<input type="text" name="venue_name" />
						<label for="city">City</label>
						<input type="text" name="City" />
						<label for="capacity">Capacity</label>
						<input type="number" name="capacity" />
						<label for="accessibility">Accessibility</label>
						<input type="text" name="accessibility" />
						<input type="submit" value="Insert" />
					</form>
					<form method="POST" action="musicFestivalDBGUI.php">
						<label for="delete_venue_name">Delete venue</label>
						<select name="delete_venue_name" id="delete_venue_name">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT venue_name FROM Venue"));
							}
							disconnectFromDB();
							?>
						</select>
						<input type="submit" value="Delete" />
					</form>
					<form method="POST" action="musicFestivalDBGUI.php">
						<label for="venues_earning_over">Venues earning over:</label>
						<input type="text" name="venues_earning_over" />
						<input type="submit" value="Search!" />
					</form>

					<?php
					if (isset($tables['venue'])) {
						echo $tables['venue'];
					}
					?>
				</div>
				
				<div class="table" style="display: none;" id="venuepayment">
					<h3>VenuePayment</h3>
					<div class="buttons">
						<form method="GET" action="musicFestivalDBGUI.php">
							<input type="hidden" name="displayTable" value="venuepayment" />
							<input type="submit" value="Display" />
						</form>
						<form method="POST" action="musicFestivalDBGUI.php">
							<input type="hidden" name="dropTable" value="venuepayment" />
							<input type="submit" value="Clear" />
						</form>
					</div>
					<form method="POST" action="musicFestivalDBGUI.php">
						<input type="hidden" name="venuepayment" />
						<label for="cash_flow_id">Cash flow ID</label>
						<select name="cash_flow_id">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT id FROM CashFlow"));
							}
							disconnectFromDB();
							?>
						</select>
						<label for="venue_name">Venue name</label>
						<select name="venue_name">
							<?php
							if (connectToDB()) {
								echo generateOptionsFromResult(executePlainSQL("SELECT venue_name FROM Venue"));
							}
							disconnectFromDB();
							?>
						</select>
						<input type="submit" value="Insert" />
					</form>
					<?php
					if (isset($tables['venuepayment'])) {
						echo $tables['venuepayment'];
					}
					?>
			</div>
		</div>
	</body>
</html>