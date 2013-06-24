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
<? if (!isset($_POST['source']) && !isset($_GET['id'])): ?>
    <?
    $_SESSION['order_by'] = "date ASC, time ASC, flow_ID ASC";
    if (arg_exists_not_null($_GET['source']) && arg_exists_not_null($_GET['sort_by']) && arg_exists_not_null($_GET['dir'])) {
        $_SESSION['active'] = $_GET['source'];
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
        $_SESSION['active'] = 'valid';
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
                    <!-- $_SESSION['order_by'] = "date, time, flow_ID"; -->
                    <legend>Подані доповіді</legend>
                    <div id="adminApplications-container">
                        <div class="tabbable" id="adminApplications"> <!-- Only required for left/right tabs -->
                            <input type="hidden" name="sort_col" value="<?=$_SESSION['sort_col']?>">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#adminApplications-all" data-toggle="tab">Всі</a></li>
                                <li><a href="#adminApplications-valid" data-toggle="tab">Дійсні</a></li>
                            </ul>
                            <div class="tab-content">
                            <?php
                                $where_start = "WHERE lectures.status ";
                                $tabs[] = array ('name' => 'valid');
                                $tabs[] = array ('name' => 'pending');
                                $tabs[] = array ('name' => 'approved');
                                $tabs[] = array ('name' => 'ready');
                                $tabs[] = array ('name' => 'all');
                                foreach ($tabs as $i=>&$value) {
                                    $tabs[$i]['active'] = $_SESSION['active'] == $tabs[$i]['name'] ? true : false;
                                    $tabs[$i]['where'] = $tabs[$i]['name'] == 'all' ? false : true;
                                }
                                $tabs[0]['filter'] = "IN ('pending', 'approved', 'ready')";
                                $tabs[1]['filter'] = "= 'pending'";
                                $tabs[2]['filter'] = "= 'approved'";
                                $tabs[3]['filter'] = "= 'ready'";
                                $tabs[4]['filter'] = NULL;
                                for ($i = 0; $i < count($tabs); $i++):
                            ?>
                                <div class="tab-pane<?=$tabs[$i]['active'] ? ' active' : ''?>" id="adminApplications-<?=$tabs[$i]['name']?>">
                                    <table class="table table-hover">
                                        <thead>
                                        <tr class="<?=$_SESSION['caret_dir']?>">
                                            <th class="unsortable">№</th>
                                            <th><span name="id">Доповідь</span></th>
                                            <th><span name="speaker">Доповідач</span></th>
                                            <th><span name="duration">Тривалість</span></th>
                                            <th><span name="status">Статус</span></th>
                                            <th><span name="date, time">Коли</span></th>
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
                                                        LEFT JOIN flows ON lectures.flow_ID = flows.ID
                                                        .($tabs[$i]['where'] ? $where_start.$tabs[$i]['filter'] : '').
                                                        ORDER BY {$_SESSION['pre_order_by']}{$_SESSION['order_by']}
                                                    ");
                                            $i = 1;
                                            while ($lectures_row = $lectures_result->fetch_assoc()):
                                                ?>
                                            <tr>
                                                <td><?=$i?></td>
                                                <td><a href="<?=LECTURES_URL."?id=".$lectures_row['id']?>"><?=$lectures_row['title']?></a></td>
                                                <td><a href="<?=PROFILE_URL."?id=".$lectures_row['speaker_ID']?>"><?=$lectures_row['speaker']?></a></td>
                                                <td><?=$lectures_row['duration']?> хвилин</td>
                                                <td><?=print_status($lectures_row['status'])?></td>
                                                <td><span><?=$lectures_row['date']?></span> <span><?=$lectures_row['time']?></span></td>
                                                <td data-toggle="tooltip" title="<?=$lectures_row['flow']?>"><a href="<?=FLOW_URL."?id=".$lectures_row['flow_ID']?>"><?=$lectures_row['flow_ID']?></td>
                                            </tr>
                                                <?php
                                                $i++;
                                            endwhile;
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            <? endfor; ?>

                                <div class="tab-pane active" id="adminApplications-valid">
                                    <!--
                                    <table class="table table-hover">
                                        <thead>
                                        <tr>
                                            <th>№</th>
                                            <th>Доповідь</th>
                                            <th>Доповідач</th>
                                            <th>Тривалість</th>
                                            <th>Статус</th>
                                            <th>Дії</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            <?php/*
                                                $lectures_result = $mysqli->query("
                                                    SELECT lectures.id, lectures.title, lectures.speaker_ID,
                                                    CONCAT(participants.surname, \" \", participants.name) AS speaker, lectures.duration,
                                                    lectures.status, dates.date, lectures.time, lectures.flow_ID, flows.name AS flow
                                                    FROM lectures
                                                    LEFT JOIN participants ON lectures.speaker_ID = participants.ID
                                                    LEFT JOIN dates ON lectures.date_ID = dates.ID
                                                    LEFT JOIN flows ON lectures.flow_ID = flows.ID
                                                    WHERE lectures.status IN ('pending', 'approved', 'ready')
                                                ");
                                                $i = 1;
                                                while ($lectures_row = $lectures_result->fetch_assoc()):
                                            ?>
                                            <tr>
                                                <td><?=$i?></td>
                                                <td><a href="<?=LECTURES_URL."?id=".$lectures_row['id']?>"><?=$lectures_row['title']?></a></td>
                                                <td><a href="<?=PROFILE_URL."?id=".$lectures_row['speaker_ID']?>"><?=$lectures_row['speaker']?></a></td>
                                                <td><?=$lectures_row['duration']?> хвилин</td>
                                                <td><?=print_status($lectures_row['status'])?></td>
                                                <td>
                                                    <form class="tableForm" action="lectures_handler.php" method="post">
                                                        <input type="hidden" name="source" value="adminApplications" />
                                                        <input type="hidden" name="lectureID" value="<?=$lectures_row['id']?>" />
                                                        <input type="hidden" name="action" value="undefined" />
                                                        <button type="submit" class="btn btn-link btn-table" name="approved"><i class="icon-ok"></i> Прийняти</button>
                                                        <button type="submit" class="btn btn-link btn-table" name="rejected"><i class="icon-remove"></i> Відхилити</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php
                                                $i++;
                                                endwhile;*/
                                            ?>
                                        </tbody>
                                    </table>
                                    -->
                                    <table class="table table-hover">
                                        <thead>
                                        <tr class="<?=$_SESSION['caret_dir']?>">
                                            <th class="unsortable">№</th>
                                            <th><span name="id">Доповідь</span></th>
                                            <th><span name="speaker">Доповідач</span></th>
                                            <th><span name="duration">Тривалість</span></th>
                                            <th><span name="status">Статус</span></th>
                                            <th><span name="date, time">Коли</span></th>
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
                                                    LEFT JOIN flows ON lectures.flow_ID = flows.ID
                                                    WHERE lectures.status IN ('pending', 'approved', 'ready')
                                                    ORDER BY {$_SESSION['pre_order_by']}{$_SESSION['order_by']}
                                                ");
                                            $i = 1;
                                            while ($lectures_row = $lectures_result->fetch_assoc()):
                                                ?>
                                            <tr>
                                                <td><?=$i?></td>
                                                <td><a href="<?=LECTURES_URL."?id=".$lectures_row['id']?>"><?=$lectures_row['title']?></a></td>
                                                <td><a href="<?=PROFILE_URL."?id=".$lectures_row['speaker_ID']?>"><?=$lectures_row['speaker']?></a></td>
                                                <td><?=$lectures_row['duration']?> хвилин</td>
                                                <td><?=print_status($lectures_row['status'])?></td>
                                                <td><span><?=$lectures_row['date']?></span> <span><?=$lectures_row['time']?></span></td>
                                                <td data-toggle="tooltip" title="<?=$lectures_row['flow']?>"><a href="<?=FLOW_URL."?id=".$lectures_row['flow_ID']?>"><?=$lectures_row['flow_ID']?></td>
                                            </tr>
                                                <?php
                                                $i++;
                                            endwhile;
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tab-pane" id="adminApplications-pending">
                                    <table class="table table-hover">
                                        <thead>
                                        <tr class="<?=$_SESSION['caret_dir']?>">
                                            <th class="unsortable">№</th>
                                            <th><span name="id">Доповідь</span></th>
                                            <th><span name="speaker">Доповідач</span></th>
                                            <th><span name="duration">Тривалість</span></th>
                                            <th><span name="status">Статус</span></th>
                                            <th><span name="date, time">Коли</span></th>
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
                                                    LEFT JOIN flows ON lectures.flow_ID = flows.ID
                                                    WHERE lectures.status = 'pending'
                                                    ORDER BY {$_SESSION['pre_order_by']}{$_SESSION['order_by']}
                                                ");
                                            $i = 1;
                                            while ($lectures_row = $lectures_result->fetch_assoc()):
                                                ?>
                                            <tr>
                                                <td><?=$i?></td>
                                                <td><a href="<?=LECTURES_URL."?id=".$lectures_row['id']?>"><?=$lectures_row['title']?></a></td>
                                                <td><a href="<?=PROFILE_URL."?id=".$lectures_row['speaker_ID']?>"><?=$lectures_row['speaker']?></a></td>
                                                <td><?=$lectures_row['duration']?> хвилин</td>
                                                <td><?=print_status($lectures_row['status'])?></td>
                                                <td><span><?=$lectures_row['date']?></span> <span><?=$lectures_row['time']?></span></td>
                                                <td data-toggle="tooltip" title="<?=$lectures_row['flow']?>"><a href="<?=FLOW_URL."?id=".$lectures_row['flow_ID']?>"><?=$lectures_row['flow_ID']?></td>
                                            </tr>
                                                <?php
                                                $i++;
                                            endwhile;
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tab-pane" id="adminApplications-approved">
                                    <table class="table table-hover">
                                        <thead>
                                        <tr class="<?=$_SESSION['caret_dir']?>">
                                            <th class="unsortable">№</th>
                                            <th><span name="id">Доповідь</span></th>
                                            <th><span name="speaker">Доповідач</span></th>
                                            <th><span name="duration">Тривалість</span></th>
                                            <th><span name="status">Статус</span></th>
                                            <th><span name="date, time">Коли</span></th>
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
                                                    LEFT JOIN flows ON lectures.flow_ID = flows.ID
                                                    WHERE lectures.status = 'approved'
                                                    ORDER BY {$_SESSION['pre_order_by']}{$_SESSION['order_by']}
                                                ");
                                            $i = 1;
                                            while ($lectures_row = $lectures_result->fetch_assoc()):
                                                ?>
                                            <tr>
                                                <td><?=$i?></td>
                                                <td><a href="<?=LECTURES_URL."?id=".$lectures_row['id']?>"><?=$lectures_row['title']?></a></td>
                                                <td><a href="<?=PROFILE_URL."?id=".$lectures_row['speaker_ID']?>"><?=$lectures_row['speaker']?></a></td>
                                                <td><?=$lectures_row['duration']?> хвилин</td>
                                                <td><?=print_status($lectures_row['status'])?></td>
                                                <td><span><?=$lectures_row['date']?></span> <span><?=$lectures_row['time']?></span></td>
                                                <td data-toggle="tooltip" title="<?=$lectures_row['flow']?>"><a href="<?=FLOW_URL."?id=".$lectures_row['flow_ID']?>"><?=$lectures_row['flow_ID']?></td>
                                            </tr>
                                                <?php
                                                $i++;
                                            endwhile;
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tab-pane" id="adminApplications-ready">
                                    <table class="table table-hover">
                                        <thead>
                                        <tr class="<?=$_SESSION['caret_dir']?>">
                                            <th class="unsortable">№</th>
                                            <th><span name="id">Доповідь</span></th>
                                            <th><span name="speaker">Доповідач</span></th>
                                            <th><span name="duration">Тривалість</span></th>
                                            <th><span name="status">Статус</span></th>
                                            <th><span name="date, time">Коли</span></th>
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
                                                    LEFT JOIN flows ON lectures.flow_ID = flows.ID
                                                    WHERE lectures.status = 'ready'
                                                    ORDER BY {$_SESSION['pre_order_by']}{$_SESSION['order_by']}
                                                ");
                                            $i = 1;
                                            while ($lectures_row = $lectures_result->fetch_assoc()):
                                                ?>
                                            <tr>
                                                <td><?=$i?></td>
                                                <td><a href="<?=LECTURES_URL."?id=".$lectures_row['id']?>"><?=$lectures_row['title']?></a></td>
                                                <td><a href="<?=PROFILE_URL."?id=".$lectures_row['speaker_ID']?>"><?=$lectures_row['speaker']?></a></td>
                                                <td><?=$lectures_row['duration']?> хвилин</td>
                                                <td><?=print_status($lectures_row['status'])?></td>
                                                <td><span><?=$lectures_row['date']?></span> <span><?=$lectures_row['time']?></span></td>
                                                <td data-toggle="tooltip" title="<?=$lectures_row['flow']?>"><a href="<?=FLOW_URL."?id=".$lectures_row['flow_ID']?>"><?=$lectures_row['flow_ID']?></td>
                                            </tr>
                                                <?php
                                                $i++;
                                            endwhile;
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tab-pane" id="adminApplications-all">
                                    <table class="table table-hover">
                                        <thead>
                                        <tr class="<?=$_SESSION['caret_dir']?>">
                                            <th class="unsortable">№</th>
                                            <th><span name="id">Доповідь</span></th>
                                            <th><span name="speaker">Доповідач</span></th>
                                            <th><span name="duration">Тривалість</span></th>
                                            <th><span name="status">Статус</span></th>
                                            <th><span name="date, time">Коли</span></th>
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
                                                    LEFT JOIN flows ON lectures.flow_ID = flows.ID
                                                    ORDER BY {$_SESSION['pre_order_by']}{$_SESSION['order_by']}
                                                ");
                                            $i = 1;
                                            while ($lectures_row = $lectures_result->fetch_assoc()):
                                                ?>
                                            <tr>
                                                <td><?=$i?></td>
                                                <td><a href="<?=LECTURES_URL."?id=".$lectures_row['id']?>"><?=$lectures_row['title']?></a></td>
                                                <td><a href="<?=PROFILE_URL."?id=".$lectures_row['speaker_ID']?>"><?=$lectures_row['speaker']?></a></td>
                                                <td><?=$lectures_row['duration']?> хвилин</td>
                                                <td><?=print_status($lectures_row['status'])?></td>
                                                <td><span><?=$lectures_row['date']?></span> <span><?=$lectures_row['time']?></span></td>
                                                <td data-toggle="tooltip" title="<?=$lectures_row['flow']?>"><a href="<?=FLOW_URL."?id=".$lectures_row['flow_ID']?>"><?=$lectures_row['flow_ID']?></td>
                                            </tr>
                                                <?php
                                                $i++;
                                            endwhile;
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <? elseif ($_SESSION['role'] == 'speaker'):; ?>
                    <legend>Ваші доповіді:</legend>
                    <table id="myApplicationsTable" class="table table-bordered table-hover">
                        <caption>Ваші доповіді</caption>
                        <thead>
                        <tr>
                            <th>№</th>
                            <th>Доповідь</th>
                            <th>Доповідач</th>
                            <th>Тривалість</th>
                            <th>Час і місце</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                            $lectures_result = $mysqli->query("
                                SELECT lectures.id, lectures.title, lectures.duration, lectures.status,
                                dates.date, lectures.time, lectures.flow_ID, flows.name AS flow
                                FROM lectures
                                LEFT JOIN participants ON lectures.speaker_ID = participants.ID
                                LEFT JOIN dates ON lectures.date_ID = dates.ID
                                LEFT JOIN flows ON lectures.flow_ID = flows.ID
                                WHERE lectures.speaker_ID = {$_SESSION['userID']}
                            ");
                            $i = 1;
                            while ($lectures_row = $lectures_result->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?=$i?></td>
                                <td><?=$lectures_row['title']?></td>
                                <td><?=$lectures_row['speaker']?></td>
                                <td><?=$lectures_row['duration']?></td>
                                <td><?="{$lectures_row['date']} {$lectures_row['time']} {$lectures_row['place']} {$lectures_row['room']}"?></td>
                            </tr>
                            <?php
                                $i++;
                                endwhile;
                            ?>
                        </tbody>
                    </table>
                    <? endif; ?>
                    <legend>Ваші реєстрації:</legend>



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
            var sort_col = $('.tabbable input[name=sort_col]').val();
            $('table th:not(.unsortable)').find("span[name='"+sort_col+"']").addClass('by');
        });

        //$('table th:not(.unsortable)').find("span[name='<?=$_SESSION['sort_col']?>']").addClass('by');

        // Initializing AJAX response modal dialog
        $('#pageAJAXresponse').modal({
            backdrop: 'static',
            keyboard: false,
            show: false
        });

        $('td[data-toggle=tooltip]').tooltip({
            placement: 'right'
        });

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
            var tab = $(this).parents('.tab-pane').attr('id');
            tab = tab.split('-')[1];
            tab = fixedEncodeURIComponent(tab);
            var sort_by = $(this).children('span').attr('name');
            sort_by = fixedEncodeURIComponent(sort_by);
            var dir = $(this).parent().attr('class');
            dir = fixedEncodeURIComponent(dir);
            var getRequest = "tab="+tab+"&sort_by="+sort_by+"&dir="+dir;
            var tabbable = $(this).parents('.tabbable').attr('id');
            $('#'+tabbable+'-container').load('lectures.php?'+getRequest+' #'+tabbable, function() {
                /*$('#pageJS-container').load('lectures.php?'+getRequest+' #pageJS', function() {
                    alert('Loaded pageJS!');
                    eval($('script#pageJS').text());
                });*/
                eval($('script#pageJS').text());
            });

        });

        $('.tableForm :submit').click(function() {
            this.form.action.value = this.name;
        });

        // Posting form using ajax
        $('.tableForm').submit(function(event) {
            event.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                url: $(this).attr('action'),
                type: "POST",
                data: formData,
                dataType: "json",
                success: function(data) {
                    alert("Here!");
                    $('#'+this.source.value+'-container').load('header.php #'+this.source.value, function() {
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
<? elseif (isset($_GET['id'])): ?>
    <!-- Lections view -->
    <div></div>
<? endif; ?>