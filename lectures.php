<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Black Angel
 * Date: 20.06.13
 * Time: 5:56
 * To change this template use File | Settings | File Templates.
 */

define('authNeeded', true);
define('sidebar', true);
require_once('constants.php');
require_once('functions.php');
if (session_status() != PHP_SESSION_ACTIVE) session_start(); # PHP >= 5.4.0

?>
<?/* if (!isset($_POST['source']) && !isset($_GET['id'])): */?>
<?
$_SESSION['order_by'] = "date ASC, time ASC, flow_ID ASC";
if (arg_exists_not_null($_GET['source']) && ($_GET['source'] != 'speakerApplications' ? arg_exists_not_null($_GET['tab']) : true) && arg_exists_not_null($_GET['sort_by']) && arg_exists_not_null($_GET['dir'])) {
    $_GET['source'] != 'speakerApplications' ? $_SESSION['active_tab'] = $_GET['tab'] : NULL;
    $_SESSION['sort_col'] = $_GET['sort_by'];
    $_SESSION['caret_dir'] = $_GET['dir'];
    switch ($_GET['dir']) {
        case 'dropdown':
            $_SESSION['sort_dir'] = " DESC";
            break;
        case 'dropup':
        default:
            $_SESSION['sort_dir'] = " ASC";
    }
    if ($_GET['sort_by'] == 'date, time') {
        $_SESSION['order_by'] = "flow_ID ASC";
        $_SESSION['pre_order_by'] = "date".$_SESSION['sort_dir'].", time".$_SESSION['sort_dir'].", ";
    }
    else $_SESSION['pre_order_by'] = $_GET['sort_by'].$_SESSION['sort_dir'].", ";
}
else {
    if ($_SESSION['role'] == 'admin') $_SESSION['active_tab'] = 'valid';
    else $_SESSION['active_tab'] = 'ready';
    $_SESSION['sort_col'] = "date, time";
    $_SESSION['pre_order_by'] = "";
    $_SESSION['sort_dir'] = " ASC";
    $_SESSION['caret_dir'] = "dropup";
    //$_SESSION['order_by'] = "date, time, flow_ID";
}
?>

<!-- Lections list -->
<? include_once('header.php'); ?>
<? if (!authNeeded || (authNeeded && isset($_SESSION['auth']))): ?>
<? include_once('page_start.php') ?>
                <!-- Page content -->

                <? if ($_SESSION['role'] == 'admin'): ?>
                <!-- ADMIN -->
                <legend>Подані доповіді</legend>
                <div id="adminApplications-container">
                    <div class="tabbable" id="adminApplications"> <!-- Only required for left/right tabs -->
                    <?php
                        $where_start = "WHERE lectures.status ";
                        $tabs[] = array ('name' => 'valid');
                        $tabs[] = array ('name' => 'pending');
                        $tabs[] = array ('name' => 'approved');
                        $tabs[] = array ('name' => 'ready');
                        $tabs[] = array ('name' => 'all');
                        foreach ($tabs as $index=>&$tab) {
                            $tab['active'] = $_SESSION['active_tab'] == $tab['name'] ? true : false;
                            $tab['where'] = $tab['name'] == 'all' ? false : true;
                        }
                        $tabs[0]['filter'] = "IN ('pending', 'approved', 'ready')";
                        $tabs[1]['filter'] = "= 'pending'";
                        $tabs[2]['filter'] = "= 'approved'";
                        $tabs[3]['filter'] = "= 'ready'";
                        $tabs[4]['filter'] = NULL;
                    ?>
                        <input type="hidden" name="sort_col" value="<?=$_SESSION['sort_col']?>">
                        <ul class="nav nav-tabs">
                            <!-- Generating tabs -->
                            <? for ($i = 0; $i < count($tabs); $i++): ?>
                            <li <?if ($tabs[$i]['active']):?> class="active"<?endif;?>>
                                <a href="#adminApplications-<?=$tabs[$i]['name']?>" data-toggle="tab"><?=status_to_tabname($tabs[$i]['name'])?></a>
                            </li>
                            <? endfor; ?>
                        </ul>
                        <div class="tab-content with-controls">
                            <!-- Generating tab contents -->
                        <? for ($i = 0; $i < count($tabs); $i++): ?>
                            <div class="tab-pane<?=$tabs[$i]['active'] ? ' active' : ''?>" id="adminApplications-<?=$tabs[$i]['name']?>">
                                <table class="table table-hover">
                                    <thead>
                                    <tr class="<?=$_SESSION['caret_dir']?>">
                                        <th class="unsortable">№</th>
                                        <th><span name="id">Доповідь</span></th>
                                        <th><span name="speaker">Доповідач</span></th>
                                        <th><span name="duration">Тривалість</span></th>
                                        <th><span name="status">Статус</span></th>
                                        <th><span name="date, time">Дата</span></th>
                                        <th><span name="flow_ID">Потік</span></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $lectures_result = $mysqli->query("
                                                    SELECT lectures.id, lectures.title, lectures.speaker_ID,
                                                    CONCAT(participants.surname, \" \", participants.name) AS speaker, lectures.duration,
                                                    lectures.status, DATE_FORMAT(dates.date, '%e %M') AS date,
                                                    DATE_FORMAT(lectures.time, '%H:%i') AS time, lectures.flow_ID, flows.name AS flow
                                                    FROM lectures
                                                    LEFT JOIN participants ON lectures.speaker_ID = participants.ID
                                                    LEFT JOIN dates ON lectures.date_ID = dates.ID
                                                    LEFT JOIN flows ON lectures.flow_ID = flows.ID"
                                                    .PHP_EOL.($tabs[$i]['where'] ? $where_start.$tabs[$i]['filter'] : '').PHP_EOL.
                                                    "ORDER BY {$_SESSION['pre_order_by']}{$_SESSION['order_by']}
                                                ");
                                        $num = 1;
                                        # Are there any rows?
                                        if (!$lectures_result->num_rows): ?>
                                        <tr>
                                            <td colspan="7" style="text-align: center;">
                                                Нічого не знайдено
                                            </td>
                                        </tr>
                                        <? else:
                                            while ($lectures_row = $lectures_result->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <input type="hidden" name="id" value="<?=$lectures_row['id']?>" />
                                            <input type="hidden" name="status" value="<?=$lectures_row['status']?>" />
                                            <td><?=$num?></td>
                                            <td><a href="<?=APPLY_URL."?view=".$lectures_row['id']?>"><?=$lectures_row['title']?></a></td>
                                            <td><a href="<?=PROFILE_URL."?id=".$lectures_row['speaker_ID']?>"><?=$lectures_row['speaker']?></a></td>
                                            <td><?=$lectures_row['duration']?> хвилин</td>
                                            <td><?=print_status($lectures_row['status'])?></td>
                                            <td class="datetime"><span><?=$lectures_row['date']?></span> <span><?=$lectures_row['time']?></span></td>
                                            <td data-toggle="tooltip" title="<?=$lectures_row['flow']?>"><a href="<?=FLOW_URL."?id=".$lectures_row['flow_ID']?>"><?=$lectures_row['flow_ID']?></td>
                                        </tr>
                                            <?php
                                            $num++;
                                            endwhile;
                                        endif;
                                        ?>
                                    </tbody>
                                </table>

                                <!-- Tab controls -->
                                <form id="tabControls-<?=$tabs[$i]['name']?>" class="tableForm form-horizontal text-center" action="lectures_handler.php" method="post">
                                    <input type="hidden" name="source" value="adminApplications" />
                                    <input type="hidden" name="lectureID" value="undefined" />
                                    <input type="hidden" name="status" value="undefined" />
                                    <input type="hidden" name="action" value="undefined" />
                                    <button type="submit" class="btn btn-success" name="approved" disabled="disabled"><i class="icon-ok"></i> Прийняти</button>
                                    <button type="submit" class="btn btn-danger" name="rejected" disabled="disabled"><i class="icon-remove"></i> Відхилити</button>
                                    <button type="button" onclick="location.href='<?=APPLY_URL.'?edit='.$_SESSION['requested_id']?>'"
                                            class="btn btn-primary hide" name="addSchedule" disabled="disabled"><i class="icon-th-list"></i> Додати в розклад</button>
                                    <button type="button" onclick="location.href='<?=APPLY_URL.'?edit='.$_SESSION['requested_id']?>'"
                                            class="btn btn-primary" name="editSchedule" disabled="disabled"><i class="icon-refresh"></i> Редагувати</button>
                                </form>
                            </div>
                        <? endfor; ?>
                        </div>
                    </div>
                </div>


                <? elseif ($_SESSION['role'] == 'speaker'):; ?>
                <!-- SPEAKER -->
                <legend>Ваші доповіді</legend>
                <div id="speakerApplications-container">
                    <div class="tabbable" id="speakerApplications"> <!-- Only required for left/right tabs -->
                        <input type="hidden" name="sort_col" value="<?=$_SESSION['sort_col']?>">
                        <table class="table table-hover table-bordered">
                            <thead>
                            <tr class="<?=$_SESSION['caret_dir']?>">
                                <th class="unsortable">№</th>
                                <th><span name="id">Доповідь</span></th>
                                <th><span name="duration">Тривалість</span></th>
                                <th><span name="status">Статус</span></th>
                                <th><span name="date, time">Дата та час</span></th>
                                <th><span name="flow_ID">Потік</span></th>
                            </tr>
                            </thead>
                            <tbody>
                                <?php
                                $lectures_result = $mysqli->query("
                                                                SELECT lectures.id, lectures.title, lectures.duration,
                                                                lectures.status, DATE_FORMAT(dates.date, '%e %M') AS date,
                                                                DATE_FORMAT(lectures.time, '%H:%i') AS time, lectures.flow_ID,
                                                                flows.name AS flow, venues.name AS place
                                                                FROM lectures
                                                                LEFT JOIN participants ON lectures.speaker_ID = participants.ID
                                                                LEFT JOIN dates ON lectures.date_ID = dates.ID
                                                                LEFT JOIN flows ON lectures.flow_ID = flows.ID
                                                                LEFT JOIN venues ON flows.venue_ID = venues.ID
                                                                WHERE lectures.speaker_ID = {$_SESSION['userID']}
                                                                ORDER BY {$_SESSION['pre_order_by']}{$_SESSION['order_by']}
                                                            ");
                                $num = 1;
                                # Are there any rows?
                                if (!$lectures_result->num_rows): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">
                                        Нічого не знайдено
                                    </td>
                                </tr>
                                <? else:
                                    while ($lectures_row = $lectures_result->fetch_assoc()):
                                ?>
                                <tr>
                                    <input type="hidden" name="id" value="<?=$lectures_row['id']?>" />
                                    <input type="hidden" name="status" value="<?=$lectures_row['status']?>" />
                                    <td><?=$num?></td>
                                    <td><a href="<?=APPLY_URL."?view=".$lectures_row['id']?>"><?=$lectures_row['title']?></a></td>
                                    <td><?=$lectures_row['duration']?> хвилин</td>
                                    <td><?=print_status($lectures_row['status'])?></td>
                                    <td class="datetime"><span><?=$lectures_row['date']?></span> <span><?=$lectures_row['time']?></span></td>
                                    <td data-toggle="tooltip" title="<?=$lectures_row['place']?>"><a href="<?=FLOW_URL."?id=".$lectures_row['flow_ID']?>"><?=$lectures_row['flow']?></td>
                                </tr>
                                    <?php
                                    $num++;
                                    endwhile;
                                endif;
                                ?>
                            </tbody>
                        </table>

                        <div class="alert alert-block hide">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <h4>Увага!</h4>
                            <p>Нажаль, відхилені та готові заявки не можна редагувати.<br />
                                При винекненні будь-яких питань Ви можете звернутись до організаторів конференції.</p>
                        </div>

                        <!-- Tab controls -->
                        <form class="tableForm form-horizontal text-center" action="lectures_handler.php" method="post">
                            <input type="hidden" name="source" value="speakerApplications" />
                            <input type="hidden" name="lectureID" value="undefined" />
                            <input type="hidden" name="status" value="undefined" />
                            <input type="hidden" name="action" value="undefined" />
                            <button type="submit" class="btn btn-success" name="pending" disabled="disabled"><i class="icon-ok"></i> Подати знову</button>
                            <button type="submit" class="btn btn-danger" name="withdrawn" disabled="disabled"><i class="icon-remove"></i> Відкликати</button>
                            <a href="#"
                                    class="btn btn-primary disabled" name="edit"><i class="icon-refresh"></i> Редагувати</a>
                        </form>
                    </div>
                </div>
                <? endif; ?>


                <? if ($_SESSION['role'] != 'admin'): ?>
                <!-- VISITOR -->
                <legend>Ваші реєстрації</legend>
                <div id="visitorApplications-container">
                    <div class="tabbable" id="visitorApplications"> <!-- Only required for left/right tabs -->
                        <?php
                        $tabs[] = array();
                        $tabs[0] = array ('name' => 'ready');
                        $tabs[1] = array ('name' => 'registrations');
                        foreach ($tabs as $index=>&$tab) {
                            $tab['active'] = $_SESSION['active_tab'] == $tab['name'] ? true : false;
                        }
                        $tabs[0]['query'] = "SELECT lectures.id, lectures.title, lectures.speaker_ID,
                                                CONCAT(participants.surname, \" \", participants.name) AS speaker, lectures.duration,
                                                DATE_FORMAT(dates.date, '%e %M') AS date, DATE_FORMAT(lectures.time, '%H:%i') AS time,
                                                lectures.flow_ID, flows.name AS flow, venues.name AS place
                                                FROM lectures
                                                LEFT JOIN participants ON lectures.speaker_ID = participants.ID
                                                LEFT JOIN dates ON lectures.date_ID = dates.ID
                                                LEFT JOIN flows ON lectures.flow_ID = flows.ID
                                                LEFT JOIN venues ON flows.venue_ID = venues.ID
                                                WHERE lectures.status = 'ready'
                                                ORDER BY {$_SESSION['pre_order_by']}{$_SESSION['order_by']}";
                        $tabs[1]['query'] = "SELECT lectures.id, lectures.title,
                                                CONCAT(participants.surname, \" \", participants.name) AS speaker, lectures.duration,
                                                DATE_FORMAT(dates.date, '%e %M') AS date, DATE_FORMAT(lectures.time, '%H:%i') AS time,
                                                flows.name AS flow, venues.name AS place
                                                FROM registrations
                                                LEFT JOIN lectures ON registrations.lecture_ID = lectures.ID
                                                LEFT JOIN participants ON lectures.speaker_ID = participants.ID
                                                LEFT JOIN dates ON lectures.date_ID = dates.ID
                                                LEFT JOIN flows ON lectures.flow_ID = flows.ID
                                                LEFT JOIN venues ON flows.venue_ID = venues.ID
                                                WHERE registrations.visitor_ID = '{$_SESSION['userID']}'
                                                ORDER BY {$_SESSION['pre_order_by']}{$_SESSION['order_by']}";
                        ?>
                        <input type="hidden" name="sort_col" value="<?=$_SESSION['sort_col']?>">
                        <ul class="nav nav-tabs">
                            <!-- Generating tabs -->
                            <? for ($i = 0; $i < count($tabs); $i++): ?>
                            <li <?if ($tabs[$i]['active']):?> class="active"<?endif;?>>
                                <a href="#visitorApplications-<?=$tabs[$i]['name']?>" data-toggle="tab"><?=status_to_tabname($tabs[$i]['name'])?></a>
                            </li>
                            <? endfor; ?>
                        </ul>
                        <div class="tab-content with-controls">
                            <!-- Generating tab contents -->
                            <? for ($i = 0; $i < count($tabs); $i++): ?>
                            <div class="tab-pane<?=$tabs[$i]['active'] ? ' active' : ''?>" id="visitorApplications-<?=$tabs[$i]['name']?>">
                                <table class="table table-hover">
                                    <thead>
                                    <tr class="<?=$_SESSION['caret_dir']?>">
                                        <th class="unsortable">№</th>
                                        <th><span name="id">Доповідь</span></th>
                                        <th><span name="speaker">Доповідач</span></th>
                                        <th><span name="duration">Тривалість</span></th>
                                        <? if ($tabs[$i]['name'] == 'ready'): ?>
                                        <th><span name="status">Реєстрація</span></th>
                                        <? endif; ?>
                                        <th><span name="date, time">Дата</span></th>
                                        <th><span name="flow_ID">Потік</span></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $lectures_result = $mysqli->query($tabs[$i]['query']);
                                        $num = 1;
                                        # Are there any rows?
                                        if (!$lectures_result->num_rows): ?>
                                        <tr>
                                            <td colspan="<?=$tabs[$i]['name'] == 'ready' ? '7' : '6'?>" style="text-align: center;">
                                                Нічого не знайдено
                                            </td>
                                        </tr>
                                        <? else:
                                            while ($lectures_row = $lectures_result->fetch_assoc()):
                                                if ($tabs[$i]['name'] == 'ready'):
                                                    $registered_result = $mysqli->query("SELECT * FROM registrations
                                                      WHERE visitor_ID = '{$_SESSION['userID']}' AND lecture_ID = '{$lectures_row['id']}'");
                                                    if ($registered_result->num_rows > 0):
                                                        $reg_status = 'registered';
                                                    else:
                                                        $reg_status = 'unregistered';
                                                    endif;
                                                endif;
                                        ?>
                                            <tr>
                                                <input type="hidden" name="id" value="<?=$lectures_row['id']?>" />
                                                <input type="hidden" name="status" value="<?=$tabs[$i]['name'] == 'ready' ? $reg_status : 'registered'?>" />
                                                <td><?=$num?></td>
                                                <td><a href="<?=APPLY_URL."?view=".$lectures_row['id']?>"><?=$lectures_row['title']?></a></td>
                                                <td><a href="<?=PROFILE_URL."?id=".$lectures_row['speaker_ID']?>"><?=$lectures_row['speaker']?></a></td>
                                                <td><?=$lectures_row['duration']?> хвилин</td>
                                                <? if ($tabs[$i]['name'] == 'ready'): ?>
                                                <td><?=print_status($reg_status)?></td>
                                                <? endif; ?>
                                                <td class="datetime"><span><?=$lectures_row['date']?></span> <span><?=$lectures_row['time']?></span></td>
                                                <td data-toggle="tooltip" title="<?=$lectures_row['place']?>"><a href="<?=FLOW_URL."?id=".$lectures_row['flow_ID']?>"><?=$lectures_row['flow']?></td>
                                            </tr>
                                                <?php
                                                $num++;
                                            endwhile;
                                        endif;
                                        ?>
                                    </tbody>
                                </table>

                                <!-- Tab controls -->
                                <form id="tabControls-<?=$tabs[$i]['name']?>" class="tableForm form-horizontal text-center" action="lectures_handler.php" method="post">
                                    <input type="hidden" name="source" value="visitorApplications" />
                                    <input type="hidden" name="lectureID" value="undefined" />
                                    <input type="hidden" name="status" value="undefined" />
                                    <input type="hidden" name="action" value="undefined" />
                                    <? if ($tabs[$i]['name'] == 'ready'): ?>
                                    <button type="submit" class="btn btn-success" name="register" disabled="disabled"><i class="icon-ok"></i> Зареєструватися</button>
                                    <? endif; ?>
                                    <button type="submit" class="btn btn-danger" name="unregister" disabled="disabled"><i class="icon-remove"></i> Відізвати реєстрацію</button>
                                </form>
                            </div>
                            <? endfor; ?>
                        </div>
                    </div>
                </div>
                <? endif; ?>

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

    <!-- Lectures scripts -->
    <script type="text/javascript" id="pageJS">
        $(document).ready(function() {
            var sort_col = $('input[name=sort_col]').val();
            $('table th:not(.unsortable)').find("span[name='"+sort_col+"']").addClass('by');
        });

        //$('table th:not(.unsortable)').find("span[name='<?=$_SESSION['sort_col']?>']").addClass('by');

        // Initializing AJAX response modal dialog
        $('#pageAJAXresponse').modal({
            backdrop: 'static',
            keyboard: false,
            show: false
        });

        // Hint: what flow does flow_ID correspond to?
        $('td[data-toggle=tooltip]').tooltip({
            placement: 'right'
        });

        // Script for sorting tables
        $('table tr.dropdown th:not(.unsortable), table tr.dropup th:not(.unsortable)').click(function() {
            if ($(this).children('span:first-child').hasClass('by')) {
                if ($(this).parent().hasClass('dropdown')) {
                    $(this).parent().removeClass('dropdown').addClass('dropup');
                }
                else if ($(this).parent().hasClass('dropup')) {
                    $(this).parent().removeClass('dropup').addClass('dropdown');
                }
            }
            else {
                $(this).parent().find('span.by').removeClass('by');
                $(this).parent().removeAttr('class').addClass('dropup');
                $(this).children('span:first-child').addClass('by');
            }
            var sort_by = $(this).children('span').attr('name');
            sort_by = fixedEncodeURIComponent(sort_by);
            var dir = $(this).parent().attr('class');
            dir = fixedEncodeURIComponent(dir);
            var tabbable = $(this).parents('.tabbable').attr('id');
            if (tabbable != 'speakerApplications') {
                var tab = $(this).parents('.tab-pane').attr('id');
                tab = tab.split('-')[1];
                tab = fixedEncodeURIComponent(tab);
                var getRequest = "tab="+tab+"&sort_by="+sort_by+"&dir="+dir+"&source="+tabbable;
            }
            else {
                var getRequest = "sort_by="+sort_by+"&dir="+dir+"&source="+tabbable;
            }
            $('#page-container').load('<?=$_SERVER['SCRIPT_NAME']?>?'+getRequest+' #page', function() {
				eval($('script#pageJS').text());
            });
        });

        function disableLink(button) {
            button.addClass('disabled').click(function (event) {
                event.preventDefault();
            });
            button.attr('href', "#");
        }

        function enableLink(button) {
            button.attr('href', "<?=APPLY_URL.'?edit='?>"+
                    $('div#speakerApplications form.tableForm input[name=lectureID]').val());
            button.removeClass('disabled').unbind('click');
        }

        // Controlling form controls buttons
        $('/*div.tab-pane */table tbody tr').click(function () {
            if (!$(this).hasClass('selected')) {
                $('tr').removeClass('selected');
                $(this).addClass('selected');
                var jqTrLectureID = $(this).children('input[name=id]');
                var jqTrStatus = $(this).children('input[name=status]');
                var jqControlsLectureID = $(this).parents('div:first').find('form.tableForm input[name=lectureID]');
                var jqControlsStatus = $(this).parents('div:first').find('form.tableForm input[name=status]');
                var jqControlsButtons = $(this).parents('div:first').find('.btn');
                jqControlsLectureID.val(jqTrLectureID.val());
                jqControlsStatus.val(jqTrStatus.val());
                var status = jqControlsStatus.val();
                switch (status) {
                    <? if ($_SESSION['role'] == 'admin'): ?>
                    case 'pending': {
                        jqControlsButtons.filter('button[name=approved]').prop('disabled', false);
                        jqControlsButtons.filter('button[name=rejected]').prop('disabled', false);
                        jqControlsButtons.filter('button[name=addSchedule]').prop('disabled', true);
                        $(this).parents('div.tab-pane').find('button[name=editSchedule]').addClass('hide');
                        $(this).parents('div.tab-pane').find('button[name=addSchedule]').removeClass('hide');
                        break;
                    }
                    case 'approved': {
                        jqControlsButtons.filter('button[name=approved]').prop('disabled', true);
                        jqControlsButtons.filter('button[name=rejected]').prop('disabled', false);
                        jqControlsButtons.filter('button[name=addSchedule]').prop('disabled', false);
                        /*$(this).parents('div.tab-pane').find('button[name=addSchedule]').addClass('hide');
                        $(this).parents('div.tab-pane').find('button[name=editSchedule]').removeClass('hide');*/
                        $(this).parents('div.tab-pane').find('button[name=editSchedule]').addClass('hide');
                        $(this).parents('div.tab-pane').find('button[name=addSchedule]').removeClass('hide');
                        break;
                    }
                    case 'rejected': {
                        jqControlsButtons.filter('button[name=approved]').prop('disabled', false);
                        jqControlsButtons.filter('button[name=rejected]').prop('disabled', true);
                        jqControlsButtons.filter('button[name=editSchedule]').prop('disabled', true);
                        $(this).parents('div.tab-pane').find('button[name=addSchedule]').addClass('hide');
                        $(this).parents('div.tab-pane').find('button[name=editSchedule]').removeClass('hide');
                        break;
                    }
                    case 'ready': {
                        jqControlsButtons.filter('button[name=approved]').prop('disabled', true);
                        jqControlsButtons.filter('button[name=rejected]').prop('disabled', false);
                        jqControlsButtons.filter('button[name=editSchedule]').prop('disabled', false);
                        $(this).parents('div.tab-pane').find('button[name=addSchedule]').addClass('hide');
                        $(this).parents('div.tab-pane').find('button[name=editSchedule]').removeClass('hide');
                        break;
                    }
                    case 'withdrawn': {
                        jqControlsButtons.filter('button[name=approved]').prop('disabled', true);
                        jqControlsButtons.filter('button[name=rejected]').prop('disabled', true);
                        jqControlsButtons.filter('button[name=editSchedule]').prop('disabled', true);
                        $(this).parents('div.tab-pane').find('button[name=addSchedule]').addClass('hide');
                        $(this).parents('div.tab-pane').find('button[name=editSchedule]').removeClass('hide');
                        break;
                    }
                    <? elseif ($_SESSION['role'] == 'speaker'): ?>
                    case 'pending':
                    case 'approved': {
                        $(this).parents('div:first').find('div.alert').addClass('hide');
                        jqControlsButtons.filter('button[name=pending]').prop('disabled', true);
                        jqControlsButtons.filter('button[name=withdrawn]').prop('disabled', false);
                        enableLink(jqControlsButtons.filter('a[name=edit]'));
                        break;
                    }
                    case 'rejected':
                    case 'ready': {
                        jqControlsButtons.filter('button[name=pending]').prop('disabled', true);
                        jqControlsButtons.filter('button[name=withdrawn]').prop('disabled', true);
                        disableLink(jqControlsButtons.filter('a[name=edit]'));
                        $(this).parents('div:first').find('div.alert').removeClass('hide');
                        break;
                    }
                    case 'withdrawn': {
                        $(this).parents('div:first').find('div.alert').addClass('hide');
                        jqControlsButtons.filter('button[name=pending]').prop('disabled', false);
                        jqControlsButtons.filter('button[name=withdrawn]').prop('disabled', true);
                        enableLink(jqControlsButtons.filter('a[name=edit]'));
                        break;
                    }
                    <? endif; ?>
                    <? if ($_SESSION['role'] != 'admin'): ?>
                    case 'pending':
                    case 'approved': {
                        $(this).parents('div:first').find('div.alert').addClass('hide');
                        jqControlsButtons.filter('button[name=pending]').prop('disabled', true);
                        jqControlsButtons.filter('button[name=withdrawn]').prop('disabled', false);
                        enableLink(jqControlsButtons.filter('a[name=edit]'));
                        break;
                    }
                    case 'rejected':
                    case 'ready': {
                        jqControlsButtons.filter('button[name=pending]').prop('disabled', true);
                        jqControlsButtons.filter('button[name=withdrawn]').prop('disabled', true);
                        disableLink(jqControlsButtons.filter('a[name=edit]'));
                        $(this).parents('div:first').find('div.alert').removeClass('hide');
                        break;
                    }
                    case 'withdrawn': {
                        $(this).parents('div:first').find('div.alert').addClass('hide');
                        jqControlsButtons.filter('button[name=pending]').prop('disabled', false);
                        jqControlsButtons.filter('button[name=withdrawn]').prop('disabled', true);
                        enableLink(jqControlsButtons.filter('a[name=edit]'));
                        break;
                    }
                    <? endif; ?>
                    <? if ($_SESSION['role'] != 'admin'): ?>
                    case 'registered': {
                        jqControlsButtons.filter('button[name=register]').prop('disabled', true);
                        jqControlsButtons.filter('button[name=unregister]').prop('disabled', false);
                        break;
                    }
                    case 'unregistered': {
                        jqControlsButtons.filter('button[name=register]').prop('disabled', false);
                        jqControlsButtons.filter('button[name=unregister]').prop('disabled', true);
                        break;
                    }
					<? endif; ?>
                }

            }
            else {
                $(this).removeClass('selected');
                var jqControlsButtons = $(this).parents('div:first').find('button[type=submit]').prop('disabled', true);
                <? if ($_SESSION['role'] == 'admin'): ?>
                $(this).parents('div.tab-pane').find('button[name=addSchedule]').addClass('hide');
                $(this).parents('div.tab-pane').find('button[name=editSchedule]').removeClass('hide');
                <? endif; ?>
                <? if ($_SESSION['role'] == 'speaker'): ?>
                disableLink(jqControlsButtons.filter('a[name=edit]'));
                <? endif; ?>
            }
        });

        // Controlling selection on tabs change
        $('ul.nav.nav-tabs li').click(function () {
            $(this).parents('div.tabbable').find('tr.selected').removeClass('selected');
        });

        // Initialized hidden field "action" in tableForms
        $('.tableForm :submit').click(function() {
            this.form.action.value = this.name;
        });

        // Posting form using ajax
        $('.tableForm').submit(function(event) {
            event.preventDefault();
            var formData = $(this).serialize();
            var sort_by = $(this).parents('div:first').find('span.by').attr('name');
            sort_by = fixedEncodeURIComponent(sort_by);
            var dir = $(this).parents('divfirst').find('thead tr:first-child').attr('class');
            dir = fixedEncodeURIComponent(dir);
            var tabbable = $(this).parents('.tabbable').attr('id');
            if (tabbable != 'speakerApplications') {
                var tab = $(this).parents('div.tab-pane.active').attr('id');
                tab = tab.split('-')[1];
                tab = fixedEncodeURIComponent(tab);
                var getRequest = "tab="+tab+"&sort_by="+sort_by+"&dir="+dir+"&source="+tabbable;
            }
            else {
                var getRequest = "sort_by="+sort_by+"&dir="+dir+"&source="+tabbable;
            }
            $.ajax({
                url: $(this).attr('action'),
                type: "POST",
                data: formData,
                dataType: "json",
                success: function(data) {
                    $('#page-container').load('<?=$_SERVER['SCRIPT_NAME']?>?'+getRequest+' #page', function() {
                        eval($('script#pageJS').text());
                    });
                }
            });
        });

    </script>


<? else:
    include_once('unauthorized.php');
endif; ?>
<? include_once('footer.php'); ?>
<?/* elseif (isset($_GET['id'])): ?>
<!-- Lections view -->
<? include_once('header.php'); ?>
<? include_once('page_start.php') ?>
                <!-- Page content -->
                <legend>Подання доповіді на конференцію</legend>




<? include_once('page_finish.php') ?>

    <!-- Some scripts -->
    <script type="text/javascript" id="pageJS">

    </script>
<? include_once('footer.php');?>
<? endif; */?>