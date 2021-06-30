<?php

/** --------------------------------------------------------------------------------------------------------------------------------------------
* Author		: @ptibat
* Dev start		: 11/12/2008
* Last modif	: 29/06/2021 17:22
* Description	: Classe gestion des erreurs PHP
--------------------------------------------------------------------------------------------------------------------------------------------- */

class debug {

/* --------------------------------------------------------------------------------------------------------------------------------------------- VARIABLES */

	public $options			= array();
	public $included_files		= false;
	public $nb_errors			= 0;
	private $errors			= array();
	private $errors_log		= array();
	

/* --------------------------------------------------------------------------------------------------------------------------------------------- CONSTRUCTEUR */
public function __construct( $options = array() )
  {
	$default = array(
		"check_private_ip"	=> true,
		"force"			=> false,
		"log_file"			=> DOC_ROOT."/lib/logs/debug.log",
		"output"			=> "log",
		"method"			=> "array",
		"couleur"			=> "#FA0000",
		"font"			=> "\"Helvetica Neue\",\"Helvetica\",Arial,sans-serif",
		"font_mono"			=> "Consolas,\"Andale Mono\",\"Lucida Console\",\"Courier New\",Courier,monospace"
	);

	$this->options = is_array($options) ? array_merge( $default , $options ) : $default;

	/* E_ALL | E_ERROR | E_WARNING | E_PARSE | E_NOTICE */
	error_reporting( E_ALL );

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
		ini_set( "display_errors" , 0 );
		ini_set( "display_startup_errors" , 0 );
	  }

	set_error_handler( array( &$this , "error" ) );
	register_shutdown_function( array( &$this , "fatal_error" ) );

  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- DESTRUCTEUR */
public function __destruct()
  {
	$this->ending();
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

	return ( isset( $error_numbers[ $num ] ) ? preg_replace( "#E_#" , "" , $error_numbers[ $num ] ) : "" );
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- TRAITE L'ERREUR REÇUE */
public function error( $niveau_erreur , $message , $fichier , $ligne , $env )
  {
	$this->nb_errors++;

	if( $this->options["output"] == "code" )
	  {
		echo functions::show_source_code( $fichier , $ligne );
		exit;
	  }
	else
	  {

	  	/* ------------------------------------------------ FOR SCREEN */

	  	$msg 		= $message;
	  	$msg 		= preg_replace( "#^Undefined variable: ([a-z0-9-_]+)#im" , "Undefined variable : <span style='display:inline-block;padding:0.2rem 0.4rem;background-color:".$this->options["couleur"].";color:#FFF;border-radius:0.2rem;'>$1</span>" , $msg );

	  	$filename 	= basename($fichier);
	  	$fichier 	= preg_replace( "#".$filename."$#" , "<b style='font-family:".$this->options["font"].";color:".$this->options["couleur"].";'>".$filename."</b>" , $fichier );
		$error 	= "<div style='font-family:".$this->options["font_mono"].";background-color:#F0EFEF;border-left:8px solid ".$this->options["couleur"].";padding:8px;color:#000000;font-size:9pt;line-height:11pt;margin-bottom:10px;white-space:pre-wrap;'>ERREUR   : ".$msg."\nFICHIER  : ".$fichier." : <span style='display:inline-block;padding:0.2rem 0.4rem;background-color:".$this->options["couleur"].";color:#FFF;border-radius:0.2rem;'>".$ligne."</span></div>";

		$this->errors[$niveau_erreur][] = $error;


	  	/* ------------------------------------------------ FOR LOG */

		$ip	= ( ( isset( $_SERVER["REMOTE_ADDR"] ) AND !empty( $_SERVER["REMOTE_ADDR"] ) ) ? $_SERVER["REMOTE_ADDR"] : "undefined" );
		$host	= ( !preg_match( "#undefined#" , $ip ) AND ( preg_match( "#^10\.[0-255]\.[0-255]\.[0-255]#" , $ip ) OR preg_match( "#^172\.[0-16]\.[0-255]\.[0-255]#" , $ip ) OR preg_match( "#^192\.168\.[0-255]\.[0-255]#" , $ip ) ) ) ? $ip : @gethostbyaddr( $ip );

		$error_log  = "\n---------------------------------------------------------------------------------------------------------------------------- ".$this->error_name( $niveau_erreur );
		$error_log .= "\nDATE :		".date( "Y-m-d H:i:s" );
		$error_log .= "\nADRESSE IP :	".$ip;
		$error_log .= "\nHOST :		".$host;
		$error_log .= "\nUSER AGENT :	".( ( isset( $_SERVER["HTTP_USER_AGENT"] ) AND !empty( $_SERVER["HTTP_USER_AGENT"] ) ) ? $_SERVER["HTTP_USER_AGENT"] : "undefined" );
		$error_log .= "\nPAGE :		".$_SERVER["REQUEST_URI"];
		$error_log .= "\nERREUR :	".$message;
		$error_log .= "\nFICHIER :	".$fichier." : ".$ligne;
		$error_log .= "\n_GET :		".serialize( $_GET );
		$error_log .= "\n_POST :	".serialize( $_POST );

		if( $this->included_files )
		  {
			$error_log .= "\nINCLUDED FILES ";
			$included_files = get_included_files();
			foreach ($included_files as $filename)
			  {
				$error_log .= "\n\t".$filename;
			  }
		  }

		$error_log .= "\n";

		$this->errors_log[$niveau_erreur][] = $error_log;



	  	/* ------------------------------------------------ */

		if( $this->options["method"] != "array" )
		  {		  	
			if( $this->options["output"] == "screen" )
			  {
				echo $error;
			  }
			else if( $this->options["output"] == "log" )
			  {
				$this->write_log( $error );
			  }
		  }

	  	/* ------------------------------------------------ */

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
		foreach( $this->errors AS $level => $errors )
		  {
			fwrite( $file , "---------------------------------------------------------------------------------- ".$this->error_name( $level )."\n" );

			foreach( $errors AS $error )
			  {
				fwrite( $file , $error."\n" );
			  }
		  }
	  }

	fclose( $file );
  }





/* --------------------------------------------------------------------------------------------------------------------------------------------- ERREUR FATALE */
public function fatal_error() {}


/* --------------------------------------------------------------------------------------------------------------------------------------------- ENDING */
public function ending( $force = false )
  {
	if( ( $this->nb_errors > 0 ) AND ( ( $force == true ) OR ( $this->options["method"] == "array" ) ) )
	  {
		if( !$force AND ( $this->options["output"] == "log" ) )
		  {
			$this->write_log();
		  }
		else if( ( $force == true ) OR ( $this->options["output"] == "screen" ) )
		  {
			echo "<div style='background-color:#333;padding:10px;color:#FFF;font-size:20pt;text-align:left;font-family:".$this->options["font"].";margin-bottom:30px;'>DEBUG : ".$this->nb_errors." erreur".( $this->nb_errors > 1 ? "s" : "" )."</div>";

			foreach( $this->errors AS $level => $errors )
			  {

			  	switch ( $level )
			  	  {
					case 1 : 	$color = "BC4D42"; break;
					case 2 : 	$color = "DFA551"; break;
					case 4 : 	$color = "F0ECBE"; break;
					case 8 : 	$color = "79AADB"; break;
			  		default :	$color = "333333"; break;
			  	  }

 				echo "<div style='background-color:".$color.";padding:10px;color:#FFF;font-size:14pt;text-align:left;font-family:".$this->options["font"].";margin-bottom:10px;'>".$this->error_name( $level )."</div>";

				foreach( $errors AS $error )
				  {
				  	if( $this->options["output"] == "screen" )
				  	  {
						echo $error;
				  	  }
				  	else
				  	  {
						echo "<pre>".$error."</pre>";
				  	  }
				  }

			  }

			exit;
		  }


	  }
  }



}





