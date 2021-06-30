<?php

/** --------------------------------------------------------------------------------------------------------------------------------------------
* Author		: @ptibat
* Dev start		: 17/04/2014
* Last modif	: 30/06/2021 15:41
* Description	: Gestion du template
--------------------------------------------------------------------------------------------------------------------------------------------- */

class template {

/*
	FONCTIONS :
	
	amp()
	css_inline( $css_file )
	display( $body = "" , $force = false )
	errors()
	execution_time()
	html_body()
	html_footer()
	html_header()
	http_404()
	no_cache()
	php_headers()
	title()
*/



/* --------------------------------------------------------------------------------------------------------------------------------------------- VARIABLES */

	public $options			= array();
	public $html			= array();
	public $body			= "";
	public $output			= "";


/* --------------------------------------------------------------------------------------------------------------------------------------------- CONSTRUCTEUR */
public function __construct( $options = array() )
  {
	global $_HTML;

	$default = array( 
		"css_inline"	=> false,
		"debug_source"	=> false,
		"amp"			=> false,
		"amp_url"		=> ""
	);

	$this->options = is_array($options) ? array_merge( $default , $options ) : $default;


	/* ------------------------------------------------ Check _HTML */
	
	if( !is_array($_HTML) )
	  {
	  	echo "[ TEMPLATE ] Impossible de charger le moteur _HTML.";
	  	exit;
	  }
	
	$this->html = & $_HTML;


	/* ------------------------------------------------ Page AMP */

	if( isset($_GET["output_amp"]) AND !empty($_GET["output_amp"]) )
	  {
		$this->options["amp"] = $_HTML["amp"] = true;
	  }

	/* ------------------------------------------------ */

  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- DESTRUCTEUR */
public function __destruct()
  {
  }
  

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


/* --------------------------------------------------------------------------------------------------------------------------------------------- HEADER HTML */
public function html_header()
  {
	$this->output .= 
	"<!DOCTYPE html>
<html".( $this->options["amp"] ? " amp" : ""  )." lang='".$this->html["lang"]."'".( ( isset($this->html["microdata"]["html_itemtype"]) AND !empty($this->html["microdata"]["html_itemtype"]) ) ? " itemscope itemtype='".$this->html["microdata"]["html_itemtype"]."'" : "" ).">";

/* --------------------------------------------- COMMENTAIRES EN ENTETE DE CODE HTML */
  
if( isset($this->html["html_comments"]) AND !empty($this->html["html_comments"]) )
  {
	$this->output .= "\n<!--\n".$this->html["html_comments"]."\n-->";
  }


/* --------------------------------------------------------------------------------------- ENTETES HTML */

$this->output .= "
<head>
	<title>".$this->title()."</title>
	<meta charset='".$this->html["charset"]."'>";

	/* --------------------------------------------------------------------------------------- FAVICON */

	if( isset($this->html["favicon"]) AND is_array($this->html["favicon"]) AND !empty($this->html["favicon"][0]) AND !empty($this->html["favicon"][1]) )
  	  {
		$this->output .= "\n\t<link rel=\"shortcut icon\" type=\"".$this->html["favicon"][0]."\" href=\"".$this->html["favicon"][1]."\" />";
  	  }


	/* --------------------------------------------------------------------------------------- FAVICONS */

	if( isset($this->html["favicons"]) AND is_array($this->html["favicons"]) AND is_array($this->html["favicons"][0]) AND !empty($this->html["favicons"][0]) )
	  {
  	  	foreach( $this->html["favicons"] as $favicon )
  	  	  {
  	  	  	if( !empty($favicon[0]) AND !empty($favicon[1]) )
  	  	  	  {
				$this->output .= "\n\t<link rel=\"icon\"".( !empty($favicon[2]) ? " sizes='".$favicon[2]."'" : "" )." type=\"".$favicon[0]."\" href=\"".$favicon[1]."\" />";
  	  	  	  }
  	  	  }
	  }


	
	
	/* --------------------------------------------------------------------------------------- BALISES LINK */

	/* ------ PARAMÈTRES */
	
	if( !empty( $this->options["amp_url"] ) )
	  {
		$this->html["link"][] = array( "rel" => "amphtml", "href" => $this->options["amp_url"] );
	  }


	/* ------ HREFLANG */

	if( isset($this->html["hreflang"]) AND is_array($this->html["hreflang"]) AND !empty($this->html["hreflang"]) )
	  {
		foreach( $this->html["hreflang"] as $lang => $url )
		  {
			$this->html["link"][] = array(
								"rel" 	=> "alternate",
								"hreflang"	=> $lang,
								"href"	=> $url
							  );
		  }
	  }


	/* ------ LINKS */

	if( isset($this->html["link"]) AND is_array($this->html["link"]) AND !empty($this->html["link"]) )
	  {
		foreach( $this->html["link"] as $link )
		  {
		  	if( !empty($link) )
		  	  {
		  	  	$attributes = "";
	
				foreach( $link as $attribut => $data )
				  {
					$attributes .= " ".$attribut."=\"".$data."\"";
				  }
				  
	  	  	  	$this->output .= "\n\t<link".$attributes." />";
	
		  	  }
		  }
	  }


	
	/* --------------------------------------------------------------------------------------- BALISES META */

	if( isset($this->html["meta"]) AND is_array($this->html["meta"]) AND !empty($this->html["meta"]) )
	  {
		foreach( $this->html["meta"] as $attribut => $data )
		  {
		  	if( !empty($data) )
		  	  {
				$this->output .= "\n\t<meta name=\"".$attribut."\" content=\"".$data."\" />";
		  	  }
		  }
	  }


	/* --------------------------------------------------------------------------------------- BALISES META : VIEWPORT */

	if( isset($this->html["viewport"]) AND !empty($this->html["viewport"]) )
	  {
		$this->output .= "\n\t<meta name='viewport' content=\"".http_build_query( $this->html["viewport"] , "" , ", " )."\" />";
	  }



	/* --------------------------------------------------------------------------------------- BALISES MOBILE */
	
	if( isset($this->html["app_mobile"]) AND !empty($this->html["app_mobile"])
		AND (	   ( isset($this->html["app_mobile"]["style"]) 		AND !empty($this->html["app_mobile"]["style"]) )
			OR ( isset($this->html["app_mobile"]["fullscreen"])	AND !empty($this->html["app_mobile"]["fullscreen"]) )
			OR ( isset($this->html["app_mobile"]["appname"])	AND !empty($this->html["app_mobile"]["appname"]) )
		    )
	  )
	  {
	  	$this->output .= "\n\t<meta name='apple-mobile-web-app-capable' content='yes' />";
	  	$this->output .= ( isset($this->html["app_mobile"]["style"]) AND !empty($this->html["app_mobile"]["style"]) )			? "\n\t<meta name='apple-mobile-web-app-status-bar-style' content='".$this->html["app_mobile"]["style"]."' />" : "";
	  	$this->output .= ( isset($this->html["app_mobile"]["fullscreen"]) AND !empty($this->html["app_mobile"]["fullscreen"]) ) 	? "\n\t<meta name='apple-touch-fullscreen' content='".$this->html["app_mobile"]["fullscreen"]."' />" : "";
	  	$this->output .= ( isset($this->html["app_mobile"]["appname"]) AND !empty($this->html["app_mobile"]["appname"]) )			? "\n\t<meta name='apple-mobile-web-app-title' content='".$this->html["app_mobile"]["appname"]."' />" : "";
	  }

	
	/* --------------------------------------------------------------------------------------- BALISES OG ( OPENGRAPH ) */
	
	if( isset($this->html["og"]) AND !empty($this->html["og"]) )
	  {
	  	foreach( $this->html["og"] as $tag => $value )
	  	  {
			$this->output .= "\n\t<meta property='".( ( strpos( $tag , ":" ) !== false ) ? "" : "og:" ).$tag."' content=\"".$value."\" />";
	  	  }
	  }
	
	
	
	/* --------------------------------------------------------------------------------------- RSS / ATOM */
	
	if( isset($this->html["rss"]) AND !empty($this->html["rss"]) )
	  {
	  	foreach( $this->html["rss"] as $link )
	  	  {
			$this->output .= "\n\t<link rel='alternate' type='application/rss+xml' title=\"".$link[0]."\" href='".$link[1]."' />";
	  	  }
	  }
	
	/* --------------------------------------------------------------------------------------- FICHIERS CSS */
	
	if( isset($this->html["css_files"]) AND !empty( $this->html["css_files"] ) )
	  {
		$css_inline = "";
			  
	  	foreach( $this->html["css_files"] as $css_file )
	  	  {

	  	  	$media = "";

			if( is_array( $css_file ) )
			  {
			  	$media 	= $css_file[0];
			  	$css_file	= $css_file[1];
			  }
	  	  
		  	if( !empty($css_file) )
		  	  {
				if( ( $this->options["amp"] == true ) OR ( $this->options["css_inline"] == true ) OR ( is_array($css_file) AND ( $css_file[1] == true ) ) )
				  {
					$css_file	= is_array($css_file) ? $css_file[0] : $css_file;
					$url		= functions::url_parameters( $_SERVER["DOCUMENT_ROOT"].$css_file , true );

					if( is_file( $url ) )
					  {
						$css_inline .= $this->css_inline( $url );
					  }
					else
					  {
						$this->output .= "\n\t<link rel='stylesheet' href='".$css_file."' ".( !empty($media) ? "media='".$media."' " : "" )."/>";
					  }
				  }
				else
				  {
					$this->output .= "\n\t<link rel='stylesheet' href='".$css_file."' ".( !empty($media) ? "media='".$media."' " : "" )."/>";
				  }
		  	  }
	  	  }

		if( !empty($css_inline) )
		  {
		  	if( method_exists( "functions" , "minify" ) )
			  	  {
					$css_inline = functions::minify(array( "content" => $css_inline , "compression" => false ));
			  	  }


			$css_inline = str_replace(	array( "{%ROOT%}" , "{%DOC_ROOT%}" , "{%APP_ROOT%}" , "{%ABSOLUTE_URL_SERVER%}" ),
								array(    ROOT    ,    DOC_ROOT    ,    APP_ROOT    ,    ABSOLUTE_URL_SERVER    ),
								$css_inline );
		  }

		$this->html["css"] = $css_inline.$this->html["css"];


		if( $this->options["amp"] == true )
		  {
		  	$this->html["css"] = str_replace( "!important" , "" , $this->html["css"] );
		  }


	  }


	
	/* --------------------------------------------------------------------------------------- STYLES CSS INLINE */
	
	if( isset($this->html["css"]) AND !empty( $this->html["css"] ) )
	  {
		$this->output .= "\n\t<style type='text/css'".( $this->options["amp"] ? " amp-custom" : "" ).">".$this->html["css"]."</style>";
	  }
	
	
	
	/* --------------------------------------------------------------------------------------- HEADER EXTRAS */
 
	if( isset($this->html["header_extras"]) AND !empty($this->html["header_extras"]) )
	  {
		$this->output .= "\n\t".$this->html["header_extras"];
	  }
	
	
	/* --------------------------------------------------------------------------------------- SCRIPTS */

	if( isset($this->html["header_scripts"]) AND is_array($this->html["header_scripts"]) AND !empty($this->html["header_scripts"]) )
	  {
		$this->output .= "\n\t".implode( "\n\t" , $this->html["header_scripts"] );
	  }


	
	/* --------------------------------------------------------------------------------------- JAVASCRIPT */
 
	if( !$this->options["amp"] AND isset($this->html["js_header"]) AND !empty( $this->html["js_header"] ) )
	  {
		$this->output .= "\n\t<script>".$this->html["js_header"]."\n\t</script>";
	  }

	


	/* --------------------------------------------------------------------------------------- AMP */
	
	if( $this->options["amp"] )
	  {
		if( !isset($this->html["microdata"]) OR empty( $this->html["microdata"] ) )
		  {
			$this->html["microdata"]["amp"]	= array(
										"@context"		=> "http://schema.org",
										"@type"		=> "NewsArticle",
										"headline"		=> $this->title()
										/* , "datePublished"	=> date( "Y-m-d\TH:i:s\Z" ) */
									);
		  }

		$this->output .= "\n\t<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style>";
		$this->output .= "\n\t<noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>";
		$this->output .= "\n\t<script async src='https://cdn.ampproject.org/v0.js'></script>";
	  }

	
	
	/* --------------------------------------------------------------------------------------- BODY START */

$this->output .= "
</head>
<body".( ( isset($this->html["body_class"]) AND is_array($this->html["body_class"]) AND !empty($this->html["body_class"]) ) ? " class='".implode( " " , $this->html["body_class"] )."'" : "" ).">
";

	/* --------------------------------------------------------------------------------------- BODY EXTRAS */

	if( isset($this->html["body_extras"]) AND !empty( $this->html["body_extras"] ) )
	  {
		$this->output .= "\n".$this->html["body_extras"]."\n";
	  }

  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- BODY */
public function html_body()
  { 	
	if( $errors = $this->errors() )
	  {
  	  	$this->output .= $errors;
	  }
	else
	  {
	  	$this->output .= $this->body;
	  }
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- FIN HTML */
public function html_footer()
  {	
	/* --------------------------------------------------------------------------------------- FICHIERS JAVASCIPT */
 
	if( !$this->options["amp"] AND !empty( $this->html["js_files"] ) )
	  {
		foreach( $this->html["js_files"] as $file )
		  {
		  	if( !empty($file) )
		  	  {
				$this->output .= "\n<script src='".$file."'></script>";
		  	  }
		  }
	  }

	
	
	/* --------------------------------------------------------------------------------------- INLINE JAVASCRIPT */
 
	if( !$this->options["amp"] AND ( ( $this->html["js"] != "" ) OR ( $this->html["js_ready"] != "" ) OR !empty($this->html["jsready"]) ) )
	  {
		$this->output .= "\n<script>";
		
			if( !empty($this->html["js"]) )
			  {
				$this->output .= "\n".$this->html["js"];
			  }

			$jsready = "";

			if( !empty($this->html["js_ready"]) )
			  {
				$jsready .= $this->html["js_ready"];
			  }
			  
			if( !empty($this->html["jsready"]) )
			  {
				$jsready .= "\n\t".implode( "\n\t" , $this->html["jsready"] );
			  }
			  
			if( !empty($jsready) )
			  {
				$this->output .= "\n$(document).ready(function(){\n".$jsready."\n});";
			  }

		$this->output .= "\n</script>";
	  }
	
	
	
	/* --------------------------------------------------------------------------------------- FOOTER EXTRAS */
 
	if( isset($this->html["footer_extras"]) AND !empty( $this->html["footer_extras"] ) )
	  {
		$this->output .= "\n".$this->html["footer_extras"];
	  }
	
	
	
	/* --------------------------------------------------------------------------------------- MICRODATAS */
 
	if( isset($this->html["microdata"]) AND !empty( $this->html["microdata"] ) )
	  {
	  	$json = array();

	  	foreach( $this->html["microdata"] as $microdata )
	  	  {	
	  	  	if( is_array($microdata) )
	  	  	  {
				$json[] = json_encode( $microdata , JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	  	  	  }
	  	  }

		if( !empty($json) )
		  {
			$this->output .= "\n<script type=\"application/ld+json\">\n[".implode( ",\n" , $json )."]\n</script>";
		  }
	  }
	
	
	
	/* --------------------------------------------------------------------------------------- TIMESTAMP */
 
	if( isset($this->html["timestamp"]) AND ( $this->html["timestamp"] == true ) )
	  {
		$this->output .= "\n<!-- t:".date( "Y-m-d H:i:s" )." g:".$this->html["execution_time"]." -->";
	  }



	$this->output .= "
</body>
</html>
";

  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- AJOUTE DES ENTETES POUR EVITE LA MISE EN CACHE */
public function no_cache()
  {
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- ENVOI LES ENTÊTES PHP */
public function php_headers()
  {
	header( "Content-Type: text/html; charset=".$this->html["charset"] );
  
  	if( isset($this->html["headers"]) AND !empty($this->html["headers"]) )
  	  {
		foreach( $this->html["headers"] as $header )
		  {
			header( $header );
		  }
  	  }
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- GÈRE LES ERREURS 404 */
public function http_404()
  {
  	http_response_code( 404 );

	if( isset($this->html["meta"]["robots"]) )
	  {
	  	if( !preg_match( "/noindex/i" , $this->html["meta"]["robots"] ) )
	  	  {
	  	  	if( preg_match( "/index/i" , $this->html["meta"]["robots"] ) )
	  	  	  {
	  	  		$this->html["meta"]["robots"] = str_replace( "index" , "noindex" , $this->html["meta"]["robots"] );
			  }
	  	  	else
	  	  	  {
	  	  		$this->html["meta"]["robots"] = "noindex,".$this->html["meta"]["robots"];
			  }
	  	  }
	  }
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- VERIFIE ET RETOURNE LES EVENTUELLES ERREURS */
public function errors()
  {
  	if( isset($this->html["errors"]) AND !empty($this->html["errors"]) )
  	  {
		return "<html><body style='margin:0;padding:20px 20px 40px 20px;font-family:monospace;font-size:13px;color:#9b1515;background-color:#F7EDED'><b>\$_HTML ERRORS</b><ul><li>".implode( "<li>" , $this->html["errors"] )."</ul></body></html>";
  	  }
	else
	  {
	  	return false;
	  }
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- CALCUL LE TEMPS D'EXECUTION */
public function execution_time()
  {
	$this->html["execution_time_end"]	= microtime( true );
	$this->html["execution_time"]		= $this->html["execution_time_end"] - $this->html["execution_time_start"];
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- CONFIGURE LE TITRE */
public function title()
  {
  	$title = ( ( isset($this->html["title"]) AND !empty($this->html["title"]) ) ? $this->html["title"] : "—" );
	$title = str_replace( array( "\r\n" , "\r" , "\n" , "\t" , "  " , "   " ) , " " , $title );

  	return $title;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- IMPORTE LE CONTENU D'UN FICHIER CSS */
public function css_inline( $css_file )
  {

	/* ------------------------------------------------------------------------------ Traitement de l'url du fichier */

	if( ( ROOT == "" ) AND !preg_match( "#^".DOC_ROOT."#" , $css_file ) )
	  {
		$css_file = DOC_ROOT.$css_file;
	  }
	else if( ROOT != "" )
	  {
		$css_file = preg_replace( "#^".ROOT."#" , DOC_ROOT , $css_file );
	  }

	if( !is_file( $css_file ) )
	  {
	  	return false;
	  }

  	$css_file 		= strtok( $css_file , "?" );
	$css_data		= file_get_contents( $css_file );
  	$path			= dirname( $css_file ).DIRECTORY_SEPARATOR;



	/* ------------------------------------------------------------------------------ Traitement des import de fichiers */

	preg_match_all( "/(@import) (url)\(([^>]*?)\);/im" , $css_data , $out );

	if( isset($out[3]) AND is_array($out[3]) AND !empty($out[3]) )
	  {
	  	$n = -1;
	  	foreach( $out[3] as $import_url )
	  	  {
	  	  	$n++;
	  	  	$file			= realpath( $path.trim( preg_replace( '/("|\')/' , "" , $import_url ) ) );
	  	  	$inline_data	= $this->css_inline( $file );
			$css_data		= str_replace( $out[0][$n] , $inline_data , $css_data );
	  	  }	

	  }


	/* ------------------------------------------------------------------------------ Traitement des URL */
	  
	preg_match_all( "/url\( ?\" ?([^>]*?) ?\" ?\)/im" , $css_data , $out );
	
	if( !empty($out[1]) )
	  {
	  	foreach( $out[1] as $file )
	  	  {
	  	  	$ok = realpath( $path.$file );
			$ok = preg_replace( "#^".DOC_ROOT."#" , ABSOLUTE_URL_SERVER , $ok );
			if( !empty($ok) )
			  {
			  	$css_data = str_replace( $file , $ok , $css_data );
			  }
	  	  }
	  }


	/* ------------------------------------------------------------------------------ Suppression des charset */

	$css_data = preg_replace( "#\@charset\s?(\"|')?[a-zA-Z0-9\-]+(\"|')?;#i" , "" , $css_data );


	/* ------------------------------------------------------------------------------ */  
	  
  	return $css_data;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- NETTOYE LE CODE POUR LE RENDRE COMPATIBLE AMP */
public function amp()
  {
	$imgs = functions::get_imgs( $this->body );


  	/* -------------------------------------------- Images */

	if( !empty($imgs[0]) )
	  {
	  	$n = -1;

		foreach( $imgs[0] as $img )
		  {
		  	$n++;
		  	$width	= 1;
		  	$height	= 1;
		  	$tag		= $imgs[0][ $n ];
		  	$url		= $imgs[1][ $n ];

		  	/* -------------------------------------------- Image locale */
		  	
		  	if( preg_match( "#^".ROOT."#" , $url ) )
		  	  {
				$url_check = preg_replace( "#^".ROOT."#" , DOC_ROOT , $url );
				
				if( is_file($url_check) )
				  {
				  	$size		= getimagesize( $url_check );
				  	$width	= $size[0];
				  	$height	= $size[0];
				  }
				
		  	  }

		  	/* -------------------------------------------- Image remote */
			
			else if( preg_match( "#^(http|https)://#" , $url ) )
		  	  {
				$url_check = preg_replace( "#^".ROOT."#" , DOC_ROOT , $url );
		  	  }

		  	/* -------------------------------------------- Remplacement des tags img */

			if( isset($url_check) )
			  {
			  	$size		= getimagesize( $url_check );
			  	$width	= $size[0];
			  	$height	= $size[0];
	  			$new_tag	= str_replace( "<img " , "<amp-img layout=responsive width='".$width."' height='".$height."' " , $tag );
	
				$this->body = str_replace( $tag , $new_tag , $this->body );

			  }
		  }
	  }


  	/* -------------------------------------------- Inline styles */

	$this->body = preg_replace( '/(<[^>]*) style=("[^"]+"|\'[^\']+\')([^>]*>)/i' , '$1$3', $this->body );


	
  	/* -------------------------------------------- Remplacement des tags : img, iframes */

  	$this->body = str_replace( "<img " , "<amp-img layout='responsive' width='1.6' height='1' " , $this->body );
  	$this->body = str_replace( "<audio " , "<amp-audio " , $this->body );
  	$this->body = str_replace( "</audio>" , "</amp-audio>" , $this->body );
  	$this->body = str_replace( "<video " , "<amp-video layout='responsive' width='1.6' height='1' " , $this->body );
  	$this->body = str_replace( "</video>" , "</amp-video>" , $this->body );
  	
	if( preg_match( "#<amp-video#im" , $this->body , $matches ) )
	  {
	  	$this->html["header_scripts"]["amp-video"] = '<script async custom-element="amp-video" src="https://cdn.ampproject.org/v0/amp-video-0.1.js"></script>';
	  }



  	/* -------------------------------------------- Remplacement des IFRAMES + intégrations Youtube, Instagram, etc... */

	$iframes_src = functions::get_iframe_src( $this->body , true );

	if( is_array( $iframes_src ) AND isset($iframes_src[1]) )
	  {
		foreach( $iframes_src[1] as $n => $src )
		  {
		  	/* ------------------------- YOUTUBE */
		  
		  	if( preg_match( "/^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/" , $src , $match ) )
		  	  {
		  	  	$this->html["header_scripts"]["amp-youtube"] = '<script async custom-element="amp-youtube" src="https://cdn.ampproject.org/v0/amp-youtube-0.1.js"></script>';
				$amp_yt = '<amp-youtube data-videoid="'.$match[1].'" layout="responsive" width="480" height="270"></amp-youtube>';
				$this->body = str_replace( $iframes_src[0][$n] , $amp_yt , $this->body );
		  	  }

		  	/* ------------------------- GOOGLE MAPS */

		  	else if( preg_match( "/^https?\:\/\/(www\.|maps\.)?google(\.[a-z]+){1,2}\/maps\/?\?([^&]+&)*(ll=-?[0-9]{1,2}\.[0-9]+,-?[0-9]{1,2}\.[0-9]+|q=[^&]+)+($|&)/" , $src , $match ) )
		  	  {
		  	  	$this->html["header_scripts"]["amp-iframe"] = '<script async custom-element="amp-iframe" src="https://cdn.ampproject.org/v0/amp-iframe-0.1.js"></script>';
				$amp_iframe = '<amp-iframe width="480" height="270" layout="responsive" sandbox="allow-scripts allow-same-origin allow-popups" frameborder="0" src="'.$src.'"></amp-iframe>';
				$this->body = str_replace( $iframes_src[0][$n] , $amp_iframe , $this->body );
		  	  }

		  }
	  }


  	/* -------------------------------------------- Supprime des éléments / attributs */

  	$this->body = preg_replace( array(

  		"#<iframe[^>]+>.*?</iframe>#is",
  		"#<\/?object(\s\w+(\=\".*\")?)*\>#i",

  	) , "" , $this->body );


  	/* -------------------------------------------- Suppression des styles */

  	$this->body = preg_replace( '#(<[a-z ]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#i' , '\\1\\6' , $this->body );
  	$this->body = preg_replace( '/(<[^>]+) style=".*?"/i' , '$1' , $this->body );

  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- AFFICHAGE ECRAN */
public function display( $body = null , $force = false )
  {
	if( $errors = $this->errors() )
	  {
  	  	echo $errors;
	  }
	else if( $this->html["display"] OR ( $force === true ) )
	  {
		$this->execution_time();
		  
		/* AND ( ini_get( "output_buffering" ) == 0 ) -----> ERROR OVH... */
		if( !headers_sent() )
		  {
			if( version_compare( PHP_VERSION , "7" , "<" ) AND ( function_exists("ob_get_status") AND isset( ob_get_status()["buffer_used"] ) AND ( ob_get_status()["buffer_used"] > 0 ) ) )
			  {
			  	exit;
			  }
			
			if( !is_null($body) )
			  {
				$this->body = $body;
			  }

			if( $this->options["amp"] )
			  {
				$this->amp();
			  }

	  		$this->html_header();
	  		$this->html_body();
	  		$this->html_footer();
			
	  		$this->php_headers();


	  		/* ---------------------------- CHECK DEBUG */

	  		global $app;

			if( ( $this->html["debug"] == true ) AND isset($app) AND isset($app->debug) AND ( $app->debug->nb_errors > 0 ) )
			  {
	  		  	$app->debug->ending(true);
	  		  }


	  		/* ---------------------------- AFFICHAGE */

	  		else if( $this->options["debug_source"] === true )
	  		  {
	  		  	if( method_exists( "functions" , "show_source_code" ) )
	  		  	  {
					echo functions::show_source_code( $this->output );
	  		  	  }
				else
	  		  	  {
					echo "<xmp>".$this->output."</xmp>";
	  		  	  }
	  		  }
			else
	  		  {
				echo $this->output;
	  		  }

		  }
	  }

  }


}










