<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Black Angel
 * Date: 14.06.13
 * Time: 9:14
 * To change this template use File | Settings | File Templates.
 */

define('authNeeded', false);
define('sidebar', false);
require_once('constants.php');
if (session_status() != PHP_SESSION_ACTIVE) session_start(); # PHP >= 5.4.0

?>
<? include_once('header.php'); ?>
<? if (!authNeeded || (authNeeded && isset($_SESSION['auth']))): ?>
<? include_once('page_start.php') ?>
                <!-- Page content -->
                <form id="signupForm" name="signupForm" class="form-horizontal" action="new_user.php" method="post" autocomplete="on">
                    <fieldset>
                        <legend>Реєстрація на конференцію</legend>
                        <div class="control-group">
                            <label for="surnameField" class="control-label required">Прізвище</label>
                            <div class="controls">
                                <input type="text" id="surnameField" class="input-xlarge" name="surname" required="required"
                                        placeholder="Іванов" maxlength="32" title="Тільки літери" pattern="[А-яІіЇїЄєЁёA-z'\s\-]{1,32}" />
                                <span class="help-inline multiline" id="fio">
                                    Прізвище, ім’я та назва компанії будуть надруковані на бейджі відвідувача
                                </span>
                            </div>

                        </div>
                        <div class="control-group">
                            <label for="nameField" class="control-label required">Ім'я</label>
                            <div class="controls">
                                <input type="text" id="nameField" class="input-xlarge" name="name" required="required"
                                        placeholder="Петро" maxlength="32" title="Тільки літери" pattern="[А-яІіЇїЄєЁёA-z'\s\-]{1,32}" />

                            </div>
                        </div>
                        <div class="control-group">
                            <label for="companyField" class="control-label">Компанія</label>
                            <div class="controls">
                                <input type="text" id="companyField" class="input-xlarge" name="company"
                                        placeholder="ТОВ &quot;Рога та копита&quot;" maxlength="32"
                                        title="Допустимі літери та символи &laquo;&quot;' ,.-&raquo;" pattern="[А-яІіЇїЄєЁёA-z&laquo;\'&quot;\-,\.\s&raquo;]{1,32}" />

                            </div>
                        </div>
                        <div class="control-group">
                            <label for="positionField" class="control-label">Посада</label>
                            <div class="controls">
                                <input type="text" id="positionField" class="input-xlarge" name="position"
                                        placeholder="Генеральний директор" maxlength="32" title="Допустимі літери, дефіс, коми та пробіли"
                                        pattern="[А-яІіЇїЄєЁёA-z\-,\s]{1,32}" />

                            </div>
                        </div>
                        <div class="control-group">
                            <label for="ageField" class="control-label required">Вік</label>
                            <div class="controls">
                                <div class="btn-group btn-block" data-toggle="buttons-radio" data-toggle-name="ageRadio">
                                    <button type="button" class="btn" name="S">18-23</button>
                                    <button type="button" class="btn" name="M">24-35</button>
                                    <button type="button" class="btn" name="L">36-45</button>
                                    <button type="button" class="btn" name="XL">від 46</button>
                                </div>
                                <input type="hidden" id="ageField" name="age" required="required" />
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
                                <div class="btn-group btn-block" data-toggle="buttons-radio" data-toggle-name="sexRadio">
                                    <button type="button" class="btn" name="M">Чоловіча</button>
                                    <button type="button" class="btn" name="F">Жіноча</button>
                                </div>
                                <input type="hidden" id="sexField" name="sex" required="required" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="emailField" class="control-label required">Email</label>
                            <div class="controls">
                                <input type="email" id="emailField" class="input-xlarge" name="email" required="required" placeholder="address@domain.com"
                                        pattern="^[A-z][A-z0-9\._-]{0,21}[A-z0-9]@[A-z0-9][A-z0-9\.-]{0,30}[A-z0-9]\.[A-z]{2,7}" title="address@domain.com"
                                        maxlength="64" oninput="checkEmail()" onchange="checkEmail()" onpaste="checkEmail()" onkeyup="checkEmail()" />
                                <span id="emailHelp" class="help-inline multiline two-lines">Використовуватиметься в якості логіна та для нагадування паролю</span>
                            </div>
                        </div>
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
                        <div class="control-group">
                            <label for="siteField" class="control-label">Веб-сторінка</label>
                            <div class="controls">
                                <input type="text" id="siteField" class="input-xlarge" name="web" placeholder="http://best-kyiv.org/"
                                        title="URL веб-сайту має бути у форматі: http://best-kyiv.org" maxlength="64"
                                        pattern="((ftp|https?)://)?([A-z0-9]+(\-|\.)?[A-z0-9]+?){1,20}\.[A-z]{2,7}(/|([A-z0-9\-?\[\]\.=&%;#!]+/?)+)?" />

                            </div>
                        </div>
                        <div class="control-group">
                            <label for="phoneField1" class="control-label required">Телефон</label>
                            <div class="controls">
                                <input type="tel" id="phoneField1" class="input-medium-btn" name="phone1" placeholder="+38(0__) ___-__-__"
                                        pattern="\+38\s?\(?0([0-9]{2}\)?\s?[0-9]{3}|[0-9]{3}\)?\s?[0-9]{2}|[0-9]{4}\)?\s?[0-9])[\s-]?[0-9]{2}[\s-]?[0-9]{2}"
                                        title="+38(0__) ___-__-__" required="required" maxlength="20" />
                                <button type="button" id="addNumber" class="btn">ще 1 номер</button>
                                <span class="help-inline">Потрібний для зв’язку та нагадування паролю</span>
                            </div>
                        </div>
                        <div class="control-group hide" id="phone2Controls">
                            <label for="phoneField2" class="control-label">Телефон 2</label>
                            <div class="controls">
                                <input type="tel" id="phoneField2" class="input-xlarge" name="phone2" placeholder="+38(0__) ___-__-__"
                                       pattern="\+38\s?\(?0([0-9]{2}\)?\s?[0-9]{3}|[0-9]{3}\)?\s?[0-9]{2}|[0-9]{4}\)?\s?[0-9])[\s-]?[0-9]{2}[\s-]?[0-9]{2}"
                                       title="+38(0__) ___-__-__" maxlength="20" />
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" id="signupButton" class="btn btn-large btn-primary disabled" disabled="disabled">Зареєструватися</button>
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

    <!-- Registration form scripts -->
    <script type="text/javascript" id="pageJS">
        var age_selected = false;
        var passwords_match = false;
        var email_free = false;
        var sex_selected = false;

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
                $('#signupButton').removeClass('disabled');
                $('#signupButton').prop('disabled', false);
            }
        });

        $('div[data-toggle-name=sexRadio] button').click(function() {
            $('#sexField').attr('value', $(this).attr('name'));
            sex_selected = true;
            if (sex_selected && passwords_match && email_free && sex_selected) {
                $('#signupButton').removeClass('disabled');
                $('#signupButton').prop('disabled', false);
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
                        $('#signupButton').removeClass('disabled');
                        $('#signupButton').prop('disabled', false);
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
                    $('#signupButton').addClass('disabled');
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
                            $('#signupButton').removeClass('disabled');
                            $('#signupButton').prop('disabled', false);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        $('#emailHelp').css('color', '#da4f49');
                        $('#emailHelp').text($.parseJSON(jqXHR.responseText));
                        $('#emailField').removeClass('valid');
                        $('#emailField').addClass('invalid');
                        email_free = false;
                        $('#signupButton').addClass('disabled');
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