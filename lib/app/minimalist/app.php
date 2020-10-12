<?php

/** --------------------------------------------------------------------------------------------------------------------------------------------
* Contact		: @ptibat
* Last modif	: 12/10/2020 11:11
* Description	: APP Minimalist
--------------------------------------------------------------------------------------------------------------------------------------------- */



/* --------------------------------------------------------------------------------------------------------------------------------------------- INITIALISATION DU MOTEUR + INCLUDES */

$modules = array(
			"template",
			"tools"
		);

require_once( realpath( __DIR__."/.." )."/_app_core.php" );


/* --------------------------------------------------------------------------------------------------------------------------------------------- CONFIGURATION */

$_HTML["meta"]["author"]		= "_HTML (template minimaliste)";
$_HTML["css_files"]["template"] 	= APP_ROOT."/styles.css?v=".filemtime( APP_DOC_ROOT."/styles.css" );



/* --------------------------------------------------------------------------------------------------------------------------------------------- CLASSE APP */

class app extends app_core {


/* --------------------------------------------------------------------------------------------------------------------------------------------- CONSTRUCTEUR */
public function __construct()
  {
	/* ------------------------------------------------ INITIALISATION DEPUIS APP_CORE */
	
  	$this->init();


	/* ------------------------------------------------ OBJETS */

	$this->tools			= new tools();
	$this->commons		= new commons();
	$this->template		= new template();


	/* ------------------------------------------------ */

  }


  
 
/* --------------------------------------------------------------------------------------------------------------------------------------------- TEMPLATE */
public function display()
  {
	/* ---------------------- Fichiers Javascript */

	$js_files = array(
		"jquery"	=> ROOT."/lib/js/jquery.min.js"
	);
	
	$this->html["js_files"] = array_merge( $js_files , $this->html["js_files"] );


	/* ---------------------- Template */

	$this->template->body = "
	<div id='container'>
	
		<div id='header'>".$this->template_header()."</div>
	
		<div id='content'>
			".$this->html["data"]["content"]."	
		
		</div>
	
	</div>";

  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- TEMPLATE : HEADER */
public function template_header()
  {  
	$data = "
		<h1>APP Minimaliste</h1>
		".$this->commons->menu();

	return $data;
  }





}









/* --------------------------------------------------------------------------------------------------------------------------------------------- CHARGEMENT DE L'APPLICATION */

$app = new app();









