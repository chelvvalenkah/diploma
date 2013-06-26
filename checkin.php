<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Black Angel
 * Date: 25.06.13
 * Time: 2:12
 * To change this template use File | Settings | File Templates.
 */

require_once('connect.php');
require_once('constants.php');
require_once('functions.php');

//$_POST['visitor'] = 33;
//$_POST['venue'] = 2;

if (arg_exists_not_null($_POST['visitor']) && arg_exists_not_null($_POST['venue'])) {
    $mysqldatetime = date("Y-m-d H:i:s");
    $venue_result = $mysqli->query("SELECT * FROM venues WHERE id = {$_POST['venue']}");
    if ($venue_result->num_rows > 0) $venue = $venue_result->fetch_assoc()['name'];
    if ($mysqli->query("INSERT INTO visits (visitor_ID, venue_ID, time) VALUES('{$_POST['visitor']}', '{$_POST['venue']}', '$mysqldatetime')")) {
        header("HTTP/1.1 200 OK");
        echo "Successfully check-in'd at $venue! :)";
    }
    else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Fucked up! :(";
    }
}
else {
    header("HTTP/1.1 400 Bad Request");
    echo "Fuck you, hacker!";
}

?>