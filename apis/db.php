<?php
define("HOSTNAME", "localhost");
define("USERNAME", "root");
define("PASSWORD", "");
define("DATABASE", "r68_vue3_shop");

//mysqli
$db = new mysqli(HOSTNAME, USERNAME, PASSWORD, DATABASE);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
//charset
$db->set_charset("utf8");