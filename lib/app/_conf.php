<?php

/** --------------------------------------------------------------------------------------------------------------------------------------------
* Contact		: @ptibat
* Dev start		: 04/11/2008
* Last modif	: 15/10/2020 17:10
* Description	: Fichier de config du site
--------------------------------------------------------------------------------------------------------------------------------------------- */

/**
* BASE DE DONNEES
*/

define( "DB_DATABASE", "_html" );
define( "DB_HOST", "localhost" );
define( "DB_USER", "user" );
define( "DB_PASSWORD", "password" );


/**
* CONFIGURATION $_HTML
*/

$_HTML["administrator"]				= ""; /* Adresse email pour envoi des erreurs */
$_HTML["site_name"] 				= "_HTML";
$_HTML["baseline"] 				= "Micro framework PHP";
$_HTML["title"] 					= $_HTML["site_name"].( !empty($_HTML["baseline"]) ? ", ".$_HTML["baseline"] : "" );
$_HTML["multilang"]				= true;
$_HTML["langs"]					= array(
								"fr" => "Français",
								"en" => "Anglais"
							);

