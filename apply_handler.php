<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Black Angel
 * Date: 20.06.13
 * Time: 1:25
 * To change this template use File | Settings | File Templates.
 */

require_once('connect.php');
require_once('constants.php');
require_once('functions.php');
if (session_status() != PHP_SESSION_ACTIVE) session_start(); # PHP >= 5.4.0

$report_data = array(); # массив полей нового пользователя
$table = "lectures";

if (!isset($_SERVER['HTTP_REFERER'])) {
    # Нет HTTP_REFERER
    header("HTTP/1.1 400 Bad Request");
    echo "Fuck you, hacker!";
    exit;
}
else {
    /*$referer_parts = explode("/", $_SERVER['HTTP_REFERER']);
    $referer = $referer_parts[count($referer_parts)-1];
    $host_parts = explode(":", $_SERVER['HTTP_HOST']);
    if ($host_parts[0] != $_SERVER['SERVER_NAME'] || strtolower($referer) != "signup.php") {*/
    if (!is_from_this_server() || !is_from_script(APPLY_URL)) {
        # Не тот сервер, или неверный HTTP_REFERER
        header("HTTP/1.1 400 Bad Request");
        echo "Fuck you, hacker!";
        exit;
    }
    else { # Не хакеры :D
        # Checking report information
        if (!arg_exists_not_null($_POST['title'])) {
            $emptyFields[] = "Тема доповіді";
        }
        else if (preg_match("#.{1,255}#u", $_POST['title'], $matches)) { # заполнен - проверяем
            $report_data['title'] = $matches[0]; # если ОК
        } else $wrongInput[] = "Тема доповіді"; # если не по шаблону

        if (!arg_exists_not_null($_POST['author'])) {
            $emptyFields[] = "Доповідач";
        }
        else {
            if ($_POST['author'] != $_SESSION['userName']) {
                $errorMsg[] = "Ви можете подавати доповіді лише від свого імені!";
            }
            else $report_data['speaker_ID'] = $_SESSION['userID'];
        }

        if (!arg_exists_not_null($_POST['duration'])) {
            $emptyFields[] = "Тривалість доповіді";
        }
        else if (preg_match("#(\d{1,2}0|\d{0,2}5)#u", $_POST['duration'], $matches)) {
            $report_data['duration'] = $matches[0];
        } else $wrongInput[] = "Тривалість доповіді";

        if (arg_exists_not_null($_POST['notes'])) {
            $report_data['notes'] = strip_tags($_POST['notes']);
        }

        if ($_SESSION['role'] == 'admin') {
            $errorMsg[] = "Організатори не можуть подавати чи редагувати доповіді!";
        }

        # Generating MySQL request
        if ($_SESSION['role'] != 'admin') {
            if (!isset($emptyFields) && !isset($wrongInput) && !isset($errorMsg)) { # если проверка прошла успешно
                $fields = "(";
                $values = "(";
                if ($_POST['mode'] == 'edit') {
                    if ($mysqli->query("SELECT * FROM lectures WHERE ID = '".$mysqli->real_escape_string($_POST['lectureID'])."' AND speaker_ID = '"
                        .$mysqli->real_escape_string($_SESSION['userID'])."'")->num_rows > 0) {
                        if (!$mysqli->query("DELETE FROM lectures WHERE ID = {$_POST['lectureID']}")) {
                            header("HTTP/1.1 500 Internal Server Error");
                            $errors['errorMsg'] = "Помилка при роботі з базою даних. Спробуйте пізніше.";
                            echo json_encode($errors);
                            exit;
                        }
                        $fields .= 'ID, ';
                        $values .= $mysqli->real_escape_string($_POST['lectureID']).', ';
                    }
                    else {
                        header("HTTP/1.1 400 Bad Request");
                        $errors['errorMsg'] = "Ви не маєте права редагувати цю доповідь!";
                        echo json_encode($errors);
                        exit;
                    }
                }
                foreach ($report_data as $column=>$data) {
                    $fields .= $column.", ";
                    $values .= "'".$mysqli->real_escape_string($data)."', ";
                }
                $fields = substr($fields, 0, strlen($fields)-2).")";
                $values = substr($values, 0, strlen($values)-2).")";
                if (($_SESSION['role'] == 'speaker' || $mysqli->query("UPDATE participants SET role = 'speaker' WHERE ID = {$_SESSION['userID']}"))
                    && $mysqli->query("INSERT INTO $table $fields VALUES $values")) {
                    $_SESSION['role'] = 'speaker';
                    header("HTTP/1.1 200 OK");
                    $response['title'] = "Вітаємо, {$_SESSION['userName']}!";
                    if ($_POST['mode'] == 'new') {
                        $response['msg'] = "Вашу заявку щодо доповіді на конференції ".CONF_NAME." зареєстровано!
                    Статус заявки можна переглянути в особистому кабінеті.";
                    }
                    elseif ($_POST['mode'] == 'edit') {
                        $response['msg'] = "Вашу заявку щодо доповіді на конференції ".CONF_NAME." відредаговано!
                    Статус заявки можна переглянути в особистому кабінеті.";
                    }
                    echo json_encode($response);
                    exit;
                }
                else {
                    header("HTTP/1.1 500 Internal Server Error");
                    $errors['errorMsg'] = "Помилка при роботі з базою даних. Спробуйте пізніше.";
                    echo json_encode($errors);
                    exit;
                }
            }
            else {
                header("HTTP/1.1 400 Bad Request");
                if (isset($emptyFields)) $errors['emptyFields'] = $emptyFields;
                if (isset($wrongInput)) $errors['wrongInput'] = $wrongInput;
                if (isset($errorMsg)) $errors['errorMsg'] = $errorMsg;
                echo json_encode($errors);
                exit;
            }
        }
        else {
            $ok = true;
            $ok = $mysqli->query("UPDATE lectures SET date_ID = '{$_POST['date']}' WHERE ID = {$_POST['lectureID']}");
            $ok = $mysqli->query("UPDATE lectures SET time = '{$_POST['time']}' WHERE ID = {$_POST['lectureID']}");
            $ok = $mysqli->query("UPDATE lectures SET flow_ID = '{$_POST['flow']}' WHERE ID = {$_POST['lectureID']}");
            if ($ok) {
                header("HTTP/1.1 200 OK");
                $response['title'] = "Вітаємо, {$_SESSION['userName']}!";
                $response['msg'] = "Доповідь успішно додано в розклад конференції ".CONF_NAME."!";
                echo json_encode($response);
                exit;
            }
            else {
                header("HTTP/1.1 500 Internal Server Error");
                $errors['errorMsg'] = "Помилка при роботі з базою даних. Спробуйте пізніше.";
                echo json_encode($errors);
                exit;
            }
        }
    }

}