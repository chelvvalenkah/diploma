<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Black Angel
 * Date: 18.06.13
 * Time: 3:41
 * To change this template use File | Settings | File Templates.
 */

require_once('connect.php');
#print_r($_POST);
$email = $mysqli->real_escape_string($_POST['email']);
$email_result = $mysqli->query("SELECT email FROM participants WHERE email = '$email'");
if ($email_result->num_rows > 0) {
    header("HTTP/1.1 409 Conflict");
    $msg = "Відвідувач з такою електронною скринькою вже зареєстрований!";
}
else {
    header("HTTP/1.1 200 OK");
    $msg = "Ваша електронна скринька відсутня в нашій базі :)";
}
echo json_encode($msg);
exit;

?>