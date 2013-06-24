<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Black Angel
 * Date: 18.06.13
 * Time: 11:02
 * To change this template use File | Settings | File Templates.
 */

preg_match('~(?:(?:ftp|https?)://)?(?:[A-z0-9]+[\-\.]?[A-z0-9]+?){1,20}\.[A-z]{2,7}(?:/(?:[A-z0-9\-?\[\]\.=&%;#!]+/*)+)?~i',
    'http://www.suck-dick.h0t-bab-es.reaaly-hard-dick-suckers.READER.services.googleusercontent.google.com.ua/tracker.php?name=ok%20%C4&age=15;25#!home', $matches);
var_dump($matches);

preg_match('#\+38\s?\(?0(?:[0-9]{2}\)?\s?[0-9]{3}|[0-9]{3}\)?\s?[0-9]{2}|[0-9]{4}\)?\s?[0-9])[\s-]?[0-9]{2}[\s-]?[0-9]{2}#',
    '+38(06554)91574', $phone);
var_dump($phone);

?>