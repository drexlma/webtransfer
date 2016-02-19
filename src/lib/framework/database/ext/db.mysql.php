<?php
/**
 * Daniel Drexlmaier
 */
/**
 * Klasse um auf eine Mysql Datenbank zuzugreifen
 * @autor: Daniel Drexlmaier
 * @since: 24.06.2004
 */
class DB_MySQL extends DB
{
	private $_database;

  /**
   * Öffnet eine Verbindung zu einem Datenbank-Server
   *
   * @param String $host Hostname
   * @param String $database Datenbankname
   * @param String $user Username
   * @param String $pass Passwort
   */
  protected function connect($host, $database, $user, $pass)
  {
    $this->connection = mysql_connect(
      $host,
      $user,
      $pass,
      TRUE
    );

    if (!$this->connection) {
      throw new DB_Exception(
        'Konnte keine Verbindung zur Datenbank aufbauen.',
        mysql_errno()
      );
    }
	$this->_database = $database;
    if (!mysql_select_db($this->_database, $this->connection)) {
      throw new DB_Exception(
        'Konnte die gewünschte Datenbank nicht auswählen.',
        mysql_errno($this->connection)
      );
    }
  }

  function dump(){
  	require_once 'dump/mysqldump.class.php';
	#$connection = @mysql_connect($dbhost,$dbuser,$dbpsw);
	$dumper = new MySQLDump($this->_database,arbeitsverzeichniss.'/backup/database.'.date('W').'.sql.gz',true,false);
	$dumper->doDump();

  }
	function sqlinjet(){
		$v = " cmdshell
EXEC
UNION
infile
load data
0xbf27
DUMPFILE
/etc/passwd
hex(/etc/passwd)
/var/www/
hex(/var/www)
outfile //
load_file 	//
/*
--
or 1
where 1




REVOKE FILE ON *.* from 'USER_NAME'@'HOST_NAME';
http://old.justinshattuck.com/2007/01/18/mysql-injection-cheat-sheet/
http://ferruh.mavituna.com/sql-injection-cheatsheet-oku/
>>";
	}
  /**
   * Schließt die Verbindung zum Datenbank server
   *
   */
  public function disconnect()
  {
        if (is_resource($this->connection)) {
          mysql_close($this->connection);
         }
  }
  /**
   * Wieviele Felder gefunden wurden
   * @return int
   */
  public function found_rows()
  {
  	$count = 0;
  	$this->query("Select FOUND_ROWS() as anz");

	while (($row = $this->fetchRow()) == true) {
		$count = $row['anz'];
	}
	return $count;
	}
	/**
	 * Escaped String
	 * @param $val
	 * @return String
	 */
	function real_escape_string($val)
	{
		return mysql_real_escape_string($val);
	}
	function stat(){

		return explode('  ', mysql_stat($this->connection));
	}
/**
 * Führt eine Datenbankabfrage aus
 *
 * @param string $query
 */
  protected function _querydb($query,$showlog = true, $render = true)
  {

    if (is_resource($this->connection)) {
      if (is_resource($this->result)) {
        mysql_free_result($this->result);
      }

      $this->result = mysql_query(
        $query,
        $this->connection
      ) or die($this->_printError(mysql_error(),$query));


    }


  }
  /**
   * Gibt die geänderten Zeilen zurück
   *
   * @return int
   */
  public function get_affected_rows()
  {
        return mysql_affected_rows($this->connection);
  }
  /**
   * Gibt den letzten PrimID zurück
   *
   * @return int
   */
  public function get_last_insert_id()
  {
        return mysql_insert_id($this->connection);
  }
  /**
   * Läuft das Ergebnis der Datenbank durch
   *
   * @return mixed
   */
   public function fetchRow()
   {
    if (is_resource($this->result)) {
      $row = mysql_fetch_assoc($this->result);

      if (is_array($row)) {
        return $row;
      } else {
        return FALSE;
      }
    } else{
    	return false;
    }
  }
	function backup($file = 'filename.sql.last.gz'){
		$dumper = new MySQLDump($this->_database,$file,true,false);
		$dumper->doDump();
	}

}


/*

try {
  $mysql = new DB_MySQL(
    'localhost',
    'test',
    'root',
    'wrongPass'
  );

  $mysql->query('SELECT spalte FROM tabelle');

  while ($row = $mysql->fetchRow()) {
    // ...
  }
}

catch (DB_Exception $e) {
  printf(
    'Ein Datenbankfehler ist aufgetreten: %s (Error-Code: %s) in %s:%s',
    $e->getMessage(),
    $e->getCode(),
    $e->getFile(),
    $e->getLine()
  );
  echo "\n\n".'Stack trace:'."\n";
  print_r($e->getTrace());
}

catch (Exception $e) {
  printf(
    'Ein allgemeiner Fehler ist aufgetreten: %s (Error-Code: %s) in %s:%s',
    $e->getMessage(),
    $e->getCode(),
    $e->getFile(),
    $e->getLine()
  );
  echo "\n\n".'Stack trace:'."\n";
  print_r($e->getTrace());
}
*/
/*

require_once 'DB_MySQL.php';

$mysql = new DB_MySQL;
$mysql->connect('localhost', 'test', 'root', '');
$mysql->query('SELECT spalte FROM tabelle');

while ($row = $mysql->fetchRow()) {
  // ...
}

$mysql->disconnect();
*/
?>