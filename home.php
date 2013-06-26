<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Black Angel
 * Date: 25.06.13
 * Time: 9:02
 * To change this template use File | Settings | File Templates.
 */

define('authNeeded', false);
define('sidebar', false);
require_once('connect.php');
require_once('constants.php');
if (session_status() != PHP_SESSION_ACTIVE) session_start(); # PHP >= 5.4.0

?>
<? include_once('header.php'); ?>
<? if (!authNeeded || (authNeeded && isset($_SESSION['auth']))): ?>
<? include_once('page_start.php');
    $participants = $mysqli->query("SELECT * FROM participants");
    $participants = $participants->num_rows;
    $speakers = $mysqli->query("SELECT * FROM participants WHERE role = 'speaker'");
    $speakers = $speakers->num_rows;
    $lectures = $mysqli->query("SELECT * FROM lectures");
    $lectures = $lectures->num_rows;
    $days = $mysqli->query("SELECT * FROM dates");
    $days = $days->num_rows;
    /*
    $firstDay = $mysqli->query("SELECT DATE_FORMAT(dates.date, '%e/%m') AS date FROM dates ORDER BY date");
    $firstDay = $firstDay->fetch_assoc();
    $firstDay = $firstDay['date'];
    $lastDay = $mysqli->query("SELECT DATE_FORMAT(dates.date, '%e/%m') AS date FROM dates ORDER BY date DESC");
    $lastDay = $lastDay->fetch_assoc();
    $lastDay = $lastDay['date'];
    */
?>
                <!-- Page content -->
                <div class="hero-unit">
                    <h1>Вітаємо Вас на сайті конференції &laquo;<?=CONF_NAME?>&raquo;!</h1>
                    <br />
                    <p><?=CONF_DESC?></p>
                    <p>
                        <a href="<?=SIGNUP_URL?>" class="btn btn-primary btn-large">Зареєструватися</a>
                    </p>
                </div>

                <br />
                <br />

                <div style="margin-left: 15px; padding-left: 20px; border-left: 10px solid #ccc;">
                    <h4>Статистика конференції</h4>
                    <p>
                        На даний момент кількість зареєстрованих відвідувачів - <?=$participants?>.<br />
                        Серед них доповідачів - <?=$speakers?>, усього доповідей - <?=$lectures?>.<br />
                        Конференція проводитиметься протягом <?=$days?> днів (<?=CONF_DATES?>) за адресою: <?=CONF_PLACE?>.<br />
                        Вже зараз Ви можете ознайомитись з <a href="<?=SCHEDULE_URL?>">розкладом конференції</a> та
                        подати на конференцію свою <a href="<?=APPLY_URL?>">власну доповідь</a>.<br />
                        Якщо Ви хочете приняти участь у конференції в якості відвідувача чи доповідача, необхідно <a href="<?=SIGNUP_URL?>">зареєструватися</a>.<br />
                        Зареєструвавшись, ви отримаєте змогу отримувати детальну інформацію щодо кожної <a href="<?=APPLY_URL.'?view=3'?>">конкретної доповіді</a> та реєструватися на них,
                        отримаєте <a href="<?=PROFILE_URL?>">особистий кабінет</a>, свій <a href="<?=SCHEDULE_URL.'?events=own'?>">власний розклад</a> конференції,
                        який зможете переглядати та редагувати на власний розсуд та <a href="<?=LECTURES_URL?>">багато іншого</a>!
                    </p>
                </div>



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