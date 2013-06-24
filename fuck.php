<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Black Angel
 * Date: 20.06.13
 * Time: 19:41
 * To change this template use File | Settings | File Templates.
 */

?>
<? if (false): ?>
<? include_once('header.php'); ?>
<? include_once('page_start.php') ?>
<? if (!authNeeded || (authNeeded && isset($_SESSION['auth']))): ?>
    <h1></h1>
<? else:
    include_once('unauthorized.php');
endif; ?>
<? include_once('page_finish.php') ?>
<? include_once('footer.php'); ?>
<? elseif (!isset($_GET)): ?>
    <div></div>
<? else:
    header("HTTP/1.1 200 OKay");
    echo json_encode($_SESSION['auth']);
endif; ?>