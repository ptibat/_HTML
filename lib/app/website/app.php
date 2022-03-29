<?php

/** --------------------------------------------------------------------------------------------------------------------------------------------
* Author		: @ptibat
* Last modif	: 15/10/2020 17:20
* Description	: APP Website
--------------------------------------------------------------------------------------------------------------------------------------------- */



/* --------------------------------------------------------------------------------------------------------------------------------------------- INITIALISATION DU MOTEUR + INCLUDES */

$modules = array(
			"debug",
			"lang",
			"template",
			"tools",
			"formulaire",
			"database"
		);

require_once( realpath( __DIR__."/.." )."/_app_core.php" );



/* --------------------------------------------------------------------------------------------------------------------------------------------- CONFIGURATION */

$_HTML["data"]["header"] 				= "";
$_HTML["data"]["footer"] 				= "";
$_HTML["favicon"] 					= array( "image/png" , ROOT."/favicon.ico" );
$_HTML["viewport"]					= array(
									"width" 		=> "device-width",
									"minimum-scale" 	=> "1.0",
									"maximum-scale" 	=> "1.0",
									"initial-scale" 	=> "1.0"
								  );

$_HTML["meta"]["author"]				= "_HTML";
$_HTML["meta"]["robots"]				= "index,follow";

$_HTML["css_files"]["template"] 			= APP_ROOT."/styles.css?v=".filemtime( APP_DOC_ROOT."/styles.css" );



/* --------------------------------------------------------------------------------------------------------------------------------------------- CLASSE APP */

class app extends app_core {


/* --------------------------------------------------------------------------------------------------------------------------------------------- CONSTRUCTEUR */
public function __construct()
  {

	/* ------------------------------------------------ INITIALISATION DEPUIS APP_CORE */
	
  	$this->init();
  	$this->init_debug();


	/* ------------------------------------------------ OBJETS */
	
	$this->debug 		= new debug();
	/*
	$this->html["db"]		= new database(array(
						"host" 	=> DB_HOST,
						"user" 	=> DB_USER,
						"password"	=> DB_PASSWORD,
						"database"	=> DB_DATABASE
					  ));
	*/
	$this->tools		= new tools();
	$this->commons		= new commons();
	$this->template		= new template();


	/* ------------------------------------------------ LANGUES */
	/*
	lang::init(array(
		"type"		=> "db",
		"sql_table"		=> "traductions"
	));
	*/



	/* ------------------------------------------------ MAINTENANCE */
	
	if( isset($this->html["config"]["maintenance"]) AND ( $this->html["config"]["maintenance"] === true ) )
	  {
	  	$this->tools->maintenance();
	  }



	/* ------------------------------------------------ VARIABLES */

	$this->html["html"]["standalone"] = isset($_GET["standalone"]) ? true : false;



	/* ------------------------------------------------ META */

	$this->html["meta"]["keywords"]	= "";
	$this->html["meta"]["description"]	= "";


	/* ------------------------------------------------ */
  }


  

/* --------------------------------------------------------------------------------------------------------------------------------------------- TEMPLATE */
public function display()
  {

	/* ---------------------------------------------- GESTION MODE MAINTENANCE */

	if( isset($this->html["config"]["maintenance"]) AND ( $this->html["config"]["maintenance"] === true ) )
	  {
	  	$this->html["body_class"][] = "maintenance";
	  }

	/* ---------------------------------------------- BODY CLASS : MOBILE */
	
	if( functions::is_mobile() )
	  {
		$this->html["body_class"][] = "mobile";
	  }

	/* =============================================================================== */
	/* ========================================================= TEMPLATE : IMPRESSION */
	/* =============================================================================== */

	if( $this->html["template"] == "print" )
	  {
		if( is_file(APP_DOC_ROOT."/styles_print.css") )
		  {
			$this->html["css_files"]["template"] = APP_ROOT."/styles_print.css?v=".filemtime( APP_DOC_ROOT."/styles_print.css" );
		  }

		$this->template->body = "<div id='print_content'>".$this->html["data"]["content"]."\n</div>";
	  }

	/* ============================================================================= */
	/* ========================================================= TEMPLATE : STANDARD */
	/* ============================================================================= */
	  
	else
	  {
		/* -------------------- Fichiers CSS */

		if( is_file(APP_DOC_ROOT."/print.css") )
		  {
			$this->html["css_files"]["print"] = array( "print" , APP_ROOT."/print.css?v=".filemtime( APP_DOC_ROOT."/print.css" ) );
		  }

		
		/* -------------------- Fichiers Javascript */

		$js_files = array(
			"jquery"		=> ROOT."/lib/js/jquery.min.js", 
			"jquery_ui"		=> ROOT."/lib/js/jquery-ui/jquery-ui.min.js",
			"functions"		=> ROOT."/lib/js/_functions.min.js",
			"app"			=> APP_ROOT."/website.js?v=".filemtime( APP_DOC_ROOT."/website.js" )
		);
		
		$this->html["js_files"] = array_merge( $js_files , $this->html["js_files"] );


		/* -------------------- Template par défaut */
		

$this->template->body = "
<div id='viewport'>

	<div id='msg'></div>

	<div id='container'>
		
		<header role='banner'>
			".$this->template_header()."
		</header>

		<section id='content' role='main'>
			".$this->html["data"]["content"]."
		</section>
		
		<footer role='contentinfo'>
			".$this->template_footer()."
		</footer>
	
	</div>

</div>";

		$this->check_msg();

	  }


  }

  

  
 
/* --------------------------------------------------------------------------------------------------------------------------------------------- TEMPLATE : HEADER */
public function template_header()
  {  
	$data = "
			<h1>_HTML</h1>
			".$this->commons->menu()."
	";

	return $data;
  }




 
/* --------------------------------------------------------------------------------------------------------------------------------------------- TEMPLATE : FOOTER */
public function template_footer()
  {
	$this->template->execution_time();
	$data = "_HTML — ".date( "d/m/Y H:i:s" )." — ".round( $this->html["execution_time"] , 3 )." sec";
	return $data;
  }














/**
* **********************************************************************************************************************************************
*
*   FONCTIONS DIVERSES
*
* **********************************************************************************************************************************************
*/




  

}









/* --------------------------------------------------------------------------------------------------------------------------------------------- CHARGEMENT DE L'APPLICATION */

$app = new app();









