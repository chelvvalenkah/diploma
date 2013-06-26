<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Black Angel
 * Date: 20.06.13
 * Time: 6:44
 * To change this template use File | Settings | File Templates.
 */

require_once('constants.php');

?>
    <? if (!isset($_SESSION['calendar_page']) && $_SERVER['SCRIPT_NAME'] != HOME_PAGE): ?>
            </div> <!-- /Page content -->
    <? endif; ?>
<? if (!sidebar): ?>
            </div> <!-- /Page content -->
            <div class="span1"></div>
<? endif; ?>
        </div> <!-- <div id="page" class="row-fluid"> -->
    </div> <!-- <div id="page-container"> -->