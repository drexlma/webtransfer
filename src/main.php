<?php


    /**
     * @date: 26.10.2012
     * @author
     *
     *
     */
    
    // Prüfen ob die PHP Version aktuellist
    if (version_compare(phpversion(), '5.4.0', '<')===true) {
        echo '<div style="font:12px/1.35em arial, helvetica, sans-serif;">';
        echo '<div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;">';
        echo '<h3 style="margin:0; font-size:1.7em; font-weight:normal; text-transform:none; text-align:left; color:#2f2f2f;">';
        echo 'Whoops, it looks like you have an invalid PHP version.';
        echo '</h3></div>';
        echo '<p>We supports PHP 5.4.0 or newer. </p>';
        echo '</div>';
        exit;
    }
    // Eindeutige ID jedes Aufrufs
    $uniqid = uniqid();
    
    // Standardeinstellungen laden
    require 'config/default.php';
    
    // Domain spezifische einstellung
    $fname = preg_replace('/[^a-z0-9\.\-_]/i', '', $_SERVER['HTTP_HOST']) . '.inc.php';
    if(file_exists(__dir__.'/config/'.$fname) && is_file(__dir__.'/config/'.$fname) && is_readable(__dir__.'/config/'.$fname)){
    	require __dir__.'/config/'.$fname;
    }
    
    // Datenbankanbindung laden
    require arbeitsverzeichniss.'/config/mysql.php';
    require arbeitsverzeichniss.'/lib/fnc.php';    
    require arbeitsverzeichniss.'/lib/framework/core.php';



    
    
