<!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="UTF-8">
	<title>Registrierung</title>
	<meta name="description" content="Kurzbeschreibung">
	<link href="design.css" rel="stylesheet">
</head>

<?php
session_start();
if($_SESSION['admin'] == 0) {
    header("Location: login.php");
	die();
} 

$db_server = "localhost";
$db_user = "root";
$db_name = "ticketsystem";

$pdo = new PDO("mysql:host=$db_server;dbname=$db_name", $db_user);

?>

<body>
<ul>
  <li><a href="tickets.php">Tickets</a></li>
  <li><a href="create.php">Ticket Erstellen</a></li>
  <li><a href="accounts.php" class="active">Accounts</a></li>
  <li style="float:right"><a href="logout.php" style="background-color:#f44336">Logout</a></li>
  <li style="float:right"><a><?php echo $_SESSION['name']?></a></li>
</ul>
<h2> Accounts </h2>
<label for = "username">Neuen Account anlegen</label>
<form action="accounts.php?create=1" method="post" style="width:15%">
	<input type="text" style="width:100%" name="username" placeholder="Benutzername" required>
	<input type="text" style="width:100%" name="firstname" placeholder="Vorname" required>
	<input type="text" style="width:100%" name="lastname" placeholder="Nachname" required>
	<select name="admin" style="width:100%">
		<option value="0">Benutzer</option>
		<option value="1">Administrator</option>
	</select>
	<button type="submit" style="width:100%">Account erstellen</button>
</form>

<table class="styled-table" align="center">
<th style="width:2%">ID</th>
<th>Benutzername</th>
<th>Vorname</th>
<th>Nachname</th>
<th>Admin</th>
<th></th>

<?php
$statement = "SELECT * FROM Benutzer";
$result = $pdo->query($statement);

foreach($result as $row) {

	if (isset($_GET['edit']) && $_GET['edit'] == $row['ID']) {
		echo "<tr>";
		echo "<td>". $row['ID'] . "</td>";
		echo "<form action='accounts.php?update=".$row['ID']."' method='post'>";
		echo "<td><input type='text' name='username' value=". $row['Benutzername'] . "></input></td>";
		echo "<td><input type='text' name='firstname' value=". $row['Vorname'] . "></input></td>";
		echo "<td><input type='text' name='lastname' value=". $row['Nachname'] . "></input></td>";
		echo "<td><select name='admin'><option value='0'>0</option><option value='1'>1</option><option selected hidden>".$row['Admin']."</option></select></td>";
		echo "<td><button type='submit'>Update</button>";
		echo "<br>";
		echo "<input type='checkbox' name='delete'>Löschen</checkbox>";
		echo "<input type='checkbox' name='reset'>Passwort zurücksetzen</checkbox>";
		echo "</td>";
		echo "</form>";
		echo "</tr>";
	} else {
		echo "<tr>";
		echo "<td>". $row['ID'] . "</td>";
		echo "<td>". $row['Benutzername'] . "</td>";
		echo "<td>". $row['Vorname'] . "</td>";
		echo "<td>". $row['Nachname'] . "</td>";
		echo "<td>". $row['Admin'] . "</td>";
		echo "<td><a href='accounts.php?edit=". $row['ID'] ."'>Bearbeiten</a></td>";
		echo "</tr>";
	}
}
?>
</table>
</body>

<?php
	if ($_SESSION['admin'] != 1) return;
	if (isset($_GET['update'])) {
		$id = $_GET['update'];
		$username = $_POST['username'];
		$firstname = $_POST['firstname'];
		$lastname = $_POST['lastname'];
		$admin = $_POST['admin'];

		if ($_POST['delete']) {
			$statement = "DELETE FROM Benutzer WHERE ID = $id";
			$pdo->query($statement);
			header("Location: accounts.php");
			die();
		} else if ($_POST['reset']) {
			$statement = "UPDATE Benutzer SET Passwort = null WHERE ID = $id";
			$pdo->query($statement);
		}

		$statement = "UPDATE Benutzer SET Benutzername='$username', Vorname='$firstname', Nachname='$lastname', Admin='$admin' WHERE ID='$id'";
		$pdo->query($statement);

		if ($id !== $_SESSION['userid']) {
			header("Location: accounts.php");	
		} else {
			header("Location: logout.php");
		}
		die();
	} else if (isset($_GET['create'])) {
		$username = $_POST['username'];
		$firstname = $_POST['firstname'];
		$lastname = $_POST['lastname'];
		$admin = $_POST['admin'];

		$statement = "INSERT INTO Benutzer (Benutzername, Vorname, Nachname, Admin) VALUES ('$username', '$firstname', '$lastname', '$admin')";
		$pdo->query($statement);

		header("Location: accounts.php");
		die();
	}
?>