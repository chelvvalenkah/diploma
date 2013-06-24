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

function print_role($role) {
    switch ($role) {
        case 'visitor': return 'Відвідувач';
        case 'speaker': return 'Доповідач';
        case 'admin': return 'Організатор';
        default: return false;
    }
}

?>