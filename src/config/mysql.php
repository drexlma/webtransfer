<?php
/**
 * @date: 1.2.2004
 * @author Daniel Drexlmaier
 *
 *
 */

/**
 * Aufbau einer Datenbankverbindung
 */
require_once arbeitsverzeichniss.'/lib/framework/database/db.php';
require_once arbeitsverzeichniss.'/lib/framework/database/ext/db.mysql.php';
try {
  $oop_db = new DB_MySQL(
    'localhost',
    'se_webtransfer',
    'se_webtransfer',
    'MLKjYm6tXQ48c9uD'
  );


}catch (DB_Exception $e) {
 /* printf(
    'Ein Datenbankfehler ist aufgetreten: %s (Error-Code: %s) in %s:%s',
    $e->getMessage(),
    $e->getCode(),
    $e->getFile(),
    $e->getLine()
  );
  echo "\n\n".'Stack trace:'."\n";
  print_r($e->getTrace());*/
	echo 'Ein Datenbankfehler ist aufgetreten';
	exit;
}
/**
 * UTF-8
 */
$oop_db->exec('SET character_set_results = utf8;');
$oop_db->exec('SET character_set_client = utf8;');
$oop_db->exec("set session sql_mode = 'NO_UNSIGNED_SUBTRACTION';");
#$oop_db->query("SET SQL_log_bin = '".arbeitsverzeichniss."logs/mysql.log-update.log'");


