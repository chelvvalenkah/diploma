<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Black Angel
 * Date: 24.06.13
 * Time: 13:42
 * To change this template use File | Settings | File Templates.
 */

define('authNeeded', true);
define('sidebar', false);
require_once('connect.php');
require_once('constants.php');
if (session_status() != PHP_SESSION_ACTIVE) session_start(); # PHP >= 5.4.0
$_SESSION['calendar_page'] = true;

?>
<? include_once('header.php'); ?>
<? if (!authNeeded || (authNeeded && isset($_SESSION['auth']))): ?>
<? include_once('page_start.php') ?>
                <!-- Page content -->
                <div id="nav">
                    <input type="button" id="nav_previous" name="nav_previous" value="&lt;" title="Previous week" />
                    <input type="button" id="nav_next" name="nav_next" value="&gt;" title="Next week" />
                </div>
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
            if ($_SESSION['role'] == 'speaker') {
                $lectures_result = $mysqli->query("SELECT lectures.id, lectures.title, lectures.duration,
                                                    DATE_FORMAT(dates.date, '%Y-%m-%e') AS date,
                                                    DATE_FORMAT(lectures.time, '%H:%i') AS time
                                                    FROM lectures
                                                    LEFT JOIN participants ON lectures.speaker_ID = participants.ID
                                                    LEFT JOIN dates ON lectures.date_ID = dates.ID
                                                    LEFT JOIN flows ON lectures.flow_ID = flows.ID
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
                                                "length: '".($lectures_row['duration']/15|0)."', ".PHP_EOL.
                                                "name: '".$lectures_row['title']."', ".PHP_EOL.
                                                "color: 'abcdef', ".PHP_EOL.
                                                "href: '".APPLY_URL.'?view='.$lectures_row['id']."' ".PHP_EOL.
                                                "}";
                        ?> conf_events.push(<?=$conf_event_string?>); <?
                    }
                }
            }

            ?>


            var conf_event = {
                date: '2013-06-25',
                start: '16:22',
                length: 5,
                name: 'TEST string long',
                desc: 'This is test!!!',
                color: 'abcdef',
                href: 'http://google.com'
            };
            conf_events.push(conf_event);

            var conf_event2 = {
                date: '2013-06-26',
                start: '14:00',
                length: 7,
                name: 'TEST2',
                desc: 'hui',
                color: 'de1d43',
                href: 'http://yandex.ua/'
            };
            conf_events.push(conf_event2);

            $agenda = $(document).eventouchcalendar({
                hour_mask: new Array(6, 24),
                day_mask: new Array(0, 7),
                cell_width: 133,
                cell_height: 9
            }, conf_events);

        });
        //]]>
    </script>

    <!-- Some scripts -->
    <script type="text/javascript" id="pageJS">

    </script>

<? else:
    include_once('unauthorized.php');
endif; ?>
<? include_once('footer.php');
unset($_SESSION['calendar_page']); ?>