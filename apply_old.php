<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Black Angel
 * Date: 19.06.13
 * Time: 21:47
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
                <form id="applicationForm" name="applicationForm" class="form-horizontal" action="<?=APPLY_SCRIPT?>" method="post" autocomplete="on">
                    <fieldset>
                        <legend>Подання доповіді на конференцію</legend>
                        <div class="control-group">
                            <label for="titleField" class="control-label required">Тема доповіді</label>
                            <div class="controls">
                                <input type="text" id="titleField" class="input-xlarge" name="title" required="required" maxlength="255"
                                       placeholder="HTML5 та CSS3: нові можливості" title="Не більше 255 символів" pattern=".{1,255}" />
                                <!--<span id="titleHelp" class="help-inline">Назва доповіді</span>-->
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="authorField" class="control-label required">Доповідач</label>
                            <div class="controls">
                                    <span id="authorField" title="Прізвище та ім'я редагуються в особистому кабінеті"
                                          class="input-xlarge uneditable-input"><?=$_SESSION['userName']?></span>
                                <input type="hidden" id="hiddenAuthorField" name="author" value="<?=$_SESSION['userName']?>" />
                                <span id="authorHelp" class="help-inline">Прізвище та ім'я редагуються в особистому кабінеті</span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="durationField" class="control-label required">Тривалість</label>
                            <div class="controls">
                                <input type="text" id="durationField" class="input-xlarge" name="duration" required="required" maxlength="3"
                                       placeholder="40" title="Тривалість доповіді має бути кратною 5 хвилинам" pattern="(\d{1,2}0|\d{0,2}5)" />
                                <span id="durationHelp" class="help-inline">в хвилинах</span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="notesField" class="control-label">Коротка інформація про доповідь</label>
                            <div class="controls">
                                <textarea rows="5" id="notesField" class="input-xlarge" name="notes"></textarea>
                                <span id="notesHelp" class="help-inline multiline fio">Інформація про зміст доповіді та інші додаткові відомості</span>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" id="applyButton" class="btn btn-large btn-primary">Подати доповідь</button>
                            <!--<button type="button" class="btn-large">Назад</button>-->
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
                        window.location.href = "<?=PROFILE_URL?>";
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