<?php

/** --------------------------------------------------------------------------------------------------------------------------------------------
* Author		: @ptibat
* Dev start		: 04/11/2008
* Last modif	: 03/06/2021 17:42
* Description	: Classe de gestion des langues
--------------------------------------------------------------------------------------------------------------------------------------------- */

class lang {

/*
	FONCTIONS :

	database_connect()
	database_disconnect()
	csv( $query , $options = array() )
	debug( $return = null )
	delete( $query )
	enum_values( $table , $field )
	get( $query )
	get_array( $query , $table=false )
	get_colonnes( $table )
	insert( $query )
	last_error()
	pagination( $query , $nb_results_par_page , $nav_link , $current , $id="pagination" , $class="pagination" )
	protect( $string )
	query( $query , $num=false )
	query_exec( $query )
	row( $query , $num=false )
	sql_data( $sql_data )
	sql_data2( $sql_data )
	update( $query )
	version( $min=null )


	INIT :

	lang::init(array(
		"type"		=> "db",
		"sql_table"		=> "traductions",
		"lg_separator"	=> "_"
	));

	lang::init(array(
		"directory"		=> APP_DOC_ROOT."/lang/",
		"lg_separator"	=> "_"
	));


*/



/* --------------------------------------------------------------------------------------------------------------------------------------------- VARIABLES */

	public static $type			= "static";
	public static $language			= "fr";
	public static $lg_default		= "_fr";
	public static $options			= array(
								"type"			=> "file",			/* file , db */
								"sql_table"			=> "traductions",		/* Nom de la table SQL */	
								"directory"			=> "/lang/",		/* Répertoire des fichiers de traductions */
								"ext" 			=> "php",			/* Extension des fichiers de traductions */
								"redirect"			=> true,
								"ini_sections" 		=> false,
								"cookie_time" 		=> 63072000,		/* 3600 * 24 * 365 * 2 */
								"lg_default"		=> "fr",
								"lg_separator"		=> "_",
								"lg_clean"			=> true
							);

	public static $data			= array();
	public static $db				= array();


/* --------------------------------------------------------------------------------------------------------------------------------------------- CONSTRUCTEUR */
public function __construct()
  {
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- DESTRUCTEUR */
public function __destruct()
  {
  }
  

/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOYE UNE ERREUR LORS DE L'EXECUTION D'UNE FONCTION INEXISTANTE */

public static function __callStatic( $function , $arguments )
  {
	echo __CLASS__." : La fonction appelée \"".$function."\" est inexistante. ".json_encode( $arguments );
	exit;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- GERE LA LANGUE UTILISEE SUR LE SITE */
public static function init( $options = array() )
  {
	/* --------------------------------------------- _HTML */
	
	global $_HTML;

	
	/* --------------------------------------------- OPTIONS */

	self::$options = is_array($options) ? array_merge( self::$options , $options ) : self::$options;

	if( !self::check_change() )
	  {
		/* --------------------------------------------- CHARGEMENT DE LA LANGUE CHOISIE */
		
		if( isset($_GET["lang"]) AND !empty($_GET["lang"]) AND array_key_exists( $_GET["lang"] , $_HTML["langs"] ) )
		   {
			$lang = $_GET["lang"];
		   }
		else if( isset($_COOKIE["lang"]) AND !empty($_COOKIE["lang"]) AND array_key_exists( $_COOKIE["lang"] , $_HTML["langs"] ) )
		   {
			$lang = $_COOKIE["lang"];
		   }
		else
		  {
			if( isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) AND !empty($_SERVER["HTTP_ACCEPT_LANGUAGE"]) )
			  {
				$lang = explode( "," , $_SERVER["HTTP_ACCEPT_LANGUAGE"] );
				$lang = strtolower( substr( chop( $lang[0] ) , 0 , 2 ) );
			  }
	
			if( !isset($lang) OR !array_key_exists( $lang , $_HTML["langs"] ) )
			  {
				$lang = "fr";
				self::save_lang();
			  }
		  }

	  }


	/* --------------------------------------------- */
	
	self::$language = strtolower( $lang );
	self::load_language();

	/* --------------------------------------------- */
	
	if( isset(self::$options["lg_default"]) AND preg_match( "#^[a-z]{2,3}$#i", self::$options["lg_default"] ) )
	  {
		self::$lg_default = self::$options["lg_separator"].self::$options["lg_default"];
	  }
	

	/* --------------------------------------------- */
	
	global $_HTML;
	
	if( $_HTML !== null )
	  {
	  	$_HTML["lang"] 	= self::$language;
  	  	$_HTML["lg"]	= self::$options["lg_separator"].$_HTML["lang"];
	  }
	
	
	/* --------------------------------------------- */
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- VERIFIE SI ON DOIT CHANGER LA LANGUE */
private static function check_change()
  {
	if( isset($_GET["set_lang"]) AND ($_GET["set_lang"]!="") )
	  {
		self::set( $_GET["set_lang"] , self::$options["redirect"] );
		return true;
	  }
	else
	  {
	  	return false;
	  }
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- GERE LA LANGUE UTILISEE SUR LE SITE */

public static function set( $language=null , $redirect=true )
  {
	if( $language == null )
	  {
		if( isset($_GET["set_lang"]) AND ($_GET["set_lang"]!='') )
		  {
			$language = $_GET["set_lang"];
		  }
	  }

	self::$language = $language;
	self::save_lang();

	if( $redirect )
	  {
		if( isset($_SERVER["HTTP_REFERER"]) AND ($_SERVER["HTTP_REFERER"] != "") )
		  {
			$page_davant = $_SERVER["HTTP_REFERER"];
		  }
		else
		  {
			$page_davant = ROOT."/";
		  }

		header("location: $page_davant");
		exit();
	  }

  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- SAUVEGARDE LA LANGUE CHOISIE */
private static function save_lang()
  {
	$_SESSION["lang"] = self::$language;
	setcookie( "lang" , self::$language , (time() + self::$options["cookie_time"]) , ROOT."/" );
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- CHARGEMENT DES TRADUCTIONS */
public static function load_language( $language=null )
  {
	global $_HTML;

  	$data		= array();
	$language 	= ( $language !== null ) ? $language : self::$language;


	/* --------------------------------------------- TYPE : database */

	if( ( self::$options["type"] == "db" ) AND !empty(self::$options["sql_table"]) )		
	  {
	  	$query = $_HTML["db"]->query( "SELECT dico, ref, type, ".$language." as lang FROM ".self::$options["sql_table"]."" );
	
		if( $query["ok"] )
		  {
			if( $query["nb"] > 0 )
			  {
				foreach( $query["data"] as $row )
				  {
				  	self::$data[ $row["dico"] ][ $row["ref"] ] = ( $row["ref"] == "data_raw" ) ? nl2br( $row["lang"] ) : $row["lang"];
				  }
			  }
		  }
		else
		  {
	  		echo "Erreur de chargement de la table des traductions...";
		  	exit;
		  }
	  }

	/* --------------------------------------------- TYPE : fichier */

	else	
	  {
		if( !is_dir( self::$options["directory"] ) OR !is_file( self::$options["directory"].$language.".".self::$options["ext"] ) )
		  {
	  		echo "Erreur : Impossible de charger le fichier de traductions...";
		  	exit;
		  }

		$file = self::$options["directory"].$language.".".self::$options["ext"];
	
		if( is_file( $file ) )
		  {
			if( self::$options["ext"] == "php" )
			  {
				global $_LANG;
				require_once( $file );
				$data = $_LANG;
			  }
			else  if( self::$options["ext"] == "ini" )
			  {
				$data = parse_ini_file( $file , self::$options["ini_sections"] );
			  }
			  
			self::$data = $data;
		  }
	  }

	/* ---------------------------------------------  */

  }

 

/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE UN TEXTE */
public static function get( $wat=null , $html=true )
  {
  	$text	= "";
  	$wat	= explode( "/" , $wat );
  	$nb	= count( $wat );

	if( isset( self::$data[ $wat[0] ] ) )
	  {
	  	$text = self::$data[ $wat[0] ];
	
		if( $nb > 1 )
		  {
		  	for( $i = 1 ; $i < $nb ; $i++ )
		  	  {
		  	  	if( isset( $text[ $wat[$i] ] ) )
		  	  	  {
		  	  	  	$text = $text[ $wat[$i] ];
		  	  	  }
		  	  }
		  }
	  }

	$text = !is_array($text) ? $text : "";

	/* ---------------------- Si le texte n'est pas trouvé, on cherche dans le dictionnaire commun */

	if( empty($text) AND ( $nb === 1 ) AND isset(self::$data["common"][ $wat[0] ]) AND !is_array(self::$data["common"][ $wat[0] ]) )
	  {
	  	$text = self::$data["common"][ $wat[0] ];
	  }
	
	if( ( $html === false ) AND !empty($text) )
	  {
		$text = preg_replace( "/<br\W*?\/>/" , "\n" , $text );
		$text = strip_tags( $text );
	  }

	return $text;
  }

 

/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE UN TEXTE TRADUIT DEPUIS LA BASE DE DONNÉES */
public static function get_db( $wat=null )
  {
	return isset( self::$db[ $wat ] ) ? self::$db[ $wat ] : "";
  }

 

/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LES LIENS HREFLANG */
public static function hreflangs( $url = null , $just_return = false )
  {
	global $_HTML;
	
	$hreflangs = array();
	
	foreach( $_HTML["langs"] as $lang => $langue )
	  {
	  	if( $lang != $_HTML["lang"] )
	  	  {
			$hreflangs[ $lang ] = ( $url !== null ) ? str_replace( "##LANG##" , $lang , $url ) : functions::current_url( array( "lang" => $lang ) );
	  	  }
		else
		  {
			$hreflangs[ $lang ] = functions::current_url();
		  }
	  }

	if( $just_return === false )
	  {
	  	$_HTML["hreflang"] = $hreflangs;
	  }

	return $hreflangs;
  }




}



















