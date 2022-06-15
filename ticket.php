<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>Ticket #<?php echo $_GET['id'] ?></title>
    <meta name="description" content="Kurzbeschreibung">
    <link href="design.css" rel="stylesheet">
</head>

<body>

    <?php
    session_start();
    if (!isset($_SESSION['userid'])) {
        header("Location: index.php");
        die();
    }

    $db_server = "localhost";
    $db_user = "root";
    $db_name = "ticketsystem";
    $pdo = new PDO("mysql:host=$db_server;dbname=$db_name", $db_user);

    $ticketID = $_GET['id'];

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


    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['text'])) {
            $text = $_POST['text'];
            $statement = "INSERT INTO Antwort (Benutzer, Ticket, Text) VALUES (" . $_SESSION['userid'] . ", $ticketID, '$text')";
            $pdo->query($statement);
            header("Location: ticket.php?id=$ticketID");
            die();
        }

        $statement = "SELECT * FROM Ticket WHERE Ticketnummer = $ticketID";
        $result = $pdo->query($statement);
        $ticket = $result->fetch();

        if ($_SESSION['admin'] == 0) return;
        else if (isset($_POST['status'])) {
            $stat = $_POST['status'];
            if ($stat != $ticket['Status']) {
                $statement = "UPDATE Ticket SET Status = $stat WHERE Ticketnummer = $ticketID";
                $pdo->query($statement);
                $statement = "INSERT INTO Antwort (Benutzer, Ticket, Text) VALUES (" . $_SESSION['userid'] . ", $ticketID, 'Status wurde geändert auf: $status[$stat]')";
                $pdo->query($statement);
            }
            header("Location: ticket.php?id=$ticketID");
            die();
        } else if (isset($_POST['priority'])) {
            $prio = $_POST['priority'];
            if ($prio != $ticket['Priorität']) {
                $statement = "UPDATE Ticket SET Priorität = $prio WHERE Ticketnummer = $ticketID";
                $pdo->query($statement);
                $statement = "INSERT INTO Antwort (Benutzer, Ticket, Text) VALUES (" . $_SESSION['userid'] . ", $ticketID, 'Priorität geändert auf: $priority[$prio]')";
                $pdo->query($statement);
            }
            header("Location: ticket.php?id=$ticketID");
            die();
        } else if (isset($_POST['delete'])) {
            $statement = "DELETE FROM Antwort WHERE Ticket = $ticketID";
            $pdo->query($statement);
            $statement = "DELETE FROM Ticket WHERE Ticketnummer = $ticketID";
            $pdo->query($statement);
            header("Location: tickets.php");
            die();
        }
    }
    ?>

    <ul>
        <li><a href="tickets.php">Tickets</a></li>
        <li><a href="create.php">Ticket Erstellen</a></li>

        <?php
        if ($_SESSION['admin'] == 1) {
            echo '<li><a href="accounts.php">Accounts</a></li>';
        }
        ?>

        <li style="float:right"><a href="logout.php" style="background-color:#f44336">Logout</a></li>
        <li style="float:right"><a><?php echo $_SESSION['name'] ?></a></li>
    </ul>

    <div class="container">
        <div style="float:left; width:80%">
            <?php
            if ($_GET['id'] == null) {
                header("Location: tickets.php");
                die();
            }

            $statement = "SELECT * FROM Ticket WHERE Ticketnummer = $ticketID";
            $result = $pdo->query($statement);
            $ticket = $result->fetch();

            if (!$ticket) {
                header("Location: tickets.php");
                die();
            }

            if ($ticket['Ersteller'] != $_SESSION['userid'] && $_SESSION['admin'] == 0) {
                header("Location: tickets.php");
                die();
            }

            $statement = "SELECT * FROM Benutzer WHERE ID = $ticket[Ersteller]";
            $result = $pdo->query($statement);
            if ($result) {
                $creator = $result->fetch();
            } else {
                $creator['Vorname'] = "Deleted";
                $creator['Nachname'] = "User";
            }

            echo '<h2 class="toph2">Ticket #' . $ticketID . ' - ' . $status[$ticket['Status']] . '</h2>';
            echo '<h4 class="subh4">' . $creator['Vorname'] . ' ' . $creator['Nachname'] . '</h4><br>';
            echo '<b>Erstelldatum</b>: ' . $ticket['Timestamp'] . '<br>';
            echo '<b>Priorität</b>: ' . $priority[$ticket['Priorität']] . '<br>';
            echo '<b>Raum</b>: ' . $ticket['Raum'] . '<br>';
            echo '<b>Gerät</b>: ' . $ticket['Gerät'] . '<br>';
            echo '<br>';
            echo '<b>Problembeschreibung</b>: <br><div class="comment">' . $ticket['Problembeschreibung'] . '</div><br>';


            $statement = "SELECT * FROM Antwort WHERE Ticket = $ticketID";
            $result = $pdo->query($statement);
            if ($result !== null) {
                foreach ($result as $row) {
                    $statement = "SELECT * FROM Benutzer WHERE ID = $row[Benutzer]";
                    $result = $pdo->query($statement);
                    if ($result) {
                    $user = $result->fetch();
                    } else {
                        $user['Vorname'] = "Deleted";
                        $user['Nachname'] = "User";
                    }

                    echo '<br>';
                    echo '<div class="comment">';
                    echo '<b>' . $user['Vorname'] . ' ' . $user['Nachname'] . '</b>: ' . $row['Timestamp'] . '<br>';
                    echo '<i>' . $row['Text'] . '</i><br>';
                    echo '</div>';

                    $statement = "UPDATE Antwort SET gelesen = 1 WHERE ID = $row[ID]";

                    if ($_SESSION['admin'] == 1) {
                        if ($user['Admin'] == 0) {
                            $pdo->query($statement);
                        }
                    } else {
                        if ($user['Admin'] == 1) {
                            $pdo->query($statement);
                        }
                    }
                }
            }

            ?>
            <form action="ticket.php?id=<?php echo $ticketID; ?>" method="post">
                <textarea name="text" rows="5" cols="50" required></textarea><br>
                <button type="submit">Kommentieren</button>
            </form>
        </div>
        <div style="float:right; width:15%">
            <br>
            <form action="ticket.php?id=<?php echo $ticketID; ?>" method="post" class=<?php echo $_SESSION['admin'] == 1 ? 'show' : 'hidden' ?>>
                <select name="status">
                    <option value="0">Offen</option>
                    <option value="1">In Bearbeitung</option>
                    <option value="2">Geschlossen</option>
                    <option selected hidden value="<?php echo $ticket['Status'] ?>"><?php echo $status[$ticket['Status']] ?></option>
                </select>
                <button type="submit" style="background-color:darkcyan">Status ändern</button>
            </form>
            <br>
            <form action="ticket.php?id=<?php echo $ticketID; ?>" method="post" class=<?php echo $_SESSION['admin'] == 1 ? 'show' : 'hidden' ?>>
                <select name="priority">
                    <option value="0">Niedrig</option>
                    <option value="1">Normal</option>
                    <option value="2">Hoch</option>
                    <option value="3">Kritisch</option>
                    <option selected="selected" hidden value="<?php echo $ticket['Priorität'] ?>"><?php echo $priority[$ticket['Priorität']] ?></option>
                </select>
                <button type="submit" style="background-color:salmon">Priorität ändern</button>
            </form>
            <form action="ticket.php?id=<?php echo $ticketID; ?>" method="post" class=<?php echo $_SESSION['admin'] == 1 ? 'show' : 'hidden' ?>>
                <button type="submit" name="delete" style="background-color:red">Ticket löschen</button>
            </form>
        </div>
    </div>
    <br>
</body>