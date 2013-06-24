<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Black Angel
 * Date: 14.06.13
 * Time: 9:14
 * To change this template use File | Settings | File Templates.
 */

/*define('authNeeded', false);
define('sidebar', false);*/
require_once('constants.php');
require_once('functions.php');
if (session_status() != PHP_SESSION_ACTIVE) session_start(); # PHP >= 5.4.0

if (arg_exists_not_null($_GET['view']) || isset($_GET['signup'])) {
    define('authNeeded', false);
    if (arg_exists_not_null($_GET['view'])) {
        $_SESSION['mode'] = "view";
        $_SESSION['requested_id'] = $_GET['view'];
        if ($_GET['view'] == $_SESSION['userID']) $_SESSION['mode'] = "home";
    }
    else $_SESSION['mode'] = "signup";
}
else {
    define('authNeeded', true);
    if (arg_exists_not_null($_GET['edit'])) {
        $_SESSION['mode'] = "edit";
        $_SESSION['requested_id'] = $_GET['edit'];
    }
    else {
        $_SESSION['mode'] = "home";
        $_SESSION['requested_id'] = $_SESSION['userID'];
    }
}

if ($_SESSION['mode'] != 'signup') $user = array();

if (isset($_SESSION['auth'])) define('sidebar', true);
else define('sidebar', false);

?>
<? include_once('header.php'); ?>
<? if (!authNeeded || (authNeeded && isset($_SESSION['auth']))): ?>
<?
if ($_SESSION['mode'] != "signup") {
    if (preg_match("#\d{1,9}#u", $_SESSION['requested_id'], $matches)) {
        $_SESSION['requested_id'] = $matches[0];
        $user_result = $mysqli->query("SELECT * FROM participants WHERE id = '{$_SESSION['requested_id']}'");
        $user = $user_result->fetch_assoc();
        if ($user_result->num_rows > 0) {
            if ($_SESSION['mode'] == "edit") {
                if ($_SESSION['requested_id'] != $_SESSION['userID']) {
                    include_once('unauthorized.php');?>
                    <script type="text/javascript">
                        $('div#unauthorized p#message').text('Ви не маєте права редагувати чужий профіль!');
                        $('div#unauthorized a.btn-danger').text('Перейти до перегляду');
                        $('div#unauthorized a.btn-danger').attr('href', "<?=$_SERVER['SCRIPT_NAME'].'?view='.$_SESSION['requested_id']?>");
                    </script>
                    <?include_once('footer.php');
                    return;
                }
            }
        }
        else {
            include_once('unauthorized.php');?>
            <script type="text/javascript">
                $('div#unauthorized p#message').text('Профілю з таким ідентифікатором не існує!');
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
<form id="signupForm" name="signupForm" class="form-horizontal" action="<?=PROFILE_SCRIPT?>" method="post" autocomplete="on">
    <fieldset<?=($_SESSION['mode'] == "view" || $_SESSION['mode'] == 'home') ? ' class="viewable"' : ''?>>
        <? if ($_SESSION['mode'] == 'signup'): ?>
        <legend>Реєстрація на конференцію</legend>
        <? elseif ($_SESSION['mode'] == 'home'): ?>
        <legend>Мій профіль</legend>
        <? elseif ($_SESSION['mode'] == 'view'): ?>
        <legend>Перегляд профілю</legend>
        <? elseif ($_SESSION['mode'] == 'edit'): ?>
        <legend>Редагування профілю</legend>
        <? endif; ?>
        <div class="control-group">
            <label for="surnameField" class="control-label required">Прізвище</label>
            <div class="controls">
                <? if ($_SESSION['mode'] == "signup" || $_SESSION['mode'] == "edit"): ?>
                <input type="text" id="surnameField" class="input-xlarge" name="surname" required="required"
                       placeholder="Іванов" maxlength="32" title="Тільки літери" pattern="[А-яІіЇїЄєЁёA-z'\s\-]{1,32}"
                       value="<?=($_SESSION['mode'] == "edit") ? $user['surname'] : ''?>" />
                <span class="help-inline multiline" id="fio">
                    Прізвище, ім’я та назва компанії будуть надруковані на бейджі відвідувача
                </span>
                <? else: ?>
                <span id="surnameField" class="input-xlarge uneditable-input"><?=$user['surname']?></span>
                <? endif; ?>
            </div>

        </div>
        <div class="control-group">
            <label for="nameField" class="control-label required">Ім'я</label>
            <div class="controls">
                <? if ($_SESSION['mode'] == "signup" || $_SESSION['mode'] == "edit"): ?>
                <input type="text" id="nameField" class="input-xlarge" name="name" required="required"
                       placeholder="Петро" maxlength="32" title="Тільки літери" pattern="[А-яІіЇїЄєЁёA-z'\s\-]{1,32}"
                       value="<?=($_SESSION['mode'] == "edit") ? $user['name'] : ''?>" />

                <? else: ?>
                <span id="nameField" class="input-xlarge uneditable-input"><?=$user['name']?></span>
                <? endif; ?>
            </div>
        </div>
        <? if (($_SESSION['mode'] == 'view' || $_SESSION['mode'] == 'home') && arg_exists_not_null($user['company'])): ?>
        <div class="control-group">
            <label for="companyField" class="control-label">Компанія</label>
            <div class="controls">
                <? if ($_SESSION['mode'] == "signup" || $_SESSION['mode'] == "edit"): ?>
                <input type="text" id="companyField" class="input-xlarge" name="company"
                       placeholder="ТОВ &quot;Рога та копита&quot;" maxlength="32"
                       title="Допустимі літери та символи &laquo;&quot;' ,.-&raquo;" pattern="[А-яІіЇїЄєЁёA-z&laquo;\'&quot;\-,\.\s&raquo;]{1,32}"
                       value="<?=($_SESSION['mode'] == "edit") ? $user['company'] : ''?>" />

                <? else: ?>
                <span id="companyField" class="input-xlarge uneditable-input"><?=$user['company']?></span>
                <? endif; ?>
            </div>
        </div>
        <? endif; ?>
        <? if (($_SESSION['mode'] == 'view' || $_SESSION['mode'] == 'home') && arg_exists_not_null($user['position'])): ?>
        <div class="control-group">
            <label for="positionField" class="control-label">Посада</label>
            <div class="controls">
                <? if ($_SESSION['mode'] == "signup" || $_SESSION['mode'] == "edit"): ?>
                <input type="text" id="positionField" class="input-xlarge" name="position"
                       placeholder="Генеральний директор" maxlength="32" title="Допустимі літери, дефіс, коми та пробіли"
                       pattern="[А-яІіЇїЄєЁёA-z\-,\s]{1,32}"
                       value="<?=($_SESSION['mode'] == "edit") ? $user['position'] : ''?>" />

                <? else: ?>
                <span id="positionField" class="input-xlarge uneditable-input"><?=$user['position']?></span>
                <? endif; ?>
            </div>
        </div>
        <? endif; ?>
        <? if ($_SESSION['mode'] == "view" || $_SESSION['mode'] == "home"): ?>
        <div class="control-group">
            <label for="roleField" class="control-label">Статус</label>
            <div class="controls">
                <span id="roleField" class="input-xlarge uneditable-input"><?=print_role($user['role'])?></span>
            </div>
        </div>
        <? endif; ?>
        <div class="control-group">
            <label for="ageField" class="control-label required">Вік</label>
            <div class="controls">
                <div class="btn-group btn-block"<?=($_SESSION['mode'] == "signup" || $_SESSION['mode'] == "edit") ?
                    ' data-toggle="buttons-radio"' : ''?> data-toggle-name="ageRadio">
                    <button type="button" class="btn<?=($_SESSION['mode'] != "signup" && $user['age'] == 'S')? ' active' : ''?>" name="S">18-23</button>
                    <button type="button" class="btn<?=($_SESSION['mode'] != "signup" && $user['age'] == 'M')? ' active' : ''?>" name="M">24-35</button>
                    <button type="button" class="btn<?=($_SESSION['mode'] != "signup" && $user['age'] == 'L')? ' active' : ''?>" name="L">36-45</button>
                    <button type="button" class="btn<?=($_SESSION['mode'] != "signup" && $user['age'] == 'XL')? ' active' : ''?>" name="XL">від 46</button>
                </div>
                <? if ($_SESSION['mode'] == "signup" || $_SESSION['mode'] == "edit"): ?>
                <input type="hidden" id="ageField" name="age" required="required" value="<?=($_SESSION['mode'] == "edit") ? $user['age'] : ''?>" />
                <? endif; ?>
            </div>
        </div>
        <div class="control-group">
            <label for="sexField" class="control-label required">Стать</label>
            <div class="controls">
                <!--
                <select id="sexField" name="sex" required="required" title="Вкажіть Вашу стать">
                    <option></option>
                    <option value="M">Чоловіча</option>
                    <option value="F">Жіноча</option>
                </select>
                -->
                <div class="btn-group btn-block"<?=($_SESSION['mode'] == "signup" || $_SESSION['mode'] == "edit") ?
                    ' data-toggle="buttons-radio"' : ''?> data-toggle-name="sexRadio">
                    <button type="button" class="btn<?=($_SESSION['mode'] != "signup" && $user['sex'] == 'M')? ' active' : ''?>" name="M">Чоловіча</button>
                    <button type="button" class="btn<?=($_SESSION['mode'] != "signup" && $user['sex'] == 'F')? ' active' : ''?>" name="F">Жіноча</button>
                </div>
                <? if ($_SESSION['mode'] == "signup" || $_SESSION['mode'] == "edit"): ?>
                <input type="hidden" id="sexField" name="sex" required="required" value="<?=($_SESSION['mode'] == "edit") ? $user['sex'] : ''?>" />
                <? endif; ?>
            </div>
        </div>
        <? if ($_SESSION['mode'] != "view"): ?>
        <div class="control-group">
            <label for="emailField" class="control-label required">Email</label>
            <div class="controls">
                <? if ($_SESSION['mode'] == "signup" || $_SESSION['mode'] == "edit"): ?>
                <input type="email" id="emailField" class="input-xlarge" name="email" required="required" placeholder="address@domain.com"
                       pattern="^[A-z][A-z0-9\._-]{0,21}[A-z0-9]@[A-z0-9][A-z0-9\.-]{0,30}[A-z0-9]\.[A-z]{2,7}" title="address@domain.com"
                       maxlength="64" oninput="checkEmail()" onchange="checkEmail()" onpaste="checkEmail()" onkeyup="checkEmail()"
                       value="<?=($_SESSION['mode'] == "edit") ? $user['email'] : ''?>" />
                <span id="emailHelp" class="help-inline multiline two-lines">Використовуватиметься в якості логіна та для нагадування паролю</span>
                <? else: ?>
                <span id="emailField" class="input-xlarge uneditable-input"><?=$user['email']?></span>
                <? endif; ?>
            </div>
        </div>
        <? endif; ?>
        <? if ($_SESSION['mode'] == "signup" || $_SESSION['mode'] == "edit"): ?>
        <div class="control-group">
            <label for="passwordField" class="control-label required">Введіть пароль</label>
            <div class="controls">
                <input type="password" id="passwordField" class="input-xlarge" name="password" placeholder="Надійний, але й пам'яткий"
                       title="Пароль має бути від 6 до 24 символів завдовжки. Можна використовувати латинські літери, цифри та символи ?!@#$%^&()+-*/_=.,;:'<>"
                       pattern="[A-z0-9?!@#$%^&()+\-*/_=.,;:'<>]{6,24}" required="required" maxlength="24"
                       oninput="viceVersaPass()" onchange="viceVersaPass()" onpaste="viceVersaPass()" onkeyup="viceVersaPass()" />

            </div>
        </div>
        <div class="control-group">
            <label for="confirmationField" class="control-label required">Повторіть пароль</label>
            <div class="controls">
                <input type="password" id="confirmationField" class="input-xlarge" name="confirmation" placeholder="А раптом помилилися? ;)"
                       title="Паролі мають співпадати" pattern="[A-z0-9?!@#$%^&()+\-*/_=.,;:'<>]{6,24}" required="required" maxlength="24"
                       oninput="checkPass2()" onchange="checkPass()" onpaste="checkPass()" onkeyup="checkPass2()" />
                <span id="confirmationHelp" class="help-inline">Паролі мають співпадати</span>
            </div>
        </div>
        <? endif; ?>
        <? if (($_SESSION['mode'] == 'view' || $_SESSION['mode'] == 'home') && arg_exists_not_null($user['position'])): ?>
        <div class="control-group">
            <label for="siteField" class="control-label">Веб-сторінка</label>
            <div class="controls">
                <? if ($_SESSION['mode'] == "signup" || $_SESSION['mode'] == "edit"): ?>
                <input type="text" id="siteField" class="input-xlarge" name="web" placeholder="http://best-kyiv.org/"
                       title="URL веб-сайту має бути у форматі: http://best-kyiv.org" maxlength="64"
                       pattern="((ftp|https?)://)?([A-z0-9]+(\-|\.)?[A-z0-9]+?){1,20}\.[A-z]{2,7}(/|([A-z0-9\-?\[\]\.=&%;#!]+/?)+)?"
                       value="<?=($_SESSION['mode'] == "edit") ? $user['web'] : ''?>" />

                <? else: ?>
                <span id="siteField" class="input-xlarge uneditable-input"><?=$user['web']?></span>
                <? endif; ?>
            </div>
        </div>
        <? endif; ?>
        <div class="control-group">
            <label for="phoneField1" class="control-label required">Телефон</label>
            <div class="controls">
                <? if ($_SESSION['mode'] == "signup" || $_SESSION['mode'] == "edit"): ?>
                <input type="tel" id="phoneField1" class="<?=$_SESSION['mode'] == "signup" || ($_SESSION['mode'] == "edit"
                    && !arg_exists_not_null($user['phone2'])) ? 'input-medium-btn' : 'input-xlarge'?>"
                       name="phone1" placeholder="+38(0__) ___-__-__"
                       pattern="\+38\s?\(?0([0-9]{2}\)?\s?[0-9]{3}|[0-9]{3}\)?\s?[0-9]{2}|[0-9]{4}\)?\s?[0-9])[\s-]?[0-9]{2}[\s-]?[0-9]{2}"
                       title="+38(0__) ___-__-__" required="required" maxlength="20"
                       value="<?=($_SESSION['mode'] == "edit") ? $user['phone1'] : ''?>" />
                <? if ($_SESSION['mode'] == "signup" || ($_SESSION['mode'] == "edit" && !arg_exists_not_null($user['phone2']))): ?>
                <button type="button" id="addNumber" class="btn">ще 1 номер</button>
                <? endif; ?>
                <span id="phoneHelp" class="help-inline">Потрібний для зв’язку та нагадування паролю</span>
                <? else: ?>
                <span id="phoneField1" class="input-xlarge uneditable-input"><?=$user['phone1']?></span>
                <? endif; ?>
            </div>
        </div>
        <div class="control-group<?=$_SESSION['mode'] == "signup" || !arg_exists_not_null($user['phone2']) ? ' hide' : ''?>" id="phone2Controls">
            <label for="phoneField2" class="control-label">Телефон 2</label>
            <div class="controls">
                <? if ($_SESSION['mode'] == "signup" || $_SESSION['mode'] == "edit"): ?>
                <input type="tel" id="phoneField2" class="input-xlarge" name="phone2" placeholder="+38(0__) ___-__-__"
                       pattern="\+38\s?\(?0([0-9]{2}\)?\s?[0-9]{3}|[0-9]{3}\)?\s?[0-9]{2}|[0-9]{4}\)?\s?[0-9])[\s-]?[0-9]{2}[\s-]?[0-9]{2}"
                       title="+38(0__) ___-__-__" maxlength="20"
                       value="<?=($_SESSION['mode'] == "edit") ? $user['phone2'] : ''?>" />

                <? else: ?>
                <span id="phoneField2" class="input-xlarge uneditable-input"><?=$user['phone2']?></span>
                <? endif; ?>
            </div>
        </div>
        <div class="form-actions">
            <? if ($_SESSION['mode'] == 'signup'): ?>
            <button type="submit" id="signupButton" class="btn btn-large btn-primary disabled" disabled="disabled">Зареєструватися</button>
            <a href="<?=HOME_URL?>" id="backButton" class="btn btn-large">На головну</a>
            <? elseif ($_SESSION['mode'] != 'edit'): ?>
            <? if ($_SESSION['mode'] == 'home'): ?>
            <a href="<?=PROFILE_URL.'?edit='.$_SESSION['requested_id']?>" id="editButton" class="btn btn-large btn-primary">Редагувати</a>
            <? endif; ?>
            <a href="<?=LECTURES_URL?>" id="lecturesButton" class="btn btn-large">До списку доповідей</a>
            <? else: ?>
            <button type="submit" id="saveButton" class="btn btn-large btn-primary disabled" disabled="disabled">Зберігти</button>
            <a href="<?=PROFILE_URL?>" id="cancelButton" class="btn btn-large">Відміна</a>
            <? endif; ?>
            <!--<button type="button" class="btn-large">Назад</button>-->
            <input type="hidden" name="mode" value="<?=$_SESSION['mode']?>" />
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

<!-- Registration form scripts -->
<script type="text/javascript" id="pageJS">
var passwords_match = false;
<? if ($_SESSION['mode'] != 'edit'): ?>
var age_selected = false;
var email_free = false;
var sex_selected = false;
<? else: ?>
var age_selected = true;
var email_free = true;
var sex_selected = true;
<? endif; ?>


// Initializing password mismatch popover
$('#confirmationField').popover({
    title: 'Помилка!',
    content: 'Паролі, що Ви ввели, не співпадають! :(',
    trigger: 'manual'
});

$('div[data-toggle-name=ageRadio] button').click(function() {
    $('#ageField').attr('value', $(this).attr('name'));
    age_selected = true;
    if (age_selected && passwords_match && email_free && sex_selected) {
        $('.form-actions button[type=submit]').removeClass('disabled');
        $('.form-actions button[type=submit]').prop('disabled', false);
    }
});

$('div[data-toggle-name=sexRadio] button').click(function() {
    $('#sexField').attr('value', $(this).attr('name'));
    sex_selected = true;
    if (sex_selected && passwords_match && email_free && sex_selected) {
        $('.form-actions button[type=submit]').removeClass('disabled');
        $('.form-actions button[type=submit]').prop('disabled', false);
    }
});

$('#addNumber').click(function() {
    $('#phone2Controls').slideDown('fast');
    $('#addNumber').addClass('hide');
    $('#phoneField1').removeClass('input-medium-btn').addClass('input-xlarge');
});

function viceVersaPass() {
    if ($('#confirmationField').val().length > 0) checkPass();
}

function checkPass() {
    //$('#passwordCheck').addClass('hide');
    if ($('#passwordField').val().length > 0 && $('#confirmationField').val().length >= 6 && $('#passwordField').val().length >= 6) {
        if ($('#confirmationField').val() == $('#passwordField').val()) {
            $('#confirmationField').popover('hide');
            $('#confirmationField').removeClass('invalid');
            $('#confirmationField').addClass('valid');
            /*$('#passwordCheck').text('Відмінно! Паролі співпадають :)');
            $('#passwordCheck').css('color', 'green');
            $('#passwordCheck').removeClass('hide');*/
            $('#confirmationHelp').text('Відмінно! Паролі співпадають :)');
            $('#confirmationHelp').css('color', '#54c516');
            passwords_match = true;
            if (age_selected && passwords_match && email_free && sex_selected) {
                $('.form-actions button[type=submit]').removeClass('disabled');
                $('.form-actions button[type=submit]').prop('disabled', false);
            }
            /*setTimeout(function() {
                $('#passwordCheck').addClass('hide');
            }, 3000);*/
        }
        else {
            $('#confirmationField').removeClass('valid');
            $('#confirmationField').addClass('invalid');
            $('#confirmationField').popover('show');
            /*$('#passwordCheck').text('Паролі не співпадають!');
            $('#passwordCheck').css('color', 'red');
            $('#passwordCheck').removeClass('hide');*/
            $('#confirmationHelp').text('Паролі мають співпадати');
            $('#confirmationHelp').css('color', '#1E8ACF');
            passwords_match = false;
            $('.form-actions button[type=submit]').addClass('disabled');
            document.getElementById("signupButton").disabled = true;
            /*setTimeout(function() {
                $('#passwordCheck').addClass('hide');
            }, 3000);*/
        }
    }
}

function checkPass2() {
    $('#confirmationField').popover('hide');
    $('#confirmationField').removeClass('invalid');
    $('#confirmationField').removeClass('valid');
}

// Initializing AJAX response modal dialog
$('#pageAJAXresponse').modal({
    backdrop: 'static',
    keyboard: false,
    show: false
});

// Posting form using ajax
$('#signupForm').submit(function(event) {
    event.preventDefault();
    var formData = $('#signupForm').serialize();
    var email = $('#emailField').serialize();
    var pass = $('#passwordField').serialize();
    var auth_url = "auth.php";
    $.ajax({
        url: $(this).attr('action'),
        type: "POST",
        data: formData,
        dataType: "json",
        success: function(data) {
            $('#pageAJAXresponseLabel').text(data.title);
            $('#pageAJAXresponse div.modal-body p').text(data.msg);
            $('#pageAJAXresponse').modal('show');
            <? if (isset($_SESSION['auth'])): ?>
                $.ajax({
                    url: auth_url,
                    type: "POST",
                    data: "action=logout",
                    success: function() {
                        $.ajax({
                            url: auth_url,
                            type: "POST",
                            data: email+"&"+pass+"&action=login",
                            success: function() {
                                $('#pageAJAXresponse div.modal-footer button').click(function () {
                                    window.location.href = "<?=PROFILE_URL?>";
                                });
                            }
                        });
                    }
                });
                <? else: ?>
                $.ajax({
                    url: auth_url,
                    type: "POST",
                    data: email+"&"+pass+"&action=login",
                    success: function() {
                        $('#pageAJAXresponse div.modal-footer button').click(function () {
                            window.location.href = "<?=PROFILE_URL?>";
                        });
                    }
                });
                <? endif; ?>
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

function checkEmail() {
    if ($('#emailField').is(':valid')) {
        $.ajax({
            type: "POST",
            url: "checkemail.php",
            data: { email: $('#emailField').val() },
            dataType: "json",
            success: function(data, textStatus, jqXHR) {
                $('#emailHelp').css('color', '#54c516');
                $('#emailHelp').text(data);
                $('#emailField').removeClass('invalid');
                $('#emailField').addClass('valid');
                email_free = true;
                if (age_selected && passwords_match && email_free && sex_selected) {
                    $('.form-actions button[type=submit]').removeClass('disabled');
                    $('.form-actions button[type=submit]').prop('disabled', false);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#emailHelp').css('color', '#da4f49');
                $('#emailHelp').text($.parseJSON(jqXHR.responseText));
                $('#emailField').removeClass('valid');
                $('#emailField').addClass('invalid');
                email_free = false;
                $('.form-actions button[type=submit]').addClass('disabled');
                document.getElementById("signupButton").disabled = true;
            }
        });
    }
    else if ($('#emailField').val() == "") {
        $('#emailField').removeClass('invalid');
        $('#emailHelp').removeAttr('style');
        $('#emailHelp').text('Використовуватиметься в якості логіна та для нагадування паролю');
    }
}
</script>

<? else:
    include_once('unauthorized.php');
endif; ?>
<? include_once('footer.php'); ?>