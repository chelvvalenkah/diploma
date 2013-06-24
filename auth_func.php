<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Black Angel
 * Date: 18.06.13
 * Time: 10:24
 * To change this template use File | Settings | File Templates.
 */

require_once('connect.php');

function is_authorized() {
    if (isset($_SESSION['userID'])) {
        return true;
    }
    else {

    }
}

?>