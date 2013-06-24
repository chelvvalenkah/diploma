<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Black Angel
 * Date: 20.06.13
 * Time: 20:09
 * To change this template use File | Settings | File Templates.
 */

include_once('connect.php');
include_once('constants.php');
include_once('functions.php');
if (session_status() != PHP_SESSION_ACTIVE) session_start(); # PHP >= 5.4.0

# Lections AJAX
if (is_from_this_server() && is_from_script(LECTURES_URL) && arg_exists_not_null($_POST['source']) && arg_exists_not_null($_POST['lectureID']) && arg_exists_not_null($_POST['action'])) {
    if ($_POST['source'] == 'visitorApplications' && $_SESSION['role'] != 'admin') {
        if ($_POST['action'] == 'register') {
            $mysqldatetime = date("Y-m-d H:i:s");
            if ($mysqli->query("INSERT INTO registrations (visitor_ID, lecture_ID, time) VALUES('{$_SESSION['userID']}', '{$_POST['lectureID']}', '$mysqldatetime')")) {
                header("HTTP/1.1 200 OK");
                echo json_encode("Registered! :)");
            }
        }
        elseif ($_POST['action'] == 'unregister') {
            if ($mysqli->query("DELETE FROM registrations WHERE visitor_ID = '{$_SESSION['userID']}' AND lecture_ID = '{$_POST['lectureID']}'")) {
                header("HTTP/1.1 200 OK");
                echo json_encode("Unregistered... :(");
            }
        }
    }
    else {
        $auth_result = $mysqli->query("SELECT * FROM lectures WHERE id = {$_POST['lectureID']} AND speaker_ID = {$_SESSION['userID']}");
        if ($_SESSION['role'] == 'admin' || $auth_result->num_rows > 0) {
            switch ($_POST['action']) {
                case 'rejected':
                case 'withdrawn':
                    $mysqli->query("UPDATE lectures SET date_ID = NULL WHERE id = '{$_POST['lectureID']}'");
                    $mysqli->query("UPDATE lectures SET time = NULL WHERE id = '{$_POST['lectureID']}'");
                case 'pending':
                case 'approved':
                    if ($mysqli->query("UPDATE lectures SET status = '{$_POST['action']}' WHERE id = '{$_POST['lectureID']}'")) {
                        header("HTTP/1.1 200 OK");
                        echo json_encode("OK!");
                    }
                    break;
            }
        }
    }
}
else {
    header("HTTP/1.1 400 Bad Request");
    $msg = "Fuck you, hacker!";
    if ($_SERVER['REQUEST_METHOD'] == "GET") {
        echo $msg;
    }
    else {
        echo json_encode($msg);
    }
    exit;
}
?>