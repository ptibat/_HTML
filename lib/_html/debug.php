<?php

/** --------------------------------------------------------------------------------------------------------------------------------------------
* Contact		: @ptibat
* Dev start		: 11/12/2008
* Last modif	: 14/02/2019 17:40
* Description	: Classe gestion des erreurs PHP
--------------------------------------------------------------------------------------------------------------------------------------------- */

class debug {

/* --------------------------------------------------------------------------------------------------------------------------------------------- VARIABLES */

	public $options				= array();
	public $included_files			= false;
	private $errors				= array();
	private $nb_errors			= 0;
	

/* --------------------------------------------------------------------------------------------------------------------------------------------- CONSTRUCTEUR */
public function __construct( $options = array() )
  {
	$default = array(
		"check_private_ip"	=> true,
		"force"			=> false,
		"log_file"			=> DOC_ROOT."/lib/logs/debug.log",
		"output"			=> "log",
		"method"			=> "array"
	);

	$this->options = is_array($options) ? array_merge( $default , $options ) : $default;

	if( 	   isset($_GET["_debug_display_errors"] )
		OR ( $this->options["force"] === true ) 
		OR ( ( $this->options["check_private_ip"] === true ) AND functions::is_private_ip() ) 
	  )
	  {
		ini_set( "display_errors" , 1 );
		ini_set( "display_startup_errors" , 1 );
	  }
	else
	  {
		/* error_reporting( E_ERROR | E_WARNING | E_PARSE | E_NOTICE ); */
		error_reporting( E_ALL );
		set_error_handler( array ( &$this, "error" ) );
	  }
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- DESTRUCTEUR */
public function __destruct()
  {  	
	if( ( $this->nb_errors > 0 ) AND ( $this->options["method"] == "array" ) )
	  {
		if( $this->options["output"] == "log" )
		  {
			$this->write_log();
		  }
		else if( $this->options["output"] == "screen" )
		  {
			echo "<div style='background-color:#DFDFDF;padding:10px;color:#000000;font-size:20pt;text-align:left;font-family:arial,sans-serif;'>DEBUG : ".$this->nb_errors." erreur(s)</div>";

			foreach( $this->errors AS $error )
			  {
				echo $error."\n";
			  }
		  }
	  }
  }
  

/* ---------------------------------------------------------------------------------------------------------------------------------------------  RENVOYE UNE ERREUR LORS DE L'EXECUTION D'UNE FONCTION INEXISTANTE */
public function __call( $function , $arguments )
  {
	echo __CLASS__." : La fonction appelée \"".$function."\" est inexistante. ".json_encode( $arguments );
	exit;
  }

public static function __callStatic( $function , $arguments )
  {
	echo __CLASS__." : La fonction appelée \"".$function."\" est inexistante. ".json_encode( $arguments );
	exit;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LE NOM D'UNE ERREUR EN FONCTION DE SON NUMERO */
public function error_name( $num )
  {
	$error_numbers = array( 
					    "1" => "E_ERROR" ,
					    "2" => "E_WARNING" ,
					    "4" => "E_PARSE" ,
					    "8" => "E_NOTICE" ,
					   "16" => "E_CORE_ERROR" ,
					   "32" => "E_CORE_WARNING" ,
					   "64" => "E_COMPILE_ERROR" ,
					  "128" => "E_COMPILE_WARNING" ,
					  "256" => "E_USER_ERROR" ,
					  "512" => "E_USER_WARNING" ,
					 "1024" => "E_USER_NOTICE" ,
					 "2048" => "E_STRICT" ,
					 "4096" => "E_RECOVERABLE_ERROR" ,
					 "8192" => "E_DEPRECATED" ,
					"16384" => "E_USER_DEPRECATED" ,
					"30719" => "E_ALL" );

	return ( isset( $error_numbers[ $num ] ) ? $error_numbers[ $num ] : "" );
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- TRAITE L'ERREUR RECUE */
public function error( $niveau_erreur , &$message , &$fichier , &$ligne , &$env )
  {
	$this->nb_errors++;

	if( $this->options["output"] == "screen" )
	  {
		$error = "<pre style='background-color:#DFDFDF;border-left:10px solid #D70D70;padding:8px;color:#000000;font-size:9pt;'>\nERREUR : ".$message."\nFICHIER : ".$fichier." : <b>".$ligne."</b></pre>";

		if( $this->options["method"] == "array" )
		  {
			$this->errors[] = $error;
		  }
		else
		  {
			echo $error;
		  }
	  }
	else if( $this->options["output"] == "code" )
	  {
		echo functions::show_source_code( $fichier , $ligne );
		exit;
	  }
	else if( $this->options["output"] == "log" )
	  {
		$ip	= ( ( isset( $_SERVER["REMOTE_ADDR"] ) AND !empty( $_SERVER["REMOTE_ADDR"] ) ) ? $_SERVER["REMOTE_ADDR"] : "undefined" );
		$host	= ( !preg_match( "#undefined#" , $ip ) AND ( preg_match( "#^10\.[0-255]\.[0-255]\.[0-255]#" , $ip ) OR preg_match( "#^172\.[0-16]\.[0-255]\.[0-255]#" , $ip ) OR preg_match( "#^192\.168\.[0-255]\.[0-255]#" , $ip ) ) ) ? $ip : @gethostbyaddr( $ip );

		$error  = "\n---------------------------------------------------------------------------------------------------------------------------- ".$this->error_name( $niveau_erreur );
		$error .= "\nDATE :		".date( "Y-m-d H:i:s" );
		$error .= "\nADRESSE IP :	".$ip;
		$error .= "\nHOST :		".$host;
		$error .= "\nUSER AGENT :	".( ( isset( $_SERVER["HTTP_USER_AGENT"] ) AND !empty( $_SERVER["HTTP_USER_AGENT"] ) ) ? $_SERVER["HTTP_USER_AGENT"] : "undefined" );
		$error .= "\nPAGE :		".$_SERVER["REQUEST_URI"];
		$error .= "\nERREUR :		".$message;
		$error .= "\nFICHIER :		".$fichier." : ".$ligne;
		$error .= "\n_GET :		".serialize( $_GET );
		$error .= "\n_POST :		".serialize( $_POST );

		if( $this->included_files )
		  {
			$error .= "\nINCLUDED FILES ";
			$included_files = get_included_files();
			foreach ($included_files as $filename)
			  {
				$error .= "\n\t".$filename;
			  }
		  }

		$error .= "\n";

		if( $this->options["method"] == "array" )
		  {
			$this->errors[] = $error;
		  }
		else
		  {
			$this->write_log( $error );
		  }
	  }
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- ECRIT DANS LE FICHIER DE LOG */
public function write_log( $error=NULL )
  {
	$file	= fopen( $this->options["log_file"] , "a" );

	if( $this->options["method"]=="inline" )
	  {
		fwrite( $file , $error );
	  }
	else if( $this->options["method"]=="array" )
	  {
		foreach( $this->errors AS $error )
		  {
			fwrite( $file , $error."\n" );
		  }
	  }

	fclose( $file );
  }

}





