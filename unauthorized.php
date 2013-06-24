<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Black Angel
 * Date: 19.06.13
 * Time: 22:53
 * To change this template use File | Settings | File Templates.
 */

if (session_status() != PHP_SESSION_ACTIVE) session_start(); # PHP >= 5.4.0

?>
<? include_once('page_start.php') ?>
                <!-- Unauthorized -->
                <div id="unauthorized" class="alert alert-block alert-error fade in">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <h4 class="alert-heading">Щось пішло не так!</h4>
                    <p id="message">Ця сторінка доступна лише для авторізованих користувачів.
                        Ви можете увійти або зареєструватися, щоб мати можливість її переглядати.</p>
                    <p id="buttons">
                        <a class="btn btn-danger" href="<?=SIGNUP_URL?>">Зареєструватися</a> <a class="btn" href="<?=HOME_URL?>">На головну</a>
                    </p>
                </div>
<? include_once('page_finish.php') ?>