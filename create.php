<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Ticket erstellen</title>
    <meta name="description" content="Tickets">
    <link href="design.css" rel="stylesheet">
</head>

<?php
session_start();
if (!isset($_SESSION['userid'])) {
    header("Location: tickets.php");
    die();
}
?>

<body>
<ul>
  <li><a href="tickets.php">Tickets</a></li>
    <li><a href="create.php" class="active">Ticket Erstellen</a></li>
    <?php
	if ($_SESSION['admin'] == 1) {
		echo '<li><a href="accounts.php">Accounts</a></li>';
	}
	?>
  
  <li style="float:right"><a href="logout.php" style="background-color:#f44336">Logout</a></li>
  <li style="float:right"><a><?php echo $_SESSION['name']?></a></li>
</ul>
    <?php
    if (isset($_GET['create'])) {
        $db_server = "localhost";
        $db_user = "root";
        $db_name = "ticketsystem";
        $pdo = new PDO("mysql:host=$db_server;dbname=$db_name", $db_user);

        $description = $_POST['description'];
        $room = $_POST['room'];
        $device = $_POST['device'];

        $statement = "INSERT INTO Ticket (Ersteller, Timestamp, Problembeschreibung, Raum, Gerät) VALUES ('" . $_SESSION['userid'] . "', NOW(), '" . $description . "', '" . $room . "', '" . $device . "')";
        $result = $pdo->query($statement);

        if ($result !== null) {
            echo "<script>alert('Ticket erfolgreich erstellt!');</script>";
            header("Location: tickets.php");
            die();
        } else {
            echo "<script>alert('Ticket konnte nicht erstellt werden!');</script>";
        }
    } else {
        echo '<h2>Ticket erstellen</h2>';
    }
    ?>

    <form action="?create=1" method="post" class="createform">

        <div class="container">
            <label for="description"><b>Beschreibung</b></label>
            <textarea name="description" placeholder="Bitte beschreiben Sie ihr Problem..." required></textarea>
            <!-- <label for="room"><b>Raum</b></label> -->
            <input type="text" placeholder="Raum" name="room" required>
            <!-- <label for="device"><b>Gerät</b></label> -->
            <input type="text" placeholder="Gerät" name="device" required>
            <button type="submit">Ticket erstellen</button>
        </div>

        <div class="container" style="background-color:#444444">
            <a href="tickets.php">
                <button type="button" class="cancelbtn" >Abbrechen</button>
            </a>
        </div>
    </form>

</body>