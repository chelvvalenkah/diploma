<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Black Angel
 * Date: 24.06.13
 * Time: 13:42
 * To change this template use File | Settings | File Templates.
 */

define('sidebar', false);
require_once('connect.php');
require_once('constants.php');
require_once('functions.php');
if (session_status() != PHP_SESSION_ACTIVE) session_start(); # PHP >= 5.4.0
$_SESSION['calendar_page'] = true;

if (arg_exists_not_null($_GET['events']) && ($_GET['events'] == 'all' || $_GET['events'] == 'own')) {
    if ($_SESSION['role'] != 'admin') $_SESSION['mode'] = $_GET['events'];
    else $_SESSION['mode'] = 'all';
}
else $_SESSION['mode'] = "all";
if ($_SESSION['mode'] == 'own') define('authNeeded', true);
else define('authNeeded', false);

?>
<? include_once('header.php'); ?>
<? if (!authNeeded || (authNeeded && isset($_SESSION['auth']))): ?>
<? include_once('page_start.php') ?>
                <!-- Page content -->
                <!--
                <div id="nav">
                    <input type="button" id="nav_previous" name="nav_previous" value="&lt;" title="Previous week" />
                    <input type="button" id="nav_next" name="nav_next" value="&gt;" title="Next week" />
                </div>
                -->

                <? if (arg_exists_not_null($_SESSION['auth']) && $_SESSION['role'] != 'admin'): ?>
                <div class="row-fluid text-center">
                    <div class="btn-group btn-block" data-toggle="buttons-radio" data-toggle-name="scheduleRadio">
                        <a href="<?=SCHEDULE_URL?>?events=own" class="btn<?=$_SESSION['mode'] == "own" ? ' active' : ''?>" name="own">Мій розклад</a>
                        <a href="<?=SCHEDULE_URL?>?events=all" class="btn<?=$_SESSION['mode'] == "all" ? ' active' : ''?>" name="all">Загальний розклад</a>
                    </div>
                </div>
                <? endif; ?>

                <table id="hoursList"></table>
                <div id="limitsChoice"></div>
                <table id="agenda">
                    <thead>
                    <tr>
                        <!-- Days name -->
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <!-- TD of each days -->
                    </tr>
                    <div id="topMask"></div>
                    <div id="bottomMask"></div>
                    <div id="leftMask"></div>
                    <div id="rightMask"></div>
                    </tbody>
                </table>
                <div id="limitsChoiceHorizontal"></div>
                <div id="fixResize"></div>

<? include_once('page_finish.php') ?>

    <script type='text/javascript' src='./eventouchcalendar/includes/jquery-ui-1.8.17.custom.js'></script>
    <script type='text/javascript' src='./eventouchcalendar/includes/jquery.ui.core.min.js'></script>
    <script type='text/javascript' src='./eventouchcalendar/includes/jquery.ui.widget.min.js'></script>
    <script type='text/javascript' src='./eventouchcalendar/includes/jquery.ui.mouse.min.js'></script>
    <script type='text/javascript' src='./eventouchcalendar/includes/jquery.ui.position.min.js'></script>
    <script type='text/javascript' src='./eventouchcalendar/includes/jquery.ui.draggable.min.js'></script>
    <script type='text/javascript' src='./eventouchcalendar/includes/jquery.ui.resizable.min.js'></script>
    <script type='text/javascript' src='./eventouchcalendar/includes/jquery.ui.selectable.min.js'></script>
    <script type='text/javascript' src='./eventouchcalendar/includes/jquery.ui.droppable.min.js'></script>
    <script type='text/javascript' src='./eventouchcalendar/includes/jquery.ui.slider.min.js'></script>
    <script type='text/javascript' src='./eventouchcalendar/includes/jquery.ui.dialog.min.js'></script>

    <!-- SHADOWBOX -->
    <script type='text/javascript' src='./eventouchcalendar/includes/shadowbox-3.0.3/shadowbox.js'></script>

    <!-- JQUERY - MINICOLORS -->
    <script type='text/javascript' src='./eventouchcalendar/includes/jquery-minicolors/jquery.miniColors.min.js'></script>

    <!-- DATES MANAGEMENT -->
    <script type='text/javascript' src='./eventouchcalendar/includes/date.js'></script>

    <script type='text/javascript' src='./eventouchcalendar/eventouchcalendar.crypt.js'></script>
    <script type="text/javascript">
        //<![CDATA[
        $(document).ready(function() {

            // Example of good values for cells
            var CELL_WIDTH_LITTLE = 100, CELL_WIDTH_MEDIUM = 150, CELL_WIDTH_LARGE = 200;
            var CELL_HEIGHT_LITTLE = 7, CELL_HEIGHT_MEDIUM = 9, CELL_HEIGHT_LARGE = 15;

            /*
                Default values
                --------------

            hour_mask:		new Array(8, 20),
            day_mask:		new Array(1, 7),
            cell_width:		150,
            cell_height:	9
            */

            var conf_events = new Array();

            <?php
            if ($_SESSION['mode'] == 'all') {
                $lectures_result = $mysqli->query("SELECT lectures.id, lectures.title, lectures.duration,
                                                    DATE_FORMAT(dates.date, '%Y-%m-%e') AS date,
                                                    DATE_FORMAT(lectures.time, '%H:%i') AS time
                                                    FROM lectures
                                                    LEFT JOIN participants ON lectures.speaker_ID = participants.ID
                                                    LEFT JOIN dates ON lectures.date_ID = dates.ID
                                                    WHERE lectures.status = 'ready'");
                if ($lectures_result->num_rows > 0) {
                    $num = 0;
                    while ($lectures_row = $lectures_result->fetch_assoc()) {
                        $conf_event_string = "{ ".PHP_EOL.
                            "date: '".$lectures_row['date']."', ".PHP_EOL.
                            "start: '".$lectures_row['time']."', ".PHP_EOL.
                            "length: '".$lectures_row['duration']."', ".PHP_EOL.
                            "name: '".$lectures_row['title']."', ".PHP_EOL.
                            "color: '".($num%2 == 1 ? '5bb75b' : '327CCB')."', ".PHP_EOL.
                            "href: '".APPLY_URL.'?view='.$lectures_row['id']."' ".PHP_EOL.
                            "}";
                        ?> conf_events.push(<?=$conf_event_string?>); <?
                        $num++;
                    }
                }
            }
            elseif (arg_exists_not_null($_SESSION['auth'])) {
                if ($_SESSION['role'] == 'speaker') {
                    $lectures_result = $mysqli->query("SELECT lectures.id, lectures.title, lectures.duration,
                                                    DATE_FORMAT(dates.date, '%Y-%m-%e') AS date,
                                                    DATE_FORMAT(lectures.time, '%H:%i') AS time
                                                    FROM lectures
                                                    LEFT JOIN participants ON lectures.speaker_ID = participants.ID
                                                    LEFT JOIN dates ON lectures.date_ID = dates.ID
                                                    WHERE lectures.speaker_ID = {$_SESSION['userID']}
                                                    AND lectures.status = 'ready'");
                    if ($lectures_result->num_rows > 0) {
                        while ($lectures_row = $lectures_result->fetch_assoc()) {
                            /*
                            $time = explode(':', $lectures_row['time']);
                            $corr = $time[1]%15 > 10 ? 1 : 0;
                            $time[1] = (($time[1]/15|0) + $corr) * 15;
                            $lectures_row['time'] = implode(':', $time);
                            */
                            $conf_event_string = "{ ".PHP_EOL.
                                "date: '".$lectures_row['date']."', ".PHP_EOL.
                                "start: '".$lectures_row['time']."', ".PHP_EOL.
                                "length: '".$lectures_row['duration']."', ".PHP_EOL.
                                "name: '".$lectures_row['title']."', ".PHP_EOL.
                                "color: 'de1d43', ".PHP_EOL.
                                "href: '".APPLY_URL.'?view='.$lectures_row['id']."' ".PHP_EOL.
                                "}";
                            ?> conf_events.push(<?=$conf_event_string?>); <?
                        }
                    }
                }
                if ($_SESSION['role'] != 'admin') {
                    $lectures_result = $mysqli->query("SELECT lectures.id, lectures.title, lectures.duration,
                                                        DATE_FORMAT(dates.date, '%Y-%m-%e') AS date,
                                                        DATE_FORMAT(lectures.time, '%H:%i') AS time
                                                        FROM registrations
                                                        LEFT JOIN lectures ON registrations.lecture_ID = lectures.ID
                                                        LEFT JOIN participants ON lectures.speaker_ID = participants.ID
                                                        LEFT JOIN dates ON lectures.date_ID = dates.ID
                                                        WHERE registrations.visitor_ID = '{$_SESSION['userID']}'
                                                        ORDER BY date, time");
                    if ($lectures_result->num_rows > 0) {
                        while ($lectures_row = $lectures_result->fetch_assoc()) {
                            $conf_event_string = "{ ".PHP_EOL.
                                "date: '".$lectures_row['date']."', ".PHP_EOL.
                                "start: '".$lectures_row['time']."', ".PHP_EOL.
                                "length: '".$lectures_row['duration']."', ".PHP_EOL.
                                "name: '".$lectures_row['title']."', ".PHP_EOL.
                                "color: '327CCB', ".PHP_EOL.
                                "href: '".APPLY_URL.'?view='.$lectures_row['id']."' ".PHP_EOL.
                                "}";
                            ?> conf_events.push(<?=$conf_event_string?>); <?
                        }
                    }
                }
            }


            ?>

            $agenda = $(document).eventouchcalendar({
                hour_mask: new Array(6, 24),
                day_mask: new Array(0, 7),
                cell_width: 133,
                cell_height: 9
            }, conf_events);

        });
        //]]>
    </script>

    <!-- Schedule scripts -->
    <script type="text/javascript" id="pageJS">

    </script>

<? else:
    include_once('unauthorized.php');
endif; ?>
<? include_once('footer.php');
unset($_SESSION['calendar_page']); ?>