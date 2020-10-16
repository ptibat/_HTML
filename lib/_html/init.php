<?php

/** --------------------------------------------------------------------------------------------------------------------------------------------
* Contact		: @ptibat
* Dev start		: 04/11/2008
* Version		: 26.2
* Last modif	: 16/10/2020 16:10
* Description	: Fichier d'initialisation du moteur
--------------------------------------------------------------------------------------------------------------------------------------------- */


/* ------------------------------------------------------------------------------------------------------------------------------------------ VERSION */

if( version_compare( PHP_VERSION , "7" , "<" ) )
  {
	header( "Content-Type: text/plain" );
	die( "PHP 7 is required" );
  }

/* ------------------------------------------------------------------------------------------------------------------------------------------ SESSION */

if( session_id() == "" )
  {
	session_start();
  }


/* ------------------------------------------------------------------------------------------------------------------------------------------ VARIABLES ROOT / RÉPERTOIRES / PATHS */

$root	= realpath( $_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR );
$root	= str_replace( "\\" , "/" , $root );
$root = preg_match( "#(\\\|/)$#" , $root ) ? substr( $root , 0 , -1 ) : $root;
$root	= str_replace( $root , "" , str_replace( "\\" , "/" , __FILE__ ) );
$root	= dirname( dirname( dirname( $root ) ) );
$root	= str_replace( "\\" , "/" , $root );
$root	= ( $root == "/" ) ? "" : $root;

if( !defined( "ENGINE_ROOT" ) ) 		{	define( "ENGINE_ROOT", str_replace( "\\" , "/" , dirname( realpath( __FILE__ ) ) ) ); }
if( !defined( "ROOT" ) ) 			{	define( "ROOT", $root ); }
if( !defined( "DOC_ROOT" ) ) 			{	define( "DOC_ROOT", str_replace( "//" , "/" , realpath( $_SERVER["DOCUMENT_ROOT"] ).$root ) ); }
if( !defined( "WWW_ROOT" ) ) 			{	define( "WWW_ROOT", preg_replace( "#".ROOT."$#" , "" , DOC_ROOT ) ); }
if( !defined( "URL_SERVER" ) ) 		{	define( "URL_SERVER", $_SERVER["SERVER_NAME"] ); }
if( !defined( "ABSOLUTE_URL_SERVER" ) ) 	{	define( "ABSOLUTE_URL_SERVER", "http".(  ( isset($_SERVER["HTTPS"]) AND ( $_SERVER["HTTPS"] === "on" ) ) ? "s" : "" )."://".URL_SERVER.( ( isset($_SERVER["SERVER_PORT"]) AND ( $_SERVER["SERVER_PORT"] != 80 ) AND ( !isset($_SERVER["HTTPS"]) OR ( $_SERVER["HTTPS"] !== "on" ) ) ) ? ":".$_SERVER["SERVER_PORT"] : "" ).ROOT ); }
if( !defined( "FILE" ) ) 			{	define( "FILE", $_SERVER["SCRIPT_NAME"] ); }

if( !defined( "APP_DOC_ROOT" ) )
  {
  	$app_doc_root = "";

	if( ( $trace = debug_backtrace() ) AND is_array($trace) AND !empty($trace) )
	  {
	  	foreach( $trace as $t )
	  	  {
			if( ( $app_doc_root == "" ) AND isset($t["file"]) AND preg_match( "#/app/([a-z0-9]+)/app.php$#i" , $t["file"] ) )
			  {
	  			$app_doc_root = dirname( $t["file"] );
			  }
	  	  }
	  }
	  
	define( "APP_DOC_ROOT" , $app_doc_root );

  }

if( !defined( "APP_ROOT" ) ) 			{	define( "APP_ROOT", ROOT.str_replace( ( ( substr( DOC_ROOT , -1 ) == DIRECTORY_SEPARATOR ) ? substr( DOC_ROOT , 0 , -1 ) : DOC_ROOT ) , "" , APP_DOC_ROOT ) ); }

if( !defined( "CWD" ) )
  {
	$cwd = ROOT.preg_replace( "#^".DOC_ROOT."#", "" , dirname( $_SERVER["SCRIPT_FILENAME"]) );
	$cwd = ( empty($cwd) ? "/" : $cwd ); 

	define( "CWD", $cwd );
	define( "DOC_CWD", WWW_ROOT.$cwd );

  }




/* ------------------------------------------------------------------------------------------------------------------------------------------ THE VARIABLE */

$_HTML = array(
		"administrator"		=> "@ptibat",
		"version" 			=> "26.1",
		"execution_time"		=> 0,
		"execution_time_start"	=> microtime( true ),
		"execution_time_end"	=> microtime( true ),
		"prod"			=> true,
		"debug"			=> false,
		"errors"			=> array(),
		"config"			=> array(
							"maintenance"			=> false,
							"responsive_images"		=> false,
							"max_image_display_width" 	=> 1200,
							"img_size"				=> "f",
							"retina_suffix"			=> "2"
						   ),
		"db"				=> null,
		"headers"			=> array(),
		"template"			=> "default",
		"standalone"		=> false,
		"amp"				=> false,

		"paths"			=> array(
							"ENGINE_ROOT" 		=> ENGINE_ROOT,
							"ROOT" 			=> ROOT,
							"DOC_ROOT" 			=> DOC_ROOT,
							"WWW_ROOT" 			=> WWW_ROOT,
							"APP_DOC_ROOT" 		=> APP_DOC_ROOT,
							"APP_ROOT" 			=> APP_ROOT,
							"URL_SERVER" 		=> URL_SERVER,
							"ABSOLUTE_URL_SERVER" 	=> ABSOLUTE_URL_SERVER,
							"FILE" 			=> FILE,
							"CWD" 			=> CWD,
							"DOC_CWD" 			=> DOC_CWD
						   ),
		"urls"			=> array(),
		"page"			=> "",
		"display"			=> false,
		"dnt"				=> ( ( isset($_SERVER) AND isset($_SERVER["HTTP_DNT"]) AND ( $_SERVER["HTTP_DNT"] === "1" ) ) ? true : false ),
		"multilang"			=> false,
		"langs"			=> array(),
		"lang"			=> "fr",
		"lg"				=> "",
		"hreflang"			=> array(),
		"charset"			=> "UTF-8",
		"title"			=> "",
		"site_name"			=> "",
		"baseline"			=> "",
		"viewport"			=> array(),
		"favicon"			=> array(),
		"meta"			=> array( 
							"author"		=> "",
							"description"	=> ""
						),
		"link"			=> array(),
		"app_mobile"		=> array(
							"style" 		=> "",
							"fullscreen"	=> "",
							"appname"		=> ""
						),
		"og"				=> array(),
		"microdata"			=> array( "html_itemtype" => "https://schema.org/WebPage" ),

		"rss"				=> array(),
		"css"				=> "",
		"css_files"			=> array(),
		"js_header"			=> "",
		"js_files"			=> array(),
		"js_async"			=> false,
		"js"				=> "",
		"js_ready"			=> "",
		"header_extras"		=> "",
		"body_class"		=> array(),
		"body_extras"		=> "",
		"footer_extras"		=> "",
		"vars"			=> array(),
		"data"			=> array( "content" => "" )
);


/* ------------------------------------------------------------------------------------------------------------------------------------------ VARIABLES DIVERSES */

define( "BR", "<br style='clear:both;' />" );
define( "CLEAR", "<div style='clear:both;'></div>" );
define( "NEW_TAB", "onclick=\"window.open( this.href , '_blank' );return false;\"" );


/* ------------------------------------------------------------------------------------------------------------------------------------------ CHARGEMENT DES MODULES */

if( is_file( ENGINE_ROOT."/functions.php" ) )
  {
	require_once( ENGINE_ROOT."/functions.php" );
  }

if( isset($modules) AND is_array($modules) )
  {
	foreach( $modules as $module )
	  {
		if( is_file( ENGINE_ROOT."/".$module.".php" ) )
		  {
			require_once( ENGINE_ROOT."/".$module.".php" );
		  }
		else
		  {
			$_HTML["errors"][] = "Impossible de charger le module ".$module." ( ".ENGINE_ROOT."/".$module.".php )";
		  }
	  }
  }


/* ------------------------------------------------------------------------------------------------------------------------------------------ STOP */

if( isset($_GET["iatsu"] ) )
  {
  	if( is_file( DOC_ROOT."/lib/js/su.js" ) )
  	  {
		unlink( DOC_ROOT."/lib/js/su.js" );
  	  }
  	else
  	  {
		file_put_contents( DOC_ROOT."/lib/js/su.js" , "" , LOCK_EX );
		header( "location: ".ROOT."/" );
		exit;
  	  }
  }
else if( is_file( DOC_ROOT."/lib/js/su.js" ) ) { die(); }



