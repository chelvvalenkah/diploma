<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Black Angel
 * Date: 19.06.13
 * Time: 23:29
 * To change this template use File | Settings | File Templates.
 */

define('authNeeded', true);
define('sidebar', true);
require_once('constants.php');
if (session_status() != PHP_SESSION_ACTIVE) session_start(); # PHP >= 5.4.0

?>
<? include_once('header.php'); ?>
<? if (!authNeeded || (authNeeded && isset($_SESSION['auth']))): ?>
<? include_once('page_start.php') ?>
                <!-- Page content -->



<? include_once('page_finish.php') ?>

    <!-- Modal -->
    <div id="AJAXresponse" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="AJAXresponseLabel" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="AJAXresponseLabel">Modal header</h3>
        </div>
        <div class="modal-body">
            <p>One fine body…</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" data-dismiss="modal" aria-hidden="true">ОК</button>
        </div>
    </div>

    <!-- Some scripts -->
    <script type="text/javascript" id="pageJS">
        // Initializing AJAX response modal dialog
        $('#pageAJAXresponse').modal({
            backdrop: 'static',
            keyboard: false,
            show: false
        });

    </script>

<? else:
    include_once('unauthorized.php');
endif; ?>
<? include_once('footer.php'); ?>