<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Black Angel
 * Date: 18.06.13
 * Time: 13:38
 * To change this template use File | Settings | File Templates.
 */

    require_once('connect.php');
    require_once('constants.php');
    require_once('functions.php');
    require_once('conf_user.php');
    if (session_status() != PHP_SESSION_ACTIVE) session_start();

if (is_from_this_server() && arg_exists_not_null($_POST['action'])) {
    if ($_POST['action'] == "login") {
        if (arg_exists_not_null($_POST['email']) && arg_exists_not_null($_POST['password'])) {
            $email = $mysqli->real_escape_string($_POST['email']);
            $email_result = $mysqli->query("SELECT email FROM participants WHERE email = '$email'");
            if ($email_result->num_rows > 0) {
                $password = md5(md5($_POST['password']));
                $user_result = $mysqli->query("SELECT * FROM participants WHERE email = '$email' AND password = '$password'");
                if ($user_result->num_rows > 0) {
                    header("HTTP/1.1 200 OK");
                    $user = $user_result->fetch_object('conf_user');
                    $_SESSION['auth'] = true;
                    $_SESSION['userID'] = $user->ID;
                    $_SESSION['userName'] = $user->surname." ".$user->name;
                    $_SESSION['role'] = $user->role;
                    //$_SESSION['user'] = $user;
                    $response['title'] = "Авторизовано";
                    $response['msg'] = "Ласкаво просимо, $user->name! Ви вдало увійшли до свого акаунту.";
                    //$response['object'] = $user;
                    if (isset($_POST['remember']) && $_POST['remember'] == 'on') {
                        setcookie('login', $user->email, time()+60*60*24*30);
                        setcookie('hash', $user->password, time()+60*60*24*30);
                    }
                    //$user_row = $user_result->fetch_assoc();
                    //$user = array('id' => $user_row['ID'], 'surname' => $user_row['surname'], 'name' => $user_row['name']);
                    echo json_encode($response);
                    exit;
                }
                else {
                    header("HTTP/1.1 400 Bad Request");
                    $response['title'] = "Не авторизовано";
                    $response['msg'] = "Вказанний пароль не підходить до акаунту з електронною скринькою $_POST[email].
            Перевірте правильність введених даних та спробуйте ще раз.";
                    echo json_encode($response);
                    exit;
                }
            }
            else {
                header("HTTP/1.1 400 Bad Request");
                $response['title'] = "Не зареєстрований";
                $response['msg'] = "Акаунт з електронною скринькою $_POST[email] не зареєстрований";
                echo json_encode($response);
                exit;
            }
        }
        else {
            header("HTTP/1.1 400 Bad Request");
            $response['title'] = "Oops!";
            $response['msg'] = "Fuck you, hacker!";
            echo json_encode($response);
            exit;
        }
    }
    else if ($_POST['action'] == "logout") {
        header("HTTP/1.1 200 OK");
        $response['title'] = "Ви вийшли";
        $response['msg'] = "До побачення, {$_SESSION['userName']}! Ви вдало вийшли з акаунту.";
        logout();
        echo json_encode($response);
        exit;
    }
}
else {
    # Какая-то хуйня
    header("HTTP/1.1 400 Bad Request");
    if ($_SERVER['REQUEST_METHOD'] == "GET") {
        echo "Fuck you, hacker!";
        exit;
    }
    $response['title'] = "Oops!";
    $response['msg'] = "Fuck you, hacker!";
    //echo "Fuck you, hacker!";
    echo json_encode($response);
    exit;
}

?>