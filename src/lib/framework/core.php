<?php
/**
 * @autor: Daniel Drexlmaier
 * Copyright �2004-2012 Daniel Drexlmaier. All Rights Reserved.
 * This file may not be redistributed in whole or significant part.
 */


if(!function_exists('_nachricht')){
	/**
	 * Standard nachricht, Mit Jquery wird es zum Popup
	 *
	 * @param string $body Inhalt
	 * @param string $type Nachrichtentyp
	 * @return string
	 */
	function _nachricht($body, $type = 'n')
	{
		static $id = 0;
		switch ($type){
			case 'i':
				$tpl_class 	= 'alert-info';
				break;
			case 'ok':
				$tpl_class 	= 'alert-success';
				break;
			case 'e':
				$tpl_class 	= 'alert-error';
				break;
			default:
				$tpl_class 	= 'alert-info';
		}
		++$id;
		return '<div class="alert '.$tpl_class.'"> '.$body.'</div>';

	}
}



function mime_type($filename) {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'mp4' => ' video/mp4',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } else {
            return 'application/octet-stream';
        }
    }

/**
 * Arbeitsspeicherauslastung mit Byteumwandlung
 * @return string Größe mit Maßeinheit
 *
 */
	function getMemoryUsage()
	{
        return humanSize ( memory_get_usage(true) );
    }
	/**
	 * Ermittelt die Max upload größe
	 */
	function getMaxUploadSize(){
		if(ini_get('file_uploads') == "1"){
			return floor(
						min(
							array(
								(double) ini_get('memory_limit'),
								(double) ini_get('post_max_size'),
								(double) ini_get('upload_max_filesize')
							)
						)
					).' MB';
		} else {
			return '-1';
		}
	}
	function humanDate($date){
		return DDTime::niceDate($date);
	}
    /**
     * Zeigt eine Größe als Menschenlesbar na
     * http://de.wikipedia.org/wiki/Byte
     * @param int $mem_usage
     * @return string
     */
    function humanSize($size)
    {
    	$size = (double) $size;
   	 	if ($size < 1024){
            return $size." Byte";

   	 	} elseif ($size < (1024*1024) ){
            return round($size/1024,2)." KiB";

        }elseif ($size < (1024*1024*1024) ){
            return round($size/(1024*1024),2)." MiB";

        }else{
            return round($size/(1024*1024*1024),2)." GiB";
		}

    }
    function formatNumber($number,$decimals = 0){
    	return number_format((double)$number, $decimals, ',', '.');


    }



function findLanguage(){
	if(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])){
		$languages = strtolower( $_SERVER["HTTP_ACCEPT_LANGUAGE"] );
		 #$languages = ' fr-ch;q=0.3, da, en-us;q=0.8, en;q=0.5, fr;q=0.3';
		// need to remove spaces from strings to avoid error
		$languages = str_replace( ' ', '', $languages );
		$languages = explode( ',', $languages );
		foreach ( $languages as $language_list )
		{
			// pull out the language, place languages into array of full and primary
			// string structure:
			$temp_array = array();
			// slice out the part before ; on first step, the part before - on second, place into array
			$temp_array[0] = substr( $language_list, 0, strcspn( $language_list, ';' ) );//full language
			$temp_array[1] = substr( $language_list, 0, 2 );// cut out primary language
			//place this array into main $user_languages language array
			$user_languages[] = $temp_array;
		}
		return trim($user_languages[0][1]);
	} else {
		return 'de';
	}
}



/**
 * Ausnahme Klasse
 * @author drexlmaier
 *
 */
		class DD_Exception extends Exception
		{
		    public function __toString() {
		    	echo '<pre>'.$this->getTraceAsString().'</pre>';
		        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
		    }

		}


