<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Black Angel
 * Date: 17.06.13
 * Time: 5:43
 * To change this template use File | Settings | File Templates.
 */

require_once('connect.php');
require_once('constants.php');
require_once('functions.php');

//$user_data = array(); # массив полей нового пользователя
$table = "participants";

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
    if (!is_from_this_server() || !is_from_script(SIGNUP_URL)) {
        # Не тот сервер, или неверный HTTP_REFERER
        header("HTTP/1.1 400 Bad Request");
        echo "Fuck you, hacker!";
        exit;
    }
    else { # Не хакеры :D
        # Checking user information
        $user_data = check_user_info($mysqli, $emptyFields, $wrongInput, $errorMsg);

        # Generating MySQL request
        if (!isset($emptyFields) && !isset($wrongInput) && !isset($errorMsg)) { # если проверка прошла успешно
            $fields = "(";
            $values = "(";
            foreach ($user_data as $column=>$data) {
                $fields .= $column.", ";
                $values .= "'".$mysqli->real_escape_string($data)."', ";
            }
            $fields = substr($fields, 0, strlen($fields)-2).")";
            $values = substr($values, 0, strlen($values)-2).")";
            if ($mysqli->query("INSERT INTO $table $fields VALUES $values")) {
                header("HTTP/1.1 200 OK");
                $response['title'] = "Вітаємо, {$user_data['name']}!";
                $response['msg'] = "Вітаємо, Вас зареєстровано на конференцію ".CONF_NAME."!
                Логін та пароль вислані на Вашу електронну поштову скриньку.";
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

}

/*echo $_SERVER['HTTP_REFERER'].'<br />';
echo $_SERVER['HTTP_HOST'].'<br />';
echo $_SERVER['SERVER_NAME'].'<br />';
echo $_SERVER['HTTP_USER_AGENT'].'<br />';
echo $_SERVER['SCRIPT_NAME'].'<br />';

function check_post_data() {
    $ok = true;
    if (isset($_POST['surname'])) $ok = false;
    if (isset($_POST['name'])) $ok = false;
    if (isset($_POST['company'])) $ok = false;
    if (isset($_POST['position'])) $ok = false;
    if (isset($_POST['age'])) $ok = false;
    if (isset($_POST['email'])) $ok = false;
    if (isset($_POST['password'])) $ok = false;
    if (isset($_POST['confirmation'])) $ok = false;
    if (isset($_POST['site'])) $ok = false;
    if (isset($_POST['phone1'])) $ok = false;
    if (isset($_POST['phone2'])) $ok = false;
    return $ok;
}

if (isset($_POST['surname'])) print($_POST['surname']."<br />");
if (isset($_POST['name'])) print($_POST['name']."<br />");
if (isset($_POST['company'])) print($_POST['company']."<br />");
if (isset($_POST['position'])) print($_POST['position']."<br />");
if (isset($_POST['age'])) print($_POST['age']."<br />");
if (isset($_POST['email'])) print($_POST['email']."<br />");
if (isset($_POST['email'])) print($_POST['password']."<br />");
if (isset($_POST['email'])) print(md5($_POST['confirmation'])."<br />");
if (isset($_POST['site'])) print($_POST['site']."<br />");
if (isset($_POST['phone1'])) print($_POST['phone1']."<br />");
if (isset($_POST['phone2'])) print($_POST['phone2']."<br />");

print("Email match: ".preg_match("#^[A-z][A-z0-9\._-]{0,21}[A-z0-9]@[A-z0-9][A-z0-9\.-]{0,30}[A-z0-9]\.[A-z]{2,7}#", $_POST['email'], $matches));
print_r($matches);*/
?>