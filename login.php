<!DOCTYPE html>
<html lang="de">

<head>
	<meta charset="UTF-8">
	<title>Tickets Login</title>
	<meta name="description" content="Kurzbeschreibung">
	<link href="design.css" rel="stylesheet">
</head>

<?php
session_start();
if(isset($_SESSION['userid'])) {
    header("Location: tickets.php");
	die();
}

$db_server = "localhost";
$db_user = "root";
$db_name = "ticketsystem";

try {
$pdo = new PDO("mysql:host=$db_server;dbname=$db_name", $db_user);
$connected = true;
}
catch(PDOException $e)
{
  echo $e->getMessage();
  $connected = false;                       
}
?>

<body>

	<h1> Login </h1>

	<form action="?login=1" method="post" style="max-width:max-content">
		<input type="text" name="username" placeholder="Benutzername" autofocus=true autocomplete="username" required></br>
		<input type="password"name="password" placeholder="Passwort" autocomplete="current-password" required></br>
		<button type="submit" name="submit" <?php echo $connected ? "" : "disabled" ?>>Login</button>
	</form>

</body>

</html>

<?php

if (isset($_GET['login'])) {

	$username = $_POST["username"];
	$password = $_POST["password"];

	$statement = $pdo->prepare("SELECT * FROM Benutzer WHERE Benutzername = :username");
	$result = $statement->execute(array('username' => $username));
	$user = $statement->fetch();

	if ($user !== false && $password !== false) {
		if ($user['Passwort'] !== null) {
			if (password_verify($password, $user['Passwort'])) {
				$_SESSION['name'] = $user['Vorname'] . " " . $user['Nachname'];
				$_SESSION['userid'] = $user['ID'];
				$_SESSION['admin'] = $user['Admin'];
				header("Location: tickets.php");
				die();
			}
			else {
				echo "Benutzername oder Passwort falsch.<br>";
			}
		} else {
			$statement = "UPDATE Benutzer SET Passwort = '" . password_hash($password, PASSWORD_DEFAULT) . "' WHERE ID = " . $user['ID'];
			$pdo->query($statement);
			echo "Ihr Passwort wurde erfolgreich gespeichert. Sie können sich nun einloggen.";
		}
	} else {
		echo "Benutzername oder Passwort falsch.<br>";
	}
}
?>