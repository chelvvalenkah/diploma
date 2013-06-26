<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Black Angel
 * Date: 19.06.13
 * Time: 21:47
 * To change this template use File | Settings | File Templates.
 */

//define('authNeeded', true);
//define('sidebar', true);
require_once('constants.php');
require_once('functions.php');
if (session_status() != PHP_SESSION_ACTIVE) session_start(); # PHP >= 5.4.0

if (arg_exists_not_null($_GET['view'])) {
    define('authNeeded', false);
    $_SESSION['mode'] = "view";
    $_SESSION['requested_id'] = $_GET['view'];
}
else {
    define('authNeeded', true);
    if (arg_exists_not_null($_GET['edit'])) {
        $_SESSION['mode'] = "edit";
        $_SESSION['requested_id'] = $_GET['edit'];
    }
    else $_SESSION['mode'] = "new";
}

if ($_SESSION['mode'] != 'new') $lecture = array();

if (isset($_SESSION['auth'])) define('sidebar', false);
else define('sidebar', false);

?>
<? include_once('header.php'); ?>
<? if (!authNeeded || (authNeeded && isset($_SESSION['auth']))): ?>
<?
if ($_SESSION['mode'] == "view" || $_SESSION['mode'] == "edit") {
    if (preg_match("#\d{1,9}#u", $_SESSION['requested_id'], $matches)) {
        $_SESSION['requested_id'] = $matches[0];
        $lecture_result = $mysqli->query("SELECT lectures.id, lectures.title, lectures.speaker_ID,
                                            CONCAT(participants.surname, \" \", participants.name) AS speaker, lectures.duration,
                                            lectures.status, lectures.date_ID, DATE_FORMAT(dates.date, '%e %M %Y') AS date,
                                            DATE_FORMAT(lectures.time, '%H:%i') AS time, lectures.flow_ID, flows.name AS flow,
                                            venues.name AS place, lectures.notes
                                            FROM lectures
                                            LEFT JOIN participants ON lectures.speaker_ID = participants.ID
                                            LEFT JOIN dates ON lectures.date_ID = dates.ID
                                            LEFT JOIN flows ON lectures.flow_ID = flows.ID
                                            LEFT JOIN venues ON flows.venue_ID = venues.ID
                                            WHERE lectures.id = '{$_SESSION['requested_id']}'
                                          ");
        $lecture = $lecture_result->fetch_assoc();
        if ($lecture_result->num_rows > 0) {
            if ($_SESSION['mode'] == "edit") {
                if ($_SESSION['role'] != "admin" && $lecture['speaker_ID'] != $_SESSION['userID']) {
                    include_once('unauthorized.php');?>
                    <script type="text/javascript">
                        $('div#unauthorized p#message').text('Ви не маєте права редагувати цю доповідь!');
                        $('div#unauthorized a.btn-danger').text('Перейти до перегляду');
                        $('div#unauthorized a.btn-danger').attr('href', "<?=$_SERVER['SCRIPT_NAME'].'?view='.$_SESSION['requested_id']?>");
                    </script>
                    <?include_once('footer.php');
                    return;
                }
                else if ($lecture['speaker_ID'] == $_SESSION['userID'] && $lecture['status'] == 'ready') {
                    $_SESSION['mode'] = 'view';
                }
            }
        }
        else {
            include_once('unauthorized.php');?>
            <script type="text/javascript">
                $('div#unauthorized p#message').text('Лекція з таким ідентифікатором не існує!');
                $('div#unauthorized a.btn-danger').addClass('hide');
            </script>
            <?include_once('footer.php');
            return;
        }
    }
}
?>
<? include_once('page_start.php') ?>
                <!-- Page content -->
                <form id="applicationForm" name="applicationForm" class="form-horizontal" action="<?=APPLY_SCRIPT?>" method="post" autocomplete="on">
                    <fieldset<?=($_SESSION['mode'] == "view" || ($_SESSION['mode'] == 'edit' && $_SESSION['role'] == 'admin')) ? ' class="viewable"' : ''?>>
                        <? if ($_SESSION['mode'] == "new"): ?>
                        <legend>Подання доповіді на конференцію</legend>
                        <? elseif ($_SESSION['mode'] == "view"): ?>
                        <legend>Перегляд доповіді</legend>
                        <? elseif ($_SESSION['mode'] == "edit"):
                            if ($_SESSION['role'] == 'admin'): ?>
                        <legend>Долучення доповіді до розкладу конференції</legend>
                            <? else: ?>
                        <legend>Редагування доповіді</legend>
                            <? endif;
                        endif; ?>
                        <div class="control-group">
                            <label for="titleField" class="control-label required">Тема доповіді</label>
                            <div class="controls">
                                <? if ($_SESSION['mode'] == "new" || ($_SESSION['mode'] == "edit" && $_SESSION['role'] != 'admin')): ?>
                                <input type="text" id="titleField" class="input-xlarge" name="title" required="required" maxlength="255"
                                       placeholder="HTML5 та CSS3: нові можливості" title="Не більше 255 символів" pattern=".{1,255}"
                                       value="<?=($_SESSION['mode'] == "edit") ? $lecture['title'] : ''?>" />
                                <!--<span id="titleHelp" class="help-inline">Назва доповіді</span>-->
                                <? else: ?>
                                <span id="titleField" class="input-xxlarge uneditable-input"><?=$lecture['title']?></span>
                                <? endif; ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="authorField" class="control-label required">Доповідач</label>
                            <div class="controls">
                                <? if ($_SESSION['mode'] == "new" || ($_SESSION['mode'] == "edit" && $_SESSION['role'] != 'admin')): ?>
                                <span id="authorField" title="Прізвище та ім'я редагуються в особистому кабінеті" class="input-xlarge uneditable-input">
                                    <?=($_SESSION['mode'] != 'new') ? $lecture['speaker'] : $_SESSION['userName']?>
                                </span>
                                <input type="hidden" id="hiddenAuthorField" name="author" value="<?=$_SESSION['userName']?>" />
                                <span id="authorHelp" class="help-inline">Прізвище та ім'я редагуються в особистому кабінеті</span>
                                <? else: ?>
                                <span id="authorField" class="input-xxlarge uneditable-input"><?=$lecture['speaker']?></span>
                                <? endif; ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="durationField" class="control-label required">Тривалість</label>
                            <div class="controls">
                                <? if ($_SESSION['mode'] == "new" || ($_SESSION['mode'] == "edit" && $_SESSION['role'] != 'admin')): ?>
                                <input type="text" id="durationField" class="input-xlarge" name="duration" required="required" maxlength="3"
                                       placeholder="40" title="Тривалість доповіді має бути кратною 5 хвилинам" pattern="(\d{1,2}0|\d{0,2}5)"
                                       value="<?=($_SESSION['mode'] == "edit") ? $lecture['duration'] : ''?>" />
                                <span id="durationHelp" class="help-inline">в хвилинах</span>
                                <? else: ?>
                                <span id="durationField" class="input-xxlarge uneditable-input"><?=$lecture['duration'].' хвилин'?></span>
                                <? endif; ?>
                            </div>
                        </div>
                        <? if ($_SESSION['mode'] != "new"): ?>
                        <div class="control-group">
                            <label for="statusField" class="control-label">Статус заявки</label>
                            <div class="controls">
                                <span id="statusField" class="<?=$_SESSION['mode'] == "edit" && $_SESSION['role'] != 'admin' ?
                                    'input-xlarge ' : 'input-xxlarge '?> uneditable-input" title="Статус заявки редагується організаторами">
                                    <?=print_status($lecture['status'])?>
                                </span>
                                <? if ($_SESSION['mode'] == "edit" && $_SESSION['role'] != 'admin'): ?>
                                <span id="statusHelp" class="help-inline">Статус заявки редагується організаторами</span>
                                <? endif; ?>
                            </div>
                        </div>
                        <? endif; ?>
                        <div class="control-group">
                            <label for="notesField" class="control-label">Коротка інформація про доповідь</label>
                            <div class="controls">
                                <? if ($_SESSION['mode'] == "new" || ($_SESSION['mode'] == "edit" && $_SESSION['role'] != 'admin')): ?>
                                <textarea rows="5" id="notesField" class="input-xlarge" name="notes"><?=($_SESSION['mode'] == "edit") ? $lecture['notes'] : ''?></textarea>
                                <span id="notesHelp" class="help-inline multiline fio">Інформація про зміст доповіді та інші додаткові відомості</span>
                                <? else: ?>
                                <textarea rows="5" id="notesField" class="input-xxlarge" name="notes" disabled="disabled"><?=$lecture['notes']?></textarea>
                                <? endif; ?>
                            </div>
                        </div>
                    <? if (($lecture['status'] == 'ready' && $_SESSION['mode'] == 'view') || ($_SESSION['role'] == 'admin' && $_SESSION['mode'] == 'edit' && ($lecture['status'] == 'approved' || $lecture['status'] == 'ready'))): ?>
                    </fieldset>
                    <fieldset<?=$_SESSION['mode'] == 'view' ? ' class="viewable"' : ''?>>
                        <legend>Час і місце</legend>
                        <div class="control-group">
                            <label for="dateField" class="control-label required">Дата</label>
                            <div class="controls">
                                <? if ($_SESSION['role'] == 'admin' && $_SESSION['mode'] == 'edit'): ?>
                                <select id="dateField" class="span3" name="date" required="required" title="Виберіть дату зі списку">
                                    <option></option>
                                    <?php
                                    $dates_result = $mysqli->query("SELECT ID, date, DATE_FORMAT(date, '%e %M %Y') AS dateFormatted,
                                        openingTime, DATE_FORMAT(openingTime, '%H:%i') AS openingTimeFormatted,
                                        closingTime, DATE_FORMAT(closingTime, '%H:%i') AS closingTimeFormatted FROM dates");
                                    if ($dates_result->num_rows > 0):
                                        while ($dates = $dates_result->fetch_assoc()):
                                    ?>
                                    <option value="<?=$dates['ID']?>" <?=($lecture['status'] == 'ready' && $lecture['date_ID'] == $dates['ID']) ? ' selected="selected"' : '' ?>
                                            data-open="<?=$dates['openingTimeFormatted']?>" data-close="<?=$dates['closingTimeFormatted']?>">
                                        <?=$dates['dateFormatted']?>
                                    </option>
                                    <?php
                                        endwhile;
                                    endif;
                                    ?>
                                </select>
                                <? else: ?>
                                <span id="dateField" class="input-xlarge uneditable-input"><?=$lecture['date']?></span>
                                <? endif; ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="timeField" class="control-label required">Час</label>
                            <div class="controls">
                                <? if ($_SESSION['role'] == 'admin' && $_SESSION['mode'] == 'edit'): ?>
                                <input type="text" id="timeField" class="input-mini" name="time" required="required"
                                       placeholder="15:00" maxlength="5" title="Час у форматі 15:00"
                                       pattern="([01][0-9]|2[0-3]):[0-5][0-9]" />
                                <span id="timeHelp" class="help-inline">Спочатку виберіть дату</span>
                                <? else: ?>
                                <span id="timeField" class="input-xlarge uneditable-input"><?=$lecture['time']?></span>
                                <? endif; ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="flowField" class="control-label required">Потік</label>
                            <div class="controls">
                                <? if ($_SESSION['role'] == 'admin' && $_SESSION['mode'] == 'edit'): ?>
                                <select id="flowField" class="span6" name="flow" required="required" title="Виберіть потік зі списку">
                                    <option></option>
                                    <?php
                                    $flows_result = $mysqli->query("SELECT flows.ID, flows.name, venues.name AS place FROM flows
                                                                    LEFT JOIN venues ON flows.venue_ID = venues.id");
                                    if ($flows_result->num_rows > 0):
                                        while ($flows = $flows_result->fetch_assoc()):
                                    ?>
                                    <option value="<?=$flows['ID']?>" <?=($lecture['status'] == 'ready' &&
                                        $lecture['flow_ID'] == $flows['ID']) ? ' selected="selected"' : '' ?> data-place="<?=$flows['place']?>">
                                        <?=$flows['ID'].': '.$flows['name']?>
                                    </option>
                                    <?php
                                        endwhile;
                                    endif;
                                    ?>
                                </select>
                                <? else: ?>
                                <span id="flowField" class="input-xlarge uneditable-input"><?=$lecture['flow_ID'].
                                    ": ".$lecture['flow']?></span>
                                <? endif; ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="placeField" class="control-label">Місце</label>
                            <div class="controls">
                                <span id="placeField" title="Місце проведення потоку конференції" class="input-xlarge uneditable-input">
                                    <? if ($_SESSION['role'] == 'admin' && $_SESSION['mode'] == 'edit'): ?>
                                    Спочатку виберіть потік зі списку
                                    <? else: ?>
                                    <?=$lecture['place']?>
                                    <? endif; ?>
                                </span>
                            </div>
                        </div>
                    <? endif; ?>
                        <div class="form-actions">
                            <? if ($_SESSION['mode'] == "new"): ?>
                            <button type="submit" id="applyButton" class="btn btn-large btn-primary">Подати доповідь</button>
                            <? elseif ($_SESSION['mode'] == "edit" && ($_SESSION['role'] == 'speaker' ||
                            ($_SESSION['role'] == 'admin' && ($lecture['status'] == 'approved' || $lecture['status'] == 'ready')))): ?>
                            <button type="submit" id="saveButton" class="btn btn-large btn-primary">Зберігти</button>
                            <input type="hidden" name="lectureID" value="<?=$_SESSION['requested_id']?>" />
                            <? elseif ($lecture['speaker_ID'] == $_SESSION['userID'] && $lecture['status'] != 'ready'): ?>
                            <a href="<?=APPLY_URL.'?edit='.$_SESSION['requested_id']?>" id="editButton" class="btn btn-large btn-primary">Редагувати</a>
                            <? elseif ($_SESSION['mode'] == "view" && $lecture['status'] == 'ready' && STAGE == 'registration'
                                && $lecture['speaker_ID'] != $_SESSION['userID'] && $_SESSION['role'] != 'admin'): ?>
                            <button type="submit" id="registerButton" class="btn btn-large btn-primary">Зареєструватися</button>
                            <? endif; ?>
                            <a href="<?=LECTURES_URL?>" id="backButton" class="btn btn-large">Назад</a>
                            <input type="hidden" name="mode" value="<?=STAGE == 'registration' && $lecture['status'] == 'ready' ? 'register' : $_SESSION['mode']?>" />
                        </div>
                    </fieldset>
                </form>

<? include_once('page_finish.php') ?>

    <!-- Modal -->
    <div id="pageAJAXresponse" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="pageAJAXresponseLabel" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="pageAJAXresponseLabel">Modal header</h3>
        </div>
        <div class="modal-body">
            <p>One fine body…</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" data-dismiss="modal" aria-hidden="true">ОК</button>
        </div>
    </div>

    <!-- Add report scripts -->
    <script type="text/javascript" id="pageJS">
        $('select#flowField').change(function() {
            $('span#placeField').text($('select#flowField option:selected').attr('data-place'));
        });

        function FormatNumberLength(num, length) {
            var r = "" + num;
            while (r.length < length) {
                r = "0" + r;
            }
            return r;
        }

        $('select#dateField').change(function() {
            if ($('select#dateField option:selected').val() != '') {
                var openingTime = $('select#dateField option:selected').attr('data-open');
                var closingTime = $('select#dateField option:selected').attr('data-close');
                $('span#timeHelp').text('з '+openingTime+' до '+closingTime);
                var open_hour_str = openingTime.split(':')[0];
                var open_mins_str = openingTime.split(':')[1];
                var close_hour_str = closingTime.split(':')[0];
                var close_mins_str = closingTime.split(':')[1];
                var open_hour = parseInt(open_hour_str);
                var open_mins = parseInt(open_mins_str);
                var close_hour = parseInt(close_hour_str);
                var close_mins = parseInt(close_mins_str);
                var pre_pattern = open_mins < 55 ? '('+(open_hour < 10 ? '0?'+parseInt(open_hour_str) : open_hour_str)+':'+(open_mins < 50 ? '(' : '')+(FormatNumberLength(open_mins+5, 2))+(open_mins < 50 ? '|['+((open_mins/10|0)+1)+'-5][05])' : '')/*+'|'*/ : '(';
                var post_pattern = close_mins > 5 ? '|'+(close_hour < 10 ? '0?'+parseInt(close_hour_str) : close_hour_str)+':'+(close_mins > 10 ? '(' : '')+(FormatNumberLength(close_mins-5, 2))+(close_mins > 10 ? '|[0-'+((close_mins/10|0)-1)+'])' : '') + ')' : ')';
                var pattern = '';
                if (close_hour - open_hour >= 2) {
                    open_hour += 1;
                    close_hour -= 1;
                    open_hour_str = FormatNumberLength(open_hour, 2);
                    close_hour_str = FormatNumberLength(close_hour, 2);
                    pattern += '|';
                    if ((open_hour/10|0) != (close_hour/10|0)) pattern += '(';
                    if (open_hour < 10) {
                        pattern += '0?'/*+open_hour_str[0]*/;
                        if (open_hour == 9) pattern += '9';
                        else {
                            pattern += '['+open_hour_str[1]+'-';
                            if (close_hour < 10) pattern += close_hour_str[1]+']';
                            else pattern += '9]';
                        }
                    }
                    if (close_hour >= 10) {
                        if (open_hour < 10) pattern += '|1[0-';
                        else pattern += '1['+open_hour_str[1]+'-';
                        if (close_hour < 20) pattern += close_hour_str[1]+']';
                        else pattern += '9]';
                    }
                    if (close_hour >= 20) {
                        if (open_hour < 20) pattern += '|2[0-'+close_hour_str[1]+']';
                        else pattern += '2['+open_hour_str[1]+'-'+close_hour_str[1]+']';
                    }
                    if ((open_hour/10|0) != (close_hour/10|0)) pattern += ')';
                    pattern += ':[0-5][05]';
                }
                pattern = pre_pattern + pattern + post_pattern;
                //alert(pattern);
                $('input#timeField').attr('pattern', pattern);
            }
            else {
                $('span#timeHelp').text('Спочатку виберіть дату');
                $('input#timeField').attr('pattern', '\d{1,2}:\d{2}');
            }
        });

        // Initializing AJAX response modal dialog
        $('#pageAJAXresponse').modal({
            backdrop: 'static',
            keyboard: false,
            show: false
        });

        // Posting form using ajax
        $('#applicationForm').submit(function(event) {
            event.preventDefault();
            $('#hiddenAuthorField').val($('#authorField').text().trim());
            var formData = $('#applicationForm').serialize();
            $.ajax({
                url: $(this).attr('action'),
                type: "POST",
                data: formData,
                dataType: "json",
                success: function(data) {
                    $('#pageAJAXresponseLabel').text(data.title);
                    $('#pageAJAXresponse div.modal-body p').text(data.msg);
                    $('#pageAJAXresponse').modal('show');
                    $('#pageAJAXresponse div.modal-footer button').click(function () {
                        if ($('form#applicationForm div.form-actions input[name=mode]').val() == 'new') {
                            window.location.href = "<?=LECTURES_URL?>";
                        }
                        else window.location.href = "<?=APPLY_URL.'?view='.$_SESSION['requested_id']?>";
                    });
                },
                error: function(xhr, textStatus, errorThrown) {
                    $('#pageAJAXresponseLabel').text("Помилка!");
                    var response = $.parseJSON(xhr.responseText);
                    var msg = "";
                    if (response.emptyFields) {
                        msg += "Не заповнені дані: ";
                        for (var i = 0; i < response.emptyFields.length; i++) {
                            msg += response.emptyFields[i];
                            if (i != response.emptyFields.length-1) msg += ", ";
                            else msg += ". ";
                        }
                    }
                    if (response.wrongInput) {
                        msg += "Ці поля заповнені з помилками: ";
                        for (var i = 0; i < response.wrongInput.length; i++) {
                            msg += response.wrongInput[i];
                            if (i != response.wrongInput.length-1) msg += ", ";
                            else msg += ". ";
                        }
                    }
                    if (response.errorMsg) {
                        for (var i = 0; i < response.errorMsg.length; i++) {
                            msg += response.errorMsg[i];
                        }
                    }
                    $('#pageAJAXresponse div.modal-body p').text(msg);
                    $('#pageAJAXresponse').modal('show');
                }
            });
        });
    </script>

<? else:
    include_once('unauthorized.php');
endif; ?>
<? include_once('footer.php'); ?>