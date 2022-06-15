<!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="UTF-8">
	<title>Tickets</title>
	<meta name="description" content="Tickets">
	<link href="design.css" rel="stylesheet">
</head>
<body>

<?php
session_start();
if(!isset($_SESSION['userid'])) {
    header("Location: tickets.php");
	die();
}
?>

<ul>
  <li><a href="tickets.php" class="active">Tickets</a></li>
  <li><a href="create.php">Ticket Erstellen</a></li>
  <?php
	if ($_SESSION['admin'] == 1) {
		echo '<li><a href="accounts.php">Accounts</a></li>';
	}
	?>
  
  <li style="float:right"><a href="logout.php" style="background-color:#f44336">Logout</a></li>
  <li style="float:right"><a><?php echo $_SESSION['name']?></a></li>
</ul>
<?php
$db_server = "localhost";
$db_user = "root";
$db_name = "ticketsystem";

$pdo = new PDO("mysql:host=$db_server;dbname=$db_name", $db_user);

if ($_SESSION['admin'] == 0) {
	$statement = "SELECT * FROM Ticket WHERE Ersteller = ". $_SESSION['userid'];
	$result = $pdo->query($statement);
	echo "<h2 class='toph2'>Meine Tickets (". $result->rowCount(). ")</h2>";
} else {
	$statement = "SELECT * FROM Ticket";
	$result = $pdo->query($statement);
	echo "<h2 class='toph2'>Ticketübersicht (". $result->rowCount(). ")</h2>";
}



?>
<br>
<table class="styled-table" align="center">
<th style="width:2%">ID</th>
<th>Ersteller</th>
<th>Aktualisiert</th>
<th>Raum</th>
<th>Gerät</th>
<th>Priorität</th>
<th>Status</th>

<?php
$status = array(
    0 => "Offen",
    1 => "In Bearbeitung",
    2 => "Geschlossen"
);

$priority = array(
    0 => "Niedrig",
    1 => "Normal",
    2 => "Hoch",
    3 => "Kritisch"
);

	if ($result !== null) {
		foreach ($result as $row) {
			if ($_SESSION['admin'] == 0) {
				if ($row['Ersteller'] !== $_SESSION['userid']) {
					continue;
				}
			}

			$statement = "SELECT * FROM Benutzer WHERE ID = ". $row['Ersteller'];
			$result2 = $pdo->query($statement);
			if ($result2) {
				$creator = $result2->fetch();
			} else {
				$creator['Nachname'] = "Deleted User";
			}

			$statement = "SELECT * FROM Antwort WHERE Ticket = ". $row['Ticketnummer']. " ORDER BY ID DESC LIMIT 1";
			$result3 = $pdo->query($statement);
			$lastanswer = $result3->fetch();
				$updated = $lastanswer['Timestamp'] ?? $row['Timestamp'];

			if ($_SESSION['admin']) {
				$read = $lastanswer['gelesen'] ?? 0;
			} else {
				$read = $lastanswer['gelesen'] ?? 1;
			}
			

			if (isset($lastanswer['Benutzer'])) {
				$b = $lastanswer['Benutzer'];
			} else {
				$b = 0;
			}

			$statement = "SELECT * FROM Benutzer WHERE ID = ". $b;
			$result4 = $pdo->query($statement)->fetch();

			if ($result4) {
				$a = $result4['Admin'];
			} else {
				$a = 0;
			}

			$class = '';
			if ($read == 0) {
				if ($b != $_SESSION['userid']) {
					$class = ' class="unread"';
					if ($_SESSION['admin'] && $a == 1) {
						$class = '';
					}
				}
			}
			
			echo '<tr onclick="location.href=`ticket.php?id='.$row['Ticketnummer'].'`">';
			echo '<td>'. $row['Ticketnummer'] .'</td>';
			echo '<td>'. $creator['Nachname'] .'</td>';
			echo '<td'.$class.'>'. $updated .'</td>';
			echo '<td>'. $row['Raum'] .'</td>';
			echo '<td>'. $row['Gerät'] .'</td>';
			echo '<td>'. $priority[$row['Priorität']] .'</td>';
			echo '<td>'. $status[$row['Status']] .'</td>';
			echo '</tr>';
			
		}
	} else {
		echo "</table>";
		echo "Keine Tickets in der Datenbank gespeichert.";
	}
		
?>
</table>
</body>
</html>