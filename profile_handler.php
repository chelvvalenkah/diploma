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
if (session_status() != PHP_SESSION_ACTIVE) session_start(); # PHP >= 5.4.0

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
    if (!is_from_this_server() || !is_from_script(PROFILE_URL)) {
        # Не тот сервер, или неверный HTTP_REFERER
        header("HTTP/1.1 400 Bad Request");
        echo "Fuck you, hacker!";
        exit;
    }
    else { # Не хакеры :D
        # Checking user information
        if (!isset($_POST['surname']) || empty($_POST['surname'])) { # если обязательный параметр не заполнен
            $emptyFields[] = "Прізвище";
        }
        else if (preg_match("#[А-яІіЇїЄєЁёA-z'\s\-]{1,32}#u", $_POST['surname'], $matches)) { # заполнен - проверяем
            $user_data['surname'] = $matches[0]; # если ОК
        } else $wrongInput[] = "Прізвище"; # если не по шаблону

        if ((!isset($_POST['name']) || empty($_POST['name']))) {
            $emptyFields[] = "Ім'я";
        }
        else if (preg_match("#[А-яІіЇїЄєЁёA-z'\s\-]{1,32}#u", $_POST['name'], $matches)) {
            $user_data['name'] = $matches[0];
        } else $wrongInput[] = "Ім'я";

        if ((isset($_POST['company']) && !empty($_POST['company']))) { # если заполнен необязательный параметр
            if (preg_match('#[А-яІіЇїЄєЁёA-z\xAB\'\"\-,\.\s\xBB]{1,32}#u', $_POST['company'], $matches)) { # проверяем по шаблону
                $user_data['company'] = $matches[0]; # если ОК
            } else $wrongInput[] = "Компанія"; # если не по шаблону
        }

        if ((isset($_POST['position']) && !empty($_POST['position']))) {
            if (preg_match('#[А-яІіЇїЄєЁёA-z\-,\s]{1,32}#u', $_POST['position'], $matches)) {
                $user_data['position'] = $matches[0];
            } else $wrongInput[] = "Посада";
        }

        if ((!isset($_POST['age']) || empty($_POST['age']))) {
            $emptyFields[] = "Вік";
        }
        else if (preg_match("#(S|M|L|XL){1}#u", $_POST['age'], $matches)) {
            $user_data['age'] = $matches[0];
        } else $wrongInput[] = "Вік";

        if ((!isset($_POST['sex']) || empty($_POST['sex']))) {
            $emptyFields[] = "Стать";
        }
        else if (preg_match("#(M|F){1}#u", $_POST['sex'], $matches)) {
            $user_data['sex'] = $matches[0];
        } else $wrongInput[] = "Стать";

        if ((!isset($_POST['email']) || empty($_POST['email']))) {
            $emptyFields[] = "Email";
        }
        else if (preg_match("#^[A-z][A-z0-9\._-]{0,21}[A-z0-9]@[A-z0-9][A-z0-9\.-]{0,30}[A-z0-9]\.[A-z]{2,7}#u", $_POST['email'], $matches)) {
            $email = $mysqli->real_escape_string($_POST['email']);
            $email_result = $mysqli->query("SELECT id, email FROM participants WHERE email = '$email'");
            $email_row = $email_result->fetch_assoc();
            if (($_POST['mode'] == 'edit' && ($email_row['id'] == $_SESSION['userID'])) || $email_result->num_rows == NULL) {
                $user_data['email'] = $matches[0];
            }
            else $errorMsg[] = "Відвідувач з такою електронною скринькою вже зареєстрований!";
        } else $wrongInput[] = "Email";

        if ((!isset($_POST['password']) || empty($_POST['password']))) {
            $emptyFields[] = "Пароль";
        }
        #else if (preg_match('#'.preg_quote('[A-z0-9?!@#$%^&()+\-*/_=.,;:\'<>]{6,24}', '#').'#u', $_POST['password'], $matches)) {
        else if (preg_match('#[A-z0-9\?!@\#\$%\^&\(\)+\-*/_=\.,;:\'<>]{6,24}#u', $_POST['password'], $matches)) {
            if ($_POST['password'] == $_POST['confirmation']) {
                $user_data['password'] = md5(md5($matches[0]));
            } else $errorMsg[] = "Паролі, що Ви ввели, не співпадають!";
        } else $wrongInput[] = "Пароль";

        if ((isset($_POST['web']) && !empty($_POST['web']))) {
            if (preg_match('~(?:(?:ftp|https?)://)?(?:[A-z0-9]+(\-|\.)?[A-z0-9]+?){1,20}\.[A-z]{2,7}(?:/|(?:[A-z0-9\-?\[\]\.=&%;#!]+/?)+)?~iu', $_POST['web'], $matches)) {
                $user_data['web'] = $matches[0];
            } else $wrongInput[] = "Веб-сторінка";
        }

        if ((!isset($_POST['phone1']) || empty($_POST['phone1']))) {
            $emptyFields[] = "Телефон";
        }
        else if (preg_match('#\+38\s?\(?0(?:[0-9]{2}\)?\s?[0-9]{3}|[0-9]{3}\)?\s?[0-9]{2}|[0-9]{4}\)?\s?[0-9])[\s-]?[0-9]{2}[\s-]?[0-9]{2}#', $_POST['phone1'], $matches)) {
            $user_data['phone1'] = $matches[0];
        } else $wrongInput[] = "Телефон";

        if ((isset($_POST['phone2']) && !empty($_POST['phone2']))) {
            if (preg_match('#\+38\s?\(?0(?:[0-9]{2}\)?\s?[0-9]{3}|[0-9]{3}\)?\s?[0-9]{2}|[0-9]{4}\)?\s?[0-9])[\s-]?[0-9]{2}[\s-]?[0-9]{2}#', $_POST['phone2'], $matches)) {
                $user_data['phone2'] = $matches[0];
            } else $wrongInput[] = "Телефон 2";
        }

        # Generating MySQL request
        if (!isset($emptyFields) && !isset($wrongInput) && !isset($errorMsg)) { # если проверка прошла успешно
            $fields = "(";
            $values = "(";
            if ($_POST['mode'] == 'edit') {
                if ($_SESSION['requested_id'] == $_SESSION['userID']) {
                    if (!$mysqli->query("DELETE FROM participants WHERE ID = {$_SESSION['userID']}")) {
                        header("HTTP/1.1 500 Internal Server Error");
                        $errors['errorMsg'] = "Помилка при роботі з базою даних. Спробуйте пізніше.";
                        echo json_encode($errors);
                        exit;
                    }
                    $fields .= 'ID, ';
                    $values .= $mysqli->real_escape_string($_SESSION['userID']).', ';
                }
                else {
                    header("HTTP/1.1 400 Bad Request");
                    $errors['errorMsg'] = "Ви не маєте права редагувати цей профіль!";
                    echo json_encode($errors);
                    exit;
                }
            }
            foreach ($user_data as $column=>$data) {
                $fields .= $column.", ";
                $values .= "'".$mysqli->real_escape_string($data)."', ";
            }
            if ($_SESSION['role'] == 'admin' && arg_exists_not_null($_POST['org']) && $_POST['org'] == 'yes') {
                $fields .= 'role, ';
                $values .= "'admin', ";
            }
            $fields = substr($fields, 0, strlen($fields)-2).")";
            $values = substr($values, 0, strlen($values)-2).")";
            if ($mysqli->query("INSERT INTO $table $fields VALUES $values")) {
                header("HTTP/1.1 200 OK");
                $response['title'] = "Вітаємо, {$user_data['name']}!";
                if ($_POST['mode'] == 'signup') {
                    $response['msg'] = "Вітаємо, Вас зареєстровано на конференцію ".CONF_NAME."!
                    Логін та пароль вислані на Вашу електронну поштову скриньку.";
                }
                elseif ($_POST['mode'] == 'edit') {
                    $response['msg'] = "Ваш профіль успішно відредаговано!
                    Зараз Вас буде перенаправлено до особистого кабінету.";
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