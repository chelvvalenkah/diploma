<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Black Angel
 * Date: 14.06.13
 * Time: 9:37
 * To change this template use File | Settings | File Templates.
 */
    $host = 'localhost';
    $user = 'fedorov';
    $pass = 'ik9123';
    $db = 'diploma';

    /*mysql_connect($host, $user, $pass) or die("Could not connect: ".mysql_error());
    mysql_select_db($db) or die("Could not select database: ".mysql_error());
    mysql_query("SET NAMES 'utf8'");*/

    $mysqli = new mysqli($host, $user, $pass, $db);
    if ($mysqli->connect_error) {
        die('Ошибка подключения (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }
    else {
        $mysqli->query("SET NAMES 'utf8'");
        $mysqli->query("SET lc_time_names='ru_RU'");
    }
?>