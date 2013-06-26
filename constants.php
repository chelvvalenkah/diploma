<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Black Angel
 * Date: 18.06.13
 * Time: 4:19
 * To change this template use File | Settings | File Templates.
 */

define('CONF_NAME', 'EasyConf 2013', true);
define('CONF_SUBNAME', 'Всеукраїнська студентська конференція', true);
define('CONF_PLACE', 'НТУУ "КПІ", м. Київ', true);
define('CONF_DATES', '24-30 червня 2013', true);
define('CONF_DESC', CONF_SUBNAME.' «'.CONF_NAME.'» - це конференція, доступна для кожного студента.
 У нас Ви можете не хвилюватись через те, що Ви ще новачок чи недостатньо талановиті,
  адже поряд з Вам такі ж студенти, як і Ви! <br />Не відкладай на `завтра` - отримай новий досвід,
   знання та цікаві знайомства вже сьогодні!', true);
define('STAGE', 'registration', true);
define('ROOT_URL', '/', true);
define('HOME_PAGE', ROOT_URL.'home.php', true);
define('PROFILE_URL', ROOT_URL.'profile.php', true);
define('SIGNUP_URL', PROFILE_URL.'?signup', true);
define('APPLY_URL', ROOT_URL.'application.php', true);
define('LECTURES_URL', ROOT_URL.'lectures.php', true);
define('FLOW_URL', ROOT_URL.'flow.php', true);
define('SCHEDULE_URL', ROOT_URL.'schedule.php', true);
define('PROFILE_SCRIPT', ROOT_URL.'profile_handler.php', true);
define('APPLY_SCRIPT', ROOT_URL.'apply_handler.php', true);
define('LECTURES_SCRIPT', ROOT_URL.'lectures_handler.php', true);


?>