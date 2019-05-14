<?php

/** --------------------------------------------------------------------------------------------------------------------------------------------
* Contact		: @ptibat
* Dev start		: 21/01/2013
* Last modif	: 17/04/2019 12:00
* Description	: Extension de la class APP avec un essemble de fonctions communes à plusieurs APP
--------------------------------------------------------------------------------------------------------------------------------------------- */

/**
* CONFIG
*/

$_HTML["vars"]["google_analytics"]		= "";		/* UA-0000000-0 */
$_HTML["vars"]["google_tag_manager"]		= "";		/* GTM-000000 */
$_HTML["vars"]["google_tag_manager_amp"]	= "";		/* GTM-000000 */
$_HTML["vars"]["statistik"]			= "";		/* xxxxxxxx */
$_HTML["vars"]["links"]				= array(
									"linkedin" 		=> "",	/* https://www.linkedin.com/company/…	*/
									"facebook" 		=> "",	/* https://www.facebook.com/… 		*/
									"twitter" 		=> "",	/* https://twitter.com/… 			*/
									"google_plus" 	=> "",	/* https://plus.google.com/… 			*/
									"pinterest" 	=> "",	/* https://www.pinterest.com/…		*/
									"behance" 		=> "",	/* https://www.behance.net/… 			*/
									"instagram" 	=> ""		/* https://www.instagram.com/…		*/
							  );

$_HTML["urls"]		= array(
					"home"			=> "/",
					"preview"			=> "/preview.htm",
					"doc"				=> "/documentation.htm",
					"variable"			=> "/variable.php",
					"minimalist"			=> "/minimalist.htm"
				  );


$_HTML["js_header"] .=   "var _ROOT = '".ROOT."';"
				."var _ABSOLUTE_URL_SERVER = '".ABSOLUTE_URL_SERVER."';"
				."var _OLD_IE = false;"
				."var _MOBILE = ".( functions::is_mobile() ? "true" : "false" ).";";


/**
* CLASSE COMMONS
*/

class commons {

/* --------------------------------------------------------------------------------------------------------------------------------------------- VARIABLES */

	public $html			= array();
	public $app			= array();
	public $sections		= array();
	public $sql_tables		= array();
	public $codes_commandes	= array(
							0 	=> "En attente de paiement",
							1 	=> "Annulé avant le paiement",
							2 	=> "Annulé lors du paiement",
							3	=> "Autorisation de paiement refusée",
							10 	=> "Achat validé",
							11 	=> "En attente de validation",
							20 	=> "Traitement en cours...",
							30 	=> "Expédiée",
							59 	=> "ACHAT TEST",
							90	=> "Executable response non trouvé",
							91	=> "Erreur d'appel API",
							99 	=> "Erreur d'achat"
					  );


/* ---------------------------------------------------------------------------------------------------------------------------------------------  RENVOYE UNE ERREUR LORS DE L'EXECUTION D'UNE FONCTION INEXISTANTE */
public function __call( $function , $arguments )
  {
	echo __CLASS__." : La fonction appelée \"".$function."\" est inexistante. ".json_encode( $arguments );
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- CONSTRUCTEUR */
public function __construct()
  {
	global $_HTML;
	$this->html = & $_HTML;

	global $app;
	$this->app = & $app;
  }
  
/* --------------------------------------------------------------------------------------------------------------------------------------------- DESTRUCTEUR */
public function __destruct(){}
  


/* --------------------------------------------------------------------------------------------------------------------------------------------- CORRECTIF POUR FIREFOX vs TINYMCE */
public function clean_tinymce_firefox( $data )
  {
	return preg_replace( "#<div>(\s+|\xC2\xA0)<\/div>#u" , "" , $data );
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- MENU COMMUN A PLUSIEURS APP */
public function menu()
  {
	$data = "
			<ul id='menu'>
				<li><a".( $this->html["page"] == "home" 			? " class='current'" : "" )." href='".$this->app->tools->url( "home" )."'>Home</a></li>
				<li><a".( $this->html["page"] == "preview" 		? " class='current'" : "" )." href='".$this->app->tools->url( "preview" )."'>Preview CSS</a></li>
				<li><a".( $this->html["page"] == "doc" 			? " class='current'" : "" )." href='".$this->app->tools->url( "doc" )."'>Documentation</a></li>
				<li><a".( $this->html["page"] == "variable" 		? " class='current'" : "" )." href='".$this->app->tools->url( "variable" )."'>Variable \$_HTML</a></li>
				<li><a".( $this->html["page"] == "app_minimalist" 	? " class='current'" : "" )." href='".$this->app->tools->url( "minimalist" )."'>APP Minimaliste</a></li>
			</ul>";

	return $data;
  }















/**
* **********************************************************************************************************************************************
*
*  EXEMPLES DE FONCTIONS COMMUNES À TOUTES LES APP
*
* **********************************************************************************************************************************************
*/

  
/* --------------------------------------------------------------------------------------------------------------------------------------------- GENERE L'URL VERS UNE PAGE */
public function make_url( $type , $data , $root_or_wat = true )
  {
  	$url = "";

	if( array($data) )
	  {
		if( ( $type == "projets" ) AND isset($data["id"]) AND isset($data["ur"]) )
		  {
		  	$url = "/projets/".$data["ur"]."-".$data["id"].".htm";
		  }

		else if( ( $type == "blog" ) AND isset($data["id"]) AND isset($data["ur"]) )
		  {
		  	$url = "/blog/".$data["ur"]."-".$data["id"].".htm";
		  }

	  }

	return ( ( $root_or_wat === true ) ? ROOT : "" ).$url;

  }





  
/* --------------------------------------------------------------------------------------------------------------------------------------------- + */




}


























