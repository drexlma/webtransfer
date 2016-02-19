<?php
/**
 * Daniel Drexlmaier 2004
 */
/*
trait DB_Security{


 	 protected function quote($string){
 	 	return $string;
 	 }
}*/
class DB_Exception extends Exception {}

abstract class DB
{
	#use DB_Security;

	public static $query_id = 0;
	protected $result;
	protected $connection;
	public function __construct($host, $database, $user, $pass)
	{
    	$this->connect($host, $database, $user, $pass);
 	 }


  abstract protected function connect($host, $database, $user, $pass);
  abstract function disconnect();
  abstract function found_rows();
  abstract function real_escape_string($val);
  abstract protected function _querydb($query);
  abstract function get_affected_rows();
  abstract function get_last_insert_id();
  abstract function fetchRow();

  public function query($query,$showlog = false, $render = false)
  {
  	 #   trigger_error("Deprecated function DB::query(".$query.") called.", E_USER_DEPRECATED );
  	    return $this->_query($query,$showlog, $render);
  }
  protected function _query($query,$showlog = false, $render = false)
  {

  	$query = trim($query);
  	++self::$query_id;
  	if( strpos($query,'*') !== false){
  		$bgcol = 'CF3333';
  	} elseif( strpos($query,'UPDATE') === 0){
  		$bgcol = '89691C';
  	} else if (strpos($query,'DELETE') === 0){
  		$bgcol = 'DFA7A9';
  	} else if (strpos($query,'SELECT') === 0){
  		$bgcol = '3E6D31';
  	} else if (strpos($query,'SET') === 0){
  		$bgcol = '7F7977';
  	} else {
  		$bgcol = '1C3482';
  	}
	if($render === true){
  		$time_start = microtime(true);
	}
	#$showlog = true;
  	if($showlog === true){
	  	echo '<div style="float:left;width:80%; border:1px solid #77919F; padding:2px; margin:2px; background-color:#'.$bgcol.'; font-size:10px;">';
	  	echo '<b>'.self::$query_id.':</b> ';
	  	echo str_replace(
	  					array(
	  						'SQL_CALC_FOUND_ROWS',
	  						'FOUND_ROWS()'
	  					) ,
	  					array(
	  						'<span style="color:#AF9415;  font-size:10px;">SQL_CALC_FOUND_ROWS</span>',
	  						'<span style="color:#AF9415;  font-size:10px;">FOUND_ROWS()</span>'
	  					) ,
	  					$query
	  			) ;

	  	echo "</div>";
  	}

   	$r = $this->_querydb($query);

  	if($render === true){
		$time_end = microtime(true);
		$secs = ($time_end - $time_start);
		#file_put_contents(arbeitsverzeichniss.'/tmp/mysql_query.log', self::$query_id.';'.time().';'.$secs.';'.$query."\n", FILE_APPEND | LOCK_EX);

		if($secs > 0.01){
			file_put_contents(arbeitsverzeichniss.'/tmp/mysql_veryslow_query.log', self::$query_id.';'.time().';'.$secs.';'.$query."\n", FILE_APPEND | LOCK_EX);
			$bgcol = 'red';
		} else if($secs > 0.002){
			file_put_contents(arbeitsverzeichniss.'/tmp/mysql_slow_query.log', self::$query_id.';'.time().';'.$secs.';'.$query."\n", FILE_APPEND | LOCK_EX);
			$bgcol = 'orange';
		}else {
			$bgcol = 'green';
		}
		if(isset($this->duplselect[md5($query)])){
			file_put_contents(arbeitsverzeichniss.'/tmp/mysql_duplicate_query.log', self::$query_id.';'.time().';'.$secs.';'.$query."\n", FILE_APPEND | LOCK_EX);
			$secs = 'XXX '.$secs;
		} else {
			$this->duplselect[md5($query)] = true;
		}
		if($showlog === true){
			echo '<div style="float:right;clear:right;	width:15%; border:1px solid #77919F; padding:2px; margin:2px; background-color:'.$bgcol.'; font-size:10px;">';
		  	echo '<b>'.$secs.'sec</b> ';

		  #	echo '(<a href="javascript:alert(\''.str_replace("\n",'\n',addslashes(print_r(debug_backtrace(),1))).'\')">dump</a>)';
		  	echo '</div>';
		}

		#file_put_contents(arbeitsverzeichniss.'/tmp/mysql_dump.log', self::$query_id.';'.time().';\''.serialize(debug_backtrace())."'\n", FILE_APPEND | LOCK_EX);

  	}
  	return $r;

  }

  // select("Selelct id from x Where lastname=? and Age=?", );
  // params = array('m','18);
	 public function select($sql, Array $params = array()){
	 	return $this->exec($sql,$params);

	 }
	 function read($sql, Array $params = array()){
	 	$this->select($sql,$params);
	 	$array = array();
	 	while ($row = $this->fetchRow()) {
	 		$array[] = $row;
	 	}
	 	mysql_free_result($this->result);
	 	return $array();

	 }
	 public function exec($sql, Array $params = array()){
	 	foreach($params as $k => $v){
	 		$sql = str_replace($k,"'".$this->real_escape_string($v)."'",$sql);
	 	}
	 	return $this->_query($sql);

	 }
/**
 * Query Builder
 * Baut ein SQL Query selber zusammen
 *
 * @param string $table
 * @param array $felder
 * @return int
 */
	public function insert($table, Array $felder, $option = '')
	{
		$query = "INSERT $option INTO `$table` SET ";
		$queryfelder = array();
		foreach($felder as $k => $v){
			$queryfelder[] = "`$k` = '".$this->real_escape_string($v)."'";
		}
		$query .= " ". implode(' , ',$queryfelder);
		
		$this->_query($query);

		return $this->get_last_insert_id();
	}
	/**
	 * Query Update builder
	 *
	 * @param string $table
	 * @param array $felder
	 * @param array $where
	 * @return string
	 */
	public function update($table, Array $felder,Array $where)
	{
		$query = "UPDATE `$table` SET ";
		$queryfelder = array();
		$querywhere = array();
		foreach($felder as $k => $v){
			if(!is_array($v)){
				$queryfelder[] = "`$k` = '".$this->real_escape_string($v)."'";
			} else {
				throw new DB_Exception('array is not allowed');
				#$queryfelder[] = "`$k` = ".$this->real_escape_string($v[0])." ";

			}
		}
		foreach($where as $k => $v){
			$querywhere[] = "`$k` = '".$this->real_escape_string($v)."'";
		}
		$query .= " ". implode(' , ',$queryfelder);
		if(count( $querywhere ) > 0){
			$query .= " WHERE ". implode(' , ',$querywhere);
		}
		$this->_query($query);
		return $this->get_affected_rows();
	}


	public function delete($table, Array $where)
	{
		if(count($where) <= 0){
			throw new DB_Exception('no where');
		}
		$query = "DELETE FROM `$table` ";
		$querywhere = array();

		foreach($where as $k => $v){
			$querywhere[] = "`$k` = '".$this->real_escape_string($v)."'";
		}
		if(count( $querywhere ) > 0){
			$query .= " WHERE ". implode(' , ',$querywhere);
		}
		$this->_query($query);
		return $this->get_affected_rows();
	}

function sql_highlights($string){
	$repl['=']						= '#FF0099';
	$repl['SELECT']					= '#990066';
	$repl['*']						= '#990099';
	$repl['FROM']					= '#990066';
	$repl['LIMIT']					= '#990066';
	$repl['WHERE']					= '#990066';
	$repl['DELETE']					= '#990066';
	$repl['INSERT']					= '#990066';
	$repl['INTO']					= '#990066';
	$repl['SET']					= '#990066';
	$repl['UPDATE']					= '#990066';

	$repl['N‰chste Autoindex']		= '#999900';
	$repl['Betroffene Datens‰tze']	= '#999900';

	$repl2['SELECT']				= '<br>&nbsp;&nbsp;&nbsp;';
	$repl2['UPDATE']				= '<br>&nbsp;&nbsp;&nbsp;';
	$repl2['INTO']					= '<br>&nbsp;&nbsp;&nbsp;';
	$repl2[',']						= '<br>&nbsp;&nbsp;&nbsp;';
	$repl2['SET']					= '<br>&nbsp;&nbsp;&nbsp;';
	$repl2['FROM']					= '<br>&nbsp;&nbsp;&nbsp;';
	$repl2['WHERE']					= '<br>&nbsp;&nbsp;&nbsp;';

	$repl3['FROM']					= '<br>';
	$repl3['SET']					= '<br>';
	$repl3['LIMIT']					= '<br>';
	$repl3['WHERE']					= '<br>';
	/*
	*	F¸gt danach was ein
	*/
		foreach($repl2 as $k => $v){
			$string = str_ireplace($k,$k.$v,$string);
		}
	/*
	*	F¸gt davor was ein
	*/
		foreach($repl3 as $k => $v){
			$string = str_ireplace($k,$v.$k,$string);
		}
	/*
	*	Ersetzt etwas mit farben
	*/
		foreach($repl as $k => $v){
			$string = str_ireplace($k,'<font color="'.$v.'">'.$k.'</font>',$string);
		}

	preg_match_all("|`(.*)`|", $string, $pregs);
	for ($j=0; $j<sizeof($pregs[0]); $j++) {
		$string = str_ireplace($pregs[0][$j],'<font color="#339900">`'.$pregs[1][$j].'`</font>',$string);
	}

	return '<pre style="border:1px solid black;padding:5px;">'.$string.'</pre>';
}
	protected function _printError($error,$query)
	{
		if(defined('DEBUG') && DEBUG === true){

			echo '<h1>MySQL Error</h1>';
			echo $error.' <h1>Query</H1> '.$this->sql_highlights($query);
			echo getDebugBacktrace();
		} else {
			echo 'DB flops';
		}

		error_log ($_SERVER['HTTP_USER_AGENT']. ' '.$error.' query: '.$query."|".print_r($_SERVER,1)."|".print_r($_REQUEST,1), 3, _cnf_log_path_mysql);;

	#	echo '<pre>';
	#	var_dump(debug_backtrace());
	#	echo '</pre>';

		exit;

	}


  /**
   * Wird beim Zerstören des Objektes aufgerufen. Datenbankverbindung wird geschlossen
   *
   */
   public function __destruct()
   {
    	$this->disconnect();
 	}
}
/**
 *
 * @author drexlmaier
 *
 */
class SQL_Where
{
	#use DB_Security;

	protected function quote($string){
		return $string;
	}
	/**
	 * Welche Felder für die SQL Abfrage erlaubt sind
	 * @var array
	 */
	private $_where_field_list = array();
	/**
	 * WHERE teil der zusammen gebaut wird
	 * @var array
	 */
	private $_where = array();
	/**
	 * Verbundsmethode
	 * @var string
	 */
	private $_whereoption = 'AND';
	/**
	 * Sortierung für das WHERE
	 * @var array
	 */
	private $_orderby = array();


	/**
	 * Setzt die Felder für Where
	 * @param Array $liste
	 */
	function __construct(Array $liste = array())
	{
		$this->setWhere_field_list($liste);
	}
	/**
	 * Setzt die Felder für Where
	 * @param Array $liste
	 */
	function setWhere_field_list(Array $liste = array())
	{
		$this->_where_field_list = $liste;
		$this->_where_field_list['site'] = 'site';
		$this->_where_field_list['aktiv'] = 'aktiv';
		$this->_where_field_list['language'] = 'language';
	}
	/**
	 * Setzt eine Option für die Abfrage
	 * AND OR XOR...
	 * @param $option
	 * @return bool ob erfolgreich
	 */
	function setWhereoption($option = 'AND')
	{
		if($option == 'AND'){
			$this->_whereoption = 'AND';
			return true;
		} if($option == 'OR'){
			$this->_whereoption = 'OR';
			return true;
		} if($option == 'XOR'){
			$this->_whereoption = 'XOR';
			return true;
		}else {
			$this->_whereoption = 'AND';
			return true;
		}
		return false;
	}
	/**
	 * Gibt die Optionen zurück AND OR XOR,...
	 * @return String
	 */
	function getWhereoption()
	{
		return $this->_whereoption;
	}


	/**
	 * Setzt mehrere Einschränkungen
	 *
	 * @param array $setWhere
	 */
	function setWhere(Array $setWhere, $option = '=' )
	{
		$this->_where = array();
		foreach($setWhere as $k => $v){
			$this->addWhere($k,$v,$option );
		}
	}
	/**
	 * Fügt eine Einschränkung hinzu
	 *
	 * @param String $key
	 * @param String $value
	 */
	function addWhere ( $key , $value, $option = '=' )
	{
		if ( isset ( $this->_where_field_list[$key] ) ) {
			if(
				( is_string($value) && $value != '' )
				or
				(is_numeric($value) && $value > 0  )
			) {
			if( $option == '=' ) {
					if(isset($this->_where_field_list[$value] )){
						$this->_where[$key] = '`'.$this->_where_field_list[$key]."` = `".$this->quote($value)."` ";
					} else {
						$this->_where[$key] = '`'.$this->_where_field_list[$key]."` = '".$this->quote($value)."' ";
					}
				} elseif( $option == '>' ) {
					if(isset($this->_where_field_list[$value] )){
						$this->_where[$key] = '`'.$this->_where_field_list[$key]."` > `".$this->quote($value)."` ";
					} else {
						$this->_where[$key] = '`'.$this->_where_field_list[$key]."` > ".intval($value)." ";
					}
				} elseif( $option == '<' ) {
					if(isset($this->_where_field_list[$value] )){
						$this->_where[$key] = '`'.$this->_where_field_list[$key]."` < `".$this->quote($value)."` ";
					} else {
						$this->_where[$key] = '`'.$this->_where_field_list[$key]."` < ".intval($value)." ";
					}
				} elseif( $option == '<=' ) {
					$this->_where[$key] = '`'.$this->_where_field_list[$key]."` <= ".intval($value)." ";
				} elseif( $option == '>=' ) {
					$this->_where[$key] = '`'.$this->_where_field_list[$key]."` >= ".intval($value)." ";
				} else if($option  == 'like') {
					$this->_where[$key] = '`'.$this->_where_field_list[$key]."` like '%".$this->quote($value)."%' ";
				} else if($option  == 'in') {
					$this->_where[$key] = '`'.$this->_where_field_list[$key]."` in ('".implode("','",$this->quote($value))."') ";

				} else{
					error_log ($_SERVER['HTTP_USER_AGENT']. ' 1# Where-Filed not found in line '.__line__." addWhere ( $key , $value, $option = '=' )".print_r($_SERVER,1), 3, _cnf_log_path_access);;
					die('183 err');
					return false;
				}
			} else if( is_array($value) ) {
				$this->_where[$key] = $this->_where_field_list[$key]." in ('".implode("','",$this->quote($value))."') ";
			} else {
				error_log ($_SERVER['HTTP_USER_AGENT']. ' 2# Where-Filed not found in line '.__line__." addWhere ( $key , $value, $option = '=' )".print_r($_SERVER,1), 3, _cnf_log_path_access);;
				return false;
			}
		} else {
			error_log ($_SERVER['HTTP_USER_AGENT']. ' 3# Where-Filed not found in line '.__line__." addWhere ( $key , $value, $option = '=' )".print_r($_SERVER,1), 3, _cnf_log_path_access);;
			return false;
		}
		return true;
	}

	/**
	 * Gibt das Where-Statement zurück
	 *
	 * @return String
	 */
	function getWhere()
	{
		$ret = '';
		if(count($this->_where) > 0){
			$ret = ' WHERE ( '.implode(' '.$this->getWhereoption().' ', $this->_where).' ) ';

			if(isset($this->_where_field_list['deleted'])) {
				$ret .= ' AND deleted = 0 ';
			}

			return $ret;

		} else {
			if(isset($this->_where_field_list['deleted'])){
				$ret .= ' deleted = 0 ';
			}
		}
		return $ret;
	}

	/**
	 * Führt mehrere Felder der Liste zum sortieren hinzu
	 *
	 * @param array $odlist Liste mit Key als feld und Value als Asc oder Desc
	 */
	function setOrderBy(Array $odlist)
	{
		$this->_orderby = array();
		foreach( $odlist as $k => $v){
			$this->addOrderBy($k , (int) $v);
		}
	}
	/**
	 * Hinzufügen eines Feldes um zu sortieren
	 *
	 * @param string $key
	 * @param int $art Ob 1 asc oder  0 Desc
	 */
	function addOrderBy($key, $art = 0)
	{
		if(isset($this->_where_field_list[$key])) {
			if($art == '1'){
				$this->_orderby[] = '`'.$this->_where_field_list[$key].'`'." asc";
			} else {
				$this->_orderby[] = '`'.$this->_where_field_list[$key].'`'." desc";
			}
		}
	}
	/**
	 * Gibt das SQL-WHERE Statement zurück
	 *
	 * @return String
	 */
	function getOrderby()
	{
		if(count($this->_orderby)){
			return ' ORDER BY '.implode(',',$this->_orderby);
		} else {
			return '';
		}
	}

}