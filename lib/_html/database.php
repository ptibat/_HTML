<?php

/** --------------------------------------------------------------------------------------------------------------------------------------------
* Author		: @ptibat
* Dev start		: 18/02/2013
* Last modif	: 09/07/2021 14:48
* Description	: Classe de gestion de base de données SQL // PDO
--------------------------------------------------------------------------------------------------------------------------------------------- */

class database {

/*
	FONCTIONS :

	csv( $query , $options = array() )
	database_connect()
	database_disconnect()
	debug( $return = null )
	delete( $query )
	enum_values( $table , $field )
	exec_time( $wat )
	get( $query )
	get_array( $query , $table=false )
	get_colonnes( $table )
	get_error()
	get_tables()
	get_values( $query , $colonne_id = 0 )
	get_vendor()
	insert( $query )
	last_error()
	pagination( $query , $nb_results_par_page , $nav_link , $current , $id="pagination" , $class="pagination" )
	protect( $string )
	query( $query , $num=false )
	query_exec( $query )
	row( $query , $num=false )
	sql_data( $sql_data )
	table_exists( $tablename )
	update( $query )
	version( $min=null )
*/

/* --------------------------------------------------------------------------------------------------------------------------------------------- VARIABLES */

	public $pdo				= null;
	public $options			= array();
	public $current_query		= "";
	public $execution_time		= 0;
	public $last_error		= array( "time" => "" , "error" => "" );
	public $server_vendor		= "";
	public $server_version		= "";
	public $prefix			= "";
	public $tables			= array();


/* --------------------------------------------------------------------------------------------------------------------------------------------- CONSTRUCTEUR */
public function __construct( $options = array() )
  {
	$default = array( 
		"driver"		=> "mysql",
		"host"		=> "localhost",
		"file"		=> "",			/* Pour fichier sqlite */
		"port"		=> 3306,
		"database"		=> "",
		"user" 		=> null,
		"password"		=> null,
		"charset"		=> "utf8",
		"persistent"	=> false,
		"prefix"		=> ""
	);

	$options = is_array($options) ? array_merge( $default , $options ) : $default;

	$strrpos = strrpos( $options["host"] , ":" );
	if( $strrpos > 4  )
	  {
		$explode 		= explode( ":" , preg_replace( "#http(s?)://#" , "" , $options["host"] ) );
		$options["host"]	= $explode[0];
		$options["port"]	= $explode[1];
	  }

	$this->options = $options;

	$this->prefix = ( ( $this->options["prefix"] != "" ) AND preg_match( "#^[\w_-]+$#" , $this->options["prefix"] ) ) ? $this->options["prefix"] : "";

	$this->database_connect();

	$this->get_tables();
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOYE UNE ERREUR LORS DE L'EXECUTION D'UNE FONCTION INEXISTANTE */
public function __call( $function , $arguments )
  {
	echo __CLASS__." : La fonction appelée \"".$function."\" est inexistante. ".json_encode( $arguments );
	exit;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- CONNEXION A LA BASE DE DONNEES */
private function database_connect()
  {
	try
	  {
		if( ( $this->options["driver"] == "sqlite" ) AND !empty($this->options["file"]) )
		  {
		  	if( !is_file($this->options["file"]) )
		  	  {
		  	  	echo "ERROR DB FILE";
		  	  	exit;
		  	  }
			else
			  {
				$this->pdo = new PDO(
						$this->options["driver"].":".$this->options["file"],
						$this->options["user"],
						$this->options["password"],
						array(
							PDO::ATTR_PERSISTENT 		=> $this->options["persistent"],
							PDO::MYSQL_ATTR_FOUND_ROWS 	=> true
						)
				);
			  }
		  }
		else
		  {
			$this->pdo = new PDO(
					$this->options["driver"]
									.( !empty($this->options["host"]) ? ":host=".$this->options["host"].":".$this->options["port"] : "" )
									.( !empty($this->options["database"]) ? ";dbname=".$this->options["database"] : "" ),
					
					$this->options["user"],
					$this->options["password"],
					array(
						PDO::ATTR_PERSISTENT 		=> $this->options["persistent"],
						PDO::MYSQL_ATTR_FOUND_ROWS 	=> true,					/* ERROR POSSIBLE */
						PDO::ATTR_ERRMODE			=> PDO::ERRMODE_EXCEPTION,
						PDO::ATTR_DEFAULT_FETCH_MODE	=> PDO::FETCH_ASSOC
					)
			);

			$this->get_vendor();

		  }

		if( isset($this->options["charset"]) AND !empty($this->options["charset"]) )
		  {
			$this->pdo->exec("set names ".$this->options["charset"] );
		  }



		/* ----------------------------------------------  */
		
		$this->clear_login();


		/* ---------------------------------------------- */
		
		return true;

	  }
	catch( PDOException $e )
	  {
	  	$this->error($e);
	  }

  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- DECONNEXION DE LA BASE DE DONNEES */
private function database_disconnect()
  {
	$this->pdo = null;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RÉCUPÈRE LES TABLES DE LA BASE */
private function get_tables()
  {
  	$t 		= "Tables_in_".$this->options["database"];
	$query 	= $this->query( "SHOW TABLES WHERE ".$t." LIKE '".$this->prefix."%'" );

	if( $query["nb"] > 0 )
	  {
	  	foreach( $query["data"] as $a => $table )
	  	  {
	  	  	$table = $table[$t];
	  	  	$this->tables[ preg_replace( "#^".$this->prefix."#" , "" , $table ) ] = $table;
	  	  }
	  }
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- DECONNEXION DE LA BASE DE DONNEES */
private function error( $e )
  {
	global $_HTML;

	if( isset($_HTML["administrator"]) AND functions::check_email($_HTML["administrator"]) )
	  {
		$error  = "\nErreur de connexion au serveur de bases de données";
		$error .= "\n----------------------------------------------------------";
		$error .= "\n";
		$error .= "\nDATE :       ".date( "d/m/Y H:i:s" );
		$error .= "\nMicrotime :  ".microtime();
		$error .= "\nURL :        http".(  ( isset($_SERVER["HTTPS"]) AND ( $_SERVER["HTTPS"] === "on" ) ) ? "s" : "" )."://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
		$error .= "\nDRIVER :     ".$this->options["driver"];
		$error .= "\nUSER :  	  ".str_pad( "" , strlen( $this->options["user"] ) , "*" ).substr( $this->options["user"] , -3 );
		$error .= "\nPASSWORD :   ".str_pad( "" , strlen( $this->options["password"] ) , "*" ).substr( $this->options["password"] , -3 );
		$error .= "\nHOST :       ".$this->options["host"];
		$error .= "\nDATABASE :   ".$this->options["database"];
		$error .= "\nERROR :      \n\n".$e->getMessage();
		$error .= "\n";
		
		error_log( $error , 1 , $_HTML["administrator"] );
	  }

	$message = "";

	if( isset($_HTML["prod"]) AND ( $_HTML["prod"] == false ) )
	  {
		$message .= "<b>Erreur serveur</b><br />".$e->getMessage()."<br /><br />";
	  }

	if( isset($_HTML["debug"]) AND ( $_HTML["debug"] == true ) )
	  {
	  	$debug = $this->debug(1);
	  	if( !empty($debug) )
	  	  {
			$message .= "<br /><b>DEBUG :</b><br /><br />".$this->debug(1)."<br /><br />";
	  	  }
	  }


	if( $message != "" )
	  {
	  	$message = "<html><body style='margin:0;padding:20px 20px 40px 20px;font-family:monospace;font-size:14px;color:#9b1515;background-color:#F7EDED'>".$message."</body></html>";
	  }
	else
	  {
  		$message = "E.";

  		if( isset($_GET["_html_force_debug_database"]) )
  		  {
	  		$_HTML["errors"][] = "Erreur serveur : ".$e->getMessage();return false;
  		  }
	  }


  	echo $message;
	exit;


  }
  
  
  
/* --------------------------------------------------------------------------------------------------------------------------------------------- RECUPERE LE TYPE DE BASE DE DONNÉES (mysql, mariadb, ... ) */
public function get_vendor()
  {
	$query = $this->query( "SHOW VARIABLES like '%version%'" , "key" );

	if( $query["nb"] > 0 )
	  {
		$info 			= $query["data"];
		$this->server_vendor  	= strtok( $info["version_comment"] , " " );
		$this->server_version 	= $info["version"];
	  }
  }
  
  
  
/* --------------------------------------------------------------------------------------------------------------------------------------------- PROTECTION : RESET DES IDENTIFIANTS */
public function clear_login()
  {
	$this->options["user"] 		= "";
	$this->options["password"]	= "";
  }
  
  
  
/* --------------------------------------------------------------------------------------------------------------------------------------------- RENSEIGNE LA DERNIÈRE ERREUR */
public function last_error()
  {
  	$this->last_error["time"]	= functions::microtime();
  	$this->last_error["error"]	= $this->get_error()["msg"];
  	return $this->last_error;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- EXECUTE UNE REQUETE ET RENVOI LES RESULTATS DANS UN TABLEAU PHP */
public function query_exec( $query )
  {
  	if( $this->pdo !== null )
  	  {
	  	$this->current_query = $query;

	  	try
		  {
  			return $this->pdo->query( $query );
		  }
		catch( PDOException $e )
		  {
		  	$this->error($e);
		  }

  	  }
	else
	  {
		return false;
	  }
  }
  
  
/* --------------------------------------------------------------------------------------------------------------------------------------------- EXECUTE UNE REQUETE ET RENVOI LES RESULTATS DANS UN TABLEAU PHP */
public function query( $query , $fetch = false )
  {
  	$data = array(
  		"ok"	 		=> false,
  		"execution_time" 	=> 0,
  		"nb" 			=> 0,
  		"data" 		=> array(),
  		"msg" 		=> "",
  		"query" 		=> $query,
  	);

	if( ( $fetch === true ) OR ( $fetch === "num" ) )
	  {
		$fetch = PDO::FETCH_NUM;
	  }
	else if( $fetch === "key" )
	  {
		$fetch = PDO::FETCH_KEY_PAIR;
	  }
	else
	  {
		$fetch = PDO::FETCH_ASSOC;
	  }


	try
	  {
	  	$this->exec_time("start");

	  	$query = $this->query_exec( $query );
		

		if( $query )
		  {
		  	$data["ok"]		= true;
		  	$data["data"]	= $query->fetchAll( $fetch );
		  	$data["nb"]		= ( $this->options["driver"] == "sqlite" ) ? count( $data["data"] ) : $query->rowCount();
		  }
		else
		  {
		  	$error 		= $this->pdo->errorInfo();
		  	$error 		= $error[2];

		  	$data["msg"]	= "Erreur de requête : ".$error;
		  }
	  	
	  	$this->exec_time("end");

	  	$data["execution_time"] = $this->execution_time;

		return $data;
	  }
	catch( PDOException $e )
	  {
	  	$this->error($e);
	  }
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- EXECUTE UNE REQUETE ET RENVOI UNE SEULE LIGNE */
public function row( $query , $num=false )
  {
  	$this->exec_time("start");

  	try
	  {
	  	$query = $this->query_exec( $query );

		if( $query )
		  {
			$data = $query->fetch( ( $num === true ) ? PDO::FETCH_NUM : PDO::FETCH_ASSOC );
		  }
		else
		  {
		  	$data = "Erreur de requête : ".$this->last_error()["error"];
		  }

	  }
	catch( PDOException $e )
	  {
	  	$this->error($e);
	  }

  	$this->exec_time("end");

	return $data;
  }
  
  
  
/* --------------------------------------------------------------------------------------------------------------------------------------------- INSERTION DE DONNÉES DANS UNE TABLE */
public function insert( $query )
  {
  	$this->exec_time("start");
  	
  	$return = false;

  	try
	  {
	  	if( $this->query_exec( $query ) )
	  	  {
	  	  	$id = $this->pdo->lastInsertId();
			$return = ( !empty($id) AND is_numeric($id) AND ( $id > 0 ) ) ? $id : true;
	  	  }
		else
		  {
		  	$this->last_error();
		  }

	  }
	catch( PDOException $e )
	  {
	  	$this->error($e);
	  }

  	$this->exec_time("end");
  	
	return $return;
  }
  
  
  
/* --------------------------------------------------------------------------------------------------------------------------------------------- MISE A JOUR DE DONNÉES */
public function update( $query )
  {
  	$this->exec_time("start");
  	
  	$return = false;

  	try
	  {
	  	if( $q = $this->query_exec( $query ) )
	  	  {
			$return = $q->rowCount();
	  	  }
		else
		  {
		  	$this->last_error();
		  }

	  }
	catch( PDOException $e )
	  {
	  	$this->error($e);
	  }

  	$this->exec_time("end");
  	
	return $return;
  }
  
  
  
/* --------------------------------------------------------------------------------------------------------------------------------------------- SUPPRESSION DE LA BASE DE DONNÉES */
public function delete( $query )
  {
  	$this->exec_time("start");
  	
  	$return = false;

  	try
	  {
	  	if( $q = $this->query_exec( $query ) )
	  	  {
			$return = $q->rowCount();
	  	  }
		else
		  {
		  	$this->last_error();
		  }

	  }
	catch( PDOException $e )
	  {
	  	$this->error($e);
	  }

  	$this->exec_time("end");
  	
	return $return;
  }
  
  
  
/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LA VERSION DE MYSQL */
public function version( $min=null )
  {
	return $this->row("select version() as v")["v"];
  }
  
  
  
/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOI UNE CHAÎNE DE CARACTÈRES PROTEGÉES POUR UNE REQUETE SQL */
public function protect( $string )
  {
	return $this->pdo->quote( $string );
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE UN RÉSULTAT UNIQUE */
public function get( $query )
  {
  	$this->exec_time("start");

  	try
	  {
	  
	  	$data		= "";
	  	$query	= $this->query_exec( $query );

		if( $query )
		  {
			$data = $query->fetch( PDO::FETCH_NUM );
			$data = isset($data[0]) ? $data[0] : "";
		  }
		else
		  {
		  	$error = $this->pdo->errorInfo();
		  	$error = $error[2];

		  	echo "Erreur de requête : ".$error;exit;
		  }

	  }
	catch( PDOException $e )
	  {
	  	$this->error($e);
	  }

  	
  	$this->exec_time("end");
  	
	return $data;
  }

  
/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LES RESULTATS SOUS FORME DE TABLEAU */
public function get_array( $query )
  {
	$return 		= array();
	$query		= $this->query( $query );

	if( $query["nb"] > 0 )
	  {
		foreach( $query["data"] as $row )
		  {
			$i = 0;
			$ligne = array();
			foreach( $row as $key => $value )
			  {
			  	/*
				$i++; if( $i % 2 == 0 ){}
				*/

				$ligne[ $key ] = $value;

			  }

			$return[] = $ligne;
		  }
	  }

	return $return;
  }

  
/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LES COLONNES D'UNE TABLE */
public function get_colonnes( $table )
  {
	$return = array();

  	$query = $this->query_exec( "SHOW COLUMNS FROM ".$table );
	$colonnes = $query->fetchAll( PDO::FETCH_ASSOC );

	foreach( $colonnes as $colonne )
	  {
	  	$type		= $colonne["Type"];
	  	$size		= "";
		$null 	= ( $colonne["Null"] == "NO" ? false : true );

		if( preg_match( "#([a-z]+)(\([0-9]+\))?#i" , $type , $matches ) AND ( count($matches) > 1 ) )
		  {
			$type	= $matches[1];
			$size	= isset($matches[2]) ? preg_replace( "#(\(|\))#" , "" , $matches[2] ) : "";
		  }

	  	$return[ $colonne["Field"] ] = array(
	  							"type"	=> $type,
	  							"size"	=> $size,
	  							"null"	=> $null,
	  							"default"	=> $colonne["Default"],
	  							"key"		=> $colonne["Key"],
	  							"extra"	=> $colonne["Extra"]
	  						 );
	  }

	return $return;
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LES ERREURS */
public function debug( $return = null )
  {
	$error 	= $this->get_error();
	$html		= "";
	$tpl_titre	= "<div style='padding:12px 20px;background:#E27A7A;color:#fff;white-space:pre-line;font-family:monospace;font-size:15px;'>{%DATA%}</div>";
	$tpl_data	= "<div style='padding:20px;margin-bottom:40px;background:#FFFAFA;color:#AA1111;white-space:pre-wrap;font-family:monospace;font-size:13px;'>{%DATA%}</div>";

	if( $error["state"] === true )
	  {
		$html .= str_replace( "{%DATA%}" , "ERREUR" , $tpl_titre );
		$html .= str_replace( "{%DATA%}" , $error["msg"] , $tpl_data );
	  }

	if( !empty($this->current_query) )
	  {
		$html .= str_replace( "{%DATA%}" , "REQUÊTE" , $tpl_titre );
		$html .= str_replace( "{%DATA%}" , $this->current_query , $tpl_data );
	  }

	if( is_array( $this->last_error ) AND isset($this->last_error["time"]) AND isset($this->last_error["error"]) AND !empty($this->last_error["error"]) )
	  {
		$html .= str_replace( "{%DATA%}" , "DERNIÈRE ERREUR ( ".$this->last_error["time"]." )" , $tpl_titre );
		$html .= str_replace( "{%DATA%}" , $this->last_error["error"] , $tpl_data );
	  }


  	if( $return === 1 )
  	  {
		return $html;
  	  }
  	else if( $return === 2 )
  	  {
		return $error;
  	  }
	else
	  {
		echo $html;
		exit;
	  }
  }


 
 
   
/* --------------------------------------------------------------------------------------------------------------------------------------------- GENERE DES DONNEES AU FORMAT CSV */
public function csv( $query , $options = array() )
  {
	/* -------------------------------------------------- */
	
	$default = array( 
		"headers"		=> false,
		"charset"		=> "UTF-8"
	);

	$options = is_array($options) ? array_merge( $default , $options ) : $default;

	/* -------------------------------------------------- */

  	$csv		= "";
  	$query	= $this->query_exec( $query );
	$nb		= $query->rowCount();

	if( $query AND ( $nb > 0 ) )
	  {
		$csv	= fopen( "php://temp/maxmemory:". ( 5 * 1024 * 1024 ) , "r+" );
	  	$data = $query->fetchAll( PDO::FETCH_ASSOC );
	  	
	  	
	  	if( $options["headers"] === true )
	  	  {
			$colonnes = array_keys( (array) $data[0] );
	  	  	fputcsv( $csv, $colonnes );
	  	  }
	  	else if( is_array($options["headers"]) )
	  	  {
		  	fputcsv( $csv, $options["headers"] );
	  	  }


	  	foreach( $data as $row )
	  	  {
	  	  	if( $options["charset"] != "UTF-8" )
	  	  	  {
				array_walk( $row , function ( &$value ) {

					global $options;

					$value = iconv( mb_detect_encoding( $value ) , $options["charset"] , $value );
		
				});
	  	  	  }
	  	  	
	  	  	fputcsv( $csv, $row );
	  	  }

		rewind( $csv );
		$csv = stream_get_contents( $csv );

	  }
	else
	  {
		$csv = "ERREUR CSV";
	  }
  	
	return $csv;

	/* -------------------------------------------------- */
	
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- PAGINATION COMPATIBLE PDO */
public function pagination( $query , $nb_results_par_page , $nav_link , $current , $id="pagination" , $class="pagination" )
  {
	$navigation			= "";
	$current	 		= is_numeric($current) ? $current : 1;
	$page				= ( $current - 1 ) * $nb_results_par_page;
	$data				= $this->query( $query );
	$nb_results			= $data["nb"];
	$nb_pages			= floor( $nb_results / $nb_results_par_page );
	if( ($nb_results % $nb_results_par_page) > 0 ) { $nb_pages++; }

	if( $nb_pages > 1 )
	  {
		$navigation = "<div id='".$id."'>\n";

		if( $current > 1 )
		  {
			$navigation   .= "<a id='".$class."_previous' class='".$class."_number' href='".str_replace( "{%PAGE%}" , ( $current-1 ), $nav_link )."'>&laquo;</a>";
		  }

		if( $nb_pages > 10 )
		  {
			if( $current > 5 )
			  {
				$add_first	= "<a class='".$class."_number".( ($current==1) ? " ".$class."_number_current" : "" )."' href='".str_replace( "{%PAGE%}" , "1" , $nav_link )."'>1</a> ... ";
				if( ($current+5) > $nb_pages )
				  {
					$for_start	= $current - (9 - ($nb_pages - $current));
					$for_end	= $nb_pages + 1;
					$add_end	= "";
				  }
				else
				  {
					$for_start	= ( $current - 4 );
					$for_end	= ( $current + 5 );
					$add_end	= "... <a class='".$class."_number".( ($current==$nb_pages) ? " ".$class."_number_current" : "" )."' href='".str_replace( "{%PAGE%}" , $nb_pages , $nav_link )."'>".$nb_pages."</a>";
				  }
			  }
			else
			  {
				$for_start	= 1;
				$for_end	= 11;
				$add_first	= "";
				$add_end	= "... <a class='".$class."_number".( ($current==$nb_pages) ? " ".$class."_number_current" : "" )."' href='".str_replace( "{%PAGE%}" , $nb_pages , $nav_link )."'>".$nb_pages."</a>";
			  }


			$navigation .= $add_first;
			for( $i=$for_start ; $i<$for_end ; $i++ )
			  {
				$navigation   .= "<a class='".$class."_number".( ($i==$current) ? " ".$class."_number_current" : "" )."' href='".str_replace( "{%PAGE%}" , $i , $nav_link )."'>".$i."</a>";
			  }
			$navigation .= $add_end;


		  }
		else
		  {
			for( $i=1 ; $i < ($nb_pages+1) ; $i++ )
			  {
				$navigation   .= "<a class='".$class."_number".( ($i==$current) ? " ".$class."_number_current" : "" )."' href='".str_replace( "{%PAGE%}" , $i , $nav_link )."'>".$i."</a>";
			  }
		  }

		if( $current < $nb_pages )
		  {
			$navigation   .= "<a id='".$class."_next' class='".$class."_number' href='".str_replace( "{%PAGE%}" , ( $current+1 ), $nav_link )."'>&raquo;</a>";
		  }

		$navigation .= "
				</div>";
	  }


	$query = $this->query( $query." LIMIT $page , ".$nb_results_par_page."" );

	if( $query["nb"] > 0 )
	  {
		$return["data"]			= $query["data"];
		$return["navigation"]		= $navigation;
		$return["current"]		= $current;
		$return["nb_pages"]		= $nb_pages;
		$return["nb_results"]		= $nb_results;
		$return["nb_par_pages"]		= $nb_results_par_page;

		return $return;
	  }
	else
	  {
		return false;
	  }
  }




/* --------------------------------------------------------------------------------------------------------------------------------------------- CONVERTIT UN TABLEAU CHAMPS/VALEUR AU FORMAT REQUETE SQL */
public function sql_data( $sql_data )
  {
	$sql = array();

	/*
	$before_sql_data	= false;
	global $app;
	if( isset($app) AND method_exists( $app, "before_sql_data" ) )
  	  {
		$before_sql_data = true;
  	  }
  	if( $before_sql_data )
  	  {
		$tmp 		= $app->before_sql_data( $field , $data );
		$field	= $tmp["field"];
		$data		= $tmp["data"];
  	  }
	*/

	foreach( $sql_data as $field => $data )
	  {
	  
		$sql[] = $field." = ".$this->protect( $data );
	  }

	return implode( ",\n" , $sql );
  }




/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOI LES VALEURS POSSIBLES POUR UN CHAMP ENUM */
public function enum_values( $table , $field )
  {
  	$enum = array();
	$type = $this->row( "SHOW COLUMNS FROM ".$table." WHERE Field = '".$field."'" )["Type"];

	if( preg_match( "/^enum\(\'(.*)\'\)$/" , $type , $matches ) )
	  {
		$enum	= explode( "','" , $matches[1] );
	  }

	return $enum;	

  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LES RESULTATS D'UNE SEULE COLONNE SOUS FORME DE TABLEAU */
public function get_values( $query , $colonne_id = 0 )
  {
	$return 		= array();
	$colonne_id 	= is_numeric($colonne_id) ? $colonne_id : 0;
	$query		= $this->query( $query , true );

	if( $query["nb"] > 0 )
	  {
		foreach( $query["data"] as $row )
		  {
			$return[] = $row[ $colonne_id ];
		  }
	  }

	return $return;
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LES RESULTATS D'UNE SEUL COLONNE SOUS FORME DE TABLEAU */
public function exec_time( $wat )
  {
	if( $wat == "start" )
	  {
	  	$this->execution_time = microtime( true );
	  }
	else if( $wat == "end" )
	  {
	  	$this->execution_time = number_format( ( microtime( true ) - $this->execution_time ) , 10 );
	  }
	else
	  {
	  	$this->execution_time = 0;
	  }
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LES RESULTATS D'UNE SEUL COLONNE SOUS FORME DE TABLEAU */
public function get_error()
  {
  	$return = array(
  		"state" 	=> false,
  		"msg"		=> "",
  		"data"	=> array()
  	);

	if( method_exists( $this->pdo , "errorInfo" ) )
	  {
		$error = $this->pdo->errorInfo();

		if( is_array($error) AND !empty($error) )
		  {
		  	$return = array(
		  		"state" 	=> true,
		  		"msg"		=> ( ( isset($error[2]) AND !empty($error[2]) ) ? $error[2] : "" ),
		  		"data"	=> $error
		  	);
		  }
	  }


	return $return;
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- VERIFIE SI UNE TABLE EXISTE */
public function table_exists( $tablename )
  {
	$check = $this->pdo->query( "SHOW TABLES LIKE '".$tablename."'" );

	return ( $check AND ( $check->rowCount() > 0) ) ? true : false;
  }
  
  
  
/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOI LE NOM D'UNE TABLE AVEC LE PRFIX S'IL EXISTE */
public function t( $tablename )
  {
	return $this->prefix.$tablename;
  }







/* --------------------------------------------------------------------------------------------------------------------------------------------- DESTRUCTEUR */
public function __destruct()
  {
	$this->database_disconnect();
  }

}




