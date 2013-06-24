<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Black Angel
 * Date: 20.06.13
 * Time: 6:10
 * To change this template use File | Settings | File Templates.
 */

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
            <div class="span1"></div>
            <div class="span10">
<? endif; ?>