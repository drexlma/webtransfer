<?php


/**
 * @author: Daniel Drexlmaier
 */
#ob_start("ob_gzhandler");
ob_start();
date_default_timezone_set('Europe/Berlin');

	//	Arbeitsverzeichniss ermitteln
 	$path_parts = pathinfo(__FILE__);
	define('arbeitsverzeichniss', realpath($path_parts['dirname'].'/../'));

	// Pfad für PHP Fehlermeldungen
	define('_cnf_log_path_php', arbeitsverzeichniss.'/logs/error.log');
	// Pfad für Mail Fehlermeldungen
	define('_cnf_log_path_mail', arbeitsverzeichniss.'/logs/mail.log');
	// Pfad für Mysql Fehlermeldungen
	define('_cnf_log_path_mysql', arbeitsverzeichniss.'/logs/mysql.log');
	// Pfad für Fehlerhafte zugriffe
	define('_cnf_log_path_access', arbeitsverzeichniss.'/logs/access.log');
	define('_cnf_log_path_thumbs', arbeitsverzeichniss.'/logs/thumbs.log');


	define('DEBUG', false);
	// Pfad für die Templates
	define('_cnf_template_folder', realpath(arbeitsverzeichniss.'/templates/').'/');
	// Pfad für Compilierte Templates
	define('_cnf_template_c_folder', realpath(arbeitsverzeichniss.'/tmp/templates_c/').'/');
	// Pfad für PHP Session
	define('_cnf_session_path', realpath(arbeitsverzeichniss.'/tmp/session').'/');
	define('_cnf_upload_tmp_dir', realpath(arbeitsverzeichniss.'/tmp/upload_tmp_dir').'/');



	if(ini_get('magic_quotes_gpc')==false){
		#die('magic_quotes_gpc error');
		function magicQuotes_Addslashes(&$value, $key) {
			$value = addslashes($value);
		}
    	$gpc = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
    	array_walk_recursive($gpc, 'magicQuotes_Addslashes');
	}



	/**
	*	Verfallsdatum der seite.
	* 	ist eher wichtig für den Browsercache das es da zu keinen Problemen kommt und man immer die neuste Seite hat
	*/
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') .' GMT');

	#header("Expires: ".gmdate("D, d M Y H:i:s", time()+(86400*30))." GMT");
    #header("Last-Modified: ".gmdate("D, d M Y H:i:s", time())." GMT");

	header('Pragma: no-cache');
	header('Cache-Control: no-store, no-cache, max-age=0, must-revalidate');

	$charset = 'utf-8';
    header('Content-Type: text/html; charset='.$charset);

    // Clickjacking-Problem für IE
    // http://www.heise.de/newsticker/meldung/Clickjacking-Problem-in-Browsern-bleibt-bestehen-907651.html
    header('X-FRAME-OPTIONS: DENY');


    /**
     * Standard PHP Einstellungen
     */
	ini_set('display_errors', 0);
	#ini_set('error_reporting', E_ALL  );
	#ini_set('error_reporting', E_ALL  & ~E_NOTICE  );
	ini_set('log_errors', 1);
	ini_set('error_log', 'syslog');
	#ini_set('error_log', _cnf_log_path_php );
	#ini_set('session.save_path', _cnf_session_path );

	//ini_set('memory_limit', '20M');
	ini_set('session.cookie_lifetime', 60*60*24*30*6);
	ini_set('session.cookie_httponly', true);
	#set_time_limit(10);
	session_start();

	/**
	 * Upload
	 */
	#ini_set('upload_tmp_dir', arbeitsverzeichniss.'/tmp/upload/');
	//ini_set('upload_max_filesize', '10M');
	ini_set('max_input_time', '2600');
	//ini_set('post_max_size', '10M');



	 $userregex = '([0-9A-ZÄÖÜ\- _\.~])';
 // '.$userregex.'*$/i', $_lang['regex']['userregex'])


$url_impressum = 'http://www.xxx.de/impressum.html';
$mail_admin_uploadinfo = 'admin@DOMAIN.de';

$mail_allow_owner = array('@DOMAIN.de');