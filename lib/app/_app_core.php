<?php

/** --------------------------------------------------------------------------------------------------------------------------------------------
* Contact		: @ptibat
* Last modif	: 15/10/2020 17:00
* Description	: APP Website : Classe APP_CORE
--------------------------------------------------------------------------------------------------------------------------------------------- */



/* --------------------------------------------------------------------------------------------------------------------------------------------- INITIALISATION DU MOTEUR + INCLUDES */

require_once( realpath( __DIR__."/.." )."/_html/init.php" );		/* PHP 7 : dirname( __DIR__ , 1 ) */
require_once( DOC_ROOT."/lib/app/_conf.php" );				/* Fichier de config générale */
require_once( DOC_ROOT."/lib/app/_commons.php" );			/* Fonctions communes à plusieurs app */



/* --------------------------------------------------------------------------------------------------------------------------------------------- CLASSE APP CORE */

class app_core {

/* --------------------------------------------------------------------------------------------------------------------------------------------- VARIABLES */

	public $html = array();



/* ---------------------------------------------------------------------------------------------------------------------------------------------  RENVOYE UNE ERREUR LORS DE L'EXECUTION D'UNE FONCTION INEXISTANTE */
public function __call( $function , $arguments )
  {
	if( method_exists( $this , $function ) )
	  {
		$func = $this->$function;
		return call_user_func_array( $func , $arguments );
	  }
	else
	  {
		$this->html["errors"][] = __CLASS__." : La fonction appelée \"".$function."\" n'existe pas. ".( !empty($arguments) ? json_encode( $arguments ) : "" );
	  }
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- CONSTRUCTEUR */
public function __construct(){}


/* --------------------------------------------------------------------------------------------------------------------------------------------- INITIALISATION */
public function init()
  { 	
	global $_HTML;			/* Variable globale _HTML */
	$this->html = & $_HTML;
  }
 
 
 
/* --------------------------------------------------------------------------------------------------------------------------------------------- DESTRUCTEUR */
public function __destruct()
  {
  	if( method_exists( $this, "display" ) AND isset($this->template) AND ( $this->html["display"] === true ) )
  	  {
		$this->display();		
		$this->template->display();
  	  }
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- GESTION DU MESSAGE TEMPORAIRE */
public function check_msg()
  {
	if( isset($_SESSION["msg"]) AND !empty($_SESSION["msg"]) )
	  {
		$this->html["js_ready"] .= "\nmsg({ text : \"".functions::xss_protect( $_SESSION["msg"] , false , false , "<br><b><a><i><u><hr>"  )."\" , delay : 10000 , exit : true });";
		unset( $_SESSION["msg"] );
	  }
  }




/* --------------------------------------------------------------------------------------------------------------------------------------------- DESACTIVE LE MODE PROD POUR LES IP LOCALES */
public function init_debug()
  {
	if( class_exists( "functions" ) AND isset($_SERVER["SERVER_ADDR"]) AND functions::is_private_ip() )
	  {
		$this->html["prod"]	= false;
		$this->html["debug"]	= true;
	  }
  }


  

}










