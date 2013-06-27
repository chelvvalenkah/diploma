<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Black Angel
 * Date: 17.06.13
 * Time: 10:02
 * To change this template use File | Settings | File Templates.
 */

require_once('connect.php');
require_once('constants.php');
require_once('functions.php');
require_once('conf_user.php');
if (session_status() != PHP_SESSION_ACTIVE) session_start();
check_authorization($mysqli);

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Реєстрація на конференцію - <?=CONF_NAME?></title>
    <!-- Bootstrap -->
    <link rel="stylesheet" href="css/bootstrap.min.css" media="all" />
    <link rel="stylesheet" href="css/bootstrap-responsive.min.css" media="all" />
    <link href="css/style.css" rel="stylesheet" media="all" />

    <? if (isset($_SESSION['calendar_page']) && $_SESSION['calendar_page']): ?>
    <link rel="stylesheet" type="text/css" media="screen" href="./eventouchcalendar/style.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="./eventouchcalendar/includes/shadowbox-3.0.3/shadowbox.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="./eventouchcalendar/includes/jquery-minicolors/jquery.miniColors.css" />
    <!--[if lt IE 9]>
        <script src="./eventouchcalendar/html5.js"></script>

        <style type="text/css">
            .ui-draggable-dragging {
                filter: alpha(opacity=50);
                filter: progid:DXImageTransform.Microsoft.Alpha(opacity=50);
            }

            #agenda tbody span.ui-selecting {
                background: #569DE8;
                border-top: 1px solid #569DE8;
            }

            #topMask {
                position: absolute;

                background: #DDD;

                z-index: 10;

                filter: alpha(opacity=60);
                filter: progid:DXImageTransform.Microsoft.Alpha(opacity=60);
            }

            #bottomMask {
                position: absolute;

                background: #DDD;

                z-index: 10;

                filter: alpha(opacity=60);
                filter: progid:DXImageTransform.Microsoft.Alpha(opacity=60);
            }

            #leftMask {
                position: absolute;

                width: 0px;

                background: #DDD;

                z-index: 10;

                filter: alpha(opacity=60);
                filter: progid:DXImageTransform.Microsoft.Alpha(opacity=60);
            }

            #rightMask {
                position: absolute;

                width: 0px;

                background: #DDD;

                z-index: 10;

                filter: alpha(opacity=60);
                filter: progid:DXImageTransform.Microsoft.Alpha(opacity=60);
            }
        </style>
    <![endif]-->
    <? endif; ?>

    <!-- We NEED these SCRIPTS! -->
    <? if (isset($_SESSION['calendar_page']) && $_SESSION['calendar_page']): ?>
    <script type='text/javascript' src='./eventouchcalendar/includes/jquery-1.7.1.min.js'></script>
    <? else: ?>
    <script src="js/jquery-1.10.1.min.js"></script>
    <? endif; ?>
    <!--<script src="http://code.jquery.com/jquery-latest.js"></script>-->
    <script src="js/bootstrap.min.js"></script>

    <!-- Common scripts -->
    <script type="text/javascript">
        function fixedEncodeURIComponent (str) {
            return encodeURIComponent(str).replace(/[!'()]/g, escape).replace(/\*/g, "%2A");
        }
    </script>
</head>
<body>
<div class="container-fluid">
    <div id="header-container">
        <div id="header">
            <!-- Header -->
            <div class="row-fluid above-menu">
                <div class="span4">
                    <img class="logo" src="img/logo.png">
                </div>
                <div class="span4">
                    <blockquote>
                        <strong><?=CONF_SUBNAME?> &laquo;<?=CONF_NAME?>&raquo;</strong>
                        <small><em><?=CONF_PLACE.", ".CONF_DATES?></em></em></small>
                    </blockquote>
                </div>
                <div class="span4" id="authBlock">
                    <!-- Login form -->
                    <form id="loginForm" name="loginForm" action="auth.php" method="post"
                          class="authForm form-horizontal form-login<?if (isset($_SESSION['auth'])):?> hide<?endif;?>">
                        <div class="control-group">
                            <div class="controls">
                                <input type="text" class="span6" name="email" placeholder="Email" required="required">
                                <input type="password" class="span6" name="password" placeholder="Пароль" required="required">
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="controls">
                                <label class="checkbox span4">
                                    <input type="checkbox" name="remember" checked="checked">Запам'ятати
                                </label>
                                <button type="submit" class="btn btn-primary span3">Увійти</button>
                                <div class="btn-group span5">
                                    <a class="btn btn-warning span9" href="signup.php">Реєстрація</a>
                                    <button class="btn btn-warning dropdown-toggle span3" data-toggle="dropdown">
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a href="signup.php">Реєстрація</a></li>
                                        <li><a href="#forgot">Забули пароль?</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="action" value="login">
                    </form>

                    <!-- Profile form -->
                    <div id="profileBlock" class="row-fluid<?if (!isset($_SESSION['auth'])):?> hide<?endif;?>">
                        <div class="span12 alert alert-info">
                            <div class="span4">
                                <img src="img/avatar.gif" class="img-polaroid photo-header text-center" alt="Ваша фотографія" />
                            </div>
                            <div class="span8">
                                <div class="input-prepend">
                                    <span class="add-on"><i class="icon-user"></i></span>
                                        <input id="prependedInput" type="text" class="span10" disabled="disabled" value="<?=$_SESSION['userName']?>">
                                </div>
                                <form id="logoutForm" name="logoutForm" class="authForm" action="auth.php" method="post">
                                    <div class="btn-group span12">
                                        <a class="btn btn-mini btn-success" href="<?=PROFILE_URL?>">
                                            <? if ($_SESSION['role'] != 'admin'): ?><i class="icon-home"></i> <? endif; ?>Особистий кабінет
                                        </a>
                                        <? if ($_SESSION['role'] == 'admin'): ?>
                                        <a class="btn btn-mini btn-warning" href="settings.php"><i class="icon-wrench"></i></a>
                                        <? endif; ?>
                                        <button type="submit" class="btn btn-mini btn-danger">
                                            <? if ($_SESSION['role'] != 'admin'): ?><i class="icon-off"></i> <? endif; ?>Вихід
                                        </button>
                                    </div>
                                    <input type="hidden" name="action" value="logout">
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="navbar">
                <div class="navbar-inner">
                    <a class="brand" href="<?=ROOT_URL?>"><?=CONF_NAME?></a>
                    <ul class="nav">
                        <li<? if ($_SERVER['SCRIPT_NAME'] == ROOT_URL) echo ' class="active"' ?>><a href="<?=ROOT_URL?>">Головна</a></li>
                        <? if (!arg_exists_not_null($_SESSION['auth']) || $_SESSION['role'] == 'admin'): ?>
                        <li<? if ($_SERVER['SCRIPT_NAME'] == PROFILE_URL && $_SERVER['QUERY_STRING'] == 'signup') echo ' class="active"' ?>><a href="<?=SIGNUP_URL?>">Реєстрація</a></li>
                        <? endif; ?>
                        <? if (arg_exists_not_null($_SESSION['auth'])): ?>
                        <li<? if ($_SERVER['SCRIPT_NAME'] == PROFILE_URL && $_SERVER['QUERY_STRING'] == '') echo ' class="active"' ?>><a href="<?=PROFILE_URL?>">Профіль</a></li>
                        <li<? if ($_SERVER['SCRIPT_NAME'] == LECTURES_URL || ($_SERVER['SCRIPT_NAME'] == APPLY_URL &&
                            (arg_exists_not_null($_GET['view']) || arg_exists_not_null($_GET['edit'])))) echo ' class="active"' ?>><a href="<?=LECTURES_URL?>">Доповіді</a></li>
                        <? endif; ?>
                        <li<? if ($_SERVER['SCRIPT_NAME'] == SCHEDULE_URL) echo ' class="active"' ?>><a href="<?=SCHEDULE_URL?>">Розклад</a></li>
                        <li<? if ($_SERVER['SCRIPT_NAME'] == APPLY_URL && $_SERVER['QUERY_STRING'] == '') echo ' class="active"' ?>><a href="<?=APPLY_URL?>">Подати доповідь</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="headerAJAXresponse" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="headerAJAXresponseLabel" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="headerAJAXresponseLabel">Modal header</h3>
        </div>
        <div class="modal-body">
            <p>One fine body…</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" data-dismiss="modal" aria-hidden="true">ОК</button>
        </div>
    </div>

    <!-- Header scripts -->
    <script type="text/javascript" id="headerJS">
        // Initializing AJAX response modal dialog
        $('#headerAJAXresponse').modal({
            show: false
        });

        $('.authForm').submit(function(event) {
            event.preventDefault();
            //if (this != document.forms.loginForm) event.preventDefault();
            //var url = (this == document.forms.loginForm) ? "auth.php" : $(this).attr('action');
            var formData = $(this).serialize();
            $.ajax({
                url: $(this).attr('action'),
                type: "POST",
                data: formData,
                dataType: "json",
                success: function(data) {
                    $('#header-container').load('<?=$_SERVER['SCRIPT_NAME']?> #header', function() {
                        eval($('script#headerJS').text());
                    });
<?/* if (authNeeded): */?>
                    $('#page-container').load('<?=$_SERVER['REQUEST_URI']?> #page', function(responseText, textStatus, XMLHttpRequest) {
                        if (!$('script#pageJS').length && $('script#pageJS', responseText).length) {
                            var js = document.createElement('script');
                            js.id = 'pageJS';
                            js.innerHTML = $('script#pageJS', responseText).html();
                            $('#page-container').append($(js));
                        }
                        else eval($('script#pageJS').text());
                        //console.log(responseText);
                    });
<?/* endif; */?>
                    $('#headerAJAXresponseLabel').text(data.title);
                    $('#headerAJAXresponse div.modal-body p').text(data.msg);
                    $('#headerAJAXresponse').modal('show');
                    /*$('.ConfHeader').detach();
                    $.ajax({
                        url: "header.php",
                        type: "GET",
                        datatype: "html",
                        success: function(data) {
                            $('#page').before(data);
                        }
                    });*/
                    /*$('#headerAJAXresponseLabel').text(data.title);
                    $('#headerAJAXresponse div.modal-body p').text(data.msg);
                    $('#headerAJAXresponse').modal('show');*/
                },
                error: function(xhr, textStatus, errorThrown) {
                    var response = $.parseJSON(xhr.responseText);
                    $('#headerAJAXresponseLabel').text(response.title);
                    $('#headerAJAXresponse div.modal-body p').text(response.msg);
                    $('#headerAJAXresponse').modal('show');
                }
            })
        });
    </script>

<?php
echo PHP_EOL;
?>