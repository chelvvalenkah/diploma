<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Black Angel
 * Date: 18.06.13
 * Time: 17:56
 * To change this template use File | Settings | File Templates.
 */

require_once('connect.php');
require_once('constants.php');

function is_from_this_server() {
    $host_parts = explode(":", $_SERVER['HTTP_HOST']);
    return $host_parts[0] == $_SERVER['SERVER_NAME'];
}

function is_from_script($script_name) {
    if (arg_exists_not_null($_SERVER['HTTP_REFERER'])) {
        $referer_parts = explode("?", $_SERVER['HTTP_REFERER']);
        $referer_parts = explode("/", $referer_parts[0]);
        $referer = "/".$referer_parts[count($referer_parts)-1];
        return strtolower($referer) == $script_name;
    }
    else return false;
}

function arg_exists_not_null($argument_name) {
    return isset($argument_name) && !empty($argument_name);
}

function check_authorization($mysqli) {
    if (isset($_SESSION['auth'])) {
        return true;
    }
    else {
        if (arg_exists_not_null($_COOKIE['login']) && arg_exists_not_null($_COOKIE['hash'])) {
            $email = $mysqli->real_escape_string($_COOKIE['login']);
            $email_result = $mysqli->query("SELECT email FROM participants WHERE email = '$email'");
            if ($email_result->num_rows > 0) {
                $password = $_COOKIE['hash'];
                $user_result = $mysqli->query("SELECT * FROM participants WHERE email = '$email' AND password = '$password'");
                if ($user_result->num_rows > 0) {
                    $user = $user_result->fetch_object('conf_user');
                    $_SESSION['auth'] = true;
                    $_SESSION['userID'] = $user->ID;
                    $_SESSION['userName'] = $user->surname." ".$user->name;
                    $_SESSION['role'] = $user->role;
                    return true;
                }
                else { # cookie password is wrong
                    setcookie("login", "", time()-1);
                    setcookie("hash", "", time()-1);
                    return false;
                }
            }
            else { # cookie email is not registred
                setcookie("login", "", time()-1);
                setcookie("hash", "", time()-1);
                return false;
            }
        }
    }
}

function logout() {
    setcookie("login", "", time()-1);
    setcookie("hash", "", time()-1);
    /*unset($_SESSION['userID']);
    unset($_SESSION['userName']);*/
    session_unset();
    session_destroy();
    setcookie("PHPSESSID", "", time()-1);
}

/**
 * Checks POSTed input of registration form
 */
function check_user_info($mysqli, &$emptyFields, &$wrongInput, &$errorMsg) {
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
        $email_result = $mysqli->query("SELECT email FROM participants WHERE email = '$email'");
        if ($email_result->num_rows == NULL) {
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

    return $user_data;
}

function print_status($status) {
    switch ($status) {
        case 'pending': return 'нерозглянута';
        case 'approved': return 'прийнята';
        case 'rejected': return 'відхилена';
        case 'withdrawn': return 'відкликана';
        case 'ready': return 'затверджена';
        case 'registered': return 'зареєстрований';
        case 'unregistered': return 'незареєстрований';
        default: return false;
    }
}

function status_to_tabname($status) {
    switch ($status) {
        case 'valid': return 'Дійсні';
        case 'pending': return 'Нерозглянуті';
        case 'approved': return 'Прийняті';
        case 'rejected': return 'Відхилені';
        case 'withdrawn': return 'Відкликані';
        case 'ready': return $_SESSION['role'] == 'admin' ? 'Затверджені' : 'Список доповідей';
        case 'all': return 'Всі';
        case 'registrations': return 'Ваші реєстрації';
        default: return false;
    }
}

?>