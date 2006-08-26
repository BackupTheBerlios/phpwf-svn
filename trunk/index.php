<?php

require ("sip/conf/main.conf.php");
include(strtolower("sip/".$WFconf[applicationbaseclass]).".php");

$myApp = new $WFconf[applicationbaseclass]();


?>
