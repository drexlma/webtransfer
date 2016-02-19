<?php 


function sendMail($empfanger,$tpltype, Array $vars = array()){
	$header = 'From: noreply@'.$_SERVER['HTTP_HOST'].'' . "\r\n";

	if($tpltype == 'FileDownloadComplete'){
		$subject = 'Datei wurde erfolgreich runtergeladen';
		$message = ''.$vars['absender'].' hat Ihre Datei '.$vars['datei'].' erfolgreich am '.date('d.m.Y H:i:s').' runtergeladen.'."\r\n";
		$message .= 'User Agent: '.$_SERVER['HTTP_USER_AGENT']."\r\n";
		
	} elseif($tpltype == 'FileUploadextMail'){
		$subject = ''.$vars['absender'].' hat Ihnen eine neue Datei geschickt';
		$message = ''.$vars['absender'].' hat Ihnen eine neue Datei geschickt.'."\r\n"."\r\n";
		$message .= ''.$vars['nachricht'].' '."\r\n"."\r\n";
		if($vars['haltbarkeit'] > 0){
			$message .= 'Die Datei wird nach '.$vars['haltbarkeit'].' Tagen automatisch gelöscht.'."\r\n"."\r\n";
		} else {
			$message .= 'Die Datei wird sofort nach dem Download automatisch gelöscht.'."\r\n"."\r\n";
		}
		$message .= 'Name: '.$vars['datei']."\r\n";
		$message .= 'Größe: '.$vars['size']."\r\n";
		$message .= 'Link: '.$vars['link'];

		$header .= 'Reply-To: '.$vars['absender'].'' . "\r\n" ;

	}elseif($tpltype == 'FileUploadIntMail'){
		$subject = "=?UTF-8?B?".base64_encode(' Danke für den Upload')."?=";
		$message = 'Fertig hochgeladen'."\r\n"."\r\n";

		$message .= 'Ihre Datei/en wurden erfolgreich hochgeladen. Sobald ein Empfänger Ihre Datei heruntergeladen hat, werden Sie benachrichtigt.  '."\r\n"."\r\n";
		$message .= ''.$vars['nachricht'].' '."\r\n"."\r\n";
		if($vars['haltbarkeit'] > 0){
			$message .= 'Ihre Datei wird nach '.$vars['haltbarkeit'].' Tagen automatisch gelöscht'."\r\n"."\r\n";
		} else {
			$message .= 'Ihre Datei wird sofort nach dem Download automatisch gelöscht'."\r\n"."\r\n";
		}
		$message .= 'Empfänger: '.$vars['empfanger']."\r\n";
		$message .= 'Absender: '.$vars['absender']."\r\n";
		$message .= 'Name: '.$vars['datei']."\r\n";
		$message .= 'Größe: '.$vars['size']."\r\n";


	}
	
	$header .=  "MIME-Version: 1.0" . "\r\n" .
			"Content-type: text/plain; charset=UTF-8" . "\r\n";
	mail($empfanger, $subject, $message,$header);
}

function setFileDownloadComplete(Array $file, Array $empfanger, Array $absender){
	$GLOBALS['oop_db']->query( "UPDATE `file` SET anz_downloads = anz_downloads + 1 WHERE `file_id` =  ".intval($file['file_id'])." LIMIT 1");
	$GLOBALS['oop_db']->query( "UPDATE `empfanger` SET download_date = ".time()." WHERE empfanger_id = ".intval($empfanger['empfanger_id'])." AND `file_id` =  ".intval($file['file_id'])." LIMIT 1");
	sendMail($absender['mail'],'FileDownloadComplete', array('absender'=> $empfanger['mail'],'datei'=>$file['name']));
}

function getFile($id,$ac){
	$q = "SELECT `file_id`, `path`, `name`, `adddate`, `ip`, `accesscode`, `password`, `anz_downloads`, `haltbarkeit`  FROM `file` WHERE `file_id` =  ".intval($id)." AND accesscode = '".mysql_real_escape_string($ac)."'  LIMIT 1";
	$GLOBALS['oop_db']->query( $q);
	while ($row = $GLOBALS['oop_db']->fetchRow()) {

		return $row;
	}
	return false;
}
function cleanFiles(){
	$files = array();
	$q = "SELECT `file_id` FROM `file` WHERE haltbarkeit != '0' && ( adddate + (60*60*24*haltbarkeit) ) < ".time()." LIMIT 1";
	$GLOBALS['oop_db']->query( $q);
	while ($row = $GLOBALS['oop_db']->fetchRow()) {
		$files[] = $row;
	}
	foreach($files as $k => $v){
		deleteDBFile($v['file_id']);
	}
	 
}

function getAbsender($id){
	$q = "SELECT absender_id, mail  FROM `absender` WHERE `file_id` =  ".intval($id)."  LIMIT 1";
	$GLOBALS['oop_db']->query( $q);
	while ($row = $GLOBALS['oop_db']->fetchRow()) {
		return $row;
	}
	return false;
}
function deleteFile($id){

	$files = array();
	$q = "SELECT file_id, name, path  FROM `file` WHERE `file_id` =  ".intval($id)."  LIMIT 1";
	$GLOBALS['oop_db']->query( $q);
	while ($row = $GLOBALS['oop_db']->fetchRow()) {
		$files[] = $row;
	}
	foreach($files as $k => $v){
		if(file_exists($v['path']) && !is_dir($v['path'])){
			return unlink($v['path']);
		} else {
			return true;
		}
	}
	return false;
}
function deleteDBFile($id){
	if(deleteFile($id)){
		$q = "DELETE FROM `file` WHERE `file_id` =  ".intval($id)."  LIMIT 1";
		$GLOBALS['oop_db']->query( $q);
		return true;
	} else {
		return false;
	}
}

function getEmpfanger($id,$uid){
	$q = "SELECT empfanger_id, mail  FROM `empfanger` WHERE `file_id` =  ".intval($id)." and `empfanger_id` =  ".intval($uid)."  LIMIT 1";
	$GLOBALS['oop_db']->query( $q);
	while ($row = $GLOBALS['oop_db']->fetchRow()) {
		return $row;
	}
	return false;
}
