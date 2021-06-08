<?php

/** --------------------------------------------------------------------------------------------------------------------------------------------
* Contact		: @ptibat
* Dev start		: 07/05/2007
* Version		: 26.1
* Last modif	: 08/06/2021 10:50
* Description	: Classe de fonctions en tout genre
* Required 		: PHP 7
--------------------------------------------------------------------------------------------------------------------------------------------- */

class functions {
	
/* --------------------------------------------------------------------------------------------------------------------------------------------- VARIABLES */

	private static $cle_de_cryptage	= "0101010101";			/* Clé pour le cryptage/décryptage */
	private static $log			= array();				/* Evénements qui seront ajouté au fichier de LOG */
	private static $repertoire_cache	= "/cache/";			/* Répertoire de stockage des fichiers mis en cache */
	private static $execution_times	= array();				/* Temps d'executions */
	private static $directory_sort	= "name";				/* Tri par defaut du listing des répertoires */
	public static  $php_user		= "";					/* Nom du compte utilisateur connecté avec PHP Auth */


/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOYE UNE ERREUR LORS DE L'EXECUTION D'UNE FONCTION INEXISTANTE */
public static function __callStatic( $m , $a )
  {
	echo __CLASS__." : La fonction appelée \"".$m."\" est inexistante.\n";
	exit;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOI LA VARIABLE ROOT */
public static function root()
  {
	global $_HTML;
	return ( isset($_HTML) AND isset($_HTML["paths"]["ROOT"]) ) ? $_HTML["paths"]["ROOT"] : "";
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- LISTE LES FONCTIONS DE LA CLASSE */
public static function funcs( $return=false )
  {
	$func = get_class_methods( __CLASS__ );
	sort( $func );

	if( $return === true )
	  {
		return $func;
	  }
	else
	  {
		print_r( $func );
	  }
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LA RACINE DU SITE  */
public static function get_root( $file , $sub_directories=0 )
  {
	$sub_directories = is_numeric($sub_directories) ? $sub_directories : 0;

	$root	= realpath( $_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR );
	$root	= str_replace( "\\" , "/" , $root );
	$root = preg_match( "#(\\\|/)$#" , $root ) ? substr( $root , 0 , -1 ) : $root;
	$root	= str_replace( $root , "" , str_replace( "\\" , "/" , $file ) );

	for( $r=0 ; $r<=$sub_directories ; $r++ )
	  {
		$root	= dirname( $root );
	  }

	$root	= str_replace( "\\" , "/" , $root );
	$root	= ( $root == "/" ) ? "" : $root;

	return $root;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOI L'URL DE LA PAGE EN COURS */
public static function current_url( $parameters = array() , $root = true )
  {
	$protocol	= "http".(  ( isset($_SERVER["HTTPS"]) AND ( $_SERVER["HTTPS"] === "on" ) ) ? "s" : "" )."://";
	$url		= $protocol.$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
	$url		= self::url_parameters( $url , $parameters );
	return $url;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOI UNE URL AVEC LES PARAMÈTRES MODIFIÉS */
public static function url_parameters( $url , $parameters = array() )
  {
	$query	= parse_url( $url , PHP_URL_QUERY );
  	$clean_url	= preg_replace( "#\??".$query."$#" , "" , $url );

	if( $parameters === true )
	  {
	  	$url = $clean_url;
	  }
	else if( !empty($parameters) AND is_array($parameters) )
	  {
		parse_str( $query , $query );
		$query = array_merge( $query , $parameters );
	  	
		$url = $clean_url."?".http_build_query( $query );
	  }
	return $url;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOIE L'ADRESSE IP MEME DERRIERE UN PROXY */
public static function ip( $proxy = false )
  {
	if( isset( $_SERVER ) )
	   {
		if( $proxy AND isset($_SERVER["HTTP_X_FORWARDED_FOR"]) AND !empty($_SERVER["HTTP_X_FORWARDED_FOR"]) AND preg_match("#^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$#", $_SERVER["HTTP_X_FORWARDED_FOR"] ) )
		  {
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		  }
		else if( $proxy AND isset( $_SERVER["HTTP_CLIENT_IP"] ) )
		  {
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		  }
		else
		  {
			$ip = $_SERVER["REMOTE_ADDR"];
		  }
	  }
	else
	  {
		if( $proxy AND getenv("HTTP_X_FORWARDED_FOR") )
		  {
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		  }
		else if( $proxy AND getenv("HTTP_CLIENT_IP") )
		  {
			$ip = getenv("HTTP_CLIENT_IP");
		  }
		else
		  {
			$ip = getenv("REMOTE_ADDR");
		  }
	  }

	if( $ip == "::1" )
	  {
	  	$ip = getHostByName(getHostName());
	  }

	return $ip;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOIE L'ADRESSE IP MEME DERRIERE UN PROXY */
public static function is_ip( $check = null )
  {
  	$return 	= false;
	$ip		= self::ip();

  	if( is_array($check) AND !empty($check) )
  	  {
  	  	foreach( $check as $check_ip )
  	  	  {
  	  	  	if( $check_ip == $ip )
  	  	  	  {
  	  	  	  	$return = true;
  	  	  	  }
  	  	  }
  	  }
	else if( !is_null($check) AND self::is_valid_ip($check) AND ( $check == $ip ) )
  	  {
		$return = true;
  	  }

	return $return;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOIE L'HOST EN FONCTION D'UNE IP */
public static function host( $ip=null )
  {
	if( $ip === null )
	  {
		$ip = self::ip();
	  }
	return self::is_private_ip( $ip ) ? $ip : @gethostbyaddr($ip);
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOIE L'USER AGENT */
public static function ua()
  {
	return ( isset($_SERVER["HTTP_USER_AGENT"]) AND !empty($_SERVER["HTTP_USER_AGENT"]) ) ? $_SERVER["HTTP_USER_AGENT"] : "Unknown";
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOIE LA PAGE DE PROVENANCE (REFERER) */
public static function referer()
  {
	return ( isset($_SERVER["HTTP_REFERER"]) AND !empty($_SERVER["HTTP_REFERER"]) ) ? $_SERVER["HTTP_REFERER"] : "";
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- TYPE D'ENCODAGE */
public static function string_encoding( $string )
  {
	if( $string === mb_convert_encoding( mb_convert_encoding( $string , "UTF-32", "UTF-8"), "UTF-8", "UTF-32") )
	  {
		return "UTF-8";
	  }
	else if( mb_convert_encoding( $string , "ASCII" ) )
	  {
		return "ASCII";
	  }
	else
	  {
		return "auto";
	  }
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- ALIAS DE LA FONCTION STRING_ENCODER() */
public static function _( $string , $quotes=false , $transform="none" )
  {
	return self::string_encoder( $string , $quotes , $transform );
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- ENCODE UNE CHAINE */
public static function string_encoder( $string , $quotes=false , $transform="none" )
  {
	$string = str_replace( "&" , "&amp;" , $string );
	if(isset($transform) AND ($transform=="upper"))		{ $string = self::uppercase($string); }
	if(isset($transform) AND ($transform=="lower"))		{ $string = self::lowercase($string); }
	$string = mb_convert_encoding( $string , "HTML-ENTITIES" , self::string_encoding($string) );
	$string = str_replace( "'" , "&#39;" , $string );
	$string = str_replace( "&#128;" , "&euro;" , $string );

	if( $quotes === true )
	  {
		$string = str_replace( '"' , "&#34;" , $string );
	  }
	  
	return $string;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- DECODE UNE CHAINE */
public static function string_decoder( $string , $transform="none" )
  {
	$string = html_entity_decode( $string , NULL , self::string_encoding($string) );
	return $string;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- CONVERT TO MAJUSCULES */
/**
* 
* convert_to_majuscules
* convert_to_minuscules
* convert_to_title
* 
* self::string_encoding( $txt ); 
* 
*/

public static function uppercase( $txt )
  {
	return mb_convert_case( $txt , MB_CASE_UPPER );
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- CONVERT TO MINUSCULES */
public static function lowercase( $txt )
  {
	return mb_convert_case( $txt , MB_CASE_LOWER );
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- CONVERT TO TITLE */
public static function capitalize( $txt )
  {
	return mb_convert_case( $txt , MB_CASE_TITLE );
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- CAPITALE A CHAQUE PREMIERE LETTRE D'UN MOT */
public static function uc( $txt )
  {
	$txt = strtolower( $txt );
	$txt = ucwords( $txt );
	$txt = implode( "-" , array_map( "ucfirst", explode( "-" , $txt ) ) );

	return $txt;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- CONVERT TO FIRST MAJUSCULES */
public static function convert_to_first_majuscule( $string )
  {
	$string	= explode( " " , $string );
	$string[0]	= mb_convert_case( $string[0] , MB_CASE_TITLE , self::string_encoding( $string[0] ) );
	$string	= implode( " " , $string );

	return self::string_encoder( $string );
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- DECODAGE UTF8 D'UN TABLEAU OU UNE CHAINE */
public static function utf8_decode( $var , $urldecode="" )
  {
	if( is_array( $var ) )
	  {
		while( list($key , $value) = each($var) )
		  {
			if(isset($urldecode) AND ($urldecode=="urldecode"))		{ $value = urldecode($value);  }
			if(isset($urldecode) AND ($urldecode=="rawurldecode"))	{ $value = rawurldecode($value);  }
			$value = str_replace( "œ" , self::string_encoder("œ") , $value );
			$value = str_replace( "ÿ" , self::string_encoder("ÿ") , $value );
			$value = str_replace( "Ÿ" , self::string_encoder("Ÿ") , $value );
			$var[$key] = utf8_decode( $value );
		  }
		return $var;
	  }
	else
	  {
		if(isset($urldecode) AND ($urldecode=="urldecode"))		{ $var = urldecode($var);  }
		if(isset($urldecode) AND ($urldecode=="rawurldecode"))	{ $var = rawurldecode($var);  }
		$var = str_replace( "œ" , self::string_encoder("œ") , $var );
		$var = str_replace( "ÿ" , self::string_encoder("ÿ") , $var );
		$var = str_replace( "Ÿ" , self::string_encoder("Ÿ") , $var );
		return utf8_decode( $var );
	  }
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- ENCODAGE UTF8 */
public static function utf8_encode( $var )
  {
	if( is_array( $var ) )
	  {
		while( list($key , $value) = each($var) )
		  {
			$var[$key] = utf8_encode( $value );
		  }
		return $var;
	  }
	else
	  {
		return utf8_encode( $var );
	  }
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- CONVERTISSEUR DE CHAINE STRICT ( &#000; ) */

public static function strictify( $string , $full=false )
  {
	$fixed = mb_convert_encoding( $string , "HTML-ENTITIES" , self::string_encoding($string) );
	$fixed = html_entity_decode( $fixed );
	$fixed = str_replace( "'" , "&#39;" , $fixed );
	$fixed = str_replace( '"' , "&#34;" , $fixed );
	/*
		$fixed = str_replace( "<" , "&#60;" , $fixed );
		$fixed = str_replace( ">" , "&#62;" , $fixed );
	*/

	$start = ( ($full==true) ? 0 : 127 );

	$trans_array = array();
	for ($i=$start; $i<255; $i++)
	  {
		$trans_array[ chr($i) ] = "&#".$i.";";
	  }

	$really_fixed = strtr( $fixed , $trans_array );

	return $really_fixed;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- CONVERTISSEUR DE CHAINE STRICT POUR JAVASCRIPT (OCTAL) */
public static function strictify_javascript( $string )
  {
	$fixed = self::strictify( $string , true );
	$fixed = str_replace( "&#" , "" , $fixed );
	$fixed = explode( ";" , $fixed );

	$really_fixed = "";

	for($i=0 ; $i<count($fixed)-1 ; $i++ )
	  {
		$really_fixed .= "\\".decoct( $fixed[$i] );
	  }

	return $really_fixed;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- SUPPRIME LES TAGS HTML QUI NE SONT PAS PASSES EN PARAMETRES */
public static function remove_html_tags( $text , $tags="" )
  {
	if( !preg_match( "/\<br\>/" , $tags ) )
	  {
	  	$text = preg_replace( "/\<br ?\/?\>/i" , " " , $text );
	  }
	$text = strip_tags( stripslashes( $text ) , $tags );

	return $text; 
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- SUPPRIME LES IMAGES */
public static function remove_img( $text , $replace="" )
  {
	return preg_replace( "`<img[^>]*>`" , $replace , $text );
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- CONVERTIT UN TEXTE HTML EN TEXTE BRUT */
public static function texte_brut( $texte )
  {
	$texte = preg_replace("#[\n\r]+#i", " ", $texte);
	$texte = preg_replace("#<br />#i", "\n", $texte);
	$texte = preg_replace("#< (p|br)([[:space:]][^>]*)?".">#i", "\n\n", $texte);
	$texte = preg_replace("#^\n+#i", "", $texte);
	$texte = preg_replace("#\n+$#i", "", $texte);
	$texte = preg_replace("#\n +#i", "\n", $texte);
	$texte = self::remove_html_tags($texte);
	$texte = preg_replace("#( | )+#i", " ", $texte);
	$texte = trim( $texte );

	return $texte;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- SUPPRIME TOUT LE HTML ET GARDE LE TEXTE ET LES IMAGES */
public static function html2text( $string )
  {
	$search = array(  '@<script[^>]*?>.*?</script>@si', 		/* Strip out javascript */
				'@<style[^>]*?>.*?</style>@siU', 		/* Strip style tags properly */
				'@<[?]php[^>].*?[?]>@si',			/* Scripts php */
				'@<[?][^>].*?[?]>@si', 				/* Scripts php */
				'@<[\/\!]*?[^<>]*?>@si', 			/* Strip out HTML tags */
				'@<![\s\S]*?--[ \t\n\r]*>@',			/* Strip multi-line comments including CDATA */
			  );

	$string = preg_replace( $search, "" , $string );
	return $string;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- COUPE UNE CHAINE SANS COUPER LES MOTS */
public static function cut_string( $string , $length , $hellip = 1 )
  {
	$string = strip_tags($string);

	if( strlen($string) > $length )
	  {
		$string = substr( $string , 0 , $length+1 );

		if( preg_match( "# #" , $string ) )
		  {
			while( $string[strlen($string)-1]!=" " )
			  {
				$string = substr($string, 0, strlen($string)-1);
			  }
		  }

		$string = substr( $string , 0 , strlen($string)-1 );

		if( $hellip == 1 )						{ $string .= "…"; }
		else if( !empty($hellip) AND ($hellip!="0") )	{ $string .= $hellip; }

		return $string;
	  }
	else
	  {
		return $string;
	  }
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- COUPE UNE CHAINE APRES xx CARACTERES */
public static function cut_string_strict( $string , $length , $hellip = 1 )
  {
	$string = strip_tags($string);

	if( strlen($string) > $length )
	  {
		$string = substr( $string , 0 , $length );
		if( $hellip == 1 )						{ $string .= "…"; }
		else if( !empty($hellip) AND ($hellip!="0") )	{ $string .= $hellip; }

		return $string;
	  }
	else
	  {
		return $string;
	  }
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- DECOUPE UNE CHAINE TOUS LES xx CARACTERES ENVIRON */
public static function cut_text( $string , $length=50 , $eol="<br />" )
  {
	$return	= "";
	$xplod 	= explode( "§" , wordwrap( $string , $length , "§" ) );
	foreach( $xplod as $part )
	  {
		$return .= $part.$eol;
	  }

	return $return;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- PRÉPARE UN TEXTE POUR L'ATTRIBUT ALT D'UNE IMAGE */
public static function alt( $text , $max = 150 , $guillemet = true )
  {
	/* $text = functions::remove_html_tags( $text ); */
	$text = str_replace( " " , " " , $text );
	$text = str_replace( array( "\r\n" , "\r" , "\n" , "\t" , "  " , "   " ) , " " , $text );
	$text = preg_replace( "/\.(?=[a-z\d])/i" , ". " , $text );
	$text = preg_replace( "/\. (fr|com|eu|org)( )?$/i" , ".$1" , $text );
	$text = functions::cut_string( $text , $max );
	$text = str_replace( '"' , ( $guillemet ? "''" : "" ) , $text );
	$text = trim( $text );

	return $text;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- DECOUPE UNE CHAINE TOUS LES xx CARACTERES ENVIRON */
public static function raw_text( $texte , $length=null , $new_lines=false )
  {
	$texte = self::remove_html_tags( $texte );
	$texte = html_entity_decode( $texte );
	$texte = trim( $texte );
	$texte = str_replace( "\t" , "" , $texte );
	$texte = str_replace( "\n\n" , "\n" , $texte );
	$texte = str_replace( "\n\n" , "\n" , $texte );

	if( $new_lines == false )
	  {
		$texte = str_replace( "\n" , ". " , $texte );
		$texte = str_replace( ".." , "." , $texte );
	  }

	$texte = str_replace( "&nbsp;" , " " , $texte );
	$texte = str_replace( "   " , " " , $texte );
	$texte = str_replace( "  " , " " , $texte );

	if( is_numeric($length) )
	  {
	  	$texte = self::cut_string( $texte , $length );
	  }
	return $texte;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- TRANSFORME UNE CHAINE POUR L'URL REWRITE */
public static function rewrite_url( $text , $separator="-" )
  {
	$text = trim( $text );
	$text = str_replace( " " , $separator , $text );
	$text = str_replace( "." , $separator , $text );
	$text = str_replace( "'" , "-" , $text );
	$text = self::no_accents( $text );
	$text = strtolower( $text );
	$text = preg_replace("#[^a-z0-9-_]#" , "" , $text );
	$text = preg_replace( "#[_]+#" , $separator , $text );
	$text = str_replace( $separator.$separator.$separator , $separator , $text );
	$text = str_replace( $separator.$separator , $separator , $text );
	
	return $text;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- VÉRIFIE SI LE TEXTE EST VALIDE POUR L'URL REWRITING */
public static function is_valide_url_rewrite( $text , $spaces=false )
  {
	if( preg_match( "/^([a-z0-9\-\_".( $spaces ? " " : "" )."])+$/mi" , $text ) )
	  {
		return true;
	  }
	else
	  {
		return false;
	  }
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- SUPPRIME LES ACCENTS D'UNE CHAINE */
public static function no_accents( $string , $method=1 )
  {
	if( $method === 1 )
	  {
		$string = strtr( utf8_decode($string) , utf8_decode( "àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ" ), "aaaaaceeeeiiiinooooouuuuyyAAAAAACEEEEIIIINOOOOOUUUUY" );
	  }
	else if( $method === 2 )
	  {
		$string		= preg_replace('#\&([A-za-z])(?:acute|cedil|circ|grave|ring|tilde|uml)\;#', '\1', $string);
		$string		= preg_replace('#\&([A-za-z]{2})(?:lig)\;#', '\1', $string);						/* pour les ligatures e.g. '&oelig;' */
		$string		= preg_replace('#\&[^;]+\;#', '', $string);									/* supprime les autres caractères */
	  }
	else if( $method === 3 )
	  {
		$string		= htmlentities( $string , ENT_COMPAT , self::string_encoding($string) );
		$string		= preg_replace( "/&([a-zA-Z])(uml|acute|grave|circ|tilde|lig|cedil|ring);/" , "$1" , $string );
	  }

	return $string;
  }
/* --------------------------------------------------------------------------------------------------------------------------------------------- NETTOYE UN TEXTE DES CARACTÈRES SPECIAUX */
public static function clean_text( $txt )
  {
	return str_replace( "  " , " " , preg_replace( "/[^\p{L}0-9\-\_\ \\n]/u" , "" , $txt ) );
  }
/* --------------------------------------------------------------------------------------------------------------------------------------------- NE GARDE DE LA CHAINE QUE LES CARACTERE ALPHANUMERIQUES */
public static function only_alphanumeric( $txt , $replace = "" , $spaces = true , $tirets = true )
  {
  	$tirets = $tirets ? "\-\—\_" : "";
  	$reg = ( $spaces == true ) ? "#[^A-Z0-9".$tirets."\ ]#i" : "#[^A-Z0-9".$tirets."]#i";
	$txt = self::no_accents( $txt );
	$txt = preg_replace( $reg , $replace , $txt );
	return $txt;
  }
/* --------------------------------------------------------------------------------------------------------------------------------------------- NE GARDE DE LA CHAINE QUE LES CARACTERE NUMERIQUES */
public static function only_numeric( $txt , $replace="" )
  {
	return preg_replace ("#[^0-9\,\.\-]#i", $replace, $txt );
  }
/* --------------------------------------------------------------------------------------------------------------------------------------------- GENERE UNE CHAINE UNIQUE DE X CARACTERES */
public static function random( $nb_char=8 , $type=1 , $nb_strings=1 )
  {
	$string = ( is_numeric($nb_strings) AND ( $nb_strings > 1 ) ) ? array() : "";

	switch( $type )
	  {
		case 1 :	$chaine = "abcdefghijklmnopqrstuvwxyz"; break;
		case 2 :	$chaine = "abcdefghijklmnopqrstuvwxyz0123456789"; break;
		case 3 :	$chaine = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"; break;
		case 4 :	$chaine = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"; break;
		case 5 :	$chaine = "0123456789"; break;
		case 6 :	$chaine = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"; break;
		case 7 :	$chaine = "0123456789abcdef"; break;
		case 8 :	$chaine = "012345689ABCDEFGHJKMNPRSTUVWXYZ"; break;
		default :	$chaine = "0123456789"; break;
	  }

	if( $type == 99 )
	  {
		if( is_array($string) )
		  {
			for( $n=0 ; $n<$nb_strings ; $n++ )
			  {
				$tmp = substr( str_shuffle( "abcdefghijklmnopqrstuvwxyz0123456789" ) , 0 , $nb_char );
				if( !in_array( $tmp , $string ) )
				  {
					$string[$n]	= $tmp;
				  }
				else
				  {
					$n = $n-1;
				  }
			  }
		  }
		else
		  {
			$string = substr( str_shuffle( "abcdefghijklmnopqrstuvwxyz0123456789" ) , 0 , $nb_char );
		  }
	  }
	else
	  {
		if( is_array($string) )
		  {
			for( $n=0 ; $n<$nb_strings ; $n++ )
			  {
				$tmp = "";
				srand((double)microtime()*1000000);
				for($i=0; $i<$nb_char; $i++)
				  {
					$tmp .= $chaine[rand()%strlen($chaine)];
				  }

				if( !in_array( $tmp , $string ) )
				  {
					$string[$n]	= $tmp;
				  }
				else
				  {
					$n = $n-1;
				  }
			  }
		  }
		else
		  {
			srand((double)microtime()*1000000);
			for($i=0; $i<$nb_char; $i++)
			  {
				$string .= $chaine[rand()%strlen($chaine)];
			  }
		  }
	  }

	return $string;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- FORMATTE UN NUMERO DE TELEPHONE */
public static function format_phone_number( $number )
  {
	$number	= preg_replace( "#[^+0-9]#i" , "" , $number );
	$number	= trim( $number );
	return $number;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- DETECTE LES TELEPHONES MOBILES */
public static function is_mobile( $ua = null )
  {
	$user_agent 	= ( $ua !== null ) ? $ua : self::ua();
	$mobile_browser 	= "0";

	if( preg_match( "/(up.browser|up.link|windows ce|iemobile|mmp|symbian|smartphone|midp|wap|phone|vodafone|o2|pocket|mobile|pda|psp|fennec)/i" , strtolower( $user_agent ) ) )
	  {
		$mobile_browser++;
	  }

	if( ( $ua == null )
		AND
		  (
			( isset($_SERVER["HTTP_ACCEPT"]) AND ( ( strpos( strtolower($_SERVER["HTTP_ACCEPT"]) , "text/vnd.wap.wml" ) > 0) OR (strpos(strtolower($_SERVER["HTTP_ACCEPT"]), "application/vnd.wap.xhtml+xml" ) > 0 ) ) )
			OR ( (((isset($_SERVER["HTTP_X_WAP_PROFILE"]) OR isset($_SERVER["HTTP_PROFILE"]) OR isset($_SERVER["X-OperaMini-Features"]) or isset($_SERVER["UA-pixels"])))) )
		  )
	  )
	  {
		$mobile_browser++;
	  }

	$mobile_ua		= strtolower( substr( $user_agent , 0 , 4 ) );
	$mobile_agents	= array('acs-','alav','alca','amoi','audi','aste','avan','benq','bird','blac','blaz','brew','cell','cldc','cmd-','dang','doco','eric',
					'hipt','inno','ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-','maui','maxo','midp','mits','mmef','mobi',
					'mot-','moto','mwbp','nec-','newt','noki','opwv','palm','pana','pant','pdxg','phil','play','pluc','port','prox','qtek','qwap',
					'sage','sams','sany','sch-','sec-','send','seri','sgh-','shar','sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli',
					'tim-','tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp','wapr','webc','winw','winw','xda','xda-');

	if( in_array( $mobile_ua , $mobile_agents ) )
	  {
		$mobile_browser++;
	  }


	if( $mobile_browser > 0 )
	  {
		return true;
	  }
	else
	  {
		return false;
	  }
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- URL TO LINK */
public static function make_linkable( $text , $twitter=false , $blank=true )
  {
	$blank = ( $blank == true ? " target='_blank'" : "" );

	$text = " ".$text." ";
	$text = preg_replace( "`(((f|ht){1}tp(s)?://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)`", "<a href='\\1'".$blank.">\\1</a>", $text);
	$text = preg_replace( "`([[:space:]()[{}>])(www.[-a-zA-Z0-9@:%_\+.~#?&//=]+)`", "\\1<a href='http://\\2'".$blank.">\\2</a>", $text);
	$text = preg_replace( "#([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})#" , "<a href='mailto:\\1'>\\1</a>", $text);

	if( $twitter )
	  {
		$text = preg_replace( "/(^| )@(\w+)/", "\\1<a href='http://www.twitter.com/\\2'".$blank.">@\\2</a>" , $text );
		$text = preg_replace( "/(^| )#(\w+)/", "\\1<a href='http://search.twitter.com/search?q=\\2'".$blank.">#\\2</a>" , $text );
	  }

	$text = trim( $text );

	return $text;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- URL TO LINK */
public static function make_link( $link , $text=null , $blank=true )
  {
	$text		= ( $text !== null ) ? $text : $link;
	$blank 	= ( $blank == true ? " target='_blank'" : "" );

	$link = " ".$link." ";
	$link = preg_replace( "`(((f|ht){1}tp(s)?://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)`", "<a href='\\1'".$blank.">".$text."</a>", $link);
	$link = preg_replace( "`([[:space:]()[{}>])(www.[-a-zA-Z0-9@:%_\+.~#?&//=]+)`", "\\1<a href='http://\\2'".$blank.">".$text."</a>", $link);
	$link = preg_replace( "#([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})#" , "<a href='mailto:\\1'>".$text."</a>", $link);
	$link = trim( $link );

	return $link;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LA TAILLE D'UN FICHIER */
public static function file_size( $data )
  {
	if( is_file( $data ) OR is_numeric( $data ) )
	  {
		$taille = is_numeric( $data ) ? $data : filesize( $data );

		for( $i=0 ; $i<8 && $taille >= 1024 ; $i++ )
		  {
			$taille = $taille / 1024;
		  }

		if( $i > 0 )
		  {
			return preg_replace( "/,00$/" , "" , (float)number_format( $taille , 2 , "." , "" ) )." ".substr( "KMGTPEZY" , $i - 1 , 1 )."o";
		  }
		else
		  {
			return $taille." o";
		  }
	  }
	else
	  {
		$taille = "Pas de fichier";
	  }


	return $taille;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LA TAILLE DE PLUSIEURS FICHIERS */
public static function files_size( $wat , $hr = true )
  {
  	$taille	= 0;
  	$files		= array();

  	if( is_array($wat) )
  	  {
  	  	$files = $wat;
  	  }
	else if( !empty($wat) )
	  {
  	  	$files = glob( $wat );
	  }

	foreach( $files as $filename )
	  {
  		if( file_exists($filename) AND is_file($filename ) )
  		  {
			$taille += filesize("$filename");
  		  }
	  }

	return ( $hr == true ) ? self::file_size( $taille ) : $taille;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LA TAILLE D'UN REPERTOIRE */
public static function directory_size( $path , $recursive = true )
  {
	$size = 0;
	if(!is_dir($path) || !is_readable($path))
	  {
		return 0;
	  }
	$fd = dir( $path );

	while($file = $fd->read())
	  {
		if(($file != ".") && ($file != ".."))
		  {
			if(@is_dir("$path$file/"))
			  {
				$size += $recursive ? self::directory_size("$path$file/") : 0;
			  }
			else
			  {
				$size += filesize("$path$file");
			  }
		  }
	  }

	$fd->close();
	return $size;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- LISTE LE CONTENU D'UN REPERTOIRE (RECURSIF) */
public static function directory_lister( $path , $show="all" , $recursive=false , $sort=NULL , $infos="date:size" , $regex="" )
  {
	if( substr( $path , -1 ) != "/" )
	  {
		$path .= "/";
	  }

	if( $sort != NULL )
	  {
		self::$directory_sort = ( preg_match( "#^(name|basename|size|date|access|perms|type|ext)$#i" , $sort ) ) ? $sort : "name";
	  }

	$infos	= explode( ":" , $infos );
	$return	= array();

	if( is_dir( $path ) )
	  {
		$i		= 0;
		$return	= array();
		$directory	= opendir( $path );

		while( $file = readdir( $directory ) )
		  {
			if( !empty($file) AND ($file != ".") AND ($file != "..") )
			  {
				$fichier			= $path.$file;
				$type				= filetype( $fichier );

				if( ( !isset($regex) OR empty($regex) )
				 OR ( isset($regex) AND !empty($regex) AND preg_match( $regex , $file ) ) )
				  {
					$values			= array();
					$values["type"]		= $type;
					$values["url"]		= $fichier.( ($type=="dir") ? "/" : "" );
					$values["name"]		= $file;
					$values["basename"]	= self::filename_only( $file );
				
					if( ( $recursive == true ) AND is_dir( $fichier ) )	/* Liste le sous répertoire */
					  {
						if( in_array( "date" , $infos ) )		{ $values["date"]		= filemtime( $fichier ); 	}
						if( in_array( "perms" , $infos ) )		{ $values["perms"]	= fileperms( $fichier ); 	}
						if( in_array( "access" , $infos ) )		{ $values["access"]	= fileatime( $fichier ); 	}
	
						$values["dir"]	= self::directory_lister( $fichier."/" , $show , true , $sort , implode( ":" , $infos ) );
						$return[]		= $values;
					  }
	
					else
					  {
						if(      ( ( $show == "directory" ) AND is_dir( $fichier ) )
							OR ( ( $show == "files" ) AND is_file( $fichier ) )
							OR ( ( $show == "all" ) )
						  )
						  {
							if( $type != "dir")				{ $values["ext"]		= strtolower( substr( strrchr( $fichier , "." ) , 1 ) ); 	}
							if( in_array( "date" , $infos ) )		{ $values["date"]		= filemtime( $fichier ); 	}
							if( in_array( "size" , $infos ) )		{ $values["size"]		= filesize( $fichier ); 	}
							if( in_array( "perms" , $infos ) )		{ $values["perms"]	= fileperms( $fichier ); 	}
							if( in_array( "access" , $infos ) )		{ $values["access"]	= fileatime( $fichier ); 	}
	
							$return[] = $values;
						  }
					  }
	
					$i++;
				  }
			  }
		  }

		usort( $return , "functions::directory_sort" );

		closedir( $directory );
	  }

	return $return;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- TRI LE LISTING DES REPERTOIRES */
public static function directory_sort( $a , $b )
  {
	$sort = self::$directory_sort;

	if ($a[ $sort ] == $b[ $sort ])
	  {
		return 0;
	  }

	return ($a[ $sort ] < $b[ $sort ]) ? -1 : 1;
	/* return ($a[ $sort ] > $b[ $sort ]) ? -1 : 1; */
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LE NUMERO DE LA SEMAINE */
public static function numero_semaine( $date ) /* 0000-00-00 */
  {
	$jour		= strftime( "%d" , strtotime($date) );
	$mois		= strftime( "%m" , strtotime($date) );
	$annee	= strftime( "%Y" , strtotime($date) );

	$J		= gregoriantojd( $mois , $jour , $annee );
	$D4		= ($J+31741-($J %7))% 146097 % 36524 % 1461;
	$L		= floor($D4/1460);
	$D1		= (($D4-$L) % 365)+$L;
	$wn		= floor($D1/7)+1;

	return ( substr('00'.$wn, -2) );
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RETURN STRING DATE */
public static function date_to_text( $day , $type_return=NULL )
  {
	$date_day		= strftime ("%d", strtotime($day));
	$date_jour		= strftime ("%A", strtotime($day));
	$date_mois		= strftime ("%m", strtotime($day));
	$date_annee		= strftime ("%Y", strtotime($day));

	switch ($date_jour)
	  {
		case "Monday" : 		$date_jour = "Lundi";		break;
		case "Tuesday" : 		$date_jour = "Mardi";		break;
		case "Wednesday" :	$date_jour = "Mercredi";	break;
		case "Thursday" :		$date_jour = "Jeudi";		break;
		case "Friday" :		$date_jour = "Vendredi";	break;
		case "Saturday" :		$date_jour = "Samedi";		break;
		case "Sunday" :		$date_jour = "Dimanche";	break;
	  }

	if( is_numeric($day) && ($type_return == "mois") )	{ $date_mois = $day; }
	switch ($date_mois)
	  {
		case  1 :			$date_mois = "Janvier";		break;
		case  2 :			$date_mois = "Février";		break;
		case  3 :			$date_mois = "Mars";		break;
		case  4 :			$date_mois = "Avril";		break;
		case  5 :			$date_mois = "Mai";		break;
		case  6 :			$date_mois = "Juin";		break;
		case  7 :			$date_mois = "Juillet";		break;
		case  8 :			$date_mois = "Août";		break;
		case  9 :			$date_mois = "Septembre";	break;
		case 10 :			$date_mois = "Octobre";		break;
		case 11 :			$date_mois = "Novembre";	break;
		case 12 :			$date_mois = "Décembre";	break;
	  }

	if( $type_return == "jour" )		{ return $date_jour; }
	else if( $type_return == "mois" )	{ return $date_mois; }
	else if( $type_return == "my" )	{ return $date_mois." ".$date_annee; }
	else if( $type_return == "dmy" )	{ return $date_day." ".$date_mois." ".$date_annee; }
	else						{ return $date_jour." ".$date_day." ".$date_mois." ".$date_annee; }

  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LA SAISON D'UNE DATE YYYY-MM-DD */
public static function saison( $date=NULL )
  {
	if( $date == NULL )
	  {
		$date = date( "Y-m-d" );
	  }

	$limits = array(
				"/12/21" => "Hiver",
				"/09/21" => "Automne",
				"/06/21" => "Été",
				"/03/21" => "Printemps",
				"/01/01" => "Hiver" 
			  );

	foreach( $limits AS $key => $value)
	  {
		$limit = date("Y").$key;

		if( strtotime( $date ) >= strtotime( $limit ) )
		  {
			return $value;
		  }
	  }
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE UN TABLEAU AVEC LES JOURS / HEURES / MINUTES / SECONDES D'UN TEMPS EN SECONDES , DURÉE */
public static function duree( $time1 , $time2 = null )
  {
	if( isset($time2) AND ($time2!==null) AND is_numeric($time2) AND ( $time2 > 0 ) )
	  {
		$time = $time2 - $time1;
	  }
	else
	  {
		$time = $time1;
	  }


	$return["jours"]		= intval($time/86400);
	$return["heures"]		= str_pad( (intval($time/3600 )%24) , 2 , "0", STR_PAD_LEFT);
	$return["minutes"]	= str_pad( (intval($time/60 )%60) , 2 , "0", STR_PAD_LEFT);
	$return["secondes"]	= str_pad( ($time%60) , 2 , "0", STR_PAD_LEFT);
	$return["duree"]		= ( ( $return["heures"] > 0 ) ? $return["heures"]."h " : "" )
					 .( ( $return["minutes"] > 0 ) ? $return["minutes"]."min " : "" )
					 ." ".$return["secondes"]."s";

	if( $return["jours"] > 0 )
	  {
		$return["duree"] = $return["jours"]." jour".( ( $return["jours"] > 1 ) ? "s" : "" )." ".$return["duree"];
	  }

	return $return;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- CALCUL LE NOMBRE DE JOURS ENTRE 2 DATES */
/**
* format de date : yyyy-mm-dd
*/
public static function nb_jours( $date1 , $date2=null )
  {
  	if( is_null($date2) )
  	  {
		$date2 = $date1;
		$date1 = date("Y-m-d");
  	  }
	$date1	= strtotime( $date1 );
	$date2	= strtotime( $date2 );
	$diff		= $date2 - $date1;

	return floor( $diff / (60 * 60 * 24) );
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- CALCUL L'AGE */
public static function age( $date_de_naissance )
  {
	list($annee, $mois, $jour) = explode("-", $date_de_naissance);
	$today['mois']	= date('n');
	$today['jour']	= date('j');
	$today['annee']	= date('Y');

	$annees = $today['annee'] - $annee;

	if ($today['mois'] <= $mois)
	  {
		if ($mois == $today['mois'])
		  {
			if ($jour > $today['jour'])
			$annees--;
		  }
		else
		  {
			$annees--;
		  }
	  }
	return $annees;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOYE LE TEMPS EN SECONDES */
public static function time_in_seconds( $time )
  {
	if( !is_numeric( $time ) )
	  {
		$time = time() - strtotime( "now - ".$time );
	  }

	return $time;
  }
  

/* --------------------------------------------------------------------------------------------------------------------------------------------- CRYPTAGE D'UNE CHAINE */
public static function crypt_string( $string )
  {
	return base64_encode( bin2hex( hash( "SHA256" , $string ) ) );
  }
  

/* --------------------------------------------------------------------------------------------------------------------------------------------- CAMOUFFLE UN CHAINE */
public static function hide_string( $string )
  {
	$return = "";
	for( $i=0 ; $i<strlen($string) ; $i++ )
	  {
		$return .= utf8_decode(  self::random( 4 , 4 ).$string[ $i ] );
	  }
	return $return;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- DECAMOUFFLE UN CHAINE CAMOUFFLEE */
public static function unhide_string( $string )
  {
	$return = "";
	for( $i=4 ; $i<strlen($string) ; $i = $i + 5 )
	  {
		$return .= $string[ $i ];
	  }
	return $return;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- CRYPTAGE D'UN MOT DE PASSE COMPATIBLE HTPASSWD */
public static function htpasswd( $password )
  {
	/* crypt( trim( $password  ) , CRYPT_STD_DES ); */
	return crypt( $password , base64_encode( $password ) );
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- CODE UNE CHAINE EN MD5_KOD */
public static function md5_kod( $string , $md5=false )
  {
	$time		= substr( md5(time()) , 0 , 8 );
	$string	= ( $md5===true ) ? md5( $string ) : $string;
	$string	= explode( "-" , wordwrap( $string , 8 , "-" , true ) ); 
	$string	= $string[2]."-".$time."-".$string[1]."-".$string[3]."-".$string[0];

	return $string;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- DECODE UNE CHAINE MD5_KOD */
public static function md5_dekod( $string )
  {
	$string = explode( "-" , wordwrap( $string , 8 , "-" , true ) ); 
	$string = $string[4].$string[2].$string[0].$string[3];

	return $string;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- CODE UNE CHAINE EN MD5_KOD */
public static function sha1_kod( $string , $sha1=false )
  {
	$time		= substr( sha1(time()) , 0 , 8 );
	$string	= ( $sha1===true ) ? sha1( $string ) : $string;
	$string	= str_rot13( $string );
	$string	= explode( "-" , wordwrap( $string , 8 , "-" , true ) ); 
	$string	= $string[4]."-".$time."-".$string[2]."-".$string[1]."-".$string[3]."-".$string[0];

	return $string;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- DECODE UNE CHAINE MD5_KOD */
public static function sha1_dekod( $string )
  {
	$string	= explode( "-" , wordwrap( $string , 8 , "-" , true ) ); 
	$string	= $string[5].$string[3].$string[2].$string[4].$string[0];
	$string	= str_rot13( $string );

	return $string;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- CRYPTAGE DE CHAINE SIMPLE */
public static function encode( $value )
  {
	$return = "";
	for( $i=0 ; $i<strlen($value) ; $i++ )
	  {
		$lettre  = ord( $value[$i] );
		$lettre  = str_pad( $lettre , 3 , "0" , STR_PAD_LEFT);
		$return .= $lettre;
	  }
	return $return;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- DECRYPTAGE DE CHAINE SIMPLE */
public static function decode( $value )
  {
	$return	= "";
	$value 	= wordwrap( $value, 3 , "|" , true );
	$value 	= explode( "|" , $value );

	for( $i=0 ; $i<count($value) ; $i++ )
	  {
		$return .= chr( $value[$i] );
	  }

	return $return;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- DIMENSIONS D'UNE POLICE */
public static function font_dimensions($dim , $police , $taille , $chars="none")
   {

	if($chars=="none")
	   {
		$charstest = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
	   }
	else
	   {
		$charstest = $chars;
	   }

	if($dim == "largeur")
	   {
		if($chars=="none")
		   {
			$char_map = "";
			$y = 0;
			for($i=0 ; $i<strlen($charstest) ; $i++)
			   {
				$char_map .= $charstest[$i]."\n";
			   }
			$dimensions  = @imagettfbbox ( $taille, 0, $police, $char_map);
		   }
		else
		   {
			$dimensions  = @imagettfbbox ( $taille, 0, $police, $charstest);
		   }

		return (abs($dimensions[2] + $dimensions[0]))+7;
	   }

	else if($dim == "hauteur")
	   {
		$dimensions  = @imagettfbbox ( $taille, 0, $police, $charstest);
		return $dimensions[1] - $dimensions[7];
	   }

	else if($dim == "y_chars")
	   {
		$dimensions  = @imagettfbbox ( $taille, 0, $police, $charstest);
		return abs($dimensions[5]);
	   }

	else
	   {
		return 0;
	   }

   }



/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LE MIME-TYPE D'UNE EXTENSION */
public static function mime_type( $data )
  {
	$mime = "application/octet-stream";

	if( is_file( $data ) )
	  {
		if( function_exists("finfo_file") )
		  {
			$finfo	= finfo_open(FILEINFO_MIME_TYPE);
			$mime		= finfo_file( $finfo , $data );
			finfo_close( $finfo );
		  }
		else if( function_exists("mime_content_type") )
		  {
			$mime = mime_content_type( $data );
		  }
		else if( !stristr(ini_get("disable_functions"), "shell_exec") )
		  {
			/* http://stackoverflow.com/a/134930/1593459 */
			$data = escapeshellarg( $data );
			$mime = shell_exec("file -bi " . $data );
		  }
	  }
	else if( is_file( "types_mime.php" ) )
	  {
		require_once( "types_mime.php" );

		$ext = str_replace( "." , "" , $data );

		if( isset($content_types[ $ext ]) AND !empty($content_types[ $ext ]) )
		  {
			$mime = $content_types[ $ext ];
		  }
	  }

	return $mime;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE L'EXTENSION DU FICHIER */
public static function ext( $filename )
   {
	return strtolower( substr( strrchr( $filename , "." ) , 1 ) );
   }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LE NOM DU FICHIER SANS L'EXTENSION */
public static function filename_only( $filename )
   {
	return preg_replace( "/\.".self::ext( $filename )."$/" , "" , $filename );
   }


/* --------------------------------------------------------------------------------------------------------------------------------------------- CONVERSION DES NOMS DE FICHIERS */
public static function filename_convertor( $txt )
   {
	$ext	= strtolower( substr( strrchr( $txt , "." ) , 1 ) );

	$txt 	= substr( $txt , 0 ,  - strlen( $ext ) - 1 );
	$txt 	= self::no_accents( $txt );
	$txt	= preg_replace( "/[^\da-z\.\-\_]/i" , "" , $txt );
	$txt 	= str_replace("--" , "-" , $txt);
	$txt 	= str_replace(" " , "_" , $txt);
	$txt 	= str_replace("__" , "_" , $txt);
	$txt 	= str_replace("__" , "_" , $txt);	
	$txt 	= $txt.".".$ext;

	return $txt;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- GESTION DU CACHE - URL */
public static function cache_id( $full=true )
  {
	return sha1( $_SERVER["DOCUMENT_ROOT"].$_SERVER["REQUEST_URI"].( $full == true ? "|G|".serialize( $_GET )."|P|".serialize( $_POST ) : "" ) );
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- GESTION DU CACHE - START */
public static function cache_start( $options = array() )
  {
	if( !isset($_GET["nocache"]) )
	  {
		/* --------------------------------------------- */

		$default = array( 
			"file"	=> false,
			"cache_id" 	=> null,
			"ext" 	=> "",
			"mime" 	=> null,
			"force" 	=> false,
			"filename" 	=> null,
			"expire" 	=> null,
			"full" 	=> true
		);

		$options		= is_array($options) ? array_merge( $default , $options ) : $default;
		$options["mime"]	= ( $options["mime"] != null ) ? $options["mime"] : self::mime_type( $options["ext"] );

		/* --------------------------------------------- */

		$dossier_cache		= DOC_ROOT.self::$repertoire_cache;
		$secondes_cache		= ( ( $options["expire"] == null ) ? 31536000 : $options["expire"] );
		$cache_id			= ( $options["cache_id"] != null ) ? $options["cache_id"] : self::cache_id( $options["full"] );
		$fichier_cache		= $dossier_cache.$cache_id.".cache";
		$fichier_cache_existe 	= ( @file_exists($fichier_cache) ) ? @filemtime($fichier_cache) : 0;
		$filename			= ( $options["filename"] != null ) ? $options["filename"] : $cache_id;

		/* --------------------------------------------- */

		if( ( $options["force"] == false ) AND ( $fichier_cache_existe > ( time() - $secondes_cache ) ) )
		  {
			$file_content = file_get_contents( $fichier_cache );

			if( $options["file"] === true )
			  {
				header( "Content-type: ".$type."" );
				header( "Content-Disposition: inline; filename=".$filename );
				header( "Expires: " . date("r", time()+$secondes_cache) );

				if( self::is_ie() )
			  	  {
					header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
					header( "Pragma: public" );
			  	  }
				else
			  	  {
					header( "Pragma: no-cache" );
			  	  }

				header( "Content-Length: ".strlen( $file_content ) );	
			  }
			
			echo $file_content;
			exit;
		  }
		else
		  {
			ob_start();
			$now = date("D, d M Y H:i:s")." +0200";
			header( "Expires: ".$now );
			header( "Cache-Control: max-age=".$secondes_cache.", must-revalidate" );
		  }
	  }
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- GESTION DU CACHE - END */
public static function cache_stop( $options = array() )
  {
	if( !isset($_GET["nocache"]) )
	  {
		/* --------------------------------------------- */

		$default = array( 
			"cache_id" 	=> null,
			"full" 	=> true
		);

		$options = is_array($options) ? array_merge( $default , $options ) : $default;

		/* --------------------------------------------- */

		$dossier_cache	= DOC_ROOT.self::$repertoire_cache;
		$cache_id		= ( $options["cache_id"] != null ) ? $options["cache_id"] : self::cache_id( $options["full"] );
		$fichier_cache	= $dossier_cache.$cache_id.".cache";

		/* --------------------------------------------- */

		$pointeur = @fopen( $fichier_cache , "w" );
		fwrite($pointeur, ob_get_contents());
		fclose($pointeur);
		ob_end_flush();

		/* --------------------------------------------- */
	  }
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- PASSE UN COUP DE BALAIS DANS LE CACHE */
public static function clean_cache( $time=0 )
  {
	$chemin		= $_SERVER["DOCUMENT_ROOT"].self::root().self::$repertoire_cache;
	$temps_limit 	= self::time_in_seconds( $time );

	if( is_dir($chemin) )
	  {
		$fichiers = scandir($chemin);

		foreach( $fichiers as $fichier )
		  {
			if ($fichier == "." || $fichier == "..")
			  {
				continue;
			  }

			if( fileatime( $chemin.$fichier ) < time() - $temps_limit )
			  {
				if( unlink( $chemin.$fichier ) )
				  {
					return true;
				  }
				else
				  {
					return false;
				  }
			  }
		  }

		return false;
	  }
	else
	  {
		return false;
	  }
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- SUPPRIME UN FICHIER DU CACHE */
public static function cache_remove( $url )
  {
	$url	= md5( $_SERVER["HTTP_HOST"].$url );
	$file	= $_SERVER["DOCUMENT_ROOT"].self::root().self::$repertoire_cache."/".$url.".cache";

		echo "<br />".$file;

	if( is_file( $file ) )
	  {
		if( unlink( $file ) )
		  {
			return true;
		  }
		else
		  {
			return false;
		  }
	  }
	else
	  {
		return false;
	  }
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LE TYPE D'ERREUR HTTP EN FONCTION DU NUMERO */
public static function http_code( $num , $lang="fr" )
  {
	if( $lang == "fr" )
	  {
		switch( $num )
		  {
			case "100" :	$erreur = "OK pour continuer"; break;
			case "101" :	$erreur = "Le serveur a changé de protocoles"; break;
			case "200" :	$erreur = "Requête effectuée avec succès"; break;
			case "201" :	$erreur = "Document créé (raison : nouvelle URI)"; break;
			case "202" :	$erreur = "Requête achevée de manière asynchrone (TBS)"; break;
			case "203" :	$erreur = "Requête achevée de manière incomplète"; break;
			case "204" :	$erreur = "Aucune information à renvoyer"; break;
			case "205" :	$erreur = "Requête terminée mais formulaire vide"; break;
			case "206" :	$erreur = "Requête GET incomplète"; break;
			case "300" :	$erreur = "Le serveur ne peut pas déterminer le code de retour"; break;
			case "301" :	$erreur = "Document déplacé de façon permanente"; break;
			case "302" :	$erreur = "Document déplacé de façon temporaire"; break;
			case "303" :	$erreur = "Redirection avec nouvelle méthode d'accès"; break;
			case "304" :	$erreur = "Le champ 'if-modified-since' n'était pas modifié"; break;
			case "305" :	$erreur = "Redirection vers un proxy spécifié par l'entête"; break;
			case "307" :	$erreur = "HTTP/1.1"; break;
			case "400" :	$erreur = "Erreur de syntaxe dans l'adresse du document"; break;
			case "401" :	$erreur = "Vous n'avez pas d'autorisation d'accèder au document"; break;
			case "402" :	$erreur = "L'accès au document est soumis au paiement"; break;
			case "403" :	$erreur = "Vous n'avez pas l'autorisation d'accèder au répertoire"; break;
			case "404" :	$erreur = "La page demandée n'existe pas"; break;
			case "405" :	$erreur = "Méthode de requête du formulaire non autorisée"; break;
			case "406" :	$erreur = "Requête non acceptée par le serveur"; break;
			case "407" :	$erreur = "Autorisation du proxy nécessaire"; break;
			case "408" :	$erreur = "Temps d'accès à la page demandée expiré"; break;
			case "409" :	$erreur = "L'utilisateur doit soumettre à nouveau avec plus d'infos"; break;
			case "410" :	$erreur = "Cette ressource n'est plus disponible"; break;
			case "411" :	$erreur = "Le serveur a refusé la requête car elle n'a pas de longueur"; break;
			case "412" :	$erreur = "La précondition donnée dans la requête a échoué"; break;
			case "413" :	$erreur = "L'entité de la requête était trop grande"; break;
			case "414" :	$erreur = "L'URI de la requête était trop longue"; break;
			case "415" :	$erreur = "Type de média non géré"; break;
			case "500" :	$erreur = "Erreur de configuration interne du serveur"; break;
			case "501" :	$erreur = "Requête faite au serveur non supprimée"; break;
			case "502" :	$erreur = "Mauvaise passerelle d'accès"; break;
			case "503" :	$erreur = "Service non disponible"; break;
			case "504" :	$erreur = "Temps d'accès à la passerelle expiré"; break;
			case "505" :	$erreur = "Version HTTP non gérée"; break;
			default :		$erreur = "..."; break;
		  }

	  }
	else
	  {
		switch( $num )
		  {
			case "200" :	$erreur = "request completed"; break;
			case "201" :	$erreur = "object created, reason = new URI"; break;
			case "202" :	$erreur = "async completion (TBS)"; break;
			case "203" :	$erreur = "partial completion"; break;
			case "204" :	$erreur = "no info to return"; break;
			case "205" :	$erreur = "request completed, but clear form"; break;
			case "206" :	$erreur = "partial GET furfilled"; break;
			case "300" :	$erreur = "server couldn't decide what to return"; break;
			case "301" :	$erreur = "object permanently moved"; break;
			case "302" :	$erreur = "object temporarily moved"; break;
			case "303" :	$erreur = "redirection w/ new access method"; break;
			case "304" :	$erreur = "if-modified-since was not modified"; break;
			case "305" :	$erreur = "redirection to proxy, location header specifies proxy to use"; break;
			case "307" :	$erreur = "HTTP/1.1: keep same verb"; break;
			case "400" :	$erreur = "invalid syntax"; break;
			case "401" :	$erreur = "access denied"; break;
			case "402" :	$erreur = "payment required"; break;
			case "403" :	$erreur = "request forbidden"; break;
			case "404" :	$erreur = "object not found"; break;
			case "405" :	$erreur = "method is not allowed"; break;
			case "406" :	$erreur = "no response acceptable to client found"; break;
			case "407" :	$erreur = "proxy authentication required"; break;
			case "408" :	$erreur = "server timed out waiting for request"; break;
			case "409" :	$erreur = "user should resubmit with more info"; break;
			case "410" :	$erreur = "the resource is no longer available"; break;
			case "411" :	$erreur = "the server refused to accept request w/o a length"; break;
			case "412" :	$erreur = "precondition given in request failed"; break;
			case "413" :	$erreur = "request entity was too large"; break;
			case "414" :	$erreur = "request URI too long"; break;
			case "415" :	$erreur = "unsupported media type"; break;
			case "500" :	$erreur = "internal server error"; break;
			case "501" :	$erreur = "required not supported"; break;
			case "502" :	$erreur = "error response received from gateway"; break;
			case "503" :	$erreur = "temporarily overloaded"; break;
			case "504" :	$erreur = "timed out waiting for gateway"; break;
			case "505" :	$erreur = "HTTP version not supported"; break;
			default :		$erreur = "..."; break;

		  }
	  }

	return $erreur;

  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LES ENTETES D'UNE URL */
public static function get_headers( $url , $code=false )
  {
	$curl = curl_init();

	curl_setopt( $curl, CURLOPT_URL,            $url );
	curl_setopt( $curl, CURLOPT_HEADER,         true );
	curl_setopt( $curl, CURLOPT_NOBODY,         true );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $curl, CURLOPT_TIMEOUT,        20 );
	$headers = curl_exec( $curl );
	$headers = explode( "\n" , $headers );

	if( $code )
	  {
		preg_match( "#([0-9]{3})#" , $headers[0] , $code );
		return $code[0];
	  }
	else
	  {
		return $headers;
	  }
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- EXECUTE UNE REQUETE POST */
public static function curl( $url , $post=NULL , $ua=NULL , $debug=false )
  {
	$curl			= curl_init();
	$post_string	= "";

	curl_setopt( $curl, CURLOPT_URL , $url );
	curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1);

	if( $debug === true )
	  {
		curl_setopt( $curl, CURLOPT_VERBOSE, true);
		curl_setopt( $curl, CURLOPT_STDERR,  "php://output" );
	  }

	if( is_array( $post ) )
	  {
		foreach( $post AS $key => $value )
		  {
			$post_string .= $key."=".urlencode( $value )."&";
		  }
		rtrim( $post_string , "&" );

		curl_setopt( $curl , CURLOPT_POST , count( $post ) );
		curl_setopt( $curl , CURLOPT_POSTFIELDS , $post_string );
	  }

	if( $ua !== NULL )
	  {
		if( ( $ua === true ) AND isset($_SERVER["HTTP_USER_AGENT"]) AND !empty($_SERVER["HTTP_USER_AGENT"]) )
		  {
			curl_setopt( $curl, CURLOPT_USERAGENT, self::ua() ); 
		  }
		else
		  {
			curl_setopt( $curl, CURLOPT_USERAGENT, $ua ); 
		  }
	  }

	$result = curl_exec( $curl );

	if( ( $debug === true ) AND !$result )
	  {
		echo curl_error( $curl );
	  }
	curl_close( $curl );

	return $result;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE UN CHAMPS ANTI ROBOTS POUR FORMULAIRE */
public static function set_antirobots( $id=true )
  {
	return "<input type='text' ".( $id ===true ? "id='antirobots' " : "" )."name='antirobots' class='antirobots' />";
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- VERIFIE SI LE CHAMPS ANTI ROBOTS EST REMPLIT */
public static function verif_antirobots()
  {
	if( ( isset($_POST["antirobots"]) AND ( !empty($_POST["antirobots"]) OR ($_POST["antirobots"]!="") OR strlen($_POST["antirobots"])>0) )
	    OR ( isset($_GET["antirobots"]) AND ( !empty($_GET["antirobots"]) OR ($_GET["antirobots"]!="") OR strlen($_GET["antirobots"])>0) )   )
	  {
		self::log( "antirobots" , "Tentative d'accès à : ".$_SERVER["REQUEST_URI"]." [_POST] ".json_encode( $_POST )." [_GET] ".json_encode( $_GET ) );
		return true;
	  }
	else
	  {
		return false;
	  }
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- COMPRESSE UN TEXTE EN SUPPRIMANT LES ESPACES ET COMMENTAIRES */
public static function text_compressor( $data )
  {     
	$data = preg_replace( "!/\*[^*]*\*+([^/][^*]*\*+)*/!" , "" , $data);		/* Remove comments  */	
	$data = str_replace( array( "\r\n" , "\r" , "\n" ) , "" , $data );		/* Remove newlines */
	$data = str_replace( array( "\t" ) , " " , $data );					/* Remove tabs */
	$data = preg_replace( "/\\s{2,}/" , " ", $data );					/* Remove spaces */


	/* Supprime les espaces en trop */
	$data = str_replace('{ ', '{', $data);
	$data = str_replace(' }', '}', $data);
	$data = str_replace('; ', ';', $data);
	$data = str_replace(', ', ',', $data);
	$data = str_replace(' {', '{', $data);
	$data = str_replace('} ', '}', $data);
	$data = str_replace(': ', ':', $data);
	$data = str_replace(' ,', ',', $data);
	$data = str_replace(' ;', ';', $data);

	return $data;  
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- SUPPRIME LES LIGNES VIDES D'UNE CHAINE */
public static function remove_blank_lines( $string )
  { 
	return preg_replace( "/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/" , "\n" , $string );
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- SUPPRIME LES LIGNES VIDES D'UNE CHAINE */
public static function remove_tabs( $data )
  { 
	$data = trim( preg_replace( "/\t+/" , " " , $data ) );
	$data = preg_replace( "/[ \t]/" , " " , $data );
	return $data;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- SUPPRIME LES RETOURS A LA LIGNE */
public static function inline_string( $string )
  { 
	return trim( preg_replace( "/\s\s+/" , " ", $string ) ); /* "/\s+/" */
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- ENCODE UN IMAGE EN BASE64 (TXT) */
public static function base64_image_encode( $file , $wordwrap=NULL )
  {
	if( is_file( $file ) )
	  {
		$data	= fread( fopen( $file , "r" ) , filesize( $file ) );
		$data	= base64_encode( $data );
		$ext	= self::ext( $file );
		if( preg_match( "#^(jpg|jpeg|bmp|gif|png)$#" , $ext ) )
		  {
			$data = "data:image/".$ext.";base64,".$data;
		  }

		if( ( $wordwrap != NULL ) AND is_numeric($wordwrap) )
		  {
			$data	= wordwrap( $data, $wordwrap, "\n", true );
		  }

		return $data;
	  }

	return false;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- DECODE UNE CHAINE BASE64 VERSION HTML */
public static function base64_decode( $data )
  {
	$return	= array( "mime" => "" , "base64" => "" , "data" => "" );
	$datas	= explode( ";" , $data );

	if( isset($data[0]) AND isset($data[1]) )
	  {
		$return[ "mime" ]		= str_replace( "data:" , "" , $datas[0] );
		$return[ "base64" ]	= str_replace( "base64," , "" , $datas[1] );
		$return[ "data" ]		= base64_decode( $return[ "base64" ] );
	  }
	else
	  {
		$return[ "base64" ]	= $data;
		$return[ "data" ]		= base64_decode( $data );
	  }

	return $return;
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOI L'INVERSE D'UNE COULEUR EN HEXA */
public static function couleur_opposee( $couleur )
  {
	$r = dechex( 255 - hexdec( substr( $couleur , 0 , 2 ) ) );
	$r = ( strlen($r) > 1 ) ? $r : "0".$r;
	$g = dechex( 255 - hexdec( substr( $couleur , 2 , 2 ) ) );
	$g = ( strlen($g) > 1 ) ? $g : "0".$g;
	$b = dechex( 255 - hexdec( substr( $couleur , 4 , 2 ) ) );
	$b = ( strlen($b) > 1 ) ? $b : "0".$b;

	return $r.$g.$b;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOI LA CORRESPONDANCE D'UNE COULEUR HEXA RGB */
public static function hexa_to_rgb( $couleur , $separator=null )
  {
	$couleur = trim( str_replace( "#" , "" , $couleur ) );
	$r = hexdec( substr( $couleur , 0 , 2 ) );
	$g = hexdec( substr( $couleur , 2 , 2 ) );
	$b = hexdec( substr( $couleur , 4 , 2 ) );

	if( $separator !== null )
	  {
		return $r.$separator.$g.$separator.$b;
	  }
	else
	  {
		return array( $r , $g , $b );
	  }
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOI LA CORRESPONDANCE D'UNE COULEUR RGB EN HEX */
public static function rgb_to_hexa( $couleur )
  {
  	$couleur = array_values( $couleur );
	$r = dechex( $couleur[0] );
	$g = dechex( $couleur[1] );
	$b = dechex( $couleur[2] );

	return $r.$g.$b;
  }
/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOI L'INTENSITÉ DE LA COULEUR ( sombre 0 <--> claire 255 ) */
public static function intensite_couleur( $hex )
  {
	$hex = str_replace( "#" , "" , $hex );
	
	$c_r = hexdec(substr($hex, 0, 2));
	$c_g = hexdec(substr($hex, 2, 2));
	$c_b = hexdec(substr($hex, 4, 2));

	return ( ( $c_r * 299 ) + ( $c_g * 587 ) + ( $c_b * 114 ) ) / 1000;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOI L'INTENSITÉ DE LA COULEUR ( sombre 0 <--> claire 255 ) */
public static function is_claire( $hex , $seuil=150 )
  {
	$intensite = self::intensite_couleur( $hex );

	if( $intensite > $seuil )
	  {
	  	return true;
	  }
	else
	  {
	  	return false;
	  }
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- AJOUTE UN EVENEMNT DANS LES LOGS */
public static function log( $type , $event )
  {
	$date			= date("Y-m-d H:i:s");
	$user_agent		= self::ua();
	$ip			= self::ip();
	$host 		= self::host( $ip );

	if( isset( self::$log[$type] ) )	{ self::$log[$type] .= $date."\t".$ip."\t".$event."\t".$host."\t".$user_agent."\n"; }
	else						{ self::$log[$type]  = $date."\t".$ip."\t".$event."\t".$host."\t".$user_agent."\n"; }
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- MET A JOUR LES FICHIERS DE LOGS */
public static function write_logs()
  {
	foreach( self::$log as $key => $value )
	  {
		if( !empty( $value ) )
		  {
			$fichier_log	= $_SERVER["DOCUMENT_ROOT"].self::root()."/lib/logs/".$key.".log";
			$file			= fopen( $fichier_log , "a" );
			fwrite( $file , $value );
			fclose( $file );
		  }
	  }
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- AJOUTE UN EVENEMNT DANS LES LOGS TEMPORAIRES */
public static function log2( $event , $details=false , $logfile=null )
  {
	$logfile = ( $logfile !== null ) ? $logfile : ( $_SERVER["DOCUMENT_ROOT"].self::root()."/lib/logs/log2.log" );

	$data	 = "";
	$data	.= date("Y-m-d H:i:s")." .".self::microtime();
	$data	.= " | ".$event;

	if( $details === true )
	  {
		$ip = self::ip();
		$data .= "\t".self::ua();
		$data .= "\t".$ip;
		$data .= "\t".( self::is_private_ip( $ip ) ? $ip : @gethostbyaddr($ip) );
	  }

	$data	.= "\n";

	$file = fopen( $logfile , "a" );
	fwrite( $file , $data );
	fclose( $file );
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- CREE UN FICHIER DE LOG DES VARIABLES GET, POST et FILES */
public static function tracker( $file=null )
  {
	$data	 = "---------------------------------------------------------------------------------------------------\n";
	$data	.= date("Y-m-d H:i:s")." .".self::microtime()."\n";
	$data	.= "---------------------------------------------------------------------------------------------------\n";
	$data	.= "GET\n---\n".substr( print_r( $_GET , true ) , 7 , -2 );
	$data	.= "\n\n";
	$data	.= "POST\n----\n".substr( print_r( $_POST , true ) , 7 , -2 );
	$data	.= "\n\n";
	$data	.= "FILES\n----\n".substr( print_r( $_FILES , true ) , 7 , -2 );
	$data	.= "\n\n\n";

	$file = fopen( $file , "a" );
	fwrite( $file , $data );
	fclose( $file );
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LE NOM DU REPERTOIRE EN COURS */
public static function current_path()
  {
    $path = explode( "/", dirname($_SERVER["PHP_SELF"]) );
    return end($path);
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- REDIRIGE LA PAGE ACTUELLE DE HTTP EN HTTPS */
public static function https( $set=1 , $return=false )
  {
	$url		= ( ($_SERVER["SERVER_PORT"]=="443") ? "https" : "http" )."://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
	/*
	$new_url	= $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
	*/
	$base		= parse_url( $url );
	$chemin	= $base["host"].$base["path"];
	if( ($set==1 ) AND ($base["scheme"] != "https") )
	  {
	  	$new_url = "https://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
	  }

	if( ($set==0 ) AND ($base["scheme"] != "http") )
	  {
	  	$new_url = "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
	  }
	
	if( isset($new_url) AND $return )
	  {
	  	return $new_url;
	  }
	else if( isset($new_url) AND self::url_exists( $new_url ) )
  	  {
		header( "Location: ".$new_url );
		exit;
  	  }
	else
  	  {
		return false;
  	  }

  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- VÉRIFIE SI LE FICHIER ET COMPRESSÉ */
public static function is_minified( $file , $suffix="min" )
  {
  	$ext 		= self::ext($file);
  	$mini_file 	= preg_replace( "#.".$ext."$#" , ".".$suffix.".".$ext , $file );
  	$return 	= array( "minified" => false, "file" => $mini_file );

  	if( is_file( $file ) AND is_file( $mini_file ) AND ( filemtime( $mini_file ) > filemtime( $file ) ) )
	  {
	  	$return["minified"] = true;
	  }

  	return $return;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- COMPRESSE LE CODE SOURCE (FICHIER OU CODE) */
public static function minify( $options = array() )
  {

	/* ----------------------------------------------------------------------------------- */

	$compress 	= false;
	$filetime 	= time();


	/* ----------------------------------------------------------------------------------- */

	$default = array( 
		"return"			=> "filename",
		"content"			=> null,
		"root"	 		=> "",
		"file" 			=> "",
		"mini_file" 		=> "",
		"suffix"			=> "min",
		"filetime"			=> false,
		"comments"			=> true,
		"blank_lines"		=> true,
		"compression"		=> true,
		"spaces"			=> true,
		"extras_spaces"		=> true,
		"infos"			=> ""
	);

	$options = is_array($options) ? array_merge( $default , $options ) : $default;



	/* ----------------------------------------------------------------------------------- */

	if( !empty($options["file"]) )
	  {
		$file = $options["file"];

		if( $options["return"] == "filename" )
		  {
		  	if( !empty($options["mini_file"]) )
		  	  {
		  	  	$mini_file = $options["mini_file"];
		  	  }
		  	else
		  	  {
			  	$ext 		= self::ext($file);
			  	$mini_file 	= preg_replace( "#.".$ext."$#" , ".".$options["suffix"].".".$ext , $file );
		  	  }
		  }


	  	if( ( $options["return"] == "data" ) OR ( !is_file( $options["root"].$mini_file ) OR ( filemtime( $options["root"].$mini_file ) < filemtime( $options["root"].$file ) ) ) )
		  {
		  	$compress = true;
		  	$options["content"] = fread( fopen($options["root"].$file, "r") , filesize($options["root"].$file) );
		  }
		else if( is_file( $options["root"].$mini_file ) )
		  {
			$filetime = filemtime( $options["root"].$mini_file );
		  }

	  }

	else if( !empty($options["content"]) )
	  {
	  	$options["return"] = "data";
		$compress = true;
	  }


	
	/* ----------------------------------------------------------------------------------- */

	if( $compress )
	  {
		/* Suppression des commentaires */
		if( $options["comments"] == true)		{ $options["content"] = preg_replace( "!/\*[^*]*\*+([^/][^*]*\*+)*/!" , "" , $options["content"]); }

		/* Supprime les lignes vides */
		if( $options["blank_lines"] == true)	{ $options["content"] = preg_replace( "/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/" , "\n" , $options["content"] ); }

		/* Compression du code */
		if( $options["compression"] == true)	{ $options["content"] = str_replace( array( "\r\n" , "\r" , "\n" ) , "" , $options["content"] ); }

		/* Supprime les espaces en trop (2+) */
		if( $options["spaces"] == true)		{
									  $options["content"] = str_replace( array( "\t" ) , " " , $options["content"] );
									  $options["content"] = preg_replace( "/\\s{2,}/" , " ", $options["content"]);
									}

		/* Supprime les espaces avant et après les " : { } ( ) " ..." */
		if( $options["extras_spaces"] == true)	{
									  $options["content"] = preg_replace( "/ ?(:|;|,|=|{|}|\[|\]) ?/i" , "$1" , $options["content"] );
									  $options["content"] = preg_replace( "/\( ?/i" , "(" , $options["content"] );
									  $options["content"] = preg_replace( "/ ?\)/i" , ")" , $options["content"] );
									  $options["content"] = preg_replace( "/\n}/i" , "}" , $options["content"] );
									  $options["content"] = preg_replace( "/{\n/i" , "{" , $options["content"] );
									}


		/* TRIM */
		$options["content"] = trim( $options["content"] );


		/* Ajout des commentaires en haut du fichier compressé */
		if( !empty($options["infos"]) )		{ $options["content"] = $options["infos"]."\n".$options["content"]; }



		/* Écriture du fichier */

		if( isset($mini_file) )
		  {
		  	file_put_contents( $options["root"].$mini_file , $options["content"] );
		  }

	  }


	/* ----------------------------------------------------------------------------------- */

	if( isset($mini_file) AND ( $options["return"] != "data" ) )
	  {
	  	return $mini_file.( $options["filetime"] == true ? "?t=".$filetime : "" );
	  }
	else
	  {
		return $options["content"];
	  }


  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- AFFICHE LE CODE SOURCE LE CODE SOURCE */
public static function show_source_code( $data , $ligne=null )
  {
	if( is_file( $data ) )
	  {
		$data = fread( fopen($data, "r") , filesize($data) );
	  }

	/*
	$data = str_replace( "<" , "&lt;" , $data );
	$data = str_replace( ">" , "&gt;" , $data ); 
	*/

	$content		= "";
	$data			= preg_replace( "(\r\n|\n\r|\r)" , "\n" , $data );
	$data			= explode( "\n" , $data );
	$nb_lignes		= count( $data );
	$nb_chars		= strlen( $nb_lignes );
	$i			= 0;
	foreach( $data as $line )
	  {
		$i++;
		$content .= "<div id='ln-".$i."' class='_line'><xmp>".( str_pad( $i , $nb_chars , " " , STR_PAD_LEFT ) )." | ".$line."</xmp></div>";
	  }

	$content = "
		<style type='text/css'>
			._line:hover	
				{
					color : 			#0000FF;
					background-color : 	#fcf4da;
				}
			._line:target
				{
					background-color : 	#C6F37D;
				}
			xmp
				{
					margin : 			0;
				}
		</style>"
		.$content
		.( ( ($ligne!=null) AND is_numeric($ligne) ) ? "<script type='text/javascript'>document.location.href='".$_SERVER["REQUEST_URI"]."#ln-".$ligne."';</script>" : "" );

	return $content;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- CALCULS DE PRIX ET TVA */
public static function taxe( $montant , $wat="tva" , $taux=20 )
  {
	$return	= 0;
  	$tva0 	= $taux / 100;
  	$tva1 	= 1 + $tva0;
  
	if( $wat == "ttc" )
	  {
	  	$return = $montant * $tva1;
	  }
	else if( $wat == "ht" )
	  {
	  	$return = $montant / $tva1;
	  }
	else
	  {
	  	$return = ( $montant / $tva1 ) * $tva0;
	  }

	return self::format_prix( $return );

  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE UN PRIX SOUS LA FORME 100.99 ou 10099 pour 100.99 € */
public static function format_prix( $prix , $bank=false , $no_zeros=false )
  {
  	$prix = trim( $prix );
  	
  	if( is_numeric($prix) )
  	  {
	  	$prix = number_format( $prix , 2 , "." , ( $bank === true ? " " : "" ) );

		if( ( substr( $prix , -3 ) == ".00" ) AND ( $no_zeros == true ) )
		  {
		  	$prix = substr( $prix , 0 , -3 );
		  }
	  	return $prix;
  	  }
	else
	  {
	  	return $prix;
	  }
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- VERIFIE SI UNE ADRESSE IP CORRECTE */
public static function is_valid_ip( $ip  )
  {
	if( preg_match( "#^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$#" , $ip ) )
	  {
		return true;
	  }
	else
	  {
		return false;
	  }
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- VERIFIE SI UNE ADRESSE IP EST PRIVEE OU NON */
public static function is_private_ip( $ip=NULL  )
  {
	/*
		Classe A : 10.0.0.0	->	10.255.255.255
		Classe B : 172.16.0.0	->	172.31.255.255
		Classe C : 192.168.0.0	->	192.168.255.255

		IPv6 : LOCALHOST = ::1
	*/

	if( $ip === NULL )
	  {
		$ip = self::ip();
	  }
	if(      preg_match( "#^10\.[0-255]\.[0-255]\.[0-255]#" , $ip ) 
		OR preg_match( "#^172\.[0-16]\.[0-255]\.[0-255]#" , $ip )
		OR preg_match( "#^192\.168\.[0-255]\.[0-255]#" , $ip )
		OR preg_match( "#^127.0.0.1$#" , $ip )
		OR preg_match( "#^::1#" , $ip )
		OR preg_match( "#^fe80::1#" , $ip )
	  )
	  {
		return true;
	  }
	else
	  {
		return false;
	  }

  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- IDENTIFIE UN ROBOT */
public static function is_robot( $user_agent=null , $host=null  )
  {
	/* Updated : 15/09/2011 */

	$user_agent	= ( $user_agent !== null ) ? $user_agent : self::ua();
	$robots	=       "google"
				."|crawl"
				."|crawler"
				."|msnbot"
				."|search.msn.com"
				."|inktomisearch"
				."|looksmart"
				."|fastsearch"
				."|deepindex"
				."|directhit"
				."|teoma"
				."|quicknet"
				."|gigablast"
				."|picsearch"
				."|spider"
				."|yahoo"
				."|slurp"
				."|surveybot"
				."|therarestparser"
				."|scoutjet"
				."|mlbot"
				."|nuhk"
				."|ia_archiver"
				."|openbot"
				."|yammybot"
				."|search"
				."|browsershots"
				."|robot";


	if( preg_match( "#".$robots."#i" , $user_agent ) )
	  {
		return true;
	  }
	else if( isset($host) AND ( $host!=null ) AND preg_match( "#".$robots."#i" , $host ) )
	  {
		return true;
	  }
	else if( !isset($host) OR ( $host==null ) )
	  {
		$ip		= self::ip();
		$host 	= self::is_private_ip( $ip ) ? $ip : @gethostbyaddr($ip);

		if( preg_match( "#".$robots."#i" , $host ) )
		  {
			return true;
		  }
		else
		  {
			return false;
		  }

	  }
	else
	  {
		return false;
	  }
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOIE LE NOM DE L'OS */
public static function os( $ua=null )
  {

	/* Updated : 02/02/2021 */

	$ua = ( $ua !== null ) ? $ua : self::ua();

	/* --- WINDOWS */
	if(preg_match("#Windows 2000#i", $ua)) 						{$os = "Windows 2000";}
	else if(preg_match("#Windows CE|WinCE#i", $ua)) 				{$os = "Windows CE";}
	else if(preg_match("#Windows ME|WinME#i", $ua)) 				{$os = "Windows ME";}
	else if(preg_match("#Windows 98|Win98#i", $ua)) 				{$os = "Windows 98";}
	else if(preg_match("#Windows 95|Win95#i", $ua)) 				{$os = "Windows 95";}
	else if(preg_match("#Windows XP|WinXP|Windows NT 5.#i", $ua))		{$os = "Windows XP";}
	else if(preg_match("#Windows NT 5.2#i", $ua)) 					{$os = "Windows Server 2003";}
	else if(preg_match("#Windows NT 6.0#i", $ua)) 					{$os = "Windows Vista";}
	else if(preg_match("#Windows NT 6.1#i", $ua)) 					{$os = "Windows 7";}
	else if(preg_match("#Windows NT 6.2#i", $ua)) 					{$os = "Windows 8";}
	else if(preg_match("#Windows NT 6.3#i", $ua)) 					{$os = "Windows 8.1";}
	else if(preg_match("#Windows NT 10.0#i", $ua)) 					{$os = "Windows 10";}
	else if(preg_match("#Win16#i", $ua)) 						{$os = "Windows 3.11";}
	else if(preg_match("#Windows NT 4.0|WinNT4.0|WinNT#i", $ua)) 		{$os = "Windows NT 4";}
	else if(preg_match("#Windows NT#i", $ua)) 					{$os = "Windows NT";}
	else if(preg_match("#Windows#i", $ua)) 						{$os = "Windows";}

	/* --- DEVICES */
	else if(preg_match("#iPod#i", $ua))							{$os = "iPod";}
	else if(preg_match("#iPad#i", $ua))							{$os = "iPad";}
	else if(preg_match("#iPod|iPhone|Aspen#i", $ua))				{$os = "iPhone";}
	else if(preg_match("#Android#i", $ua))						{$os = "Android";}
	else if(preg_match("#Xbox#i", $ua))							{$os = "Xbox";}
	else if(preg_match("#Nintendo Wii#i", $ua))					{$os = "Nintendo Wii";}
	else if(preg_match("#BlackBerry#i", $ua))						{$os = "BlackBerry";}

	/* --- MAC */
	else if(preg_match("#Mac OS X 10.16#i", $ua))					{$os = "macOS Big Sur";}
	else if(preg_match("#Mac OS X 10.15#i", $ua))					{$os = "macOS Catalina";}
	else if(preg_match("#Mac OS X 10.14#i", $ua))					{$os = "macOS Mojave";}
	else if(preg_match("#Mac OS X 10.13#i", $ua))					{$os = "macOS High Sierra";}
	else if(preg_match("#Mac OS X 10.12#i", $ua))					{$os = "macOS Sierra";}
	else if(preg_match("#Mac OS X 10.11#i", $ua))					{$os = "Mac OS X El Capitan";}
	else if(preg_match("#Mac OS X 10.10#i", $ua))					{$os = "Mac OS X Yosemite";}
	else if(preg_match("#Mac OS X 10.9#i", $ua))					{$os = "Mac OS X Mavericks";}
	else if(preg_match("#Mac OS X 10.8#i", $ua))					{$os = "Mac OS X Mountain Lion";}
	else if(preg_match("#Mac OS X 10.7#i", $ua))					{$os = "Mac OS X Lion";}
	else if(preg_match("#Mac OS X 10.6#i", $ua))					{$os = "Mac OS X Snow Leopard";}
	else if(preg_match("#Mac OS X 10.5#i", $ua))					{$os = "Mac OS X Leopard";}
	else if(preg_match("#Mac OS X 10.4#i", $ua))					{$os = "Mac OS X Tiger";}
	else if(preg_match("#Mac OS X 10.3#i", $ua))					{$os = "Mac OS X Panther";}
	else if(preg_match("#Mac OS X 10.2#i", $ua))					{$os = "Mac OS X Jaguar";}
	else if(preg_match("#Mac OS X 10.1#i", $ua))					{$os = "Mac OS X Puma";}
	else if(preg_match("#Mac OS X 10.0#i", $ua))					{$os = "Mac OS X Cheetah";}
	else if(preg_match("#Mac OS X#i", $ua))						{$os = "Mac OS X";}
	else if(preg_match("#Mac|Macintosh|Mac_PowerPC#i", $ua))			{$os = "Mac";}

	/* --- LINUX */
	else if(preg_match("#Ubuntu#i", $ua)) 						{$os = "Ubuntu";}
	else if(preg_match("#Debian#i", $ua)) 						{$os = "Debian";}
	else if(preg_match("#Fedora#i", $ua)) 						{$os = "Fedora";}
	else if(preg_match("#CrOS#i", $ua)) 						{$os = "Chrome OS";}
	else if(preg_match("#Suse#i", $ua)) 						{$os = "Suse";}
	else if(preg_match("#Linux|X11#i", $ua)) 						{$os = "Linux";}
	else if(preg_match("#Unix#i", $ua)) 						{$os = "Unix";}

	/* --- OTHERS */
	else if(preg_match("#QNX#i", $ua)) 							{$os = "QNX";}
	else if(preg_match("#OS/2#i", $ua)) 						{$os = "OS/2";}
	else if(preg_match("#SunOS#i", $ua))						{$os = "SunOS";}
	else if(preg_match("#BeOS#i", $ua))							{$os = "BeOS";}

	/* --- BOTS */
	else if(preg_match("#Googlebot#i", $ua))						{$os = "Googlebot";}
	else if(preg_match("#Yahoo! Slurp#i", $ua))					{$os = "Yahoo Bot";}
	else if(preg_match("#msnbot#i", $ua))						{$os = "MSN Bot";}
	else if( self::is_robot($ua) )							{$os = "Robot";}

	/* --- CARS */
	else if(  preg_match("#Linux#i", $ua)
		AND preg_match("#QtCarBrowser#i", $ua) )					{$os = "Tesla Model S";}

	/* --- UNKNOWN */                                                                    
	else 												{$os = "Unknown";}
                                                                                           
	return $os;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- NAVIGATEUR INTERNET */
public static function browser( $ua=null )
  {
	$browser	= functions::user_agent( $ua );
	$browser	= ( isset( $browser[0] ) ? $browser[0] : "" ).( isset( $browser[1] ) ? " ".$browser[1] : "" );
	return $browser;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- NAVIGATEUR INTERNET */
public static function browser2( $ua=null )
  {
	if( $ua === null )
	  {
		$ua	= self::ua();
	  }
	else if( preg_match( "/(\))$/" , $ua) )
	  {
		$ua .= ")";
	  }

	$browsers	= array( 
				"Firefox",
				"Safari",
				"Edge",
				"Chrome",
				"Opera",
				"OPR",
				"Netscape",
				"Camino",
				"Galeon",
				"Mosaic",
				"Fennec",
				"Arora",
				"amaya",
				"Advanced Browser",
				"Firebird",
				"iCab",
				"Konqueror",
				"Lynx",
				"Minimo",
				"SymbianOS",
				"SeaMonkey",
				"Thunderbird" );

	$regex 	= "(".implode( "|" , $browsers ).")";
	$split	= preg_split("/[[:space:]]/" , $ua );
	$browsers 	= array();

	foreach( $split as $b )
	  {
	  	if( preg_match( "/^".$regex."/" , $b ) )
	  	  {
	  	  	$browsers[] = $b;
	  	  }
	  }
	
  	$browser 	= "";
	$browsers 	= array_reverse( $browsers );

	if( isset( $browsers[0] ) )
	  {
	  	$explode	= explode( "/" , $browsers[0] );
		$browser	= ( isset( $explode[0] ) ? $explode[0] : "" );
		$version	= ( isset( $explode[1] ) ? " ".$explode[1] : "" );

		if( $browser == "OPR" )
		  {
		  	$browser = "Opera";
		  }

		$browser = $browser.$version;

	  }
	else
	  {
	  	$browser = self::browser();
	  }

	return $browser;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- EXTRAIT TOUTES LES IMAGES D'UN TEXTE */
public static function get_imgs( $data )
  {
	preg_match_all( '#<img .*src=(?:"|\')(.+)(?:"|\').*>#Uis' , $data , $links );
	return $links;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- EXTRAIT TOUT LES LIENS D'UN TEXTE */
public static function get_links( $data , $regex=null )
  {
	preg_match_all("/\<a.*?href=[\"|\'](.*?)[\"|\'].*?\>/", $data , $links );
	
	$links = ( isset($links[1]) AND !empty($links[1]) ) ? $links[1] : array();
	
	if( !is_null($regex) AND !empty($links) )
	  {
	  	$tmp = array();

	  	foreach( $links as $link )
	  	  {
	  	  	if( preg_match( $regex , $link ) )
	  	  	  {
		  	  	$tmp[] = $link;
	  	  	  }
	  	  }

		$links = $tmp;
	  }
	
	return $links;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- EXTRAIT TOUT LES EMAILS D'UN TEXTE */
public static function get_emails( $data , $ext="([a-z]{2,4})" )
  {
	preg_match_all( '`\w([-_.]?\w)*@\w([-_.]?\w)*\.('.$ext.')`' , $data , $emails );
	return $emails;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- GERE LES CONTROLES DE TEMPS D'EXECUTION */
public static function exe_timer( $action , $name="tmp" )
  {
	if( $action == "start" )
	  {
		self::$execution_times[ $name ][ "start" ] = microtime( true );
	  }
	else if( $action == "stop" )
	  {
		self::$execution_times[ $name ][ "stop" ]	 = microtime( true );
		self::$execution_times[ $name ][ "timer" ] = round( self::$execution_times[ $name ][ "stop" ] - self::$execution_times[ $name ][ "start" ] , 4 );
	  }
	else if( $action == "values" )
	  {
		$return[0] = self::$execution_times[ $name ][ "start" ];
		$return[1] = self::$execution_times[ $name ][ "stop" ];
		$return[2] = self::$execution_times[ $name ][ "timer" ];

		return $return;
	  }
	else if( $action == "time" )
	  {
		return self::$execution_times[ $name ][ "timer" ];
	  }
	else if( $action == "display" )
	  {
		$return  = "START : ".self::$execution_times[ $name ][ "start" ];
		$return .= "<br />STOP : ".self::$execution_times[ $name ][ "stop" ];
		$return .= "<br />TIME : ".self::$execution_times[ $name ][ "timer" ];

		return $return;
	  }
	else if( $action == "all" )
	  {
		return self::$execution_times;
	  }
	else
	  {
		return false;
	  }
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- CALCUL LE NOMBRE DE MOTS DANS UN TEXTE */
public static function count_words( $text , $type=null )
  {
	if( $type == "php" )
	  {
		return( str_word_count( $text ) );
	  }
	else
	  {
		$text	= self::remove_html_tags( $text );
		$text = preg_replace( "/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/" , "\n" , $text );
		$text = str_replace( array( "," , "." , ";" , "?" , "!" ) , " " , $text );
		$text = str_replace( array( "\r\n" , "\r" , "\n" , "\t", '"', "'" ) , "" , $text );
		$text = str_replace( array( "   ", "  " ) , " " , $text );
		$text	= explode( " " , $text );
		$nb	= count( $text );

		if( $type == "array" )
		  {
			$return[0] = $nb;
			$return[1] = $text;

			return $return;
		  }
		else
		  {
			return $nb;
		  }
	  }
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOYE LE SYMLBOL EURO (€) POUR UN TRAITEMENT TTF */
public static function ttf_euro_symbol()
  {
	return utf8_encode( "&#8364;" );
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- CONVERTISSEUR DE COORDONNEES GPS DE DEGRES EN DECIMAL */
public static function degres_to_decimal( $strRef , $intDeg , $intMin , $intSec )
  {
	$arrLatLong 	= array();
	$arrLatLong["N"]	= 1;
	$arrLatLong["E"]	= 1;
	$arrLatLong["S"]	= -1;
	$arrLatLong["W"]	= -1;
	  
	return ($intDeg+((($intMin*60)+($intSec))/3600)) * $arrLatLong[$strRef];
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE UNE ADERSSE IP SOUS FORME DE NOMBRE */
public static function ip_to_number( $ip=null )
  {
	if( $ip==null )
	  {
		$ip = self::ip();
	  }
	
	$ip_num	= preg_split( "/[.]+/", $ip);
	$ip_num	= (double)( $ip_num[0]*16777216 ) + ( $ip_num[1]*65536 ) + ($ip_num[2]*256 ) + ( $ip_num[3] );

	return $ip_num;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE UNE ADRESSE IP EN FONCTION D'UN NOMBRE */
public static function number_to_ip( $number )
  {
        $a	= ( $number / 16777216 ) % 256;
        $b	= ( $number / 65536 ) % 256;
        $c	= ( $number / 256 ) % 256;
        $d	= ( $number ) % 256;

        $ip	= $a.".".$b.".".$c.".".$d;

        return $ip;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- VERIFIE SI LE SERVEUR DISTANT EST EN LIGNE */
public static function check_host( $host , $port=80 )
  {
	/* -------------- A REVOIR --------------  */

	$check = @fsockopen( $host, $port , $errno , $errstr , 30 );
	if( $check )
	  {
		return true;
		fclose( $check );
	  }
	else
	  {
		return false;
	  }
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- PROTEGE UNE CHAINE DES ATTAQUES XSS */
public static function xss_protect( $data , $full=false , $ltgt=false , $tags="" )
  {
	if( $ltgt )
	  {
		$data = str_replace( "<" , "&lt;" , $data );
		$data = str_replace( ">" , "&gt;" , $data );
	  }
	else
	  {
		$data = self::remove_html_tags( $data , $tags );
	  }

	if( $full )
	  {
		$data = self::only_alphanumeric( $data );
	  }

	return $data;
  }
 
/* --------------------------------------------------------------------------------------------------------------------------------------------- PREPARE UNE VALEUR POUR SQL ' " */
public static function sql_safe( $string , $quote = '"' )
  {
	if( $quote === '"' )
	  {
		$string = str_replace( "\\'" , "'" , $string );
		$string = str_replace( "'" , "\'" , $string );
	  }
	else if( $quote === "'" )
	  {
		$string = str_replace( '\\"' , '"' , $string );
		$string = str_replace( '"' , '\"' , $string );
	  }

  	$string = mysqli_real_escape_string( $string );
	
	return $string;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- CHERCHE TOUT LES LIENS EN FONCTION D'UNE EXTENSION */
public static function get_links_by_ext( $data , $ext )
  {
	/* à développer : recheche par mot clés dans un lien ( <a> ) genre : "rapidshare:mp3" */

	if( preg_match( "#^http(s)?://([a-zA-Z0-9\-_\/\.~]*)$#" , $data ) )
	  {
		$data = file_get_contents( $data );
	  }

	preg_match_all( "/http(s)?\:([a-zA-Z0-9\-_\/\.~]*)\.".$ext."/", $data , $output);

	return $output;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- DEMANDE UN LOGIN / PASSWORD POUR ACCEDER A LA PAGE */
public static function need_auth( $accounts=array() , $log_errors=false )
  {
	$auth = false;

	if( is_array($accounts) AND !empty($accounts) AND isset($_SERVER["PHP_AUTH_USER"]) AND isset($_SERVER["PHP_AUTH_PW"]) )
	  {
		foreach( $accounts as $login => $password )
		  {
		  	if( empty( $password ) AND empty($_SERVER["PHP_AUTH_PW"]) )
		  	  {
		  	  	$password = null;
		  	  }

			if( ( $_SERVER["PHP_AUTH_USER"] == $login ) AND ( $_SERVER["PHP_AUTH_PW"] == $password ) )
			  {
				$auth = true;
				self::$php_user = $_SERVER["PHP_AUTH_USER"];
				break;
			  }
		  }
	  }

	if( $auth === false )
	  {
		if( ( $log_errors === true ) AND isset($_SERVER["PHP_AUTH_USER"]) AND isset($_SERVER["PHP_AUTH_PW"]) )
		  {
			$txt  = "Tentative d'accès à : ";
			$txt .= $_SERVER["REQUEST_URI"];
			$txt .= " via ";
			$txt .= isset($_SERVER["PHP_AUTH_USER"]) ? $_SERVER["PHP_AUTH_USER"] : "";
			$txt .= " : ";
			$txt .= isset($_SERVER["PHP_AUTH_PW"]) ? $_SERVER["PHP_AUTH_PW"] : "";
			
			self::log( "need_auth_errors" , $txt );
		  }

		header( 'WWW-Authenticate: Basic realm="Authentification"' );
		header( 'HTTP/1.0 401 Unauthorized' );

		echo "<!DOCTYPE html>
		<html lang='fr'>
		<head>
		<title>Accès non autorisé</title>
		<meta http-equiv='content-type' content='text/html; charset=UTF-8' />
		</head>
		<body>
			<div style='margin:50px 50px 0px 50px;text-align:center;padding:100px;background-color:#EAD5DC;border-radius:4px;'>
					<span style='font-size:50px;'>&#128274;</span>
				<br />
				<br />
				<div style='color:#93002E;font-size:14pt;font-family:monospace,sans-serif;'>Authentification nécessaire</div>
			</div>
		</body>
		</html>";

		exit;
	  }

  }
    



    
/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOIE LES INFORATIONS GEOIP D'UN UTILISATEUR */
/*
	Update : 07/11/2019
*/
public static function get_geoip()
  {
	$continents	= array(
					"AF" => "Afrique",
					"AN" => "Antarctique",
					"AS" => "Asie",
					"EU" => "Europe",
					"NA" => "Amérique du Nord",
					"OC" => "Océanie",
					"SA" => "Amérique du Sud" );
	$regions	= array( 
					"C1" => "Alsace",
					"97" => "Aquitaine",
					"98" => "Auvergne",
					"99" => "Basse-Normandie",
					"A1" => "Bourgogne",
					"A2" => "Bretagne",
					"A3" => "Centre",
					"A4" => "Champagne-Ardenne",
					"A5" => "Corse",
					"A6" => "Franche-Comté",
					"A7" => "Haute-Normandie",
					"A8" => "Île-de-France",
					"A9" => "Languedoc-Roussillon",
					"B1" => "Limousin",
					"B2" => "Lorraine",
					"B3" => "Midi-Pyrénées",
					"B4" => "Nord-Pas-de-Calais",
					"B5" => "Pays de la Loire",
					"B6" => "Picardie",
					"B7" => "Poitou-Charentes",
					"B8" => "Provence-Alpes-Côte d'Azur",
					"B9" => "Rhône-Alpes" );

	$geoip	= array(
				"ip"				=> ( isset($_SERVER["MMDB_ADDR"]) ? $_SERVER["MMDB_ADDR"] : ( isset($_SERVER["GEOIP_ADDR"]) ? $_SERVER["GEOIP_ADDR"] : "" ) ),
				"continent"			=> ( isset($_SERVER["MM_CONTINENT_NAME"]) ? $_SERVER["MM_CONTINENT_NAME"] : ( isset($_SERVER["GEOIP_CONTINENT_CODE"]) ? ( ( isset( $continents[ $_SERVER["GEOIP_CONTINENT_CODE"] ] ) AND !empty($continents[ $_SERVER["GEOIP_CONTINENT_CODE"] ]) ) ? $continents[ $_SERVER["GEOIP_CONTINENT_CODE"] ] : $_SERVER["GEOIP_CONTINENT_CODE"] ) : "" ) ),
				"code_pays"			=> ( isset($_SERVER["MM_COUNTRY_CODE"]) ? $_SERVER["MM_COUNTRY_CODE"] : ( isset($_SERVER["GEOIP_COUNTRY_CODE"]) ? $_SERVER["GEOIP_COUNTRY_CODE"] : "" ) ),
				"pays"			=> ( isset($_SERVER["MM_COUNTRY_NAME"]) ? $_SERVER["MM_COUNTRY_NAME"] : ( isset($_SERVER["GEOIP_COUNTRY_NAME"]) ? $_SERVER["GEOIP_COUNTRY_NAME"] : "" ) ),
				"region"			=> ( isset($_SERVER["MM_REGION_NAME"]) ? $_SERVER["MM_REGION_NAME"] : ( isset($_SERVER["GEOIP_REGION"]) ? ( ( isset( $regions[ $_SERVER["GEOIP_REGION"] ] ) AND !empty($regions[ $_SERVER["GEOIP_REGION"] ]) ) ? $regions[ $_SERVER["GEOIP_REGION"] ] : $_SERVER["GEOIP_REGION"] ) : "" ) ),
				"code_region"		=> ( isset($_SERVER["MM_REGION_CODE"]) ? $_SERVER["MM_REGION_CODE"] : ( isset($_SERVER["GEOIP_REGION"]) ? $_SERVER["GEOIP_REGION"] : "" ) ),
				"ville"			=> ( isset($_SERVER["MM_CITY_NAME"]) ? $_SERVER["MM_CITY_NAME"] : ( isset($_SERVER["GEOIP_CITY"]) ? $_SERVER["GEOIP_CITY"] : "" ) ),
				"code_postal"		=> ( isset($_SERVER["MM_POSTAL_CODE"]) ? $_SERVER["MM_POSTAL_CODE"] : ( isset($_SERVER["GEOIP_POSTAL_CODE"]) ? $_SERVER["GEOIP_POSTAL_CODE"] : "" ) ),
				"latitude"			=> ( isset($_SERVER["MM_LATITUDE"]) ? $_SERVER["MM_LATITUDE"] : ( isset($_SERVER["GEOIP_LATITUDE"]) ? $_SERVER["GEOIP_LATITUDE"] : "" ) ),
				"longitude"			=> ( isset($_SERVER["MM_LONGITUDE"]) ? $_SERVER["MM_LONGITUDE"] : ( isset($_SERVER["GEOIP_LONGITUDE"]) ? $_SERVER["GEOIP_LONGITUDE"] : "" ) ),
				"timezone"			=> ( isset($_SERVER["MM_TIME_ZONE"]) ? $_SERVER["MM_TIME_ZONE"] : "" ),
				"fai"				=> ( isset($_SERVER["MM_FAI"]) ? $_SERVER["MM_FAI"] : "" )
			  );

	$geoip["code"]		= $geoip["code_pays"];
	$geoip["country"]		= $geoip["pays"];
	$geoip["city"]		= $geoip["ville"];

	return $geoip;
  }





    
/* --------------------------------------------------------------------------------------------------------------------------------------------- AFFICHE LES INFOS POUR ACTIVER LE JAVASCRIPT */
public static function please_enable_javascript()
  {
	$return = "
		<div>
			<div style='font-family:Arial,sans-serif;font-size:13px;font-weight:bold;'>Pour utiliser le site correctement, vous devez activer le Javascript</div>
			<div style='font-family:Arial,sans-serif;font-size:12px;'>Cliquez sur votre navigateur :
				<br />
				<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&bull;&nbsp;<a style='font-family:Arial,sans-serif;font-size:12px;' href='https://support.mozilla.org/fr/kb/javascript'>Firefox</a>
				<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&bull;&nbsp;<a style='font-family:Arial,sans-serif;font-size:12px;' href='http://docs.info.apple.com/article.html?path=Safari/3.0/en/9279.html'>Safari</a>
				<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&bull;&nbsp;<a style='font-family:Arial,sans-serif;font-size:12px;' href='http://support.microsoft.com/gp/howtoscript'>Internet Explorer</a>
			</div>
		</div>";

	return $return;
  }




/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE UNE ERREUR DE MAINTENANCE */
public static function temporarily_unavailable()
  {
	header("HTTP/1.1 503 Service Temporarily Unavailable");
	header("Status: 503 Service Temporarily Unavailable");
	header("Retry-After: 300"); 						/* 300 seconds */
	exit;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- AFFICHE UN MESSAGE DE MAINTENANCE */
public static function maintenance( $refresh=120 )
  {
	ob_start();
	@header( "HTTP/1.1 503 Service Temporarily Unavailable" );
	@header( "Status: 503 Service Temporarily Unavailable" );
	if( is_numeric($refresh) )
	  {
		@header( "Retry-After: ".$refresh );
	  }
	@header( "Connection: Close");

	echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
	<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='fr' lang='fr'>
	<head>
	<title>Maintenance</title>".( is_numeric($refresh) ? "\n<meta http-equiv='refresh' content='".$refresh.";url=http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]."' />" : "" )."
	<meta http-equiv='content-type' content='text/html; charset=UTF-8' />
	</head>
	<body>
		<div style='margin-top:60px;text-align:center;padding:20px 40px 20px 40px;background-color:#D70649;-moz-border-radius:10px;-moz-box-shadow: 0px 0px 5px rgba(50,50,50,0.9);'>
			<div style='color:#FFFFFF;font-size:17pt;font-weight:bold;font-family:arial,sans-serif;'>Le site est en maintenance</div>";

			if( is_numeric($refresh) )
			  {
				echo "
				<div style='color:#FFFFFF;font-size:10pt;font-family:arial,sans-serif;'>
					Cette page se rafra&icirc;chit toutes les ".$refresh." secondes
					<br />( last refresh : ".date( "d/m/Y - H:i:s" )." )
				</div>";
			  }

		echo "
		</div>
	</body>
	</html>";

	$g = ob_get_clean();
	echo $g;
	exit;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- VERIFIE LA SYNTAXE D'UNE URL */
public static function check_url( $url )
  {
	$url = str_replace( " " , "%20" , $url );

	if( !filter_var( $url , FILTER_VALIDATE_URL ) )
	  {
		return false;
	  }
	else
	  {
		return true;
	  }
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- VERIFIE LA VALIDITE D'UN EMAIL */
public static function check_email( $email )
  {
	if( false !== filter_var( $email , FILTER_VALIDATE_EMAIL ) )
	  {
		return true;
	  }
	else
	  {
		return false;
	  }
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOI SI LE NAVIGATEUR EST IE OU PAS */
public static function is_ie( $ua=null )
  {
	$ua = ( $ua !== null ) ? $ua : self::ua();

	if( isset($ua) AND preg_match( "/msie|(microsoft internet explorer|Trident\/\d{1,2}.\d{1,2}; rv:([0-9]*)|(Edge\/([0-9\.]{0,})))/i" , $ua) )
	  {
		return true;
	  }
	else
	  {
		return false;
	  }
	
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOI SI LA VERSION DE IE */
public static function ie_version( $ua=null )
  {
	$ua		= ( $ua !== null ) ? $ua : self::ua();
	$version	= null;

	if( preg_match( "/MSIE ([0-9]{1,2}(\.[0-9]{1,2})?)/" , $ua , $version ) )
	  {
		if( isset($version[1]) )
		  {
			$version = floatval( $version[1] );
		  }
	  }

	return $version;
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOI SI L'UTILISATEUR EST FACEBOOK ... OU PAS */
public static function is_facebook( $ua=null )
  {
	$ua = ( $ua !== null ) ? $ua : self::ua();

	if( !(stristr( $ua ,"facebook" ) === FALSE) )
	  {
		return true;
	  }
	else
	  {
		return true;
	  }
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- AJOUTE UN "S" A UN MOT */
public static function s( $value )
  {
	return ( $value > 1 ? "s" : "" );
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE UNE DATE AU FORMAT FR 00/00/0000 */
public static function reverse_date( $date , $separator="/" )
  {
  	/* 0000-00-00 00:00:00 */
	
	if( preg_match( "#^\d{4}(\-|\/)\d{2}(\-|\/)\d{2} \d{2}:\d{2}:\d{2}$#" , $date ) )
	  {
		$return = substr( $date , 8 , 2 ).$separator.substr( $date , 5 , 2 ).$separator.substr( $date , 0 , 4 )." ".substr( $date , 11 , 8 );
	  }

	/* 00-00-0000 00:00:00 */
	
	else if( preg_match( "#^\d{2}(\-|\/)\d{2}(\-|\/)\d{4} \d{2}:\d{2}:\d{2}$#" , $date ) )
	  {
		$return = substr( $date , 6 , 4 ).$separator.substr( $date , 3 , 2 ).$separator.substr( $date , 0 , 2 )." ".substr( $date , 11 , 8 );
	  }

	/* 0000-00-00 */
	
	else if( preg_match( "#^\d{4}(\-|\/)\d{2}(\-|\/)\d{2}$#" , $date ) )
	  {
		$return = substr( $date , 8 , 2 ).$separator.substr( $date , 5 , 2 ).$separator.substr( $date , 0 , 4 );
	  }

	/* 00-00-0000 */
	
	else if( preg_match( "#^\d{2}(\-|\/)\d{2}(\-|\/)\d{4}$#" , $date ) )
	  {
		$return = substr( $date , 6 , 4 ).$separator.substr( $date , 3 , 2 ).$separator.substr( $date , 0 , 2 );
	  }
	else
	  {
		$return = $date;
	  }

	return $return;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE UNE DATE AU FORMAT US 0000-00-00 */
public static function reverse_date_us( $date , $separator="-" )
  {
	return substr( $date , 6 , 4 ).$separator.substr( $date , 3 , 2 ).$separator.substr( $date , 0 , 2 );
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE UNE DATE AU FORMAT TIMESTAMP / INT */
public static function date_int( $date )
  {
  	if( !is_numeric( $date ) )
  	  {
  		$date = strtotime( str_replace( "/" , "-" ,  $date ) );
  	  }

	return $date;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- ALIAS DE LA FONCTION PRINT_R CI-DESSOUS */
public static function r( ...$args )
  {
    return self::print_r( ...$args );
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- AFFICHE UN PRINT_R D'UN TABLEAU COLORÉ */
public static function print_r( $arr , $return = false , $from_print_r = false , $show_array = true )
  {
  	$data = $arr;

  	if( is_object( $arr ) AND !empty( $arr ) )
  	  {
  	  	$data = (array) $data;
  	  }
  
	$html			= "";
	$css_font		= "font-family:Monaco,monospace;font-size:11px;white-space:break-spaces;";
	$css_border		= "border-color:#999;";
	$empty		= "<span style=\"".$css_font.";color:#CC0000;font-style:italic;\">(empty)</span>";

	if( is_array( $data ) )
	  {
		$html .= "<table border='1' style=\"width:100%;border-spacing:0px;border-collapse:collapse;margin-bottom:4px;text-align:left;".$css_border."\">";

		if( ( $from_print_r == true ) OR ( $show_array == true ) )
		  {
			$html .= "
			<tr>
				<td colspan='2' style=\"".$css_font.$css_border."color:#FFFFFF;background-color:#5E5750;padding:2px;\">ARRAY</td>
			</tr>";
		  }

		if( empty( $data ) )
		  {
			$html .= "
			<tr>	
				<td style=\"vertical-align:top;background-color:#FAFAFA;padding:5px;width:20%;\">".$empty."</td>
			</tr>";
		  }
		else
		  {
			foreach( $data as $k => $v )
			  {	
				$css_data_type_key	= "color:#000000;";
				$css_data_type_value	= "color:#008800;";

				if( ( $v === true ) OR ( $v === false ) )								/* true or false */
				  {
					$css_data_type_value	= "background-color:#f6f5ff;color:#684bc2;";
					$v				= ( $v === true ) ? "true" : "false";
				  }
				else if( empty( $v ) )											/* Empty Array */
				  {
					$css_data_type_value	= "background-color:#FAFAFA;color:#CC0000;";
				  }
				else if( is_numeric( $v ) )										/* Numérique */
				  {
					$css_data_type_value	= "background-color:#FCF4E3;color:#4418CC;";
				  }
				else if( functions::is_serialized( $v ) )								/* serialized */
				  {
					$css_data_type_value	= "color:#770000;";
					$v = preg_replace( '#([:"])#' , "<span style='color:#FA0000;'>$1</span>" , $v );
					$v = preg_replace( '#([{}])#' , "<span style='color:#0000ff;background-color:#dedeff;'>$1</span>" , $v );
				  }

				$html .= "
				<tr>
					<td style=\"vertical-align:top;".$css_font.$css_border."background-color:#EDEDED;padding:5px;width:20%;".$css_data_type_key."\">".$k."</td>
					<td style=\"vertical-align:top;".$css_font.$css_border."background-color:#FFFDF4;padding:5px 1px 1px 5px;".$css_data_type_value."\">";

						if( is_numeric($v) AND ( $v === 0 ) )
						  {
						  	$html .= $v;
						  }
						else
						  {
						  	$html .= self::print_r( $v , true , true );
						  }
					
				$html .= "</td>
				</tr>";

			  }
		  }

		$html .= "</table>";
	  }
	else
	  {
	  	if( $data == "" )
	  	  {
		  	$html .= $empty;
	  	  }
	  	else if( $from_print_r === false )
	  	  {
		  	$html .= "<div style=\"".$css_font."background-color:#EDEDED;padding:20px;color:#0000BB;white-space:pre;\">".$data."</div>";
	  	  }
	  	else
	  	  {
		  	$html .= $data;
	  	  }
	  }

	if( $return == true )
	  {
		return $html;
	  }
	else
	  {
		echo $html;
		exit;
	  }

  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- AJOUTE DES HEADER POUR FORCER LE TELECHARGEMENT */
public static function force_download( $filename , $size=10000 )
  {
	header( "Content-Type: application/force-download" );
	header( "Content-Length: ".$size );
	header( "Content-disposition: attachment; filename=".$filename );
	header( "Pragma: no-cache" );
	header( "Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0" );
	header( "Expires: 0" );
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE UNE SIGNATURE UNIQUE D'UN NAVIGATEUR */
public static function unik( $ip=true ,  $crypt=true )
  {
	$unik = "";
	if( $ip === true )
	  {
		$unik .= self::ip();
		$unik .= self::ip(true);
	  }
	$unik .= self::ua();
	$unik .= isset($_SERVER["HTTP_ACCEPT"]) ? $_SERVER["HTTP_ACCEPT"] : "";
	$unik .= isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) ? $_SERVER["HTTP_ACCEPT_LANGUAGE"] : "";
	$unik .= isset($_SERVER["HTTP_ACCEPT_ENCODING"]) ? $_SERVER["HTTP_ACCEPT_ENCODING"] : "";
	$unik .= isset($_SERVER["HTTP_ACCEPT_CHARSET"]) ? $_SERVER["HTTP_ACCEPT_CHARSET"] : "";

	return ( $crypt === true ) ? sha1( $unik ) : $unik;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LE MICROTIME SANS LES SECONDES */
public static function microtime( $with_time=false )
  {
	$microtime = explode( " " , microtime() );
	$microtime = substr( $microtime[0] , 2 );
	
	if( $with_time === true )
	  {
		$microtime = time().$microtime;
	  }
	else if( !empty($with_time) )
	  {
		$microtime = time().$with_time.$microtime;
	  }
	
	return $microtime;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RECHERCHE DES ELEMENTS DANS UNE CHAINE */
public static function is_in_string( $keywords , $string , $all=false , $strict=false )
  {
	$keywords		= str_replace( array( "," , ";" , ":" , "!" , "?" , "/" , "_" , "+" ) , " "  , $keywords );
	$keywords		= str_replace( array( "\t" , "  " , "   " ) , " " , $keywords );
	$keywords		= explode( " " , $keywords );
	$nb_words		= count( $keywords );
	$nb_founded		= 0;

	foreach( $keywords AS $keyword )
	  {
		$keyword = trim( $keyword );

		if( preg_match( "/".$keyword."/".( $strict === true ? "" : "i" ) , $string ) )
		  {
			$nb_founded++;
		  }
	  }

	if(  ( ( $all === false ) AND ( $nb_founded > 0 ) ) OR ( ( $all === true ) AND ( $nb_founded == $nb_words ) ) )
	  {
		return true;
	  }
	else
	  {
		return false;
	  }

  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE UNE COULEUR CORRESPONDANT À UN ADRESSE IP */
public static function color_ip( $ip , $type="HEX" )
  {
	if( !filter_var( $ip , FILTER_VALIDATE_IP ) )
	  {
		$colors = array( 0 , 0 , 0 , 0 );
	  }
	else
	  {
		$colors = explode( "." , $ip );
	  }

	if( strtoupper( $type ) === "RGB" )
	  {
		$return = "rgb( ".$colors[1]." , ".$colors[2]." , ".$colors[3]." )";
	  }
	else
	  {
		$R = str_pad( dechex( $colors[1] ) , 2 , "0", STR_PAD_LEFT );
		$G = str_pad( dechex( $colors[2] ) , 2 , "0", STR_PAD_LEFT );
		$B = str_pad( dechex( $colors[3] ) , 2 , "0", STR_PAD_LEFT );

		$return = "#".strtoupper( $R.$G.$B );
	  }

	return $return;
  }
/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE UNE COULEUR CORRESPONDANT À UNE CHAINE DE CARACTÈRES */
public static function colorify( $str , $return = "hexa" )
  {
	$color		= array( "R" => 0 , "G" => 0 , "B" => 0 );
	$str		= preg_replace("#[^0-9]#i", "" , $str );
	$nb		= strlen($str)+1;
	$nb_split	= floor( strlen($str) / 3 );
	
	if( $nb_split >= 3 )
	  {
		$split	 = str_split( substr( $str , 0 , ( 3 * $nb_split ) ) , $nb_split );
		
		foreach( $split as $i => $c )
		  {
		  	$max		= "1".str_pad( "" , strlen($c) , "0" );
			$split	[$i]	= floor( ( 255 * $c ) / $max );
		  }
		
		$color["R"] = $split[0];
		$color["G"] = $split[1];
		$color["B"] = $split[2];
	
	  }


	if( $return === true )
	  {
		return $color;
	  }
	else if( $return == "rgb" )
	  {
		return "rgb( ".implode( " , " , $color )." )";
	  }
	else
	  {
		return strtoupper( self::rgb_to_hexa( $color ) );
	  }

  }




/* --------------------------------------------------------------------------------------------------------------------------------------------- ENCODE LES CARACTÈRES D'UNE CHAINE POUR UNE URL */
public static function utf8_url_convert( $string )
  {
	$chars = array( 
"¡" => "%C2%A1",
"¢" => "%C2%A2",
"£" => "%C2%A3",
"¤" => "%C2%A4",
"¥" => "%C2%A5",
"¦" => "%C2%A6",
"§" => "%C2%A7",
"¨" => "%C2%A8",
"©" => "%C2%A9",
"ª" => "%C2%AA",
"«" => "%C2%AB",
"¬" => "%C2%AC",
"­" => "%C2%AD",
"®" => "%C2%AE",
"¯" => "%C2%AF",
"°" => "%C2%B0",
"±" => "%C2%B1",
"²" => "%C2%B2",
"³" => "%C2%B3",
"´" => "%C2%B4",
"µ" => "%C2%B5",
"¶" => "%C2%B6",
"·" => "%C2%B7",
"¸" => "%C2%B8",
"¹" => "%C2%B9",
"º" => "%C2%BA",
"»" => "%C2%BB",
"¼" => "%C2%BC",
"½" => "%C2%BD",
"¾" => "%C2%BE",
"¿" => "%C2%BF",
"À" => "%C3%80",
"Á" => "%C3%81",
"Â" => "%C3%82",
"Ã" => "%C3%83",
"Ä" => "%C3%84",
"Å" => "%C3%85",
"Æ" => "%C3%86",
"Ç" => "%C3%87",
"È" => "%C3%88",
"É" => "%C3%89",
"Ê" => "%C3%8A",
"Ë" => "%C3%8B",
"Ì" => "%C3%8C",
"Í" => "%C3%8D",
"Î" => "%C3%8E",
"Ï" => "%C3%8F",
"Ð" => "%C3%90",
"Ñ" => "%C3%91",
"Ò" => "%C3%92",
"Ó" => "%C3%93",
"Ô" => "%C3%94",
"Õ" => "%C3%95",
"Ö" => "%C3%96",
"×" => "%C3%97",
"Ø" => "%C3%98",
"Ù" => "%C3%99",
"Ú" => "%C3%9A",
"Û" => "%C3%9B",
"Ü" => "%C3%9C",
"Ý" => "%C3%9D",
"Þ" => "%C3%9E",
"ß" => "%C3%9F",
"à" => "%C3%A0",
"á" => "%C3%A1",
"â" => "%C3%A2",
"ã" => "%C3%A3",
"ä" => "%C3%A4",
"å" => "%C3%A5",
"æ" => "%C3%A6",
"ç" => "%C3%A7",
"è" => "%C3%A8",
"é" => "%C3%A9",
"ê" => "%C3%AA",
"ë" => "%C3%AB",
"ì" => "%C3%AC",
"í" => "%C3%AD",
"î" => "%C3%AE",
"ï" => "%C3%AF",
"ð" => "%C3%B0",
"ñ" => "%C3%B1",
"ò" => "%C3%B2",
"ó" => "%C3%B3",
"ô" => "%C3%B4",
"õ" => "%C3%B5",
"ö" => "%C3%B6",
"÷" => "%C3%B7",
"ø" => "%C3%B8",
"ù" => "%C3%B9",
"ú" => "%C3%BA",
"û" => "%C3%BB",
"ü" => "%C3%BC",
"ý" => "%C3%BD",
"þ" => "%C3%BE",
"ÿ" => "%C3%BF" );

	return strtr( $string , $chars );
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOIE TOUTES LES INFOS D'UNE URL (referer ou autre) */

public static function url( $url = null )
  {
	/* -------------------------------------------------------------------------------------------------- INIT */
	$url		= ( $url == null ) ? functions::current_url() : trim( $url );
	$url		= str_replace( " " , "%20" , $url );
	$url		= self::utf8_url_convert( $url );
	$url		= self::xss_protect( $url );

	$datas	= array(
					"url"			=> $url,
					"scheme"		=> "",
					"host"		=> "",
					"path"		=> "",
					"query"		=> "",
					"parametres"	=> array(),
					"hash"		=> "",
					"hash_parametres"	=> array(),
					"domain"		=> "",
					"subdomain"		=> array(),
					"ext"			=> "",
					"type"		=> "",
					"site"		=> "",
					"sitename"		=> "",
					"data"		=> "",
					"data_extended"	=> ""
				  );


	if( self::check_url( $url ) )
	  {
		$founded	= false;
		$parse	= parse_url( $url );

		/* -------------------------------------------------------------------------------------------- COMMONS */

		$datas["scheme"]		= isset($parse["scheme"]) ? $parse["scheme"] : "";
		$datas["host"]		= isset($parse["host"]) ? $parse["host"] : "";
		$datas["path"]		= isset($parse["path"]) ? $parse["path"] : "";
		$datas["query"]		= isset($parse["query"]) ? $parse["query"] : "";
		$datas["hash"]		= isset($parse["fragment"]) ? $parse["fragment"] : "";

		/* -------------------------------------------------------------------------------------------- DOMAINE */

		$domain			= self::check_domain( $datas["host"] , true );
		$ip				= self::is_valid_ip( $datas["host"] );

		$datas["ext"]		= $ip ? "" : $domain["ext"];
		$datas["domain"]		= $ip ? $datas["host"] : $domain["domain"];
		$datas["subdomain"]	= $ip ? array() : $domain["subdomain"];
		$explode			= explode( "." , $datas["domain"] );
		$explode			= array_reverse( $explode );
		$datas["site"]		= $ip ? $datas["host"] : $domain["domain"];

		/* -------------------------------------------------------------------------------------------- PARAMETRES */

		if( isset($datas["query"]) AND !empty($datas["query"]) )
		  {
			foreach( explode( "&" , $datas["query"] ) AS $param )
			  {
				$param = explode( "=" , $param );
				$datas["parametres"][ $param[0] ] = isset( $param[1] ) ? urldecode( $param[1] ) : "";
			  }
		  }

		/* -------------------------------------------------------------------------------------------- PARAMETRES DU HASH */

		if( isset($datas["hash"]) AND !empty($datas["hash"]) )
		  {
			foreach( explode( "&" , $datas["hash"] ) AS $param )
			  {
				$param = explode( "=" , $param );
				$datas["hash_parametres"][ $param[0] ] = isset( $param[1] ) ? urldecode( $param[1] ) : "";
			  }
		  }

		/* -------------------------------------------------------------------------------------------- PUBLICITE */

		if( preg_match( "#^http(s)?://(www.)?google.#i" , $url ) AND preg_match( "#url=/aclk#i" , $url ) )
		  {
			$founded				= true;
			$datas["type"]			= "pub";
			$datas["sitename"] 		= "Google AdWords";
			$datas["data"]			= "";
			$datas["data_extended"]		= "";

			if( isset($datas["parametres"]["q"]) ) 
			  {
				$datas[ "data" ]	= $datas["parametres"]["q"];
			  }
			else if( isset($datas["parametres"]["client"]) )
			  {
				$datas[ "data" ]	= $datas["parametres"]["client"];
			  }
			else
			  {
				$datas[ "data" ]	= "";
			  }
		  }
		else if( preg_match( "#googlesyndication.com#i" , $url ) OR preg_match( "#^http(s)?://googleads.g.doubleclick.net/#i" , $url ) )
		  {
			$founded				= true;
			$datas["type"]			= "pub";
			$datas["sitename"] 		= "Google Ads";
			$datas["data"]			= "";
			$datas["data_extended"]		= "";

			if( isset($datas["parametres"]["url"]) 
				OR isset($datas["parametres"]["p"])
				OR isset($datas["parametres"]["q"])
				OR isset($datas["parametres"]["ref"]) )
			  {
				$url			= isset($datas["parametres"]["url"]) ? $datas["parametres"]["url"] : "";
				$p			= isset($datas["parametres"]["p"]) ? $datas["parametres"]["p"] : "";
				$q			= isset($datas["parametres"]["q"]) ? $datas["parametres"]["q"] : "";
				$ref			= isset($datas["parametres"]["ref"]) ? $datas["parametres"]["ref"] : "";

				$datas[ "data" ]			= ( $url != "" ) ? $url : ( ( $ref != "" ) ? $ref : "" );
				$datas[ "data_extended" ]	= ( $q != "" ) ? $q : ( ( $p != "" ) ? $p : ( ( $ref != "" ) ? $ref : "" ) );
			  }
			else if( isset($datas["parametres"]["client"]) )
			  {
				$datas[ "data" ]	= $datas["parametres"]["client"];
			  }
		  }
		/* -------------------------------------------------------------------------------------------- MOBILE APPS */
	
		else if( preg_match( "#^android-app://#i" , $url ) )
		  {
			$founded				= true;
			$datas["type"]			= "app";
			$datas["sitename"] 		= "Android APP";
			
			if( preg_match( "#googlequicksearchbox#i" , $url ) )
			  {
				$datas["sitename"] = "Android Search Box";
			  }
			
		  }
		/* -------------------------------------------------------------------------------------------- SEARCH ENGINE */

		else if( preg_match( "#^http(s)?://(www.)?google.#i" , $url ) AND preg_match( "#/m/search#i" , $url ) )
		  {
			$keyword 	= "q";
			$engine	= "Google Mobile";
		  }
		else if( preg_match( "#^http(s)?://(www.)?google.([a-zA-Z]{2,3})\/images#i" , $url ) )
		  {
			$keyword 	= "q";
			$engine	= "Google Images";
		  }

		else if( preg_match( "#^http(s)?://maps.google.#i" , $url ) )
		  {
			$keyword 	= "q";
			$engine	= "Google Maps";
		  }
		else if( preg_match( "#^http(s)?://(www.)?google.#i" , $url ) )
		  {
			$keyword 	= "q";
			$engine	= "Google";
		  }
		else if( preg_match( "#^http(s)?://translate.google.#i" , $url ) )
		  {
			$keyword 	= "q";
			$engine	= "Google Translator";
		  }
		else if( preg_match( "#m.yahoo.com/#i" , $url ) AND preg_match( "#p=#i" , $url ) )
		  {
			$keyword 	= "p";
			$engine	= "Yahoo! Mobile";
		  }
		else if( preg_match( "#yahoo.#i" , $datas["host"] ) AND preg_match( "#q=#i" , $url ) )
		  {
			$keyword 	= "q";
			$engine	= "Yahoo!";
		  }
		else if( preg_match( "#yahoo.#i" , $datas["host"] ) AND preg_match( "#p=#i" , $url ) )
		  {
			$keyword 	= "p";
			$engine	= "Yahoo!";
		  }
		else if( preg_match( "#m.bing.com/#i" , $url ) AND preg_match( "#Q=#i" , $url ) )
		  {
			$keyword 	= "Q";
			$engine	= "Bing Mobile";
		  }
		else if( preg_match( "#m.bing.com/#i" , $url ) AND preg_match( "#q=#i" , $url ) )
		  {
			$keyword 	= "q";
			$engine	= "Bing Mobile";
		  }
		else if( preg_match( "#bing.com/images#i" , $url ) )
		  {
			$keyword 	= "q";
			$engine	= "Bing Images";
		  }
		else if( preg_match( "#bing.com/#i" , $url ) )
		  {
			$keyword 	= "q";
			$engine	= "Bing";
		  }
		else if( preg_match( "#altavista.com/#i" , $url ) )
		  {
			$keyword 	= "q";
			$engine	= "Altavista";
		  }
		else if( preg_match( "#qwant.com/#i" , $url ) )
		  {
			$keyword 	= "q";
			$engine	= "Qwant";
		  }
		else if( preg_match( "#yougoo.fr/#i" , $url ) )
		  {
			$keyword 	= "q";
			$engine	= "Yougoo";
		  }
		else if( preg_match( "#veosearch.com/#i" , $url ) )
		  {
			$keyword 	= "q";
			$engine	= "Veosearch";
		  }
		else if( preg_match( "#hooseek.com/#i" , $url ) )
		  {
			$keyword 	= "recherche";
			$engine	= "Hooseek";
		  }
		else if( preg_match( "#fastbrowsersearch.com/#i" , $url ) )
		  {
			$keyword 	= "q";
			$engine	= "Fast Browser Search";
		  }
		else if( preg_match( "#alltheweb.com/#i" , $url ) )
		  {
			$keyword 	= "q";
			$engine	= "All The Web";
		  }
		else if( preg_match( "#ask.com/#i" , $url ) )
		  {
			$keyword 	= "q";
			$engine	= "Ask";
		  }
		else if( preg_match( "#search.conduit.com/#i" , $url ) )
		  {
			$keyword 	= "q";
			$engine	= "Conduit";
		  }
		else if( preg_match( "#shop.ebay.#i" , $url ) )
		  {
			$keyword 	= "rawquery";
			$engine	= "Ebay";
		  }
		else if( preg_match( "#search.ke.voila.fr/S/orange#i" , $url ) )
		  {
			$keyword 	= "rdata";
			$engine	= "Orange";
		  }
		else if( preg_match( "#search.ke.voila.fr/#i" , $url ) )
		  {
			$keyword 	= "rdata";
			$engine	= "Voila";
		  }
		else if( preg_match( "#search.incredimail.com/#i" , $url ) )
		  {
			$keyword 	= "q";
			$engine	= "Incredimail";
		  }
		else if( preg_match( "#search-results.com/#i" , $url ) )
		  {
			$keyword 	= "q";
			$engine	= "Search Results";
		  }
		else if( preg_match( "#centurylink.net/search#i" , $url ) )
		  {
			$keyword	= "q";
			$engine	= "CenturyLink";
		  }
		else if( preg_match( "#searchqu.com/web#i" , $url ) )
		  {
			$keyword	= "q";
			$engine	= "SearchQU";
		  }
		else if( preg_match( "#isearch.babylon.com/#i" , $url ) )
		  {
			$keyword	= "q";
			$engine	= "Babylon";
		  }
		else if( preg_match( "#search.sweetim.com/#i" , $url ) )
		  {
			$keyword	= "q";
			$engine	= "SweetIM";
		  }
		else if( preg_match( "#search.free.fr/#i" , $url ) )
		  {
			$founded				= true;
			$datas["type"]			= "search";
			$datas["sitename"] 		= "Free";
			$datas["data"]			= "-";
		  }
		else if( preg_match( "#/aol/search#i" , $url ) )
		  {
			$keyword	= preg_match( "/query=#i/" , $url ) ? "query" : "q";
			$engine	= "AOL";
		  }
		else if( preg_match( "#search.myway.com/search#i" , $url ) )
		  {
			$keyword	= "searchfor";
			$engine	= "MyWay";
		  }
		else if( preg_match( "#wolframalpha.com/input#i" , $url ) )
		  {
			$keyword	= "i";
			$engine	= "Wolfram Alpha";
		  }
		else if( preg_match( "#ecosia.org/#i" , $url ) )
		  {
			$keyword	= "q";
			$engine	= "Ecosia";
		  }
		else if( preg_match( "#(sfr.fr/do/gsa/search|sfr.fr/recherche)#i" , $url ) )
		  {
			$founded				= true;
			$datas["type"]			= "search";
			$datas["sitename"] 		= "SFR";
			$datas["data"]			= "-";
		  }
		else if( preg_match( "#baidu.com/s#i" , $url ) )
		  {
			$keyword	= "wd";
			$engine	= "Baidu";
		  }
		else if( preg_match( "#search.msn.com/#i" , $url ) )
		  {
            $keyword	= "q";
            $engine	= "MSN Search";
		  }
		else if( preg_match( "#search.babylon.com/#i" , $url ) )
		  {
			$keyword	= "q";
			$engine	= "Babylon";
		  }
		else if( preg_match( "#search.magentic.com/#i" , $url ) )
		  {
			$keyword	= "q";
			$engine	= "Magentic";
		  }
		else if( preg_match( "#searcheo.fr/#i" , $url ) )
		  {
			$keyword	= "q";
			$engine	= "Searcheo";
		  }
		else if( preg_match( "#vizzeo.fr/#i" , $url ) )
		  {
			$keyword	= "q";
			$engine	= "Vizzeo";
		  }
		else if( preg_match( "#jeplanteunarbre.com/#i" , $url ) )
		  {
			$keyword	= "q";
			$engine	= "Je Plante Un Arbre";
		  }
		else if( preg_match( "#yandex.ru/yandsearch#i" , $url ) )
		  {
			$keyword	= "text";
			$engine	= "Yandex";
		  }

		/* -------------------------------------------------------------------------------------------- RECHERCHE DANS LES PARAMETRES */

		if( !$founded AND isset($keyword) AND !empty($keyword) AND isset($engine) AND !empty($engine) AND isset($datas["parametres"][ $keyword ]) )
		  {
			$datas["type"]			= "search";
			$datas["sitename"] 		= $engine;
			$datas["data"]			= $datas["parametres"][ $keyword ];

			if( ( $engine === "Google" ) AND isset( $datas["parametres"][ "oq" ] ) AND !empty( $datas["parametres"][ "oq" ] ) )
			  {
				$datas["data_extended"] = $datas["parametres"][ "oq" ];
			  }
		  }
		
		/* -------------------------------------------------------------------------------------------- RECHERCHE DANS LES PARAMETRES DU HASH */
		
		else if( !$founded AND isset($keyword) AND !empty($keyword) AND isset($engine) AND !empty($engine) AND isset($datas["hash_parametres"][ $keyword ]) )
		  {
			$datas["type"]			= "search";
			$datas["sitename"] 		= $engine;
			$datas["data"]			= $datas["hash_parametres"][ $keyword ];

			if( ( $engine === "Google" ) AND isset( $datas["hash_parametres"][ "oq" ] ) AND !empty( $datas["hash_parametres"][ "oq" ] ) )
			  {
				$datas["data_extended"] = $datas["hash_parametres"][ "oq" ];
			  }
		  }

		/* -------------------------------------------------------------------------------------------- REDIRECTIONS */

		else if( preg_match( "#facebook.com#i" , $datas["host"] ) AND isset($datas["parametres"]["u"]) )
		  {
			$datas["type"]			= "redirection";
			$datas["sitename"] 		= "Facebook";
			$datas["data"]			= $datas["parametres"]["u"];
		  }

		/* -------------------------------------------------------------------------------------------- CLIENTS MAIL */

		else if( !$founded AND preg_match( "#imp.free.fr#" , $url ) )
		  {
			$datas["type"]			= "mail";
			$datas["sitename"] 		= "Messagerie Free.fr";

			if( isset($datas["parametres"]["url"]) )
			  {
				$datas["data"] = "Lien vers : ".$datas["parametres"]["url"];
			  }
			else if( preg_match( "#\/message.php#" , $url ) )
			  {
				$datas["data"] = "Message email";
			  }
		  }
		else if( !$founded AND preg_match( "#mail.yahoo.#" , $url ) )
		  {
			$datas["type"]			= "mail";
			$datas["sitename"] 		= "Yahoo! Mail";
		  }
		else if( !$founded AND preg_match( "#mail.google.com/#" , $url ) )
		  {
			$datas["type"]			= "mail";
			$datas["sitename"] 		= "Gmail";
		  }
		else if( !$founded AND preg_match( "#mail.live.com/#" , $url ) )
		  {
			$datas["type"]			= "mail";
			$datas["sitename"] 		= "Live Mail";
		  }
		else if( !$founded AND preg_match( "#orange.fr/webmail#" , $url ) )
		  {
			$datas["type"]			= "mail";
			$datas["sitename"] 		= "Orange Mail";
		  }
		else if( !$founded AND preg_match( "#sfr.fr/webmail#" , $url ) )
		  {
			$datas["type"]			= "mail";
			$datas["sitename"] 		= "SFR Mail";
		  }
		else if( !$founded AND preg_match( "#laposte.net/webmail#" , $url ) )
		  {
			$datas["type"]			= "mail";
			$datas["sitename"] 		= "Webmail La Poste";
		  }
		else if( !$founded AND preg_match( "#webmail.numericable.fr#" , $url ) )
		  {
			$datas["type"]			= "mail";
			$datas["sitename"] 		= "Webmail Numericable";
		  }
		else if( !$founded AND preg_match( "#voila.fr/webmail/#" , $url ) )
		  {
			$datas["type"]			= "mail";
			$datas["sitename"] 		= "Voila Mail";
		  }
		else if( !$founded AND preg_match( "#webmail.aol.com/#" , $url ) )
		  {
			$datas["type"]			= "mail";
			$datas["sitename"] 		= "AOL";
		  }
		else if( !$founded AND preg_match( "#zimbra.free.fr/#" , $url ) )
		  {
			$datas["type"]			= "mail";
			$datas["sitename"] 		= "Messagerie Free.fr";
		  }
		else if( !$founded AND preg_match( "#mail.ru/#" , $url ) )
		  {
			$datas["type"]			= "mail";
			$datas["sitename"] 		= "Mail.ru";
		  }
		else if( !$founded AND preg_match( "#numeo.fr/readmsg.php#" , $url ) )
		  {
			$datas["type"]			= "mail";
			$datas["sitename"] 		= "Numeo";
		  }

		/* -------------------------------------------------------------------------------------------- SITES */

		else if( !$founded )
		  {
			$datas["type"]			= "site";
			$datas["sitename"] 		= ucwords( str_replace( ".".$datas["ext"] , "" , $datas["site"] ) );
			$datas["data"] 			= ( isset($datas["parametres"]["q"]) ? $datas["parametres"]["q"] : "" );
			$datas["data_extended"] 	= serialize( $datas["parametres"] );
		  }

		/* --------------------------------------------------------------------------------------------  */

		if( $datas["data_extended"] == "" )
		  {
			$datas["data_extended"] = serialize( $datas["parametres"] );
		  }

		/* --------------------------------------------------------------------------------------------  */

	  }
	else
	  {
		if( preg_match( "#^blockedReferrer$#i" , $url ) )
		  {
			$datas["type"]			= "no_referer";
			$datas["sitename"] 		= "Blocked Referer";
			$datas["data"] 			= "";
			$datas["data_extended"] 	= "";
		  }
	  }

	return $datas;
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- SUPPRIME LES PARAMETRES D'UNE URL APRES LE POINT D'INTERROGATION  */
public static function url_remove_parameters( $url )
  {
	return preg_replace( "/\?.*/" , "" , $url );
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- VERIFIE SI L'EXTENSION DU DOMAINE EST VALIDE */
public static function check_domain( $url , $return="" )
  {
	$data		= array(
					"valid"	=> false,
					"domain" 	=> "",
					"subdomain"	=> array(),
					"ext" 	=> ""
			  );
	$check	= false;
	$exts		= "(ab.ca|ac|ac.cn|ac.id|ac.ir|ac.uk|ac.yu|ac.za|ac.zw|ad|ad.jp|ae|aero|af|ag|ah.cn|ai|al|am|an|ao|aq|ar|arts.nf|as|asia|asn.au|asso.fr|asso.nc|at|au|aw|ax|az|ba|bb|bc.ca|bd|be|bf|bg|bh|bi|biz|biz.et|biz.fj|biz.ki|biz.nr|biz.om|biz.pk|biz.pl|biz.pr|biz.tj|biz.tt|biz.vn|bj|bj.cn|bl|bm|bn|bo|br|bs|bt|bw|by|bz|ca|cat|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|co.ac|co.ae|co.ag|co.ao|co.at|co.bb|co.bi|co.bw|co.ck|co.cm|co.cr|co.cz|co.dk|co.ee|co.fk|co.gg|co.gy|co.hu|co.id|co.il|co.im|co.in|co.ir|co.it|co.je|co.jp|co.ke|co.kr|co.ls|co.ma|co.mu|co.mw|co.mz|co.ni|co.nl|co.nz|co.om|co.pn|co.pt|co.ro|co.sz|co.th|co.tj|co.tt|co.tz|co.ua|co.ug|co.uk|co.uz|co.ve|co.vi|co.yu|co.za|co.zm|co.zw|com|com.ac|com.af|com.ag|com.ai|com.al|com.an|com.ar|com.au|com.az|com.ba|com.bb|com.bd|com.bh|com.bi|com.bm|com.bn|com.bo|com.br|com.bs|com.bt|com.by|com.bz|com.ci|com.cn|com.co|com.cu|com.cy|com.cz|com.dm|com.do|com.dz|com.ec|com.ee|com.eg|com.er|com.es|com.et|com.fj|com.fr|com.gd|com.ge|com.gh|com.gi|com.gl|com.gn|com.gp|com.gr|com.gt|com.gu|com.gy|com.hk|com.hn|com.hr|com.ht|com.io|com.iq|com.jm|com.jo|com.kg|com.kh|com.ki|com.kn|com.kw|com.ky|com.kz|com.lb|com.lc|com.lk|com.lv|com.ly|com.mg|com.mk|com.ml|com.mm|com.mo|com.mt|com.mu|com.mv|com.mw|com.mx|com.my|com.na|com.nf|com.ng|com.ni|com.np|com.nr|com.om|com.pa|com.pe|com.pg|com.ph|com.pk|com.pl|com.pr|com.ps|com.pt|com.py|com.qa|com.re|com.ro|com.ru|com.sa|com.sb|com.sc|com.sd|com.sg|com.sl|com.sn|com.sv|com.sy|com.tj|com.tl|com.tn|com.tp|com.tr|com.tt|com.tw|com.ua|com.uy|com.uz|com.vc|com.ve|com.vi|com.vn|com.ws|com.ye|com.zm|coop|cq.cn|cr|csiro.au|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|edu.al|edu.ar|edu.au|edu.ec|edu.ee|edu.eg|edu.jo|edu.kh|edu.mt|edu.ng|edu.np|edu.qa|edu.ye|edu.yu|ee|eh|es|et|eu|fi|fin.ec|firm.ht|firm.in|firm.nf|firm.ro|firm.ve|fj|fj.cn|fk|fm|fo|fr|ga|gd|gd.cn|ge|gen.in|gen.tr|gf|gg|gh|gi|gl|gm|gn|go.id|gouv.fr|gov.kh|gov.uk|gp|gq|gr|gr.jp|gs|gs.cn|gt|gw|gx.cn|gy|gz.cn|ha.cn|hb.cn|he.cn|hi.cn|hk|hk.cn|hl.cn|hm|hn|hn.cn|hr|ht|hu|id.ir|id.lv|ie|im|in|in.th|info|info.au|info.ec|info.et|info.fj|info.ht|info.hu|info.ki|info.nf|info.nr|info.pl|info.pr|info.ro|info.tt|info.ve|info.vn|int.ar|io|iq|ir|is|it|it.ao|je|jl.cn|jm|jo|jobs|jp|js.cn|jx.cn|kg|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|ln.cn|lr|ls|lt|ltd.gi|ltd.uk|lu|lv|ly|ma|mc|md|me|me.uk|med.ec|mf|mg|mh|mil.ar|mil.ec|mil.jo|misc|mk|ml|mm|mn|mo|mo.cn|mobi|mod.gi|mp|mq|mr|ms|mu|muni.il|museum|mv|mw|mx|my|mz|na|name|name.vn|nc|ne|ne.jp|ne.kr|ne.tz|ne.ug|net|net.ac|net.ae|net.af|net.ag|net.ai|net.al|net.an|net.ar|net.au|net.az|net.bb|net.bd|net.bn|net.bo|net.br|net.bs|net.bt|net.bz|net.ck|net.cn|net.co|net.dm|net.do|net.dz|net.ec|net.eg|net.et|net.fj|net.fk|net.gg|net.gl|net.gn|net.gp|net.gr|net.gt|net.gy|net.hk|net.hn|net.ht|net.id|net.il|net.in|net.ir|net.je|net.jo|net.kg|net.kh|net.ki|net.lb|net.lv|net.ly|net.ma|net.mk|net.ml|net.mm|net.mo|net.mt|net.mu|net.mw|net.mx|net.my|net.nf|net.ng|net.np|net.nr|net.nz|net.om|net.pe|net.pg|net.ph|net.pk|net.pl|net.pn|net.pr|net.ps|net.py|net.qa|net.ru|net.sa|net.sb|net.sc|net.sd|net.sg|net.sy|net.th|net.tj|net.tl|net.tp|net.tr|net.tt|net.tw|net.ua|net.uk|net.vc|net.ve|net.vi|net.vn|net.ye|net.zm|nf|ng|nhs.uk|nl|nm.cn|no|nom.es|nom.fr|nr|nu|nx.cn|nz|off.ai|om|or.at|or.bi|or.id|or.jp|or.ke|or.kr|or.mu|or.th|or.tz|or.ug|org|org.ac|org.ae|org.af|org.ag|org.ai|org.al|org.an|org.ar|org.au|org.az|org.bb|org.bd|org.bi|org.bn|org.bo|org.br|org.bs|org.bt|org.bz|org.ck|org.cn|org.co|org.dm|org.do|org.dz|org.ec|org.ee|org.eg|org.es|org.et|org.fj|org.fk|org.gg|org.gi|org.gl|org.gn|org.gr|org.gt|org.hk|org.hn|org.ht|org.hu|org.il|org.im|org.in|org.ir|org.je|org.jo|org.kg|org.kh|org.ki|org.lb|org.lv|org.ly|org.ma|org.mk|org.ml|org.mm|org.mo|org.mt|org.mu|org.mw|org.mx|org.my|org.mz|org.na|org.nf|org.ng|org.ni|org.np|org.nr|org.nz|org.om|org.pe|org.ph|org.pk|org.pl|org.pn|org.pr|org.ps|org.py|org.qa|org.ro|org.ru|org.sa|org.sc|org.sd|org.se|org.sg|org.sl|org.sy|org.tj|org.tl|org.tp|org.tr|org.tt|org.tw|org.ua|org.uk|org.vc|org.ve|org.vi|org.vn|org.ye|org.yu|org.za|org.zm|other.nf|oz.au|pa|pe|pe.kr|per.nf|pf|pg|ph|pk|pl|plc.uk|pm|pn|post|pp.ru|pp.se|pr|pro|pro.vn|ps|pt|pw|qa|qc.ca|qh.cn|re|rec.nf|rec.ve|ro|rs|ru|rw|sb|sc|sc.cn|sch.ir|sch.uk|sd|sd.cn|se|seoul.kr|sg|sh|sh.cn|si|sj|sk|sl|sm|sn|sn.cn|so|sr|st|store.nf|store.ve|su|sv|sx.cn|sy|sz|tc|td|tel|tf|tg|th|tj|tj.cn|tk|tl|tm|tm.fr|tm.mc|tm.mt|tm.ro|tm.se|tm.za|tn|to|tp|tr|travel|tt|tv|tv.sd|tw|tw.cn|ua|ug|uk|uk.co|us|uz|va|vc|ve|vg|vi|vn|vu|web.do|web.id|web.nf|web.pk|web.ve|wf|ws|xj.cn|xz.cn|ye|yn.cn|yt|yu|za|zj.cn|zm)";
	$url		= preg_replace( "#http(s)?://#i" , "" , $url );
	$explode	= explode( "/" , $url );

	if( isset( $explode[0] ) )
	  {
		$explode	= explode( "." , $explode[0] );
		$explode	= array_reverse( $explode );
		$nb		= count( $explode );
		$ext 		= $explode[0];

		if( $nb > 1 )
		  {
			for( $i = 2 ; $i < $nb ; $i++ )
			  {
				$data["subdomain"][] = $explode[ $i ];
			  }
		  }

		if( isset($explode[1]) AND preg_match( "#^".$exts."$#i" , $ext ) )
		  {
			$data["check"]	= true;
			$data["domain"]	= $explode[1].".".$explode[0];
			$data["ext"]	= $explode[0];
		  }
		else if( ( $nb == 1 ) )
		  {
			$data["check"]	= true;
			$data["domain"]	= $explode[0];
		  }
	  }

	if( $return === true )
	  {
		return $data;
	  }
	else if( ( $return !== "" ) AND isset( $data[ $return ] ) )
	  {
		return $data[ $return ];
	  }
	else
	  {
		return $data["check"];
	  }
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- SORTIE ECRAN EN TEXTE */
public static function plain_text()
  {
	header( "Content-Type: text/plain" );
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- AFFICHE UN BOUTON "j'aime" FACEBOOK */
public static function facebook_like_button( $url , $type="standard" , $action="like" , $width="400" , $lang="fr_FR" )
  {
	/* TYPE : 
		 standard
		 button_count
	*/

	if( $type == "standard" )
	  {
		$iframe = "<iframe src='http://www.facebook.com/plugins/like.php?"
				. "action=".$action
				. "&colorscheme=light"
				. "&href=".urlencode( $url )
				. "&layout=standard"
				. "&locale=".$lang
				. "&node_type=link"
				. "&show_faces=false"
				. "&width=".$width."' scrolling='no' frameborder='0' style='background-color:transparent;border:none;width:".$width."px;height:26px;'></iframe>";
	  }
	else
	  {
		$iframe = "<iframe src='http://www.facebook.com/plugins/like.php?"
				. "action=".$action
				. "&colorscheme=light"
				. "&href=".urlencode( $url )
				. "&layout=button_count"
				. "&locale=".$lang
				. "&node_type=link"
				. "&show_faces=false"
				. "&width=110' scrolling='no' frameborder='0' style='background-color:transparent;border:none;width:120px;height:20px'></iframe>";
	  }


	return $iframe;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE L'URL DE PARTAGE DE FACEBOOK */
public static function facebook_share_link( $url )
  {
	return "http://www.facebook.com/sharer/sharer.php?u=".urlencode( $url );
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LE NOMBRE DE LIKES D'UNE URL SUR FACEBOOK */
public static function facebook_get_nb_likes( $url )
  {
	$nb = 0;

	if( self::check_url( $url ) )
	  {
		$nb = json_decode( file_get_contents("http://api.facebook.com/method/fql.query?format=json&query=select%20like_count%20from%20link_stat%20where%20url='$url'" ) );
		if( isset($nb[0]) AND isset($nb[0]->like_count)  AND is_numeric($nb[0]->like_count) )
		  {
			$nb = $nb[0]->like_count;
		  }
	  }

	return $nb;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOIE LE NOM DU FICHIER D'UNE URL */
public static function url_filename( $url )
  {
	$array = explode( "/" , $url );
	
	return array_pop( $array );
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- PERMET DE TELECHARGER UN FICHIER */
public static function download( $url , $output )
  {
	$content	= file_get_contents( $url );
	$file		= fopen( $output , "w+" );
	fwrite( $file , $content );
	fclose( $file );

	if( file_exists($output) AND is_file($output ) )
	  {
		return true;
	  }
	else
	  {
		return false;
	  }
  }




/* --------------------------------------------------------------------------------------------------------------------------------------------- TRANSFORME LA FONCTION D'EXPLODE DE PHP EN MULTI EXPLODE */
public static function multi_explode( $delimiters , $string )
  {
	$common_delimiter = "{#|-++-++-*#}";

	if( is_array( $delimiters ) )
	  {
		foreach( $delimiters AS $del )
		  {
			$string = str_replace( $del , $common_delimiter , $string );
		  }

		$delimiters = $common_delimiter;
	  }

	return explode( $delimiters , $string );
  }




/* --------------------------------------------------------------------------------------------------------------------------------------------- CREE UN FICHIER AVEC OU SANS CONTENU */
public static function makefile( $name , $data=null , $force=true )
  {
	if( $force OR ( !$force AND !file_exists($name) AND !is_file($name) ) )
	  {
		if( $file = fopen( $name , "w+" ) )
		  {
			if( $data !== null )
			  {
				fwrite( $file , $data );
			  }
			fclose( $file );
			return true;
		  }
		else
		  {
			return false;
		  }
	  }
	else
	  {
		return false;
	  }
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- CREE UN FICHIER AVEC OU SANS CONTENU */
public static function browser_is( $browsers , $ua=null )
  {
	$return	= false;

	$ua		= self::user_agent( $ua );
	$ua		= $ua[ 0 ];
	$ua		= strtolower( $ua);

	$browsers	= strtolower( $browsers);
	$browsers	= explode( "|" , $browsers);

	foreach( $browsers AS $browser )
	  {
		if( !$return AND preg_match( "#".$browser."#i" , $ua ) )
		  {
			$return = true;
		  }
	  }

	return $return;
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LES INFORMATIONS SUR LE SERVEUR */
public static function server_infos( $print=false )
  {
	$data = "<pre>";
	$data .= "------------------------------------------------------------------------------------------------------";
	$data .= "\nServer infos _ v1.3";
	$data .= "\n------------------------------------------------------------------------------------------------------\n\n";
	
	/* --------------------------------------------------------------- Emplacement de ce script */
	$data .= "\nEmplacement du fichier : ".__FILE__;
	$data .= "\nEmplacement du fichier sur le disque : ".realpath( __FILE__ );
	/* --------------------------------------------------------------- Heure du serveur */
	$data .= "\nHeure serveur : ".date( "d/m/Y H:i:s" );
	/* --------------------------------------------------------------- Uname -a */
	$sys_value = @exec( "uname -a" );
	$data .= "\nuname - a : ".$sys_value;
	/* --------------------------------------------------------------- Uptime du serveur */
	$sys_value = @exec( "uptime" );
	$data .= "\nUptime : ".$sys_value;
	/* --------------------------------------------------------------- Version d'Apache */
	$data .= "\n";
	$data .= "\nServeur Apache : ".apache_get_version();
	$data .= "\n";
	/* --------------------------------------------------------------- Version de PHP */
	$data .= "\nVersion <span style='color:#0000EE;'>PHP :</span> <span style='font-weight:bold;color:#0000EE;'>".PHP_VERSION."</span>";
	/* --------------------------------------------------------------- Version de MySQL */
	$data .= "\nServeur <span style='color:#0000EE;'>MySQL :</span> <span style='font-weight:bold;color:#0000EE;'>".mysqli_get_client_info()."</span>";
	$data .= "\n";
	/* --------------------------------------------------------------- En-têtes HTTP */
	$data .= "\nEn-t&ecirc;tes HTTP :\n";
	$headers = apache_request_headers();
	foreach ($headers as $header => $value)
	  {
		$data .= "\n - ".$header." : ".$value;
	  }

	/* --------------------------------------------------------------- Cookies */

	if( isset($_COOKIE) )
	  {
		$data .= "\n\nCookies :\n";
		$data .= str_replace( "Array\n" , "" , print_r( $_COOKIE , true ) );
	  }

	$data .= "\n";

	/* --------------------------------------------------------------- Variables $_SERVER */
	$data .= "\n\$_SERVER :\n";
	$data .= str_replace( "Array\n" , "" , print_r( $_SERVER , true ) );
	$data .= "\n";

	/* --------------------------------------------------------------- Modules Apache */
	$data .= "\nModules Apache :\n";
	$data .= str_replace( "Array\n" , "" , print_r( apache_get_modules() , true ) );

	$data .= "</pre>";

	if( $print === true )
	  {
		echo $data;
		exit;
	  }
	else
	  {
		return $data;
	  }
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LE CONTENU D'UNE PAGE HTML ENTRE LES BALISES BODY */
public static function get_body( $data )
  {
	if( self::check_url( $data ) )
	  {
		$data = file_get_contents( $data );
	  }

	preg_match_all( '#<body\b[^>]*>(.*?)</body>#is' , $data , $data );

	if( isset($data[1]) AND count($data[1]) > 1 )
	  {
		return $data[1];
	  }
	else
	  {
		return ( isset($data[1][0]) AND !empty($data[1][0]) ) ? $data[1][0] : "";
	  }
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LE CONTENU D'UN ELEMENT DANS UNE PAGE HTML VIA SON ID OU SA CLASSE ( TAGS ) */
public static function get_html_element( $html , $value , $attribut="id" , $element="div" )
  {
	/*
		ORIGINAL : #<div(.*?)class=['\"]artist['\"](.*?)>(.*?)</div>#
		$html = str_replace( array( "\r\n" , "\r" , "\n" , "\t" , "  " , "   " ) , "" , $html );
	*/
	$content 	= array();

	preg_match_all( "#<".$element."(.*?)".$attribut."=['\"](.*?)".$value."(.*?)['\"](.*?)>(.*?)</".$element.">#m" , $html , $match );  /* #(...)#sm */
	
	if( is_array($match) AND isset($match[5]) )
	  {
		$content = $match[5];
	  }
	
	return $content;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LES ELEMENTS D'UN TABLEAU */
/* A MODIFIER : version plus complexe, qui gère les tableau multi dimensions */

public static function list_tab( $data , $separator="<br />" )
  {
	$output = "";

	foreach( $data AS $key => $value )
	  {
		$output .= ( $separator == "</li>" ? "<li>" : "" );
		$output .= $value;
		$output .= $separator;
	  }

	return $output;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- FONCTION INVERSE DE NL2BR */

public static function br2nl( $data )
  {
	/* chr(13).chr(10) */
	return preg_replace( "#<br[[:space:]]*/?[[:space:]]*>#i" , "\r\n" , $data );
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOI UNE URL DE GOOGLE CACHE */

public static function google_cache( $url )
  {
	return "http://www.google.com/search?q=cache:".$url;
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE UNE DATE AU FORMAT DATE */

public static function time_to_date( $time , $format="fr" , $heure=false )
  {
  	$date 	= "";
	$d		= strftime ("%d", $time );
	$m		= strftime ("%m", $time );
	$Y		= strftime ("%Y", $time );
	
	$H		= strftime ("%H", $time );
	$M		= strftime ("%M", $time );
	$S		= strftime ("%S", $time );
	
  	if( $format == "us" )
  	  {
  	  	$date = $Y."-".$m."-".$d;
  	  }
  	else
  	  {
  	  	$date = $d."/".$m."/".$Y;
  	  }
  	  
  	if( $heure == true )
  	  {
		$date .= " - ".$H.":".$M.":".$S;
  	  }
  	
  	return $date;
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE DES DONNEES JSON SOUS FORME DE TABLEAU PHP */

public static function get_json( $url , $assoc=false )
  {
  	$data = array();
  	
  	if( self::check_url( $url ) )
  	  {
	  	$data = file_get_contents( $url );
		$data = json_decode( $data , $assoc );
  	  }
  	  
 	return $data;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- AUTO SPLIT UNE CHAINE DE CARACTERES */
public static function auto_split( $string , $nb_lines=2 , $separator="<br />" , $min=false )
  {
	$string 	= strip_tags($string);
	$string	= preg_replace( "/\s+/" , " " , $string );
	
	$explode	= explode( " " , $string );
	$nb_words	= count( $explode );
	$data		= array();

	if( !is_numeric( $nb_lines ) )
	  {
		$nb_lines = 2;
	  }
	
	if( $min === false )
	  {
		$nb_split = ceil( $nb_words / $nb_lines );
	  }
	else
	  {
		$nb_split = floor( $nb_words / $nb_lines );
	  }

	$nb_split	= ( $nb_split < 1 )  ? 1 : $nb_split;
	
	$l = 0;
	$n = 0;

	foreach( $explode as $line )
	  {
		if( !( $l % $nb_split ) )
		  {
			$n++;
			$data[ $n ] = $line;
		  }
		else
		  {
			$data[ $n ] .= " ".$line;
		  }
		
		
		$l++;
	  }

	if( $separator == "tab" )
	  {
		return $data;
	  }
	else
	  {
		return implode( $separator , $data );
	  }

  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- REORDONNE UN TABLEAU EN +1/-1 A PARTIR D'UNE CLE */
public static function array_progressive_key_sort( $array , $key )
  {
	$return 	= array();
	$nb_lines 	= count( $array );

	if( $key > 1 )
	  {
	  	$curr			= $key;
	  	$curr_min		= $key;
	  	$curr_max		= $key;
	  	$array_order[1]	= $key;
	
		for( $i = 1 ; $i < $nb_lines ; $i++ )
		  {
		  	if( ( $curr > 1 ) AND ( $curr < $nb_lines ) )
		  	  {
		  		$array_order[ ( $i + 1 ) ] = ( $i % 2 == 0 ) ? ( $curr = $curr - $i ) : ( $curr = $curr + $i );
		  		$curr_max = ( $curr_max < $curr ) ? $curr : $curr_max;
		  		$curr_min = ( $curr_min > $curr ) ? $curr : $curr_min;
		  	  }
			else if( $curr_max < $nb_lines )
			  {
			  	$curr_max += 1;
		  		$array_order[ ( $i + 1 ) ] = $curr_max;
			  }
			else
			  {
			  	$curr_min -= 1;
		  		$array_order[ ( $i + 1 ) ] = $curr_min;
			  }
		  }

		
		foreach( $array_order AS $key1 => $key2 )
		  {
		  	$return[ $key1 ] = $array[ $key2 ];
		  }
	  }
	else
	  {
		$return = $array;
	  }

	return $return;
  }




/* --------------------------------------------------------------------------------------------------------------------------------------------- CREE UN REPERTOIRE ET ATTEND QU'IL SOIT WRITABLE */
public static function mkdir( $path , $perms=0755 )
  {
	if( !file_exists( $path ) )
	  {
		$anti_infinity = 0;
		mkdir( $path , $perms );
		while( 1 )
		  {
			if( is_writable( $path ) )
			  {
				break;
		  	  }
		
			if( ++$anti_infinity > 100 )
			  {
				break;
		  	  }
		
			usleep(50);
		  }
	  }
  }




/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LES ELEMENTS NEXT / PREVIOUS A PARTIR D'UN ID */

public static function get_previous_next( &$db , $query , $id , $loop=true )
  {
  	$i		= 0;
  	$current	= null;
  	$data		= array();
  	$return	= array(
					"first"		=> array(),
					"last"		=> array(),
					"previous"		=> array(),
					"next" 		=> array()
			);
  	
	$query	= $db->query( $query );
	$nb		= $query["nb"];
	
	foreach( $query["data"] as $row )
	  {	
		$data[ $i ] = $row;
	
		if( $i === 0 )
		  {
			$return[ "first" ] = $row;
		  }
		else if( $i === ( $nb -1 ) )
		  {
			$return[ "last" ] = $row;
		  }
		
		if( $row["id"] == $id )
		  {
		  	$current = $i;
		  }

		$i++;
	  }
	
		
	if( $nb === 1 )
	  {
		$return["last"] = $return["first"];
	  }	
	else
	  {
		if( ( $current == 0 ) AND ( $nb > 1 ) )
		  {
			$return["next"] = $data[ 1 ];
		  }
		else if( ( $nb > 1 ) AND ( $current == ( $nb - 1 ) ) )
		  {
			$return["previous"] = $data[ ( $current - 1 ) ];
		  }
		else if( $nb > 2 )
		  {
			$return["previous"]	= $data[ ( $current - 1 ) ];
			$return["next"]		= $data[ ( $current + 1 ) ];
		  }
		
	
		if( ( $loop === true ) AND empty($return["previous"]) )
		  {
			$return["previous"] = $return["last"];
		  }
		else if( ( $loop === true ) AND empty($return["next"]) )
		  {
			$return["next"] = $return["first"];
		  }
	  }
	
	
	return $return;
  }




/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LA LISTE DES PAYS PAR ZONE (CODE ISO) */
public static function pays( $wat = false , $return = null )
  {  
	/*
	* Version 3.2
	* Last update : 14/01/2016
	*/

$colonnes_de_tri = array(	"alpha2",
					"alpha3",
					"nom_fr",
					"nom_en",
					"capitale" );
										
$xls = "AF	Afrique	Africa	AO	AGO	Angola	Angola	Luanda
AF	Afrique	Africa	BF	BFA	Burkina Faso	Burkina Faso	Ouagadougou
AF	Afrique	Africa	BI	BDI	Burundi	Burundi	Bujumbura
AF	Afrique	Africa	BJ	BEN	Bénin	Benin	Porto-Novo
AF	Afrique	Africa	BW	BWA	Botswana	Botswana	Gaborone
AF	Afrique	Africa	CD	COD	République Démocratique Du Congo	Congo	Kinshasa
AF	Afrique	Africa	CF	CAF	République Centrafricaine	Central African Republic	Bangui
AF	Afrique	Africa	CG	COG	République Du Congo	Congo	Brazzaville
AF	Afrique	Africa	CI	CIV	Côte D'Ivoire	Côte d'Ivoire	Yamoussoukro
AF	Afrique	Africa	CM	CMR	Cameroun	Cameroon	Yaounde
AF	Afrique	Africa	CV	CPV	Cap-Vert	Cabo Verde	Praia
AF	Afrique	Africa	DJ	DJI	Djibouti	Djibouti	Djibouti
AF	Afrique	Africa	DZ	DZA	Algérie	Algeria	Algiers
AF	Afrique	Africa	EG	EGY	Égypte	Egypt	Cairo
AF	Afrique	Africa	EH	ESH	République Arabe Sahraouie Démocratique	Western Sahara	El-Aaiun
AF	Afrique	Africa	ER	ERI	Érythrée	Eritrea	Asmara
AF	Afrique	Africa	ET	ETH	Éthiopie	Ethiopia	Addis Ababa
AF	Afrique	Africa	GA	GAB	Gabon	Gabon	Libreville
AF	Afrique	Africa	GH	GHA	Ghana	Ghana	Accra
AF	Afrique	Africa	GM	GMB	Gambie	Gambia	Banjul
AF	Afrique	Africa	GN	GIN	Guinée	Guinea	Conakry
AF	Afrique	Africa	GQ	GNQ	Guinée Équatoriale	Equatorial Guinea	Malabo
AF	Afrique	Africa	GW	GNB	Guinée-Bissau	Guinea-Bissau	Bissau
AF	Afrique	Africa	KE	KEN	Kenya	Kenya	Nairobi
AF	Afrique	Africa	KM	COM	Comores	Comoros	Moroni
AF	Afrique	Africa	LR	LBR	Liberia	Liberia	Monrovia
AF	Afrique	Africa	LS	LSO	Lesotho	Lesotho	Maseru
AF	Afrique	Africa	LY	LBY	Libye	Libya	Tripoli
AF	Afrique	Africa	MA	MAR	Maroc	Morocco	Rabat
AF	Afrique	Africa	MG	MDG	Madagascar	Madagascar	Antananarivo
AF	Afrique	Africa	ML	MLI	Mali	Mali	Bamako
AF	Afrique	Africa	MR	MRT	Mauritanie	Mauritania	Nouakchott
AF	Afrique	Africa	MU	MUS	Maurice	Mauritius	Port Louis
AF	Afrique	Africa	MW	MWI	Malawi	Malawi	Lilongwe
AF	Afrique	Africa	MZ	MOZ	Mozambique	Mozambique	Maputo
AF	Afrique	Africa	NA	NAM	Namibie	Namibia	Windhoek
AF	Afrique	Africa	NE	NER	Niger	Niger	Niamey
AF	Afrique	Africa	NG	NGA	Nigeria	Nigeria	Abuja
AF	Afrique	Africa	RE	REU	La Réunion	Réunion	Saint-Denis
AF	Afrique	Africa	RW	RWA	Rwanda	Rwanda	Kigali
AF	Afrique	Africa	SC	SYC	Seychelles	Seychelles	Victoria
AF	Afrique	Africa	SD	SDN	Soudan	Sudan	Khartoum
AF	Afrique	Africa	SH	SHN	Sainte-Hélène, Ascension Et Tristan Da Cunha	Saint Helena, Ascension and Tristan da Cunha	Jamestown
AF	Afrique	Africa	SL	SLE	Sierra Leone	Sierra Leone	Freetown
AF	Afrique	Africa	SN	SEN	Sénégal	Senegal	Dakar
AF	Afrique	Africa	SO	SOM	Somalie	Somalia	Mogadishu
AF	Afrique	Africa	SS	SSD	Soudan Du Sud	South Sudan	Juba
AF	Afrique	Africa	ST	STP	Sao Tomé-Et-Principe	Sao Tome and Principe	Sao Tome
AF	Afrique	Africa	SZ	SWZ	Swaziland	Swaziland	Mbabane
AF	Afrique	Africa	TD	TCD	Tchad	Chad	N'Djamena
AF	Afrique	Africa	TG	TGO	Togo	Togo	Lome
AF	Afrique	Africa	TN	TUN	Tunisie	Tunisia	Tunis
AF	Afrique	Africa	TZ	TZA	Tanzanie	Tanzania	Dodoma
AF	Afrique	Africa	UG	UGA	Ouganda	Uganda	Kampala
AF	Afrique	Africa	YT	MYT	Mayotte	Mayotte	Mamoudzou
AF	Afrique	Africa	ZA	ZAF	Afrique Du Sud	South Africa	Pretoria
AF	Afrique	Africa	ZM	ZMB	Zambie	Zambia	Lusaka
AF	Afrique	Africa	ZW	ZWE	Zimbabwe	Zimbabwe	Harare
AN	Antarctique	Antarctica	AQ	ATA	Antarctique	Antarctica	
AN	Antarctique	Antarctica	BV	BVT	Île Bouvet	Bouvet Island	
AN	Antarctique	Antarctica	GS	SGS	Géorgie Du Sud-Et-Les Îles Sandwich Du Sud	South Georgia and the South Sandwich Islands	Grytviken
AN	Antarctique	Antarctica	HM	HMD	Îles Heard-Et-Macdonald	Heard Island and McDonald Islands	
AN	Antarctique	Antarctica	TF	ATF	Terres Australes Et Antarctiques Françaises	French Southern Territories	Port-aux-Francais
AS	Asie	Asia	AE	ARE	Émirats Arabes Unis	United Arab Emirates	Abu Dhabi
AS	Asie	Asia	AF	AFG	Afghanistan	Afghanistan	Kabul
AS	Asie	Asia	AM	ARM	Arménie	Armenia	Yerevan
AS	Asie	Asia	AZ	AZE	Azerbaïdjan	Azerbaijan	Baku
AS	Asie	Asia	BD	BGD	Bangladesh	Bangladesh	Dhaka
AS	Asie	Asia	BH	BHR	Bahreïn	Bahrain	Manama
AS	Asie	Asia	BN	BRN	Brunei	Brunei Darussalam	Bandar Seri Begawan
AS	Asie	Asia	BT	BTN	Bhoutan	Bhutan	Thimphu
AS	Asie	Asia	CC	CCK	Îles Cocos	Cocos Islands	West Island
AS	Asie	Asia	CN	CHN	Chine	China	Beijing
AS	Asie	Asia	CX	CXR	Île Christmas	Christmas Island	Flying Fish Cove
AS	Asie	Asia	GE	GEO	Géorgie	Georgia	Tbilisi
AS	Asie	Asia	HK	HKG	Hong Kong	Hong Kong	Hong Kong
AS	Asie	Asia	ID	IDN	Indonésie	Indonesia	Jakarta
AS	Asie	Asia	IL	ISR	Israël	Israel	Jerusalem
AS	Asie	Asia	IN	IND	Inde	India	New Delhi
AS	Asie	Asia	IO	IOT	Territoire Britannique de l'Océan Indien	British Indian Ocean Territory	Diego Garcia
AS	Asie	Asia	IQ	IRQ	Irak	Iraq	Baghdad
AS	Asie	Asia	IR	IRN	Iran	Iran	Tehran
AS	Asie	Asia	JO	JOR	Jordanie	Jordan	Amman
AS	Asie	Asia	JP	JPN	Japon	Japan	Tokyo
AS	Asie	Asia	KG	KGZ	Kirghizistan	Kyrgyzstan	Bishkek
AS	Asie	Asia	KH	KHM	Cambodge	Cambodia	Phnom Penh
AS	Asie	Asia	KP	PRK	Corée Du Nord	North Korea	Pyongyang
AS	Asie	Asia	KR	KOR	Corée Du Sud	South Korea	Seoul
AS	Asie	Asia	KW	KWT	Koweït	Kuwait	Kuwait City
AS	Asie	Asia	KZ	KAZ	Kazakhstan	Kazakhstan	Astana
AS	Asie	Asia	LA	LAO	Laos	Lao People's Democratic Republic	Vientiane
AS	Asie	Asia	LB	LBN	Liban	Lebanon	Beirut
AS	Asie	Asia	LK	LKA	Sri Lanka	Sri Lanka	Colombo
AS	Asie	Asia	MM	MMR	Birmanie	Myanmar	Nay Pyi Taw
AS	Asie	Asia	MN	MNG	Mongolie	Mongolia	Ulan Bator
AS	Asie	Asia	MO	MAC	Macao	Macao	Macao
AS	Asie	Asia	MV	MDV	Maldives	Maldives	Male
AS	Asie	Asia	MY	MYS	Malaisie	Malaysia	Kuala Lumpur
AS	Asie	Asia	NP	NPL	Népal	Nepal	Kathmandu
AS	Asie	Asia	OM	OMN	Oman	Oman	Muscat
AS	Asie	Asia	PH	PHL	Philippines	Philippines	Manila
AS	Asie	Asia	PK	PAK	Pakistan	Pakistan	Islamabad
AS	Asie	Asia	PS	PSE	Palestine	Palestine	East Jerusalem
AS	Asie	Asia	QA	QAT	Qatar	Qatar	Doha
AS	Asie	Asia	SA	SAU	Arabie Saoudite	Saudi Arabia	Riyadh
AS	Asie	Asia	SG	SGP	Singapour	Singapore	Singapore
AS	Asie	Asia	SY	SYR	Syrie	Syrian Arab Republic	Damascus
AS	Asie	Asia	TH	THA	Thaïlande	Thailand	Bangkok
AS	Asie	Asia	TJ	TJK	Tadjikistan	Tajikistan	Dushanbe
AS	Asie	Asia	TM	TKM	Turkménistan	Turkmenistan	Ashgabat
AS	Asie	Asia	TR	TUR	Turquie	Turkey	Ankara
AS	Asie	Asia	TW	TWN	Taïwan	Taiwan,	Taipei
AS	Asie	Asia	UZ	UZB	Ouzbékistan	Uzbekistan	Tashkent
AS	Asie	Asia	VN	VNM	Viêt Nam	Viet Nam	Hanoi
AS	Asie	Asia	YE	YEM	Yémen	Yemen	Sanaa
EU	Europe	Europe	AD	AND	Andorre	Andorra	Andorra la Vella
EU	Europe	Europe	AL	ALB	Albanie	Albania	Tirana
EU	Europe	Europe	AT	AUT	Autriche	Austria	Vienna
EU	Europe	Europe	AX	ALA	Îles Åland	Åland Islands	Mariehamn
EU	Europe	Europe	BA	BIH	Bosnie-Herzégovine	Bosnia and Herzegovina	Sarajevo
EU	Europe	Europe	BE	BEL	Belgique	Belgium	Brussels
EU	Europe	Europe	BG	BGR	Bulgarie	Bulgaria	Sofia
EU	Europe	Europe	BY	BLR	Biélorussie	Belarus	Minsk
EU	Europe	Europe	CH	CHE	Suisse	Switzerland	Bern
EU	Europe	Europe	CY	CYP	Chypre	Cyprus	Nicosia
EU	Europe	Europe	CZ	CZE	République Tchèque	Czech Republic	Prague
EU	Europe	Europe	DE	DEU	Allemagne	Germany	Berlin
EU	Europe	Europe	DK	DNK	Danemark	Denmark	Copenhagen
EU	Europe	Europe	EE	EST	Estonie	Estonia	Tallinn
EU	Europe	Europe	ES	ESP	Espagne	Spain	Madrid
EU	Europe	Europe	FI	FIN	Finlande	Finland	Helsinki
EU	Europe	Europe	FO	FRO	Îles Féroé	Faroe Islands	Torshavn
EU	Europe	Europe	FR	FRA	France	France	Paris
EU	Europe	Europe	GB	GBR	Royaume-Uni	United Kingdom of Great Britain	London
EU	Europe	Europe	GG	GGY	Guernesey	Guernsey	St Peter Port
EU	Europe	Europe	GI	GIB	Gibraltar	Gibraltar	Gibraltar
EU	Europe	Europe	GR	GRC	Grèce	Greece	Athens
EU	Europe	Europe	HR	HRV	Croatie	Croatia	Zagreb
EU	Europe	Europe	HU	HUN	Hongrie	Hungary	Budapest
EU	Europe	Europe	IE	IRL	Irlande	Ireland	Dublin
EU	Europe	Europe	IM	IMN	Île De Man	Isle of Man	Douglas
EU	Europe	Europe	IS	ISL	Islande	Iceland	Reykjavik
EU	Europe	Europe	IT	ITA	Italie	Italy	Rome
EU	Europe	Europe	JE	JEY	Jersey	Jersey	Saint Helier
EU	Europe	Europe	LI	LIE	Liechtenstein	Liechtenstein	Vaduz
EU	Europe	Europe	LT	LTU	Lituanie	Lithuania	Vilnius
EU	Europe	Europe	LU	LUX	Luxembourg	Luxembourg	Luxembourg
EU	Europe	Europe	LV	LVA	Lettonie	Latvia	Riga
EU	Europe	Europe	MC	MCO	Monaco	Monaco	Monaco
EU	Europe	Europe	MD	MDA	Moldavie	Moldova	Chisinau
EU	Europe	Europe	ME	MNE	Monténégro	Montenegro	Podgorica
EU	Europe	Europe	MK	MKD	Macédoine	Macedonia	Skopje
EU	Europe	Europe	MT	MLT	Malte	Malta	Valletta
EU	Europe	Europe	NL	NLD	Pays-Bas	Netherlands	Amsterdam
EU	Europe	Europe	NO	NOR	Norvège	Norway	Oslo
EU	Europe	Europe	PL	POL	Pologne	Poland	Warsaw
EU	Europe	Europe	PT	PRT	Portugal	Portugal	Lisbon
EU	Europe	Europe	RO	ROU	Roumanie	Romania	Bucharest
EU	Europe	Europe	RS	SRB	Serbie	Serbia	Belgrade
EU	Europe	Europe	RU	RUS	Russie	Russian Federation	Moscow
EU	Europe	Europe	SE	SWE	Suède	Sweden	Stockholm
EU	Europe	Europe	SI	SVN	Slovénie	Slovenia	Ljubljana
EU	Europe	Europe	SJ	SJM	Svalbard et Île Jan Mayen	Svalbard and Jan Mayen	Longyearbyen
EU	Europe	Europe	SK	SVK	Slovaquie	Slovakia	Bratislava
EU	Europe	Europe	SM	SMR	Saint-Marin	San Marino	San Marino
EU	Europe	Europe	UA	UKR	Ukraine	Ukraine	Kiev
EU	Europe	Europe	VA	VAT	Saint-Siège (État De La Cité Du Vatican)	Holy See	Vatican City
NA	Amérique du Nord	North America	AG	ATG	Antigua-Et-Barbuda	Antigua and Barbuda	St. John's
NA	Amérique du Nord	North America	AI	AIA	Anguilla	Anguilla	The Valley
NA	Amérique du Nord	North America	AW	ABW	Aruba	Aruba	Oranjestad
NA	Amérique du Nord	North America	BB	BRB	Barbade	Barbados	Bridgetown
NA	Amérique du Nord	North America	BL	BLM	Saint-Barthélemy	Saint Barthélemy	Gustavia
NA	Amérique du Nord	North America	BM	BMU	Bermudes	Bermuda	Hamilton
NA	Amérique du Nord	North America	BQ	BES	Pays-Bas Caribéens	Bonaire, Sint Eustatius and Saba	
NA	Amérique du Nord	North America	BS	BHS	Bahamas	Bahamas	Nassau
NA	Amérique du Nord	North America	BZ	BLZ	Belize	Belize	Belmopan
NA	Amérique du Nord	North America	CA	CAN	Canada	Canada	Ottawa
NA	Amérique du Nord	North America	CR	CRI	Costa Rica	Costa Rica	San Jose
NA	Amérique du Nord	North America	CU	CUB	Cuba	Cuba	Havana
NA	Amérique du Nord	North America	CW	CUW	Curaçao	Curaçao	Willemstad
NA	Amérique du Nord	North America	DM	DMA	Dominique	Dominica	Roseau
NA	Amérique du Nord	North America	DO	DOM	République Dominicaine	Dominican Republic	Santo Domingo
NA	Amérique du Nord	North America	GD	GRD	Grenade	Grenada	St. George's
NA	Amérique du Nord	North America	GL	GRL	Groenland	Greenland	Nuuk
NA	Amérique du Nord	North America	GP	GLP	Guadeloupe	Guadeloupe	Basse-Terre
NA	Amérique du Nord	North America	GT	GTM	Guatemala	Guatemala	Guatemala City
NA	Amérique du Nord	North America	HN	HND	Honduras	Honduras	Tegucigalpa
NA	Amérique du Nord	North America	HT	HTI	Haïti	Haiti	Port-au-Prince
NA	Amérique du Nord	North America	JM	JAM	Jamaïque	Jamaica	Kingston
NA	Amérique du Nord	North America	KN	KNA	Saint-Christophe-Et-Niévès	Saint Kitts and Nevis	Basseterre
NA	Amérique du Nord	North America	KY	CYM	Îles Caïmans	Cayman Islands	George Town
NA	Amérique du Nord	North America	LC	LCA	Sainte-Lucie	Saint Lucia	Castries
NA	Amérique du Nord	North America	MF	MAF	Saint-Martin	Saint Martin (French)	Marigot
NA	Amérique du Nord	North America	MQ	MTQ	Martinique	Martinique	Fort-de-France
NA	Amérique du Nord	North America	MS	MSR	Montserrat	Montserrat	Plymouth
NA	Amérique du Nord	North America	MX	MEX	Mexique	Mexico	Mexico City
NA	Amérique du Nord	North America	NI	NIC	Nicaragua	Nicaragua	Managua
NA	Amérique du Nord	North America	PA	PAN	Panama	Panama	Panama City
NA	Amérique du Nord	North America	PM	SPM	Saint-Pierre-Et-Miquelon	Saint Pierre and Miquelon	Saint-Pierre
NA	Amérique du Nord	North America	PR	PRI	Porto Rico	Puerto Rico	San Juan
NA	Amérique du Nord	North America	SV	SLV	Salvador	El Salvador	San Salvador
NA	Amérique du Nord	North America	SX	SXM	Sint Maarten	Sint Maarten (Dutch)	Philipsburg
NA	Amérique du Nord	North America	TC	TCA	Îles Turques-Et-Caïques	Turks and Caicos Islands	Cockburn Town
NA	Amérique du Nord	North America	TT	TTO	Trinité-Et-Tobago	Trinidad and Tobago	Port of Spain
NA	Amérique du Nord	North America	US	USA	États-Unis	United States of America	Washington
NA	Amérique du Nord	North America	VC	VCT	Saint-Vincent-Et-Les Grenadines	Saint Vincent and the Grenadines	Kingstown
NA	Amérique du Nord	North America	VG	VGB	Îles Vierges Britanniques	Virgin Islands (British)	Road Town
NA	Amérique du Nord	North America	VI	VIR	Îles Vierges Des États-Unis	Virgin Islands (U.S.)	Charlotte Amalie
OC	Océanie	Oceania	AS	ASM	Samoa Américaines	American Samoa	Pago Pago
OC	Océanie	Oceania	AU	AUS	Australie	Australia	Canberra
OC	Océanie	Oceania	CK	COK	Îles Cook	Cook Islands	Avarua
OC	Océanie	Oceania	FJ	FJI	Fidji	Fiji	Suva
OC	Océanie	Oceania	FM	FSM	Micronésie	Micronesia	Palikir
OC	Océanie	Oceania	GU	GUM	Guam	Guam	Hagatna
OC	Océanie	Oceania	KI	KIR	Kiribati	Kiribati	Tarawa
OC	Océanie	Oceania	MH	MHL	Îles Marshall	Marshall Islands	Majuro
OC	Océanie	Oceania	MP	MNP	Îles Mariannes Du Nord	Northern Mariana Islands	Saipan
OC	Océanie	Oceania	NC	NCL	Nouvelle-Calédonie	New Caledonia	Noumea
OC	Océanie	Oceania	NF	NFK	Île Norfolk	Norfolk Island	Kingston
OC	Océanie	Oceania	NR	NRU	Nauru	Nauru	Yaren
OC	Océanie	Oceania	NU	NIU	Niue	Niue	Alofi
OC	Océanie	Oceania	NZ	NZL	Nouvelle-Zélande	New Zealand	Wellington
OC	Océanie	Oceania	PF	PYF	Polynésie Française	French Polynesia	Papeete
OC	Océanie	Oceania	PG	PNG	Papouasie-Nouvelle-Guinée	Papua New Guinea	Port Moresby
OC	Océanie	Oceania	PN	PCN	Îles Pitcairn	Pitcairn	Adamstown
OC	Océanie	Oceania	PW	PLW	Palaos	Palau	Melekeok
OC	Océanie	Oceania	SB	SLB	Salomon	Solomon Islands	Honiara
OC	Océanie	Oceania	TK	TKL	Tokelau	Tokelau	
OC	Océanie	Oceania	TL	TLS	Timor Oriental	Timor-Leste	Dili
OC	Océanie	Oceania	TO	TON	Tonga	Tonga	Nuku'alofa
OC	Océanie	Oceania	TV	TUV	Tuvalu	Tuvalu	Funafuti
OC	Océanie	Oceania	UM	UMI	Îles Mineures Éloignées Des États-Unis	United States Minor Outlying Islands	
OC	Océanie	Oceania	VU	VUT	Vanuatu	Vanuatu	Port Vila
OC	Océanie	Oceania	WF	WLF	Wallis-Et-Futuna	Wallis and Futuna	Mata Utu
OC	Océanie	Oceania	WS	WSM	Samoa	Samoa	Apia
SA	Amérique du Sud	South America	AR	ARG	Argentine	Argentina	Buenos Aires
SA	Amérique du Sud	South America	BO	BOL	Bolivie	Bolivia	Sucre
SA	Amérique du Sud	South America	BR	BRA	Brésil	Brazil	Brasilia
SA	Amérique du Sud	South America	CL	CHL	Chili	Chile	Santiago
SA	Amérique du Sud	South America	CO	COL	Colombie	Colombia	Bogota
SA	Amérique du Sud	South America	EC	ECU	Équateur	Ecuador	Quito
SA	Amérique du Sud	South America	FK	FLK	Malouines	Falkland Islands	Stanley
SA	Amérique du Sud	South America	GF	GUF	Guyane	French Guiana	Cayenne
SA	Amérique du Sud	South America	GY	GUY	Guyana	Guyana	Georgetown
SA	Amérique du Sud	South America	PE	PER	Pérou	Peru	Lima
SA	Amérique du Sud	South America	PY	PRY	Paraguay	Paraguay	Asuncion
SA	Amérique du Sud	South America	SR	SUR	Suriname	Suriname	Paramaribo
SA	Amérique du Sud	South America	UY	URY	Uruguay	Uruguay	Montevideo
SA	Amérique du Sud	South America	VE	VEN	Venezuela	Venezuela	Caracas";



	$xls	= str_replace( "\r" , "" , $xls );
	$xls	= explode( "\n" , $xls );
	$data	= array();
	
	foreach( $xls as $pays )
	  {
	  	$pays = explode( "\t" , $pays );
	  	
	  	$continent		= $pays[0];
	  	$continent_fr	= $pays[1];
	  	$continent_en	= $pays[2];
	  	$alpha2 		= $pays[3];
	  	$alpha3		= $pays[4];
	  	$nom_fr		= $pays[5];
	  	$nom_en		= $pays[6];
	  	$capitale		= $pays[7];
	
		$data[ $continent ]["continent_fr"] 	= $continent_fr;
		$data[ $continent ]["continent_en"] 	= $continent_en;		
		$data[ $continent ]["pays"][ $alpha2 ] 	= array(
										"alpha2"		=> $alpha2,
										"alpha3"		=> $alpha3,
										"continent_fr"	=> $continent_fr,
										"continent_en"	=> $continent_en,
										"nom_fr"		=> $nom_fr,
										"nom_en"		=> $nom_en,
										"capitale"		=> $capitale
									  );
	  }







	if( $wat !== false )
	  {
	  	$pays = array();

	  	foreach( $data as $continent )
	  	  {
	
	  		$pays = array_merge( $pays , $continent["pays"] );
		  }

		asort( $pays );

	  	if( $wat !== true )
		  {
			if( strpos( $wat , ":" ) !== false )
			  {
			  	$return	= preg_match( "#sort#" , $return ) ? "" : $return;
		  	  	$wat 		= explode( ":" , $wat );
		  	  	$tmp 		= array();
	
				foreach( $wat as $i => $code )
				  {
				  	if( array_key_exists( $code , $pays ) )
				  	  {
						$tmp[ $code ] = ( ( $return != null ) AND array_key_exists( $return , $pays[ $code ] ) ) ? $pays[ $code ][ $return ] : $pays[ $code ];
				  	  }
				  }

				$pays = $tmp;

			  }
			else
			  {
				$code = strtoupper($wat);
				$pays = isset($pays[$code]) ? ( ( ( $return != null ) AND array_key_exists( $return , $pays[ $code ] ) ) ? $pays[ $code ][ $return ] : $pays[ $code ] ) : "";
			  }
		  }
		else
		  {
		  	if( $return !== false )
		  	  {
				if( preg_match( "#^sort:(".implode( "|" , $colonnes_de_tri ).")$#" , $return , $tri ) )
				  {
					$tri	= $tri[1];
				  }
				else
				  {
					$tri	= "nom_fr";
				  }
				  
				$tmp	= array();

				foreach( $pays as $alpha2 => $details )
				  {
				  	$tmp[ $details[ $tri ] ] = $alpha2;
				  }

				ksort( $tmp );
				$tmp2	= array();
				
				foreach( $tmp as $alpha2 )
				  {
				  	$tmp2[ $alpha2] = $pays[ $alpha2 ];
				  }

				$pays = $tmp2;  		  	  	
		  	  }
		  }
	  }

	else
	  {
	  	$pays = array();
	
		if( ( $return !== false ) AND preg_match( "#^sort:(".implode( "|" , $colonnes_de_tri ).")$#" , $return , $tri ) )
		  {
			$tri	= $tri[1];
		  }
		else
		  {
			$tri	= "nom_fr";
		  }

		foreach( $data as $code => $continent )
		  {
			$pays[ $code ] = $data[ $code ];
			$pays[ $code ]["pays"] = array();
			
			
			$tmp	= array();

			foreach( $continent["pays"] as $alpha2 => $details )
			  {
			  	$tmp[ $details[ $tri ] ] = $alpha2;
			  }
			 
			ksort( $tmp );

			foreach( $tmp as $alpha2 )
			  {
			  	$pays[ $code ]["pays"][ $alpha2 ] = $continent["pays"][ $alpha2 ];
			  }
		  }
	  }
	
	return $pays;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- DETECT LA PRESENCE D'UNE ENTETE DO NOT TRACK */
public static function do_not_track()
  {
	$dnt = null;

  	if( isset($_SERVER) )
	  {
		if( isset($_SERVER["HTTP_DNT"]) AND ( $_SERVER["HTTP_DNT"] === "1" ) )
		  {
			return true;
		  }
		else if( isset($_SERVER["HTTP_DNT"]) AND ( $_SERVER["HTTP_DNT"] === "0" ) )
		  {
			return false;
		  }
	  }

	return $dnt;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- TEST SI LA CHAINE EST DE TYPE MD5 */
public static function is_md5( $txt )
  {
 	return (bool) preg_match( "#^[0-9a-f]{32}$#i" , $txt );
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- TEST SI LA CHAINE EST DE TYPE SHA1 */
public static function is_sha1( $txt )
  {
 	return (bool) preg_match( "#^[0-9a-f]{40}$#i" , $txt );
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- FORCE LE TELECHARGEMENT D'UN FICHIER */
public static function dl( $file , $filename , $force = true )
  {

	if( is_file( $file ) AND is_readable($file) )
	  {
	  	$size 	= filesize( $file );
		$time		= date( "r" , filemtime($file) );
		$mime_type	= self::mime_type( $file );


		/* ------------------------------------------------------------------------- Découpe du fichier par range */

		if( isset($_SERVER["HTTP_RANGE"]) )
		  {
			$fichier	= fopen( $file, "rb" );
			$start	= 0;
			$end 		= $size - 1;
			$c_start	= $start;
			$c_end 	= $end;

			header( "Content-type: ".$mime_type);
			header( "Accept-Ranges: bytes" );
			
			list( , $range) = explode( "=" , $_SERVER["HTTP_RANGE"] , 2 );

			if( strpos($range, ",") !== false )
			  {
				header( "HTTP/1.1 416 Requested Range Not Satisfiable" );
				header( "Content-Range: bytes $start-$end/$size" );
				exit;
			  }
			
			if( $range == "-" )
			  {
				$c_start = $size - substr( $range , 1 );
			  }
			else
			  {
				$range 	= explode( "-" , $range );
				$c_start 	= $range[0];
				$c_end 	= ( isset($range[1]) && is_numeric($range[1]) ) ? $range[1] : $size;
			}
			  
			$c_end = ($c_end > $end) ? $end : $c_end;

			if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size)
			  {
				header( "HTTP/1.1 416 Requested Range Not Satisfiable" );
				header( "Content-Range: bytes $start-$end/$size" );
				exit;
			  }
			
			$start	= $c_start;
			$end		= $c_end;
			$length	= $end - $start + 1;

			fseek($fichier, $start);


			header( "HTTP/1.1 206 Partial Content");
			header( "Content-Range: bytes $start-$end/$size" );
			header( "Content-Length: ".$length );
			$buffer = 1024 * 8;
			
			while( !feof($fichier) && ($p = ftell($fichier) ) <= $end )
			  {
				if ($p + $buffer > $end)
				  {
					$buffer = $end - $p + 1;
				  }
			
				set_time_limit(0);
				echo fread($fichier, $buffer);
				flush();
			  }

			fclose($fichier);
			exit();

		  }

		/* ------------------------------------------------------------------------- Envoi du fichier */
		
		else
		  {
		    	if( $force === true )
		  	  {
			  	header( "Content-Type: application/force-download; name=\"".$filename."\"" ); 
				header( "Content-Disposition: attachment; filename=\"".$filename."\"" );
		  	  }
			else
		  	  {
			  	header( "Content-Type: ".$mime_type."; name=\"".$filename."\"" ); 
				header( "Content-Disposition: inline; filename=\"".$filename."\"" );
		  	  }
		
			header( "Content-Transfer-Encoding: binary" );
			header( "Content-Length: ".$size );
			header( "Last-Modified: $time" );
			readfile( $file );
		  	exit;
		  }

	  }

	else
	  {
	  	return false;
	  }
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE DU FAUX TEXTE LOREM UPSUM */
public static function lorem_ipsum( $nb_words=0 , $lorem_first = true , $paragraphes = 1 )
  {
	$text 	= ( $lorem_first === true ) ? "Lorem ipsum dolor sit amet " : "";
	$data 	= array( "a" , "ac" , "accumsan" , "ad" , "adipiscing" , "aenean" , "aliquam" , "aliquet" , "amet" , "ante" , "aptent" , "arcu" , "at" , "auctor" , "augue" , "bibendum" , "blandit" , "class" , "commodo" , "condimentum" , "congue" , "consectetur" , "consequat" , "conubia" , "convallis" , "cras" , "cubilia" , "cum" , "curabitur" , "curae" , "cursus" , "dapibus" , "diam" , "dictum" , "dictumst" , "dignissim" , "dis" , "dolor" , "donec" , "dui" , "duis" , "egestas" , "eget" , "eleifend" , "elementum" , "elit" , "enim" , "erat" , "eros" , "est" , "et" , "etiam" , "eu" , "euismod" , "facilisi" , "facilisis" , "fames" , "faucibus" , "felis" , "fermentum" , "feugiat" , "fringilla" , "fusce" , "gravida" , "habitant" , "habitasse" , "hac" , "hendrerit" , "himenaeos" , "iaculis" , "id" , "imperdiet" , "in" , "inceptos" , "integer" , "interdum" , "ipsum" , "justo" , "lacinia" , "lacus" , "laoreet" , "lectus" , "leo" , "libero" , "ligula" , "litora" , "lobortis" , "lorem" , "luctus" , "maecenas" , "magna" , "magnis" , "malesuada" , "massa" , "mattis" , "mauris" , "metus" , "mi" , "molestie" , "mollis" , "montes" , "morbi" , "mus" , "nam" , "nascetur" , "natoque" , "nec" , "neque" , "netus" , "nibh" , "nisi" , "nisl" , "non" , "nostra" , "nulla" , "nullam" , "nunc" , "odio" , "orci" , "ornare" , "parturient" , "pellentesque" , "penatibus" , "per" , "pharetra" , "phasellus" , "placerat" , "platea" , "porta" , "porttitor" , "posuere" , "potenti" , "praesent" , "pretium" , "primis" , "proin" , "pulvinar" , "purus" , "quam" , "quis" , "quisque" , "rhoncus" , "ridiculus" , "risus" , "rutrum" , "sagittis" , "sapien" , "scelerisque" , "sed" , "sem" , "semper" , "senectus" , "sit" , "sociis" , "sociosqu" , "sodales" , "sollicitudin" , "suscipit" , "suspendisse" , "taciti" , "tellus" , "tempor" , "tempus" , "tincidunt" , "torquent" , "tortor" , "tristique" , "turpis" , "ullamcorper" , "ultrices" , "ultricies" , "urna" , "ut" , "varius" , "vehicula" , "vel" , "velit" , "venenatis" , "vestibulum" , "vitae" , "vivamus" , "viverra" , "volutpat" , "vulputate" );
	$nb_data	= count( $data );

	srand((double)microtime()*1000000);

	if( is_numeric($paragraphes) AND ( $paragraphes > 1 ) )
	  {
		for( $p = 0 ; $p < $paragraphes ; $p++ )
		  {
			for( $i = 0 ; $i < $nb_words ; $i++ )
			  {
				$text .= $data[ rand() % $nb_data ]." ";
			  }
			$text .= "<br /><br />";
		  }
	  }
	else
	  {
		for( $i = 0 ; $i < $nb_words ; $i++ )
		  {
			$text .= $data[ rand() % $nb_data ]." ";
		  }
	  }


	return $text;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE UNE PLAGE DE DATE AU FORMAT TEXTE */
public static function date_range( $date_debut , $date_fin )
  {
  	$date			= "";
	$date_debut		= trim( $date_debut );
	$date_fin		= trim( $date_fin );
	
	if( ( $date_debut != $date_fin ) OR ( $date_debut != "" ) )
	  {
		if( is_numeric( $date_debut ) )
		  {
			$date_debut		= ( $date_debut > 0 ) ? date( "d/m/Y" , $date_debut ) : "";
		  }

		if( is_numeric( $date_fin ) )
		  {
			$date_fin		= ( $date_fin > 0 ) ? date( "d/m/Y" , $date_fin ) : "";
		  }	


		if( $date_debut == $date_fin )
		  {
		  	$date .= "Le ".$date_debut;
		  }
		else
		  {
			if( $date_debut != "" )
			  {
			  	$date .= ( $date_fin == "" ? "À partir du" : "du" )." ".$date_debut." ";
			  }

			if( $date_fin != "" )
			  {
			  	$date .= ( $date_debut == "" ? "jusqu'au" : "au" )." ".$date_fin;
			  }
		  }
	  }
	

	return $date;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- REMPLACE UN BOUTON DE SELECTION DE FICHIER POUR L'UPLOAD */
public static function select_file( $options = array() )
  {
	
	/* ----------------------------------------------------------------------------------- */
	
	$id = "file_". self::random( 12 , 2 );
	
	/* ----------------------------------------------------------------------------------- */
	
	$default = array( 
		"id"			=> $id,
		"name" 		=> $id,
		"class_button" 	=> "select_file_button",
		"class_status" 	=> "select_file_status",
		"text_button"	=> "Sélectionnez un fichier",
		"text_status"	=> "",
		"js"			=> null
	);



	$options = is_array($options) ? array_merge( $default , $options ) : $default;
	if( $options["js"] === null )
	  {
		$options["js"] = "$( '#".$options["id"]."' ).click().change(function(){ $( '#".$options["id"]."_status' ).html( $(this).val() ); });";
	  }

	/* ----------------------------------------------------------------------------------- */

	$data = "
	<input id='".$options["id"]."' type='file' name='".$options["name"]."' style='display:none;' />
	<input id='".$options["id"]."_button' type='button' class='".$options["class_button"]."' value=\"".$options["text_button"]."\" onclick=\"".$options["js"]."\" />
	<span id='".$options["id"]."_status' class='".$options["class_status"]."'>".$options["text_status"]."</span>";
	
	
	/* ----------------------------------------------------------------------------------- */
	
	return $data;
	
	/* ----------------------------------------------------------------------------------- */
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- DONNE LA DIRECTION CORRESPONDANT A UN POINT CARDINAL */
public static function compass( $degres , $precis=false , $english=false )
  {
	$direction	= "";
	$ouest	= $english ? "W" : "O";

	if( $precis )
	  {
		$pas		= 22.5;
		$compass	= array( "N" , "NNE" , "NE" , "NEE" , "E" , "SEE" , "SE" , "SSE" , "S" , "SS".$ouest , "S".$ouest , "S".$ouest.$ouest , $ouest , "N".$ouest.$ouest , "N".$ouest , "NN".$ouest , "N" );
	  }
	else
	  {
		$pas		= 45;
		$compass	= array( "N" , "NE" , "E" , "SE" , "S" , "S".$ouest , $ouest , "N".$ouest , "N" );
	  }


	if( ( $degres >= 0 ) AND ( $degres <= 360 ) )
	  {
		$direction	= $compass[ round( $degres / $pas ) ];
	  }

	return $direction;

  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- RECUPERE L'URL DE LA MINIATURE D'UNE VIDEO YOUTUBE */
public static function youtube_thumbnail( $video_id , $format = "default" )
  {	
	$formats = array(
				"1",
				"2",
				"3",
				"default",
				"hqdefault",
				"mqdefault",
				"sddefault",
				"maxresdefault"
		     );

	$format	= in_array( $format , $formats ) ? $format : "default";
	$url		= "http://img.youtube.com/vi/{video_id}/{format}.jpg";

	if( self::check_url( $video_id ) AND preg_match( '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i' , $video_id , $match ) )
	  {
		$video_id = $match[1];
	  }
	else if( preg_match( "#^([a-z0-9]){6,16}$#i" , $video_id ) )
	  {
		$video_id = $video_id;
	  }
	else
	  {
		$url = "";
	  }

	$url	= str_replace( "{video_id}" , $video_id , $url );
	$url	= str_replace( "{format}" , $format , $url );

	return $url;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- SUPPRIME LES RETOURS EN ARRIERE DE REPERTOIRES */
public static function dot_dot_slash( $data )
  {
	return str_replace( "../", "" , $data );
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- CONVERTIT UN TABLEU ( key / value ) EN CSS INLINE  */
public static function array_to_css( $arr )
  {
  	return is_array($arr) ? implode( ";" , array_map( function ( $v , $k ) { return $k.":".$v; } , $arr , array_keys($arr) ) ).";" : "";
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- CRÉE UNE ICONE EN FONCTION D'UNE EXTENSION */
public static function icone_ext( $ext , $width=32 , $color="auto" , $text_color="FFFFFF" )
  {

	if( $color == "auto" )
	  {
		if( preg_match( "#^(jpg|jpeg|pjpeg|png|gif|bmp|gif|png)$#i" , $ext ) )
		  {
			$color = "487200";
		  }
		else if( preg_match( "#^(pdf)$#i" , $ext ) )
		  {
			$color = "DB0000";
		  }
		else if( preg_match( "#^(zip|rar|7z)$#i" , $ext ) )
		  {
			$color = "f9b847";
		  }
		else if( preg_match( "#^(ai|eps)$#i" , $ext ) )
		  {
			$color = "EAA800";
		  }
		else if( preg_match( "#^(xls|xlsx|numbers)$#i" , $ext ) )
		  {
			$color = "029B00";
		  }
		else
		  {
			$color = "005599";
		  }	  	  	
	  }
  
	$couleur1	= "rgba( ".self::hexa_to_rgb( $color , " , " )." , 1 );";
	$couleur2	= "rgba( ".self::hexa_to_rgb( self::assombrir( $color , 1.3 ) , " , " )." , 1 );";
	
  	$text_color	= ( $text_color[0] != "#" ? "#" : "" ).$text_color;
	$coef		= 1.2222222222;
	$height	= $width * $coef;
	$size		= $width * ( ( strlen( $ext ) > 3 ? 32 : 36 ) / 100 );
	$base64	= base64_encode( "<?xml version='1.0' encoding='utf-8'?>
<svg version='1.1'
id='C1' xmlns:rdf='http://www.w3.org/1999/02/22-rdf-syntax-ns#' xmlns:dc='http://purl.org/dc/elements/1.1/' xmlns:cc='http://creativecommons.org/ns#'
xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='-282 409.9 18 22'
style='enable-background:new -282 409.9 18 22;' xml:space='preserve'>
<style type='text/css'>
.st0{fill:".$couleur1."}
.st1{fill:".$couleur2."}
</style>
<g transform='translate(0 -1028.4)'>
<path class='st0' d='M-280.9,1438.3c-0.6,0-1.1,0.5-1.1,1.1v8.9v4v6.9c0,0.6,0.5,1.1,1.1,1.1h15.7c0.6,0,1.1-0.5,1.1-1.1v-6.9v-4v-4l-6-6H-280.9z'/>
<path class='st1' d='M-264,1444.3l-6-6v4c0,1.1,0.9,2,2,2H-264z'/>
</g>
</svg>" );
	
	$css 		= array(
				"display"			=> "inline-block",
				"width"			=> $width."px",
				"height"			=> $height."px",
				"line-height"		=> $height."px",
				"font-family" 		=> "Arial,sans-serif",
				"font-weight"		=> "600",
				"font-style"		=> "normal",
				"font-size" 		=> $size."px",
				"text-transform"		=> "uppercase",
				"color" 			=> $text_color,
				"text-align"		=> "center",
				"vertical-align"		=> "middle",
				"overflow"			=> "hidden",
				
				"background-size"		=> "cover",
				"background-position"	=> "center center",
				"background-image"	=> "url(data:image/svg+xml;base64,".$base64.")"
		 	 );
	
	return "<i class='icone_ext' style='".self::array_to_css( $css )."'>".$ext."</i>";
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- CONVERTIT LES FINS DE LIGNE EN VERSION JAVASCRIPT */
public static function js_multilines( $data )
  {
	return preg_replace( "/\r\n|\r|\n/" , "\\\n" , $data );
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- AMELIORE LA LISIBILITÉ D'UN NOMBRE */
public static function number( $number , $decimales=null )
  {
	return number_format( $number , $decimales , "." , " " );
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- CONVERTIT UNE TEXTE POUR LA BALISE DESCRIPTION OG */
public static function og_description( $texte )
  {
	return functions::raw_text( html_entity_decode( $texte , ENT_QUOTES , "UTF-8" ) , 250 );
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- GENERE UN DÉGRADÉ ENTRE 2 COULEURS */
public static function degrade_couleurs( $options = array() )
  {
	/* --------------------------- */
	$default = array( 
		"from"	=> "FFFFFF",
		"to" 		=> "000000",
		"steps" 	=> 10,
		"return" 	=> "hexa"
	);

	$options = is_array($options) ? array_merge( $default , $options ) : $default;

	/* --------------------------- */

	$gradient	= array();

	$options["from"]		= ( !empty($options["from"]) AND preg_match( "#^([0-9a-z]{6})$#i", $options["from"] ) ) ? hexdec( $options["from"] ) : 0x000000;
	$options["from"]		= ( ( $options["from"] >= 0x000000 ) AND ( $options["from"] <= 0xffffff ) ) ? $options["from"] : 0x000000;
	
	$options["to"]		= ( !empty($options["to"]) AND preg_match( "#^([0-9a-z]{6})$#i", $options["to"] ) ) ? hexdec( $options["to"] ) : 0xffffff;
	$options["to"]		= ( ( $options["to"] >= 0x000000 ) AND ( $options["to"] <= 0xffffff ) ) ? $options["to"] : 0xffffff;

	$options["steps"]		= ( !empty($options["steps"]) AND is_numeric( $options["steps"] ) AND ( $options["steps"] > 0 ) AND ( $options["steps"] < 256 ) ) ? $options["steps"] : 10;

	/* --------------------------- */
	
	$R0 = ( $options["from"] & 0xff0000 ) >> 16;
	$G0 = ( $options["from"] & 0x00ff00 ) >> 8;
	$B0 = ( $options["from"] & 0x0000ff ) >> 0;

	$R1 = ( $options["to"] & 0xff0000 ) >> 16;
	$G1 = ( $options["to"] & 0x00ff00 ) >> 8;
	$B1 = ( $options["to"] & 0x0000ff ) >> 0;	

	/* --------------------------- */

	for( $i = 0 ; $i <= $options["steps"] ; $i++ )
	  {
		$R = self::degrade_couleurs_interpolate( $R0 , $R1 , $i , $options["steps"] );
		$G = self::degrade_couleurs_interpolate( $G0 , $G1 , $i , $options["steps"] );
		$B = self::degrade_couleurs_interpolate( $B0 , $B1 , $i , $options["steps"] );

		if( $options["return"] == "hexa" )
		  {
			$R = sprintf( "%02x" , $R );
			$G = sprintf( "%02x" , $G );
			$B = sprintf( "%02x" , $B );

		  	$val = $R.$G.$B;
		  }
		else if( $options["return"] == "rgb" )
		  {
		  	$val = array( floor($R) , floor($G) , floor($B) );
		  }
		else
		  {
			$val = ( ( ( $R << 8) | $G) << 8) | $B;
		  }

		$gradient[] = $val;
	  }
	  
	/* --------------------------- */
	return $gradient;
	
	/* --------------------------- */
  }


public static function degrade_couleurs_interpolate( $start , $end , $step , $max )
  {  	
	if( $start < $end )
	  {
		return ( ( $end - $start ) * ( $step / $max ) ) + $start;
	  }
	else
	  {
		return ( ( $start - $end ) *  (1 - ( $step / $max ) ) ) + $end;
	  }
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- GENERE UN NUMÉRO DE COMMANDE */
public static function numero_de_commande( $time=null )
  {
  	$date	= !is_null( $time ) ? $time : time();
	$chr	= array_merge( range( "A" , "Z" ) , range( "1" , "9" ) , str_split( self::random( 26 , 6 ) ) );

	$num  = date( "y" );									/* Année :		xx */
	$num .= str_pad( date( "z" , $date ) , 3 , "0" , STR_PAD_LEFT );		/* Jour : 		1 - 366 */
	$num .= $chr[ date( "H" , $date ) - 1 ];						/* Heures :		1 - 24 */
	$num .= date( "i" , $date );								/* Minutes : 	1 - 60 */
	$num .= $chr[ date( "s" , $date ) - 1 ];						/* Secondes : 	1 - 60 */
	$num .= self::random( 4 , 5 );							/* Random x4 */

	return $num;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE UNE COULEUR ASSOMBRIE */
public static function assombrir( $couleur , $coef = 2 )
  {
  	$type		= "";
  	$couleur	= str_replace( " " , "" , $couleur );
  
	if( preg_match( "#^([0-9a-f]{6})$#i" , $couleur ) )
	  {
  		$type		= "hexa";
	  	$couleur	= self::hexa_to_rgb( $couleur );
	  }
	else if( preg_match( "#^([0-9]{1,3}),([0-9]{1,3}),([0-9]{1,3})$#i" , $couleur  ) )
	  {
  		$type		= "rgb";
	  	$couleur	= explode( "," , $couleur );
	  }
	else
	  {
		return $couleur;
	  }


	foreach( $couleur as $id => $num )
	  {
		$couleur[ $id ] = ( $num > 0 ) ? floor( $num / $coef ) : 0;
	  }

	$couleur = ( $type == "hexa" ) ? self::rgb_to_hexa( $couleur ) : implode( " , " , $couleur );

	return $couleur;
  }
  


/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE UNE COULEUR ECLAIRCIE */
public static function eclaircir( $couleur , $coef = 2 )
  {
  	$type		= "";
  	$couleur	= str_replace( " " , "" , $couleur );
  
	if( preg_match( "#^([0-9a-f]{6})$#i" , $couleur ) )
	  {
  		$type		= "hexa";
	  	$couleur	= self::hexa_to_rgb( $couleur );
	  }
	else if( preg_match( "#^([0-9]{1,3}),([0-9]{1,3}),([0-9]{1,3})$#i" , $couleur  ) )
	  {
  		$type		= "rgb";
	  	$couleur	= explode( "," , $couleur );
	  }
	else
	  {
		return $couleur;
	  }


	foreach( $couleur as $id => $num )
	  {
		$couleur[ $id ] = ( floor( $num * $coef ) < 255 ) ? floor( $num * $coef ) : 255;
	  }

	$couleur = ( $type == "hexa" ) ? self::rgb_to_hexa( $couleur ) : implode( " , " , $couleur );

	return $couleur;
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LA VALEUR MAXI POUR L'UPLOAD */
public static function max_upload_size( $human = false )
  {
	$max_size		= self::parse_max_upload_size( ini_get( "post_max_size" ) );
	$upload_max		= self::parse_max_upload_size( ini_get( "upload_max_filesize" ) );
	
	if( $upload_max > 0 && $upload_max < $max_size )
	  {
		$max_size = $upload_max;
	  }

	return ( $human ===true ) ? self::file_size( $max_size ) : $max_size;
  }
	
public static function parse_max_upload_size( $size )
  {
	$unit = preg_replace( "/[^bkmgtpezy]/i" , "" , $size) ;
	$size = preg_replace( "/[^0-9\.]/" , "" , $size );

	if ($unit)
	  {
		return round( $size * pow( 1024 , stripos( "bkmgtpezy" , $unit[0] ) ) );
	  }
	else
	  {
		return round( $size );
	  }
  }




/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LA DESCRIPTION D'UN CODE DE STATUT COMMANDE */
public static function code_commande( $code = null , $simple = false )
  {
  	if( $simple == true )
  	  {
		$codes = array(
				0 	=> "En attente de paiement",
				1 	=> "Annulé avant le paiement",
				2 	=> "Annulé lors du paiement",
				3	=> "Autorisation de paiement refusée",
				10 	=> "Validé",
				11 	=> "En attente de validation",
				20 	=> "Traitement en cours...",
				30 	=> "Expédié",
				59 	=> "Test",
				90	=> "Executable response non trouvé",
				91	=> "Erreur d'appel API",
				99 	=> "Erreur"
		  );
  	  }
	else
  	  {
		$codes = array(
				0 	=> "La commande est en attente de paiement",
				1 	=> "La commande a été annulée avant le paiement",
				2 	=> "La commande a été annulée lors du paiement",
				3	=> "Autorisation de paiement refusée",
				10 	=> "La commande est validée",
				11 	=> "La commande est en attente de validation",
				20 	=> "Traitement en cours...",
				30 	=> "Expédiée",
				59 	=> "Test de commande",
				90	=> "Executable response non trouvé",
				91	=> "Erreur d'appel API",
				99 	=> "Erreur de commande"
		  );
  	  }


	if( $code === null )
	  {
	  	return $codes;
	  }
	else if( array_key_exists( $code , $codes ) )
	  {
	  	return $codes[ $code ];
	  }
	else
	  {
	  	return "";
	  }
  }





/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE UN NOMBRE D'ESPACES */
public static function spaces( $nb = 1 , $html = true )
  {
  	return str_repeat( ( $html ? "&nbsp;" : "" ) , ( is_numeric($nb) ? $nb : 1 ) );
  }





/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LA LISTE DES PAYS DES LANGUES ( CODE ISO 2 ) */
public static function langue( $wat = null )
  {
	$wat = !empty($wat) ? strtolower($wat) : $wat;

	$langues = array(
"aa" => "Afar",
"ab" => "Abkhaze",
"ae" => "Avestique",
"af" => "Afrikaans",
"ak" => "Akan",
"am" => "Amharique",
"an" => "Aragonais",
"ar" => "Arabe",
"as" => "Assamais",
"av" => "Avar",
"ay" => "Aymara",
"az" => "Azéri",
"ba" => "Bachkir",
"be" => "Biélorusse",
"bg" => "Bulgare",
"bh" => "Bihari",
"bi" => "Bichelamar",
"bm" => "Bambara",
"bn" => "Bengali",
"bo" => "Tibétain",
"br" => "Breton",
"bs" => "Bosnien",
"ca" => "Catalan",
"ce" => "Tchétchène",
"ch" => "Chamorro",
"co" => "Corse",
"cr" => "Cri",
"cs" => "Tchèque",
"cu" => "Vieux-slave",
"cv" => "Tchouvache",
"cy" => "Gallois",
"da" => "Danois",
"de" => "Allemand",
"dv" => "Maldivien",
"dz" => "Dzongkha",
"ee" => "Ewe",
"el" => "Grec moderne",
"en" => "Anglais",
"eo" => "Espéranto",
"es" => "Espagnol",
"et" => "Estonien",
"eu" => "Basque",
"fa" => "Persan",
"ff" => "Peul",
"fi" => "Finnois",
"fj" => "Fidjien",
"fo" => "Féroïen",
"fr" => "Français",
"fy" => "Frison",
"ga" => "Irlandais",
"gd" => "Écossais",
"gl" => "Galicien",
"gn" => "Guarani",
"gu" => "Gujarati",
"gv" => "Mannois",
"ha" => "Haoussa",
"he" => "Hébreu",
"hi" => "Hindi",
"ho" => "Hiri motu",
"hr" => "Croate",
"ht" => "Créole haïtien",
"hu" => "Hongrois",
"hy" => "Arménien",
"hz" => "Héréro",
"ia" => "Interlingua",
"id" => "Indonésien",
"ie" => "Occidental",
"ig" => "Igbo",
"ii" => "Yi",
"ik" => "Inupiak",
"io" => "Ido",
"is" => "Islandais",
"it" => "Italien",
"iu" => "Inuktitut",
"ja" => "Japonais",
"jv" => "Javanais",
"ka" => "Géorgien",
"kg" => "Kikongo",
"ki" => "Kikuyu",
"kj" => "Kuanyama",
"kk" => "Kazakh",
"kl" => "Groenlandais",
"km" => "Khmer",
"kn" => "Kannada",
"ko" => "Coréen",
"kr" => "Kanouri",
"ks" => "Cachemiri",
"ku" => "Kurde",
"kv" => "Komi",
"kw" => "Cornique",
"ky" => "Kirghiz",
"la" => "Latin",
"lb" => "Luxembourgeois",
"lg" => "Ganda",
"li" => "Limbourgeois",
"ln" => "Lingala",
"lo" => "Lao",
"lt" => "Lituanien",
"lu" => "Luba-katanga",
"lv" => "Letton",
"mg" => "Malgache",
"mh" => "Marshallais",
"mi" => "Maori de Nouvelle-Zélande",
"mk" => "Macédonien",
"ml" => "Malayalam",
"mn" => "Mongol",
"mo" => "Moldave",
"mr" => "Marathi",
"ms" => "Malais",
"mt" => "Maltais",
"my" => "Birman",
"na" => "Nauruan",
"nb" => "Norvégien Bokmål",
"nd" => "Sindebele",
"ne" => "Népalais",
"ng" => "Ndonga",
"nl" => "Néerlandais",
"nn" => "Norvégien Nynorsk",
"no" => "Norvégien",
"nr" => "Nrebele",
"nv" => "Navajo",
"ny" => "Chichewa",
"oc" => "Occitan",
"oj" => "Ojibwé",
"om" => "Oromo",
"or" => "Oriya",
"os" => "Ossète",
"pa" => "Pendjabi",
"pi" => "Pali",
"pl" => "Polonais",
"ps" => "Pachto",
"pt" => "Portugais",
"qu" => "Quechua",
"rc" => "Créole Réunionnais",
"rm" => "Romanche",
"rn" => "Kirundi",
"ro" => "Roumain",
"ru" => "Russe",
"rw" => "Kinyarwanda",
"sa" => "Sanskrit",
"sc" => "Sarde",
"sd" => "Sindhi",
"se" => "Same du Nord",
"sg" => "Sango",
"sh" => "Serbo-croate",
"si" => "Cingalais",
"sk" => "Slovaque",
"sl" => "Slovène",
"sm" => "Samoan",
"sn" => "Shona",
"so" => "Somali",
"sq" => "Albanais",
"sr" => "Serbe",
"ss" => "Swati",
"st" => "Sotho du Sud",
"su" => "Soundanais",
"sv" => "Suédois",
"sw" => "Swahili",
"ta" => "Tamoul",
"te" => "Télougou",
"tg" => "Tadjik",
"th" => "Thaï",
"ti" => "Tigrigna",
"tk" => "Turkmène",
"tl" => "Tagalog",
"tn" => "Tswana",
"to" => "Tongien",
"tr" => "Turc",
"ts" => "Tsonga",
"tt" => "Tatar",
"tw" => "Twi",
"ty" => "Tahitien",
"ug" => "Ouïghour",
"uk" => "Ukrainien",
"ur" => "Ourdou",
"uz" => "Ouzbek",
"ve" => "Venda",
"vi" => "Vietnamien",
"vo" => "Volapük",
"wa" => "Wallon",
"wo" => "Wolof",
"xh" => "Xhosa",
"yi" => "Yiddish",
"yo" => "Yoruba",
"za" => "Zhuang",
"zh" => "Chinois",
"zu" => "Zoulou"
	 );

	return ( !empty($wat) AND array_key_exists( $wat , $langues ) ) ? $langues[ $wat ] : $langues;

  }





/* --------------------------------------------------------------------------------------------------------------------------------------------- SUPPRIME LES LIGNES EN DOUBLES DANS UN TEXTE */
public static function remove_duplicate_lines( $text )
  {
	return implode( array_unique( $text ) );
  }





/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE UN IDENTIFIANT ALEATOIRE */
public static function random_id()
  {
	return "id".self::microtime(true);
  }





/* --------------------------------------------------------------------------------------------------------------------------------------------- GESTION DE LA TIMEZONE */
public static function timezone()
  {
	date_default_timezone_set( "Europe/Paris" );
  }





/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE L'URL SOURCE D'UNE IFRAME */
public static function get_iframe_src( $data , $return_original=false )
  { 
	preg_match_all( "/<iframe[^>]*src=[\"|']([^'\"]+)[\"|'][^>]*><\/iframe>/im" , $data , $output );
	
	if( is_array($output) AND isset($output[1]) AND !empty($output[1]) )
	  {
	  	if( $return_original == false )
	  	  {
	  	$output = $output[1];
	  	  }
	  
		if( count($output) > 1 )
		  {
		  	return $output;
		  }
	  	else
		  {
		  	return $return_original ? $output : $output[0];
		  }
	  }
	else
	  {
	  	return "";
	  }
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- VERIFIE SI LE LIEN EST EXTERNE OU NON */
public static function is_external_link( $url )
  {
	if( defined("ABSOLUTE_URL_SERVER") )
	  {
		return !preg_match( "#^".ABSOLUTE_URL_SERVER."#" , $url );
	  }
	else
	  {
		return ( functions::url($url)["host"] != $_SERVER["HTTP_HOST"] );
	  }
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- CONVERTIT UN TEXTE EN BRAILLE */
public static function braille( $text )
  {
	$dico = array(	"A" => "⠁",
				"B" => "⠃",
				"C" => "⠉",
				"D" => "⠙",
				"E" => "⠑",
				"F" => "⠋",
				"G" => "⠛",
				"H" => "⠓",
				"I" => "⠊",
				"J" => "⠚",
				"K" => "⠅",
				"L" => "⠇",
				"M" => "⠍", 
				"N" => "⠝",
				"O" => "⠕",
				"P" => "⠏",
				"Q" => "⠟",
				"R" => "⠗",
				"S" => "⠎",
				"T" => "⠞",
				"U" => "⠥",
				"V" => "⠧",
				"W" => "⠺",
				"X" => "⠭",
				"Y" => "⠽",
				"Z" => "⠵"
	);
	
	$text = self::only_alphanumeric( $text );
	$text = str_replace( "-" , "" , $text );
	$text = self::uppercase( $text );

	foreach( $dico as $lettre => $code )
	  {
		$text = preg_replace( "/".$lettre."/i" , $code , $text );
	  }
	
	return $text;

  }




/* --------------------------------------------------------------------------------------------------------------------------------------------- CONVERTIT DES DONNÉES JSON EN TABLEAU PHP */
/**
* Supprime les tableaux vides
*/
public static function json_to_array( $json )
  {
	$json = str_replace( '{}', '""' , $json );
	return json_decode( $json , true );
  }




/* --------------------------------------------------------------------------------------------------------------------------------------------- CONVERTIT DES DONNÉES CSV EN TABLEAU */
/**
	$regex = "/[^\\\]".$delimiter."/im";
	echo "<xmp>".$regex."</xmp>";
	echo functions::print_r( preg_split( $regex , $line ) , true );
	exit;
*/
	
public static function csv( $data , $delimiter = ";" , $remove_headers = true )
  {
	$csv 		= array();
	$nb		= count( explode( $delimiter , strtok( $data , "\n" ) ) );
  	$i		= 0;

	foreach( preg_split("/((\r?\n)|(\r\n?))/", $data) as $line )
	  {
		$array = explode( $delimiter , $line );
		
		if( ( $i > 0 ) OR ( $remove_headers == false ) )
		  {
		  	if( count($array) < $nb )
		  	  {
		  	  	for( $c = count($array) ; $c < $nb ; $c++ )
		  	  	  {
		  	  	  	$array[] = "";
		  	  	  }
		  	  }

			$csv[] = $array;

		  }
		
		$i++;

	  } 
	
	return $csv;
  }




/* --------------------------------------------------------------------------------------------------------------------------------------------- PREPARE DES REQUETES SQL POUR REMPLACER LES CARACTERES POURRIS UTF8 */
/*
	AVEC DES REQUETES SQL :

	alter table ta_table convert to character set 'latin1';  	-- décodage
	alter table ta_table convert to character set 'binary';  	-- neutralisation de l'encodage
	alter table ta_table convert to character set 'utf8';    	-- définition de l'encodage.
*/
public static function sql_utf8_replace( $table , $colonne )
  {
	$sql = "
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'Ã¢','â') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'Ã¢','â') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'Ã ','à') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'Ã ','à') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'Ã©','é') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'Ã‰','É') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'à©','é') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'í©','é') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'Ã¨','è') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'Ã§','ç') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'Â«','«') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'Â»','»') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'Ãª','ê') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'àª','ê') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'â‚¬','€') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'Ã´','ô') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'Ã¤','ä') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'Ã¹','ù') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'Ã®','î') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'à¨','è') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'àª','ê') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'Å“','œ') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'à§','ç') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'à»','û') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'à®','î') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'à´','ô') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'à‰','é') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'à€','à') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'Â','') ;
UPDATE `".$table."` SET `".$colonne."` = REPLACE(`".$colonne."` ,'â€™',\"'\") ;";

	return $sql;

  }




/* --------------------------------------------------------------------------------------------------------------------------------------------- VÉRIFIE SI LA REQUETE DU SCRIPT VIENS D'AJAX */
	
public static function is_ajax()
  {
	return ( array_key_exists("HTTP_X_REQUESTED_WITH" , $_SERVER ) AND ( strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") );
  }





/* --------------------------------------------------------------------------------------------------------------------------------------------- CHECK SI IL Y A UNE IFRAME DANS UN BOUT DE CODE */
	
public static function got_iframes( $html )
  {
  	$html = self::text_compressor( $html );
	preg_match( "/<iframe.*src=\"(.*)\".*><\/iframe>/isU" , $html , $matches );
	return $matches;
  }





/* --------------------------------------------------------------------------------------------------------------------------------------------- GENERE UN ID UNIQUE */
	
public static function unik_id( $prefix = "id_" )
  {
  	return $prefix.self::microtime(true).rand( 100 , 999 );
  }





/* --------------------------------------------------------------------------------------------------------------------------------------------- FORCE L'AFFICHAGE DES ERREURS */

public static function display_errors()
  {
	error_reporting(E_ALL);
	ini_set( "display_errors", 1 );
  }





/* --------------------------------------------------------------------------------------------------------------------------------------------- GENÈRE UN TABLEAU AVEC PARENTS / ENFANTS */

public static function tree( $data = array() , $options = array() )
  {
	$default = array( 
		"id"			=> "id",
		"parent_id" 	=> "parent_id",
		"levels" 		=> null
	);

	$options		= is_array($options) ? array_merge( $default , $options ) : $default;
  	$tree 		= array();
	$levels 		= ( !is_null($options["levels"]) AND preg_match( "#^([a-z0-9-_]){1,}$#i" , $options["levels"] ) ) ? $options["levels"] : null;
	$id 			= $options["id"];
	$parent_id 		= $options["parent_id"];

	foreach( $data as &$item )
	  {
		if( !is_null($levels) )
		  {
	  		$item[$levels] = 0;
		  }

	  	$tree[$item[$parent_id]][] = &$item;
	  } 

	unset($item);

	foreach( $data as &$item )
	  {
		if( isset($tree[$item[$id]]) )
		  {
		  	$item["childs"] = $tree[$item[$id]];

			if( !is_null($levels) )
			  {
			  	foreach( $item["childs"] as $n => $child )
			  	  {
				  	$item["childs"][$n][$levels] = $item[$levels] + 1;
			  	  }
			  }
		  }
	  }

  	return $tree[0];
  }






/* --------------------------------------------------------------------------------------------------------------------------------------------- PROTEGE LES DONNES INSERÉES DANS L'ATTRIBUT VALUE D'UN ELEMENT DE TYPE INPUT */
/* EN COURS DE DEVELOPPEMENT */
public static function value( $value , $guillemet = '"' )
  {
  	if( $guillemet == '"' )
  	  {
  	  }
	$value = trim( $value );
	return $value;
  }





/**
* EXTRAS FUNCTIONS
*/


/* --------------------------------------------------------------------------------------------------------------------------------------------- GENERATEUR DE CLE
FROM : http://www.info-3000.com/phpmysql/cryptagedecryptage.php
*/

private static function generationcle( $texte , $cle_de_cryptage )
  {
	$cle_de_cryptage	= md5( $cle_de_cryptage );
	$compteur		= 0;
	$VariableTemp	= "";
	for ( $Ctr=0 ; $Ctr<strlen($texte) ; $Ctr++ )
	  {
		if($compteur==strlen($cle_de_cryptage))
		  {
			$compteur=0;
		  }
		$VariableTemp .= substr( $texte , $Ctr , 1 ) ^ substr( $cle_de_cryptage , $compteur , 1 );
		$compteur++;
	  }
	return $VariableTemp;
  }

public static function crypte( $texte , $cle=NULL )
  {  
	if($cle==NULL)
	  {
		$cle = self::$cle_de_cryptage;
	  }

	srand((double)microtime()*1000000);
	$cle_de_cryptage = md5(rand(0,32000) );
	$compteur = 0;
	$VariableTemp = "";
	for ( $Ctr=0 ; $Ctr<strlen($texte) ; $Ctr++ )
	   {
		if($compteur==strlen($cle_de_cryptage))
		  {
			$compteur=0;
		  }
		$VariableTemp.= substr( $cle_de_cryptage , $compteur , 1 ).(substr( $texte , $Ctr , 1 ) ^ substr( $cle_de_cryptage , $compteur , 1 ) );
		$compteur++;
	   }
	return base64_encode( self::generationcle( $VariableTemp , $cle ) );
  }

public static function decrypte( $texte , $cle=NULL )
  {
	if($cle==NULL)
	  {
		$cle = self::$cle_de_cryptage;
	  }

	$texte	= self::generationcle( base64_decode($texte) , $cle );
	$VariableTemp = "";
	for ($Ctr=0;$Ctr<strlen($texte);$Ctr++)
	  {
		$md5 = substr($texte,$Ctr,1);
		$Ctr++;
		$VariableTemp.= (substr($texte,$Ctr,1) ^ $md5);
	  }
	return $VariableTemp;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- CRYPTE ET DÉCRYPTE UNE CHAINE DE CARACTERE
FROM : https://naveensnayak.wordpress.com/2013/03/12/simple-php-encrypt-and-decrypt/
*/

public static function encrypt_decrypt( $action , $string )
  {
	$output		= "";
	$encrypt_method	= "AES-256-CBC";
	$secret_key		= "key_x1549641";
	$secret_iv		= "iv__th4kyuia";
	$key			= hash( "sha256" , $secret_key );
	$iv 			= substr(hash( "sha256" , $secret_iv ) , 0 , 16 );
	
	if( $action == "encrypt" )
	  {
		$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
		$output = base64_encode($output);
	  }
	else if( $action == "decrypt" )
	  {
		$output = openssl_decrypt( base64_decode($string) , $encrypt_method , $key , 0 , $iv );
	  }
	
	return $output;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LA TAILLE D'UN FICHIER DISTANT ( FTP / HTTP )
FROM : http://fr.php.net/manual/fr/function.filesize.php#71098
*/

public static function remote_file_size( $url )
  {
	$sch = parse_url( $url , PHP_URL_SCHEME );

	if( ($sch != "http") AND ($sch != "https") AND ($sch != "ftp") AND ($sch != "ftps") )
	  {
		return false;
	  }

	if( ($sch == "http") || ($sch == "https") )
	  {
		$headers = get_headers( $url , 1 );
		if( (!array_key_exists( "Content-Length" , $headers )) )
		  {
			return false;
		  }

		return $headers[ "Content-Length" ];
	  }

	if( ($sch == "ftp") || ($sch == "ftps") )
	  {
		$server	= parse_url($url, PHP_URL_HOST);
		$port		= parse_url($url, PHP_URL_PORT);
		$path		= parse_url($url, PHP_URL_PATH);
		$user		= parse_url($url, PHP_URL_USER);
		$pass		= parse_url($url, PHP_URL_PASS);

		if( (!$server) || (!$path) )	{ return false; }
		if( !$port )			{ $port = 21; }
		if( !$user )			{ $user = "anonymous"; }
		if( !$pass )			{ $pass = "phpos@"; }

		switch ($sch)
		  {
			case "ftp" :	$ftpid = ftp_connect( $server, $port );		break;
			case "ftps" :	$ftpid = ftp_ssl_connect( $server , $port );	break;
		  }

		if (!$ftpid)
		  {
			return false;
		  }

		$login = ftp_login( $ftpid , $user , $pass );
		if (!$login)
		  {
			return false;
		  }

		$ftpsize = ftp_size( $ftpid , $path );
		ftp_close( $ftpid );
		if ($ftpsize == -1)
		  {
			return false;
		  }

		return $ftpsize;
	  }
    }





/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOIE LE NAVIGATEUR ET SA VERSION
http://www.dotvoid.com/view.php?id=68
*/

public static function user_agent( $user_agent=null , $return=false  )
  {
	/* Updated : 23/05/2016 */

	if( $user_agent === null )
	  {
		$user_agent	= self::ua();
	  }
	else if( preg_match( "/(\))$/" , $user_agent) )
	  {
		$user_agent .= ")";
	  }

	$ua		= $user_agent;
	$userAgent	= array();
	$products	= array();
	$pattern	= "([^/[:space:]]*)" . "(/([^[:space:]]*))?"
			."([[:space:]]*\[[a-zA-Z][a-zA-Z]\])?" . "[[:space:]]*"
			."(\\((([^()]|(\\([^()]*\\)))*)\\))?" . "[[:space:]]*";

	while( strlen($user_agent) > 0 )
	  {
		if ($l = preg_match( "#".$pattern."#", $user_agent, $a))
		  {
			array_push( $products, array(
								isset($a[1]) ? $a[1] : "",    	/* Product */
								isset($a[3]) ? $a[3] : "",   		/* Version */
								isset($a[6]) ? $a[6] : ""		/* Comment */
			));  	
			$user_agent = substr($user_agent, $l);
		  }
		else
		  {
			$user_agent = "";
		  }
	  }

	$founded = false;

	/* Directly catch these */
	foreach( $products as $product )
	  {
		if( $founded == false )
		  {
			switch( $product[0] )
			  {
				case "Firefox" :
				case "Safari" :
				case "Edge" :
				case "Chrome" :
				case "Opera" :
				case "OPR" :
				case "Netscape" :
				case "Camino ":
				case "Galeon" :
				case "Mosaic" :
				case "Fennec" :
				case "Arora" :
				case "amaya" :
				case "Advanced Browser" :
				case "Firebird" :
				case "iCab" :
				case "Konqueror" :
				case "Lynx" :
				case "Minimo" :
				case "SymbianOS" :
				case "SeaMonkey" :
				case "Thunderbird" :
							$userAgent[0] 	= ( isset($product[0]) ? $product[0] : null );
							$userAgent[1] 	= ( isset($product[1]) ? $product[1] : null );
							$founded 		= true;
							break;
			  }
		  }
	  }

	if( preg_match( "/(iPod|iPhone|iPad|Aspen)/i" , $ua , $matches , PREG_OFFSET_CAPTURE) )
	  {
		$data = explode( " " , stristr( $ua, "Version/" ) );
		$data = trim( str_replace( "Version/" , "" , $data[0] ) );
		$userAgent[0] = $matches[0][0];
		$userAgent[1] = $data;
	  }
	else if( preg_match( "/Trident\/\d{1,2}.\d{1,2}; rv:([0-9]*)/i" , $ua , $matches , PREG_OFFSET_CAPTURE) )
	  {
		$userAgent[0] = "Internet Explorer";
		$userAgent[1] = $matches[1][0];
	  }
	else if( preg_match( "/(Edge\/([0-9\.]{0,}))/i" , $ua , $matches , PREG_OFFSET_CAPTURE) )
	  {
		$data = explode( "/" , $matches[0][0] );
		$userAgent[0] = $data[0];
		$userAgent[1] = $data[1];
	  }
	else if( preg_match( "/(Android)/i" , $ua , $matches , PREG_OFFSET_CAPTURE) )
	  {
		$data = explode( " " , stristr( $ua, $matches[0][0] ) );
		$userAgent[0] = $data[0];
		$userAgent[1] = trim( str_replace( ";" , "" , $data[1] ) );
	  }
	else if( preg_match( "/(Xbox)/i" , $ua , $matches , PREG_OFFSET_CAPTURE) )
	  {
		$userAgent[0] = $matches[0][0];
		$userAgent[1] = "";
	  }
	else if( preg_match( "/(Nintendo Wii)/i" , $ua , $matches , PREG_OFFSET_CAPTURE) )
	  {
		$userAgent[0] = $matches[0][0];
		$userAgent[1] = "";
	  }
	else if( preg_match( "/(Lotus-Notes)/i" , $ua , $matches , PREG_OFFSET_CAPTURE) )
	  {
		$userAgent[0] = $matches[0][0];
		$userAgent[1] = "";
	  }

	if( count($userAgent) == 0 )
	  {
		/* Mozilla compatible (MSIE, konqueror, etc) */

		if( ( isset($products[0][0]) AND ($products[0][0] == "Mozilla") ) AND ( isset($products[0][2]) AND !strncmp($products[0][2], "compatible;", 11) ) )
		  {
			$userAgent = array();
			if( $cl = preg_match("#compatible; ([^ ]*)[ /]([^;]*).*#", $products[0][2], $ca) )
			  {
				$userAgent[0] = $ca[1];
				$userAgent[1] = $ca[2];
			  }
			else
			  {
				$userAgent[0] = ( isset($products[0][0]) ? $products[0][0] : null );
				$userAgent[1] = ( isset($products[0][1]) ? $products[0][1] : null );
			  }
		  }
		else
		  {
			$userAgent = array();
			$userAgent[0] = ( isset($products[0][0]) ? $products[0][0] : null );
			$userAgent[1] = ( isset($products[0][1]) ? $products[0][1] : null );

			/* $userAgent = get_browser( $ua ); */
		  }

		if( $userAgent[0] == "MSIE" )
		  {
			$userAgent[0] = "Internet Explorer";
		  }
	  }

	if( $return === true )
	  {
		return $userAgent[0]." ".$userAgent[1];
	  }
	else
	  {
		return $userAgent;
	  }
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- LIMITER LE DEBIT DE TELECHARGEMENT D'UN FICHIER
http://www.phpsources.org/scripts377-PHP.htm
*/

public static function dl_file_limited( $file , $speed=1000 )
  {
	if( file_exists($file) AND is_file($file) )
	  {
		header( "Cache-control: private" );
		header( "Content-Type: application/octet-stream" );
		header( "Content-Length: ".filesize( $file ) );
		header( "Content-Disposition: filename=$file" );
		flush();
		$fd = fopen( $file , "r" );
		while( !feof($fd) )
		  {
			echo fread( $fd , round( $speed * 1024 ) );
			flush();
			sleep(1);
		  }
		fclose( $fd );
	  }
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- DECODE LES CARACTERES UNICODE DE TYPE %u****
http://fr3.php.net/manual/fr/function.urldecode.php#64676
*/

public static function unicode_urldecode( $url )
  {
	preg_match_all( "/%u([[:alnum:]]{4})/" , $url , $a );

	foreach( $a[1] AS $uniord )
	  {
		$dec	= hexdec( $uniord );
		$utf	= "";

		if( $dec < 128 )
		  {
			$utf = chr($dec);
		  }
		else if( $dec < 2048 )
		  {
			$utf = chr(192 + (($dec - ($dec % 64)) / 64));
			$utf .= chr(128 + ($dec % 64));
		  }
		else
		  {
			$utf = chr(224 + (($dec - ($dec % 4096)) / 4096));
			$utf .= chr(128 + ((($dec % 4096) - ($dec % 64)) / 64));
			$utf .= chr(128 + ($dec % 64));
		  }

		$url = str_replace( "%u".$uniord, $utf, $url );
	  }

	return urldecode( $url );
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- SUPPRIME UN REPERTOIRE ET SON CONTENU
http://www.phpsources.org/scripts176-PHP.htm
*/

public static function delete_path( $path )
  {
	if( $path[ strlen($path) - 1 ] != "/" )
	  {
		$path .= "/";
	  }

	if( is_dir( $path ) )
	  {
		$directory = opendir( $path );
		while( $file = readdir( $directory ) )
		  {
			if( ($file != ".") AND ($file != "..") )
			{
				$fichier = $path.$file;
				if( is_dir( $fichier ) )
				  {
					self::delete_path( $fichier );
				  }
				else
				  {
					unlink( $fichier );
				  }
			}
		  }
		closedir( $directory );
		rmdir( $path );
	  }
	else if( is_file($path) )
	  {
		unlink( $path );
	  }
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- CALCUL DES LEVER ET COUCHER DE SOLEIL
http://www.phpsources.org/scripts385-PHP.htm
*/

public static function soleil( $wat=null )
  {
	$return = "";

	$fh = date("h") - gmdate("h") ;
	$la = 48.833;
	$lo = -2.333;
	$ville = "paris";
	$mois = date("m") ;
	$jour = date("d") ;

	/* fuseau horaire et coordonnées géographiques */
	$k	= 0.0172024;
	$jm	= 308.67;
	$jl	= 21.55;
	$e	= 0.0167;
	$ob	= 0.4091;
	$pi	= 3.1415926536;

	/*hauteur du soleil au lever et au coucher */
	$dr = $pi/ 180;
	$hr = $pi/ 12;
	$ht = (-40 / 60);
	$ht = $ht * $dr;
	$la = $la * $dr;
	$lo = $lo * $dr;

	/*date */
	if ($mois < 3) 
	{
		$mois = $mois + 12;
	}

	/*heure tu du milieu de la journée */
	$h = 12 + ($lo / $hr);

	/*nombre de jours écoulés depuis le 1 mars o h tu */
	$j = floor(30.61 * ($mois + 1)) + $jour + ($h / 24) - 123;

	/*anomalie et longitude moyenne */
	$m = $k * ($j - $jm);
	$l = $k * ($j - $jl);

	/*longitude vraie */
	$s =$l + 2 * $e * sin($m) + 1.25 * $e * $e * sin(2 * $m);

	/*coordonnées rectangulaires du soleil dans le repère équatorial */
	$x = cos($s);
	$y = cos($ob) * sin($s);
	$z = sin($ob) * sin($s);

	/*equation du temps et déclinaison*/
	$r = $l;
	$rx = cos($r) * $x + sin($r) * $y;
	$ry = -sin($r) * $x + cos($r) * $y;
	$x = $rx;
	$y = $ry;
	$et = atan($y / $x);
	$dc = atan($z / sqrt(1 - $z * $z));

	/*angle horaire au lever et au coucher*/
	$cs = (sin($ht) - sin($la) * sin($dc)) / cos($la) / cos($dc);
	if ($cs > 1) { $calculsol = "ne se lève pas";}
	if ($cs < -1) { $calculsol = "ne se couche pas";}
	if ($cs == 0) {
	$ah = $pi / 2;
	}else{
	$ah = atan(sqrt(1 - $cs * $cs) / $cs);
	}
	if ($cs < 0) { $ah = $ah + $pi;}

	/*lever du soleil*/
	$pm = $h + $fh + ($et - $ah) / $hr;
	if ($pm < 0) { $pm = $pm + 24;}
	if ($pm > 24) { $pm = $pm - 24;}
	$hs = floor($pm);
	$pm = floor(60 * ($pm - $hs));
	if (strlen($hs)<2) {$hs = "0".$hs;}
	if (strlen($pm)<2) {$pm = "0".$pm;}
	if ($calculsol ==""){
	$lev = $hs. ":" .$pm;
	}else{
	$lev = "---";}

	/*coucher du soleil*/
	$pm = $h + $fh + ($et + $ah) /$hr;
	if ($pm > 24) { $pm = $pm - 24;}
	if ($pm < 0) { $pm = $pm + 24;}
	$hs = floor($pm);
	$pm = floor(60 * ($pm - $hs));
	if (strlen($hs)<2) {$hs = "0".$hs;}
	if (strlen($pm)<2) {$pm = "0".$pm;}
	if( $calculsol == "" ){
	$couch = $hs. ":" .$pm;
	}else{
	$couch  = "---";}

	$return .= "Horaires du soleil à ".$ville;
	$return .= "<br />Lever = " .$lev ;
	$return .= "<br />Coucher = ". $couch;

	return $return;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- ENVOI UN MESSAGE SUR TWITTER
http://www.scripts-marketing.com/poster-un-twit-en-php/
http://www.barattalo.it/2010/09/09/how-to-change-twitter-status-with-php-and-curl-without-oauth/
*/

public static function twitter( $login , $password , $message )
  {
	$message = ( strlen( $message ) > 140 ) ? substr( $message , 0 , 139 )."…" : $message;

	if( function_exists("curl_init") )
	  {
		$ch = curl_init();

		/* get login form and parse it */
		curl_setopt($ch, CURLOPT_URL, "https://mobile.twitter.com/session/new");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_COOKIEJAR, "my_cookies.txt");
		curl_setopt($ch, CURLOPT_COOKIEFILE, "my_cookies.txt");
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Version/3.0 Mobile/1A543a Safari/419.3 ");
		$page = curl_exec($ch);
		$page = stristr($page, "<div class='signup-body'>");
		preg_match("/form action=\"(.*?)\"/", $page, $action);
		preg_match("/input name=\"authenticity_token\" type=\"hidden\" value=\"(.*?)\"/", $page, $authenticity_token);

		/* make login and get home page */
		$strpost = "authenticity_token=".urlencode($authenticity_token[1])."&username=".urlencode($login)."&password=".urlencode($password);
		curl_setopt($ch, CURLOPT_URL, $action[1]);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $strpost);
		$page = curl_exec($ch);
		/* check if login was ok */
		preg_match("/\<div class=\"warning\"\>(.*?)\<\/div\>/", $page, $warning);
		if( isset( $warning[1] ) )
		  {
			return $warning[1];
		  }

		$page = stristr($page,"<div class='tweetbox'>");
		preg_match("/form action=\"(.*?)\"/", $page, $action);
		preg_match("/input name=\"authenticity_token\" type=\"hidden\" value=\"(.*?)\"/", $page, $authenticity_token);

		/* send status update */
		$strpost = "authenticity_token=".urlencode($authenticity_token[1]);
		$tweet['display_coordinates']='';
		$tweet['in_reply_to_status_id']='';
		$tweet['lat']='';
		$tweet['long']='';
		$tweet['place_id']='';
		$tweet['text']=$message;
		$ar = array("authenticity_token" => $authenticity_token[1], "tweet"=>$tweet);
		$data = http_build_query($ar);
		curl_setopt($ch, CURLOPT_URL, $action[1]);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$page = curl_exec($ch);
		$xml	= simplexml_load_string( $page );

		return true;
	  }
	else
	  {
		return false;
	  }


	/* OLD VERION WITHOUT AUTH !

	$url 	= "http://twitter.com/statuses/update.xml";
	$curl	= curl_init();

	curl_setopt( $curl, CURLOPT_URL, $url );
	curl_setopt( $curl, CURLOPT_POST, 1 );
	curl_setopt( $curl, CURLOPT_HEADER, 0 );
	curl_setopt( $curl, CURLOPT_VERBOSE, 1 );
	curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, 1 );
	curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 2 );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $curl, CURLOPT_USERPWD, $login.":".$password );
	curl_setopt( $curl, CURLOPT_POSTFIELDS, "status=".$message );

	$buffer	= curl_exec( $curl );
	$xml		= simplexml_load_string( $buffer );

	curl_close( $curl );

	if( isset($xml->error) )
	  {
		return $xml->error;
	  }
	else
	  {
		return ( ( isset($xml->user->id) AND !empty($xml->user->id) ) ? true : false );
	  }
	*/

  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- TEST LA VALIDITÉ D'UN NUMÉRO DE SIRET
stopher : lindev.fr
*/

public static function check_siret( $numsiret )
  {
	$numsiret	= preg_replace( "#[^0-9]#i" , "" , $numsiret );

	/* Transformation de la chaine en tableau */
	$Tsiret = str_split($numsiret);

	/* Le numéro de siret doit contenir 14 chiffres */
	if( count($Tsiret) != 14)
	  {
		return false;
	  }

	/* Initialise le resultat */
	$Restest = 0;
	$curseur = 0;

	/* Passage sur chaque chiffre */
	foreach( $Tsiret AS &$num )
	  {
		/* Si le chiffre est paire */
		$num = ( ( ( $curseur + 1 ) % 2 ) + 1 ) * $num;

		/* Si > 10 on lui retire 9 */
		if( $num >= 10 )
		  {
			$num-=9;
		  }

		$Restest += $num;
		$curseur++;
	  }

	/*Si $Restest et multiple de 10 le siret est correct .. */

	if( $Restest%10 == 0 )
	  {
		return true;
	  }
	else
	  {
		return false;
	  }
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- CALCULE LA CLE D'UN CODE BARRE EAN13
http://www.edmondscommerce.co.uk/blog/php/ean13-barcode-check-digit-with-php/
*/

public static function ean13_check_digit( $digits )
  {
	$digits	= preg_replace( "#[^0-9]#i" , "" , $digits );

	if( strlen( $digits ) != 12 )
	  {
		return $digits;
	  }

	/* First change digits to a string so that we can access individual numbers */
	$digits =(string)$digits;

	/* 1. Add the values of the digits in the even-numbered positions: 2, 4, 6, etc. */
	$even_sum = $digits[1] + $digits[3] + $digits[5] + $digits[7] + $digits[9] + $digits[11];

	/* 2. Multiply this result by 3.
	$even_sum_three = $even_sum * 3;

	/* 3. Add the values of the digits in the odd-numbered positions: 1, 3, 5, etc. */
	$odd_sum = $digits[0] + $digits[2] + $digits[4] + $digits[6] + $digits[8] + $digits[10];

	/* 4. Sum the results of steps 2 and 3.
	$total_sum = $even_sum_three + $odd_sum;

	/* 5. The check character is the smallest number which, when added to the result in step 4,  produces a multiple of 10. */
	$next_ten		= (ceil($total_sum/10))*10;
	$check_digit	= $next_ten - $total_sum;

	return $digits.$check_digit;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- FONCTION DATE() DE PHP TRADUITE EN FRANCAIS
http://www.ab-d.fr/date/2010-02-01/
*/

public static function date( $format , $timestamp = null , $lang = "fr" )
  {
	$return = "";

	if( is_null($timestamp) )
	  {
		$timestamp = time();
	  }

  	if( $lang == "en" )
  	  {
		return date( $format , $timestamp );
  	  }
	else
  	  {
		$param_D = array("", "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim");
		$param_l = array("", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche");
		$param_F = array("", "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre");
		$param_M = array("", "Jan", "Fév", "Mar", "Avr", "Mai", "Jun", "Jul", "Aoû;", "Sep", "Oct", "Nov", "Déc");
	  }

	for( $i = 0, $len = strlen($format); $i < $len; $i++)
	  {
		switch($format[$i])
		  {
			case "\\" : /* double.slashes */
						$i++;
						$return .= isset($format[$i]) ? $format[$i] : "";
						break;
			case "D" :
						$return .= $param_D[date("N", $timestamp)];
						break;
			case "l" :
						$return .= $param_l[date("N", $timestamp)];
						break;
			case "F" :
						$return .= $param_F[date("n", $timestamp)];
						break;
			case "M" :
						$return .= $param_M[date("n", $timestamp)];
						break;
			default :
						$return .= date($format[$i], $timestamp);
						break;
		  }
	  }

	return $return;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- CREE DES LIENS CLICKABE SUR LES @TWITTER
http://www.snipe.net/2009/09/php-twitter-clickable-links/
*/

public static function twitterify( $data )
  {
	$data = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" onclick=\"window.open( this.href , '_blank' );return false;\">\\2</a>", $data );
	$data = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", "\\1<a href=\"http://\\2\" onclick=\"window.open( this.href , '_blank' );return false;\">\\2</a>", $data );
	$data = preg_replace("/@(\w+)/", "<a href=\"http://www.twitter.com/\\1\" onclick=\"window.open( this.href , '_blank' );return false;\">@\\1</a>", $data );
	$data = preg_replace("/#(\w+)/", "<a href=\"http://search.twitter.com/search?q=\\1\" onclick=\"window.open( this.href , '_blank' );return false;\">#\\1</a>", $data );
	return $data;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- AFFICHE UNE DATE RELATIVE SOUS LA FORME IL Y A X JOURS/HEURES/MINUTES/SECONDES
http://www.devzone.fr/snippet-php-afficher-une-date-relative
*/

public static function relative_time( $time = 469065600 )
  {
	$time		= is_numeric( $time ) ? $time : strtotime( $time );
	$difference	= time() - $time ;

	if( $difference < 0 )
	  {
		$difference = abs( $difference );

		$iSeconds		= $difference;
		$iMinutes		= round( $difference / 60 );
		$iHours		= round( $difference / 3600 );
		$iDays		= round( $difference / 86400 );
		$iWeeks		= round( $difference / 604800 );
		$iMonths		= round( $difference / 2419200 );
		$iYears		= round( $difference / 29030400 );

		if( $iSeconds < 2 )			$return = "Maintenant";
		else if( $iSeconds < 30 )		$return = "Dans quelques secondes";
		else if( $iSeconds < 60 )		$return = "Dans moins d'une minute";
		else if( $iMinutes<60 )			$return = "Dans ".$iMinutes." minute".self::s( $iMinutes );
		else if( $iHours<24 )			$return = "Dans ".$iHours." heure".self::s( $iHours );
		else if( $iDays<7 )			$return = "Dans ".$iDays." jour".self::s( $iDays );
		else if( $iWeeks <4 )			$return = "Dans ".$iWeeks." semaine".self::s( $iWeeks );
		else if( $iMonths<12 )			$return = "Dans ".$iMonths." mois";
		else						$return = "Dans ".$iYears." an".self::s( $iYears );
	  }
	else
	  {
		$iSeconds		= $difference;
		$iMinutes		= round( $difference / 60 );
		$iHours		= round( $difference / 3600 );
		$iDays		= round( $difference / 86400 );
		$iWeeks		= round( $difference / 604800 );
		$iMonths		= round( $difference / 2419200 );
		$iYears		= round( $difference / 29030400 );

		if( $iSeconds < 5 )			$return = "Maintenant";
		else if( $iSeconds < 30 )		$return = "Il y a quelques secondes";
		else if( $iSeconds < 60 )		$return = "Il y a moins d'une minute";
		else if( $iMinutes<60 )			$return = "Il y a ".$iMinutes." minute".self::s( $iMinutes );
		else if( $iHours<24 )			$return = "Il y a ".$iHours." heure".self::s( $iHours );
		else if( $iDays<7 )			$return = "Il y a ".$iDays." jour".self::s( $iDays );
		else if( $iWeeks <4 )			$return = "Il y a ".$iWeeks." semaine".self::s( $iWeeks );
		else if( $iMonths<12 )			$return = "Il y a ".$iMonths." mois";
		else						$return = "Il y a ".$iYears." an".self::s( $iYears );
	  }



	return $return;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- EFFECTUE UNE REDIRECTION HTTP
http://code.seebz.net/p/header-redirect/
*/

public static function redirect( $url, $permanent=false )
  {
	$url_infos = parse_url($url);
	if( !array_key_exists('scheme', $url_infos) )
	{
		$url  = (empty($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS'])=='off') ? 'http' : 'https';
		$url .= '://';
		$url .= $_SERVER['HTTP_HOST'];
		if( $_SERVER['SERVER_PORT'] != '80' )
			$url .= ':'. $_SERVER['SERVER_PORT'];
		
		if( preg_match('`^\/`', $url_infos['path']) )
			$url .= $url_infos['path'];
		else
		{
			$url .= '/';
			
			$c_dirs = pathinfo($_SERVER['REQUEST_URI'].'x', PATHINFO_DIRNAME);
			$c_dirs = explode('/', trim($c_dirs,'/'));
			
			$t_dirs = explode('/', $url_infos['path']);
			foreach($t_dirs as $d)
			{	
				switch($d)
				{
					case '': 
					case '.':	break;
					case '..':	array_pop($c_dirs); break;
					default:	array_push($c_dirs, $d);
				}
			}
			if( array_pop($t_dirs)==='' )
				array_push($c_dirs,'');
			
			$url .= implode('/', $c_dirs);
		}
		
		if( isset($url_infos['query']) )
			$url .= '?'. $url_infos['query'];
		
		if( isset($url_infos['fragment']) )
			$url .= '#'. $url_infos['fragment'];
	}
	
	
	if( $permanent )		header("Status: 301 Moved Permanently", true, 301);
	else				header("Status: 302 Found", true, 302);

	header("Location: {$url}");
	
	echo "The document has moved <a href=\"{$url}\">here</a>.";
	
	exit;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- CALCUL LA DISTANCE ENTRE 2 COORDONNÉES GPS
http://www.phpsources.org/scripts459-PHP.htm
 */

public static function distance_gps( $lat1, $lng1 , $lat2=null, $lng2=null )
  {
	if( $lat2 == null )
	  {
		$lat2 = ( isset($_SERVER["MM_LATITUDE"]) ? $_SERVER["MM_LATITUDE"] : ( isset($_SERVER["GEOIP_LATITUDE"]) ? $_SERVER["GEOIP_LATITUDE"] : "50.633518061238725" ) );
	  }

	if( $lng2 == null )
	  {
		$lng2 = ( isset($_SERVER["MM_LONGITUDE"]) ? $_SERVER["MM_LONGITUDE"] : ( isset($_SERVER["GEOIP_LONGITUDE"]) ? $_SERVER["GEOIP_LONGITUDE"] : "3.0689701437950134" ) );
	  }

	$earth_radius	= 6378137;   		/* Terre = sphère de 6378km de rayon */
	$rlo1			= deg2rad($lng1);
	$rla1			= deg2rad($lat1);
	$rlo2			= deg2rad($lng2);
	$rla2			= deg2rad($lat2);
	$dlo			= ($rlo2 - $rlo1) / 2;
	$dla			= ($rla2 - $rla1) / 2;
	$a			= (sin($dla) * sin($dla)) + cos($rla1) * cos($rla2) * (sin($dlo) * sin($dlo) );
	$d			= 2 * atan2(sqrt($a), sqrt(1 - $a));

	$distance		= round( ( $earth_radius * $d ) , 2 );

	if( $distance < 100 )
	  {
		$distance = round( ( $distance * 100 ) , 2 )." cm";
	  }
	else if( $distance > 1000 )
	  {
		$distance = round( ( $distance / 1000 ) , 2 )." km";
	  }
	else
	  {
		$distance = round( $distance , 2 ) ." m";
	  }


	return $distance;
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- VERIFIE SI UNE CHAINE EST SERIALISEE
Fonction dispo dans Wordpress
*/

public static function is_serialized( $data )
  {
	/* if it isn't a string, it isn't serialized */
	if ( !is_string( $data ) )
		return false;
	$data = trim( $data );
	if ( 'N;' == $data )
		return true;
	if ( !preg_match( '/^([adObis]):/', $data, $badions ) )
		return false;
	switch ( $badions[1] ) {
		case 'a' :
		case 'O' :
		case 's' :
			if ( preg_match( "/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data ) )
				return true;
			break;
		case 'b' :
		case 'i' :
		case 'd' :
			if ( preg_match( "/^{$badions[1]}:[0-9.E-]+;\$/", $data ) )
				return true;
			break;
	}
	return false;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- AFFICHE LES QUOTAS D'ESPACE DISQUE
http://forum.ovh.net/showthread.php?t=14627
*/

public static function quota_disk( $raw = false )
  {
	$quota		= shell_exec( "quota | tail -n1" );
	$quota_use		= 1024 * preg_replace( "#^ *(\d+) +(\d+) [ \d]*#" , "$1" , $quota );
	$quota_total	= 1024 * preg_replace( "#^ *(\d+) +(\d+) [ \d]*#" , "$2" , $quota );
	$quota_free		= $quota_total - $quota_use;
	$quota_pc		= ( $quota_total != 0) ? number_format(100 * $quota_use / $quota_total, 1, ",", " " ) : 0;

	$quota = array(
		"total"		=> ( $raw === true ) ? $quota_total 	: self::file_size( $quota_total ),
		"used"		=> ( $raw === true ) ? $quota_use 		: self::file_size( $quota_use ),
		"free"		=> ( $raw === true ) ? $quota_free 		: self::file_size( $quota_free ),
		"pourcentage"	=> ( $raw === true ) ? $quota_pc 		: $quota_pc." %"
	);
	
	return $quota;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- GENERE UNE COULEUR ALEATOIRE
http://www.jonasjohn.de/snippets/php/random-color.htm
*/

public static function random_color()
  {
	mt_srand((double)microtime()*1000000);
	$color = "";
	
	while( strlen($color) < 6 )
	  {
		$color .= sprintf("%02X", mt_rand(0, 255));
	  }

	return $color;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOI LES PERMISSIONS D'UN FICHIER
 http://php.net/manual/fr/function.fileperms.php
*/

public static function permissions( $file )
  {
	$perms = fileperms( $file );

	if (($perms & 0xC000) == 0xC000) {
	    /* Socket */
	    $info = 's';
	} elseif (($perms & 0xA000) == 0xA000) {
	    /* Lien symbolique */
	    $info = 'l';
	} elseif (($perms & 0x8000) == 0x8000) {
	    /* Régulier */
	    $info = '-';
	} elseif (($perms & 0x6000) == 0x6000) {
	    /* Block special */
	    $info = 'b';
	} elseif (($perms & 0x4000) == 0x4000) {
	    /* Dossier */
	    $info = 'd';
	} elseif (($perms & 0x2000) == 0x2000) {
	    /* Caractère spécial */
	    $info = 'c';
	} elseif (($perms & 0x1000) == 0x1000) {
	    /* pipe FIFO */
	    $info = 'p';
	} else {
	    /* Inconnu */
	    $info = 'u';
	}

	/* Autres */
	$info .= (($perms & 0x0100) ? 'r' : '-');
	$info .= (($perms & 0x0080) ? 'w' : '-');
	$info .= (($perms & 0x0040) ?
	            (($perms & 0x0800) ? 's' : 'x' ) :
	            (($perms & 0x0800) ? 'S' : '-'));

	/* Groupe */
	$info .= (($perms & 0x0020) ? 'r' : '-');
	$info .= (($perms & 0x0010) ? 'w' : '-');
	$info .= (($perms & 0x0008) ?
	            (($perms & 0x0400) ? 's' : 'x' ) :
	            (($perms & 0x0400) ? 'S' : '-'));

	/* Tout le monde */
	$info .= (($perms & 0x0004) ? 'r' : '-');
	$info .= (($perms & 0x0002) ? 'w' : '-');
	$info .= (($perms & 0x0001) ?
	            (($perms & 0x0200) ? 't' : 'x' ) :
	            (($perms & 0x0200) ? 'T' : '-'));

	return $info;
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- CONVERTI UN NOMBRE EN LETTRES
 http://openclassrooms.com/forum/sujet/convertir-les-chiffres-en-lettres-php-28018#message-5527889
*/

public static function int2str( $a )
  {
	$convert = explode( "." , $a );

	if( $a < 0 )
	  {
	  	return "moins ".self::int2str(-$a);
	  }

	if( $a < 17 )
	  {
		switch ($a)
		  {
			case 0: return "zéro";
			case 1: return "un";
			case 2: return "deux";
			case 3: return "trois";
			case 4: return "quatre";
			case 5: return "cinq";
			case 6: return "six";
			case 7: return "sept";
			case 8: return "huit";
			case 9: return "neuf";
			case 10: return "dix";
			case 11: return "onze";
			case 12: return "douze";
			case 13: return "treize";
			case 14: return "quatorze";
			case 15: return "quinze";
			case 16: return "seize";
		  }
	  }
	else if( $a < 20 )
	  {
		return "dix-".self::int2str( $a-10 );
	  }
	else if( $a < 100 )
	  {
		if($a%10==0)
		  {
			switch ($a)
			  {
				case 20: return "vingt";
				case 30: return "trente";
				case 40: return "quarante";
				case 50: return "cinquante";
				case 60: return "soixante";
				case 70: return "soixante-dix";
				case 80: return "quatre-vingt";
				case 90: return "quatre-vingt-dix";
			  }
		  }
		elseif( substr($a, -1)==1 )
		  {
			if( ((int)($a/10)*10)<70 )
			  {
				return self::int2str((int)($a/10)*10)."-et-un";
			  }
			else if( $a==71 )
			  {
				return "soixante-et-onze";
			  }
			else if( $a==81 )
			  {
				return "quatre-vingt-un";
			  }
			else if( $a==91 )
			  {
				return "quatre-vingt-onze";
			  }
		  }
		if ($a < 70)
		  {
			return self::int2str($a - $a % 10) . "-" . self::int2str($a % 10);
		  }
		else if ($a < 80)
		  {
			return self::int2str(60) . "-" . self::int2str($a % 20);
		  }
		else
		  {
			return self::int2str(80) . "-" . self::int2str($a % 20);
		  }
	  }
	else if ($a == 100)
	  {
		return "cent";
	  }
	else if ($a < 200)
	  {
		return self::int2str(100) . " " . self::int2str($a % 100);
	  }
	else if ($a < 1000)
	  {
		return self::int2str((int)($a / 100)) . " " . self::int2str(100) . " " . self::int2str($a % 100);
	  }
	else if ($a == 1000)
	  {
		return "mille";
	  }
	else if ($a < 2000)
	  {
		return self::int2str(1000) . " " . self::int2str($a % 1000) . " ";
	  }
	else if ($a < 1000000)
	  {
		return self::int2str((int)($a / 1000)) . " " . self::int2str(1000) . " " . self::int2str($a % 1000);
	  }
	else if ($a == 1000000)
	  {
		return "millions";
	  }
	else if ($a < 2000000)
	  {
		return self::int2str(1000000) . " " . self::int2str($a % 1000000) . " ";
	  }
	else if ($a < 1000000000)
	  {
		return self::int2str((int)($a / 1000000)) . " " . self::int2str(1000000) . " " . self::int2str($a % 1000000);
	  }
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- CALCUL UNE EQUATION DANS UNE CHAINE DE CARACTÈRES
 http://php.net/manual/fr/function.eval.php#92603
*/

public static function matheval( $equation )
  {
	$equation = preg_replace( "/[^0-9+\-.*\/()%]/" , "",$equation );
	$equation = preg_replace( "/([+-])([0-9]+)(%)/" , "*(1\$1.\$2)",$equation );
	$equation = preg_replace( "/([0-9]+)(%)/" , ".\$1",$equation );

	if( $equation == "" )
	  {
		$return = 0;
	  }
	else
	  {
		eval( "\$return=".$equation.";" );
	  }
	
	return $return;
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- VÉRIFIE SI LA CHAINE EST AU FORMAT JSON
 http://stackoverflow.com/a/6041773/851728
*/

public static function is_json( $data )
  {
	json_decode( $data );
	return (json_last_error() == JSON_ERROR_NONE);
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- PARSE UN FICHIER CSS ET RETOURNE UN TABLEAU AVEC LES CLASSES ET ID
 https://stackoverflow.com/posts/6589386/revisions
*/

public static function parse_css( $file )
  {
	$css = file_get_contents( $file );
	preg_match_all( "/(?ims)([a-z0-9\s\.\:#_\-@,]+)\{([^\}]*)\}/", $css , $arr );
	$result = array();
	
	foreach( $arr[0] as $i => $x )
	  {
		$selector		= trim( preg_replace( "/\s+/" , "" , $arr[1][$i] ) );
		$rules 		= explode( ";" , trim($arr[2][$i]) );
		$rules_arr		= array();
	
		foreach( $rules as $strRule )
		  {
			if( !empty($strRule) )
			  {
				$rule = explode(":", $strRule);
				$rules_arr[ trim( $rule[0] ) ] = isset($rule[1]) ? trim($rule[1]) : "";
			  }
		  }
	
		$selectors = explode( "," , trim($selector) );
		
		foreach ($selectors as $strSel)
		  {
			$result[$strSel] = $rules_arr;
		  }

	  }
    return $result;

  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LES INFOS D'UNE URL D'UN MEDIA SOCIAL (Youtube, Instagram, etc...)
 https://stackoverflow.com/a/36876681
*/

public static function get_social_url_infos( $url )
  {
  	$return = array(
  		"site"	=> "",
  		"url"		=> "",
  		"iframe"	=> ""
  	);

	/* ------------------------------- YOUTUBE */
	
	preg_match( "/^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/" , $url , $match );
	if( $match && strlen($match[1]) === 11 )
	  {
	  	$return = array(
	  		"site"	=> "Youtube",
	  		"url"		=> $match[0],
	  		"iframe"	=> "<iframe frameborder='0' allowfullscreen type='text/html' src='//www.youtube.com/embed/{$match[1]}?showinfo=0'></iframe>"
	  	);
	  }
	
	/* ------------------------------- INSTAGRAM */   /* REVOIR L'IFRAME ... */
	preg_match( "/^(?:https?:\/\/)?(?:www\.)?instagram.com\/p\/(.[a-zA-Z0-9\_]*)/" , $url , $match );
	if( $match && strlen($match[0]) )
	  {
	  	$return = array(
	  		"site"	=> "Instagram",
	  		"url"		=> $match[0],
	  		"iframe"	=> "<iframe frameborder='0' allowfullscreen type='text/html' src='{$match[0]}/embed/'></iframe>"
	  	);
	  }
	
	/* ------------------------------- VIMEO */
	
	preg_match( "/^(?:https?:\/\/)?(?:www\.)?(player.)?vimeo.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/" , $url , $match );
	if( $match && strlen($match[3]) )
	  {
	  	$return = array(
	  		"site"	=> "Vimeo",
	  		"url"		=> $match[0],
	  		"iframe"	=> "<iframe frameborder='0' allowfullscreen type='text/html' src='//player.vimeo.com/video/{$match[3]}'></iframe>"
	  	);
	  }
	

	/* ------------------------------- DAILYMOTION */

	preg_match( "/.+dailymotion.com\/(video|hub)\/([^_]+)[^#]*(#video=([^_&]+))?/" , $url , $match );
	if( $match && strlen($match[2]) )
	  {
	  	$return = array(
	  		"site"	=> "Dailymotion",
	  		"url"		=> $match[0],
	  		"iframe"	=> "<iframe frameborder='0' allowfullscreen type='text/html' src='//www.dailymotion.com/embed/video/{$match[2]}'></iframe>"
	  	);
	  }

    return $return;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- CONVERTIT UN TABLEAU PHP EN XML
*/

public static function array_to_xml( $array, $wrap="root" )
  {
	$xml = "";
	if ($wrap != null)
	  {
		$xml .= "\n<".$wrap.">\n";
	  }

	foreach ($array as $key=>$value)
	  {
		$xml .= "\t<".$key.">".htmlspecialchars(trim($value))."</".$key.">\n";
	  }

	if( $wrap != null )
	  {
		$xml .= "\n</".$wrap.">\n";
	  }
	return $xml;
  } 


/* --------------------------------------------------------------------------------------------------------------------------------------------- VÉRIFIE SI UNE URL EXISTE
https://stackoverflow.com/questions/2280394/how-can-i-check-if-a-url-exists-via-php
https://stackoverflow.com/questions/40830265/php-errors-with-get-headers-and-ssl
*/

public static function url_exists( $url )
  {
	stream_context_set_default([
		"ssl" => [
				"verify_peer" 		=> false,
				"verify_peer_name" 	=> false
			   ],
	]);

	$file_headers = @get_headers( $url );
	
	if( !$file_headers || ( $file_headers[0] == "HTTP/1.1 404 Not Found" ) )
	  {
		return false;
	  }
	else
	  {
		return true;
	  }
  }  


/* --------------------------------------------------------------------------------------------------------------------------------------------- STREAM UN FICHIER
Modified from : https://stackoverflow.com/questions/6914912/streaming-a-large-file-using-php
*/

public static function stream_file( $file , $retbytes = true , $cache = true )
  {
	if( is_file( $file ) AND is_readable($file) )
  	  {
		$mime_type	= self::mime_type( $file );
		$buffer 	= "";
		$cnt    	= 0;
		$handle 	= fopen( $file , "rb" );

		header( "Content-Type: ".$mime_type );

		if( $cache != false )
		  {
			$duree 	= is_numeric($cache) ? $cache : 864000; /* 864000 secondes : 10 jours */
			$expires	= gmdate("D, d M Y H:i:s", time() + $duree) . " GMT";

			header( "Expires: $expires" );
			header( "Pragma: cache" );
			header( "Cache-Control: max-age=$duree" );

		  }

		if( $handle === false )
		  {
			return false;
		  }

		while( !feof($handle) )
		  {
			$buffer = fread( $handle , ( 1024 * 1024 ) );
			echo $buffer;
			ob_flush();
			flush();

			if( $retbytes )
			  {
				$cnt += strlen( $buffer );
			  }
		  }

		$status = fclose($handle);

		if ($retbytes && $status)
		  {
			return $cnt;
		  }

		return $status;

  	  }
  	else
  	  {
  	  	return false;
  	  }
  }





/**
*    / FIN \
*/

}






