<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Black Angel
 * Date: 20.06.13
 * Time: 6:10
 * To change this template use File | Settings | File Templates.
 */

require_once('constants.php');

?>
    <div id="page-container">
        <div id="page" class="row-fluid">
            <!-- Page -->
<? if (sidebar): ?>
            <div class="span2">
                <!-- Sidebar content -->
                <h3>Sidebar</h3>
            </div>
            <div class="span10">
<? else: ?>
    <? if (!isset($_SESSION['calendar_page']) && $_SERVER['SCRIPT_NAME'] != HOME_PAGE): ?>
            <div class="span1"></div>
            <div class="span10">
    <? endif; ?>
<? endif; ?>