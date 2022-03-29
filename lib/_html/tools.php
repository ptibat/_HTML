<?php

/** --------------------------------------------------------------------------------------------------------------------------------------------
* Author		: @ptibat
* Dev start		: 21/01/2013
* Last modif	: 29/03/2022 10:44
* Description	: Extension de la class APP avec un ensemble de fonctions communes à plusieurs templates
--------------------------------------------------------------------------------------------------------------------------------------------- */


/**
* UPDATE du 17/08/2017 : class tools extends app {
*/

/* --------------------------------------------------------------------------------------------------------------------------------------------- NAMESPACES */


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



/* --------------------------------------------------------------------------------------------------------------------------------------------- CLASSE TOOLS */

class tools {

/*
	FONCTIONS :

	affichage_select_option( $row , $value , $field , $current_id = null , $current_parent = null , $level = 0 )
	btn_confirm( $options = array() )
	build_parents_array( array $elements , $parentId = 0 )
	clean_tinymce_firefox( $data )
	code_google_tag_manager()
	code_google_tag_manager_amp()
	config()
	detect_adblock()
	get_pages( $structured = true , $order_by = "position ASC" , $admin = false )
	get_parents( $query , $structured = true )
	image_display_size( $width , $height , $max = 1200 )
	log( $text , $type="log" )
	maintenance()
	msg( $msg , $display=true )
	send_mail( $options = array() )
	titre( $titre , $original = true , $separator = " — " )
	url( $ref , $root_or_wat = true , $parameters = array() )
*/

/* --------------------------------------------------------------------------------------------------------------------------------------------- VARIABLES */

	var $html = array();



/* ---------------------------------------------------------------------------------------------------------------------------------------------  RENVOYE UNE ERREUR LORS DE L'EXECUTION D'UNE FONCTION INEXISTANTE */
public function __call( $function , $arguments )
  {
	echo __CLASS__." : La fonction appelée \"".$function."\" est inexistante. ".json_encode( $arguments );
  }

/* --------------------------------------------------------------------------------------------------------------------------------------------- CONSTRUCTEUR */
public function __construct()
  {
	/* ------------------------------------------------ _HTML */
	
	global $_HTML;
	$this->html = & $_HTML;

	/* ------------------------------------------------ INIT */

	$this->config();

  }
  
/* --------------------------------------------------------------------------------------------------------------------------------------------- DESTRUCTEUR */
public function __destruct(){}








/* --------------------------------------------------------------------------------------------------------------------------------------------- MODE MAINTENANCE */
public function maintenance( $text = null )
  {

	if( isset($this->html["config"]["maintenance_ip"]) AND !empty($this->html["config"]["maintenance_ip"]) )
	  {
	  	$ip	= functions::ip();
	  	$ips	= explode( ";" , $this->html["config"]["maintenance_ip"] );

		if( in_array( $ip , $ips ) )
		  {
		  	return true;
		  }
	  }

	$text = ( !is_null($text) ? $text : ( isset($this->html["config"]["maintenance_txt"]) ? $this->html["config"]["maintenance_txt"] : "Maintenance en cours" ) );


	header( "HTTP/1.1 503 Service Unavailable" );
	header( "Retry-After: 60" );

	$img = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAOXRFWHRTb2Z0d2FyZQBBbmltYXRlZCBQTkcgQ3JlYXRvciB2MS42LjIgKHd3dy5waHBjbGFzc2VzLm9yZyl0zchKAAAAOnRFWHRUZWNobmljYWwgaW5mb3JtYXRpb25zADUuMy4xNTsgYnVuZGxlZCAoMi4wLjM0IGNvbXBhdGlibGUpRif8
ZgAAAAhhY1RMAAAADAAAAABMvS0RAAAAGmZjVEwAAAAAAAAAEAAAABAAAAAAAAAAAAA8A+gAAIIkGDIAAAKqSURBVDiNdZNPSJNhHMe/vx/P8GXT5sG6JBE6SQJraQQlyLxIjr1ozoMEHkSUEBrRxTr4b1ggxA4iCtaKDnYwGGPvi7dYwyWFJ2X4D4wOQQiC7VXHmO/ep8sG0/SB3+H58v1+ni8P/IAzx+123xsZGQEA2Gy2tM1mSwNAIp
GAruuNZ/1cenG5XFN7e3s/IpGIBwCYeYuZtwDg8PCwxbKsVU3TXl0IUBRlgYiOjo+P+4sAItoCAMuy+gFkiGjhP0Bzc/OUx+NpTKVS6xUVFX2maXZ5PJ5KItpk5s2lpaVLALqIqNfn823ouu4uNqG2tra7+/v7q0SUsdvtA8vLy5/q6uoCdrtdX1tb+wkAuq5fl1J6VVWd1TStR0oZBqAwcxOFQiFomvYgk8kM5PP5bkVRJpLJ5JuznwUA
mqY9k1JOAFhk5rDD4fguHA4H4vH4CoAVv9//1DCMK+eFAYCIokKId+3t7UcAEI/HQR0dHWkAO0S0Q0Sbra2trwOBgHUeIJFIcDqdfgHgBoB6AC4mohyAHICslDJ70euFBgCQLfoB5EQ0Gr1cNAwPD1fu7u66AOycBzAMo0YI8d7r9f4tamJubo43NjZaTk5OBg4ODjqFEC8vAkgpH5qmORWLxSJEFHY6nV/F9vb2rWw2+wVApqysrHdmZi
YSDAafCyFiDQ0NvwrVr0kpO1VVDWma9ltK+VFK+dgwjCYCgKGhoQkhxOfp6elUMBjsyefzb8vLy686nc4nAFBdXT1rmuYfIupXVXVR1/WbUspuVVWDVFpxcnLSncvlvjHz4vj4eN/8/PwHABgcHOyLxWJhAD3MfN/n860XM6d2wbKsRwAUIUS4INUXBswcBqBIKf2lmVOA0dHRMUVR7tTW1iYLUg0AFwBUVVWtMPNtVVXHSjP/AE3mEMBw
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";

  	echo "<!DOCTYPE html>
	<html lang='fr'>
  	<head>
  		<title>".( isset($this->html["title"]) ? $this->html["title"] : "Maintenance" )."</title>
  	</head>
  	<body>
	  	<div style='padding:40px;background:#FFF8C4;color:#684300;font-family:monospace;font-size:14px;text-align:center;'>
	  			<img src='".$img."' alt='' />
	  		<br />
	  		<br />".$text."
	  	</div>
  	</body>
  	</html>
  	";
  	exit;
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- RECUPÈRE LA CONFIGURATION DANS LA BASE  */

public function config()
  {
	if( isset($this->html["db"]) AND ( $this->html["db"] !== null ) )
	  {
 	 	$config	= array();
		$query	= $this->html["db"]->query( "SELECT * FROM config ORDER BY ref ASC" );
	
		if( $query["nb"] > 0 )
		  {
			foreach( $query["data"] as $row )
			  {
				$config[ $row["ref"] ] = ( $row["value"] == "true" ) ? true : ( ( $row["value"] == "false" ) ? false : $row["value"] );
			  }
		  }
	
		$this->html["config"] = array_merge( $this->html["config"] , $config );
	  }
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- STOCK UN MESSAGE TEMPORAIRE */
  
public function msg( $msg , $display=true )
  {
	if( $display === false ) 
	  {
	  	$this->html["display"] = false;
	  }
  	
	$_SESSION["msg"] = str_replace( '"' , "&#34" , $msg );
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE UNE URL CONFIGURÉE DANS L'APP */
  
public function url( $ref , $root_or_wat = true , $parameters = array() )
  {
  	$return = "";

	if( isset($this->html["urls"]) AND is_array($this->html["urls"]) AND isset($this->html["urls"][ $ref ]) )
	  {
	  	$url = $this->html["urls"][ $ref ];
		$url = functions::url_parameters( $url , $parameters );

	  	$return = $url;
	  	  	
	  	if( !preg_match("~^(?:f|ht)tps?://~i" , $url ) AND ( $root_or_wat === true ) )
	  	  {
	  	  	$return = ROOT.$return;
	  	  }

	  }

	return $return;
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LE TITRE D'UNE PAGE FORMATÉ POUR LA SECTION <HEAD> */
  
public function titre( $titre , $original = true , $separator = " — " )
  {
	$titre .= ( $original === true ? $separator.$this->html["title"] : "" );	
	$this->html["title"] = $titre;
	return $titre;

  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- CALCUL LES DIMENSIONS D'UNE IMAGE A L'AFFICHAGE */
public function image_display_size( $width , $height , $max = 1200 )
  {
  	if( isset($max) AND is_numeric($max) )
	  {
	  	$max = $max;
	  }
	else if( isset($this->html["config"]["max_image_display_width"]) AND is_numeric($this->html["config"]["max_image_display_width"]) )
	  {
	  	$max = $this->html["config"]["max_image_display_width"];
	  }
	else
	  {
	  	$max = 1200;
	  }

  	$size = array( "width" => ( ( $width > $max ) ? $max : $width ) ,  "height" => $height );
  
  	if( is_numeric($width) AND ( $width >= $max ) AND is_numeric($height) AND ( $height > 0 ) )
  	  {
		$size["height"]	= ceil( $max * $height / $width );
  	  }

	return $size;
  }

 
 
/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE UN TABLEAU AVEC LA GESTION DES PARENTS/ENFANTS */

public function get_parents( $query , $structured = true )
  {
  	$ref_section	= "pages";
	$elements 		= array();
	$childs 		= array();
	
	$query = $this->html["db"]->query( $query );
	
	if( $query["nb"] > 0 )
	  {
		foreach( $query["data"] as $row )
		  {
			if( !empty($row["parent_id"]) )
			  {
			  	$childs[ $row["parent_id"] ][] = $row["id"];
			  }

			$row["childs"] = array();
			$elements[] = $row;
		  }

		if( $structured )
		  {
			$elements = $this->build_parents_array( $elements );
		  }
		else
		  {
			foreach( $childs as $id => $arr )
			  {
				$elements[ $id ]["childs"] = $arr;
			  }
		  }
	  }
	
	return $elements;
  }

public function build_parents_array( array $elements , $parentId = 0 )
  {
	$branch = array();

	foreach( $elements as $element )
	  {
		if( $element["parent_id"] == $parentId )
		  {
			$children = $this->build_parents_array( $elements , $element["id"] );
			if( $children )
			  {
				$element["childs"] = $children;
			  }

			$branch[] = $element;
		  }
	  }
	
	return $branch;
	
  }


  
/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE UN TABLEAU AVEC LES PAGES */
public function get_pages( $structured = true , $order_by = "position ASC" , $admin = false )
  {
  	$ref_section	= "pages";
	$pages 		= array();
	$childs 		= array();
	
	$query = "SELECT
				".$ref_section.".*,
				( SELECT COUNT(fiches.id) FROM fiches WHERE fiches.ref_section='".$ref_section."' AND fiches.ref_id=".$ref_section.".id ) AS nb_fiches
				
			FROM
				".$ref_section."
			
			".( ( ( $admin == true ) AND !isset($_GET["su"]) ) ? "WHERE ".$ref_section.".admin = '1'" : "" )."
				
			ORDER BY
				".$ref_section.".".$order_by;
	
	$pages = $this->get_parents( $query , $structured );
	
	return $pages;
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- AFFICHAGE D'UNE LIGNE D'UN SELECT AVEC GESTION DES PARENTS */

public function affichage_select_option( $row , $value , $field , $current_id = null , $current_parent = null , $level = 0 )
  {
	$ligne	= "";
	$tab		= "";
	$level	= ( is_numeric($level) AND ( $level >= 0 ) ) ? $level : 0;
	$selected	= ( ( $current_parent !== null ) AND ( $current_parent == $row["id"] ) ) ? " selected='selected'" : "";
	
	for( $t = 1 ; $t < ( $level + 1 ) ; $t++ )
	  {
	  	$tab .= "• ";
	  }

	if( $current_id != $row["id"] )
	  {
		$ligne .= "<option value='".$row[ $value ]."'".$selected.">".$tab.$row[ $field ]."</option>";
	  }

	if( !empty($row["childs"]) )
	  {
	  	$next_level = $level + 1;
	  
		foreach( $row["childs"] as $data )
		  {
	  		$ligne .= $this->affichage_select_option( $data , $value , $field , $current_id , $current_parent , $next_level );
		  }
	  }

	return $ligne;
  }




/* --------------------------------------------------------------------------------------------------------------------------------------------- ENVOI UN MAIL */

public function send_mail( $options = array() )
  {
	/* ---------------------------------------------------------------------------------- */
	
	$default = array( 
		"from_email"	=> null,
		"from_name"		=> null,
		"to"			=> null,
		"email_cc"		=> "",
		"email_cci"		=> "",
		"sujet"		=> "",
		"msg"			=> "",
		"msg_brut"		=> "",
		"attachments"	=> array(),
		"tracker"		=> "",
		"smtp" 		=> array(
						"host" 		=> "",
						"port" 		=> "",
						"user" 		=> "",
						"password" 		=> "",
					   ),
		"debug"		=> false
	);

	if( isset($options["to_email"]) )
	  {
	  	echo "Erreur, version trop ancienne...";exit;
	  }

	$options = is_array($options) ? array_merge( $default , $options ) : $default;


	/* ---------------------------------------------------------------------------------- */

  	if( !is_null($options["to"]) AND is_file( DOC_ROOT."/lib/tools/phpmailer/src/PHPMailer.php" ) )
  	  {
		/* ---------------------------------------------------------------------------------- INCLUDE */

		require_once( DOC_ROOT."/lib/tools/phpmailer/src/Exception.php" );
		require_once( DOC_ROOT."/lib/tools/phpmailer/src/PHPMailer.php" );
		require_once( DOC_ROOT."/lib/tools/phpmailer/src/SMTP.php" );

		$mail = new PHPMailer;


		/* ---------------------------------------------------------------------------------- CHECK MAIL FROM */

		if( isset($options["from_email"]) AND functions::check_email($options["from_email"]) )
		  {
		  }
		else if( isset($this->html["config"]["email"]["from"]) AND functions::check_email($this->html["config"]["email"]["from"]) )
		  {
		  	$options["from_email"] = $this->html["config"]["email"]["from"];

			if( ( !isset($options["from_name"]) OR empty($options["from_name"]) ) AND isset($this->html["config"]["email"]["from_name"]) )
			  {
			  	$options["from_name"] = $this->html["config"]["email"]["from_name"];
			  }
		  }
		else
		  {
			return false;
		  }


		if( is_null($options["from_email"]) AND isset($this->html["config"]["email"]["from_name"]) AND !empty($this->html["config"]["email"]["from_name"]) )
		  {
			$options["from_name"] = $this->html["config"]["email"]["from_name"];
		  }
		
		/* ---------------------------------------------------------------------------------- SMTP */

		if( !empty($options["smtp"]["host"]) AND !empty($options["smtp"]["user"]) AND !empty($options["smtp"]["password"]) )
		  {
			$mail->IsSMTP();
			$mail->Mailer	= "smtp";
			$mail->Host 	= $options["smtp"]["host"];
			$mail->Port		= !empty($options["smtp"]["port"]) ?? 25;

			if( $options["debug"] )
			  {
				$mail->SMTPDebug	= 2;
			  }
			$mail->SMTPSecure	= "ssl";
			$mail->SMTPAuth 	= true;
			$mail->Username 	= $options["smtp"]["user"];
			$mail->Password 	= $options["smtp"]["password"];

		  }

		else if( isset($this->html["config"]["email"]["smtp"]) AND is_array($this->html["config"]["email"]["smtp"]) )
		  {
		  	if( isset($this->html["config"]["email"]["smtp"]["server"]) AND !empty($this->html["config"]["email"]["smtp"]["server"]) )
		  	  {
				$mail->IsSMTP();
				$mail->Mailer 	= "smtp";
				$mail->Host 	= $this->html["config"]["email"]["smtp"]["server"];
		  	  }

		  	if( isset($this->html["config"]["email"]["smtp"]["port"]) AND is_numeric($this->html["config"]["email"]["smtp"]["port"]) )
		  	  {
				$mail->Port = $this->html["config"]["email"]["smtp"]["port"];
		  	  }

		  	if(       isset($this->html["config"]["email"]["smtp"]["user"]) AND !empty($this->html["config"]["email"]["smtp"]["user"])
		  		AND isset($this->html["config"]["email"]["smtp"]["password"]) )
		  	  {
				$mail->SMTPSecure	= "ssl";
				$mail->SMTPAuth 	= true;
				$mail->Username 	= $this->html["config"]["email"]["smtp"]["user"];
				$mail->Password 	= $this->html["config"]["email"]["smtp"]["password"];
		  	  }
		  }


		/* ---------------------------------------------------------------------------------- MESSAGE */

		if( !isset($options["msg_brut"]) OR empty($options["msg_brut"]) )
		  {
		  	$options["msg_brut"] = functions::texte_brut( $options["msg"] );
		  }


		/* ---------------------------------------------------------------------------------- TRACKER */

		if( isset($options["tracker"]) AND !empty($options["tracker"]) )
		  {
		  	$options["msg"] = $options["msg"]."<br /><img src='".$options["tracker"]."' alt='' width='1' height='1' border='0' />";
		  }



		/* ---------------------------------------------------------------------------------- PARAMETRES */
		
		$mail->CharSet 		= "UTF-8";
		$mail->XMailer 		= " ";

		$mail->setFrom( $options["from_email"], $options["from_name"] );
		$mail->addReplyTo( $options["from_email"], $options["from_name"] );
		$mail->Subject = $options["sujet"];
		$mail->msgHTML( $options["msg"] );
		$mail->AltBody = $options["msg_brut"];


		/* ---------------------------------------------------------------------------------- ADRESSES DESTINATAIRES */

		if( is_array($options["to"]) )
		  {
			foreach( $options["to"] as $email => $name )
			  {
			  	$email	= trim( $email );
			  	$name		= trim( $name );

			  	if( functions::check_email($email) )
			  	  {
					$mail->AddAddress( $email , $name );
			  	  }
			  }
		  }
		else if( !empty($options["to"]) AND functions::check_email($options["to"]) )
		  {
			$mail->AddAddress( $options["to"] );
		  }
		else
		  {
		  	return false;
		  }



		/* ---------------------------------------------------------------------------------- ADRESSES EN COPIE visible */
		
		if( is_array($options["email_cc"]) )
		  {	
			foreach( $options["email_cc"] as $email => $name )
			  {
			  	$email	= trim( $email );
			  	$name		= trim( $name );

			  	if( functions::check_email($email) )
			  	  {
					$mail->addCC( $email , $name );
			  	  }
			  }
		  }
		else if( !empty($options["email_cc"]) AND (strpos( $options["email_cc"] , ";" ) !== false ) )
		  {
		  	$options["email_cc"] = explode( ";" , $options["email_cc"] );

			foreach( $options["email_cc"] as $email => $name )
			  {
			  	$email	= trim( $email );
			  	$name		= trim( $name );

			  	if( functions::check_email($email) )
			  	  {
					$mail->addCC( $email , $name );
			  	  }
			  }

		  }
		else if( !empty($options["email_cc"]) AND functions::check_email($options["email_cc"]) )
		  {
			$mail->addCC( $options["email_cc"] );
		  }


		/* ---------------------------------------------------------------------------------- ADRESSES EN COPIE invisible */
		
		if( is_array($options["email_cci"]) )
		  {
			foreach( $options["email_cci"] as $email => $name )
			  {
			  	$email	= trim( $email );
			  	$name		= trim( $name );

			  	if( functions::check_email($email) )
			  	  {
					$mail->addBCC( $email , $name );
			  	  }
			  }
		  }
		else if( !empty($options["email_cci"]) AND (strpos( $options["email_cci"] , ";" ) !== false ) )
		  {
		  	$options["email_cci"] = explode( ";" , $options["email_cci"] );

			foreach( $options["email_cci"] as $email => $name )
			  {
			  	$email	= trim( $email );
			  	$name		= trim( $name );

			  	if( functions::check_email($email) )
			  	  {
					$mail->addBCC( $email , $name );
			  	  }
			  }

		  }
		else if( !empty($options["email_cci"]) AND functions::check_email($options["email_cci"]) )
		  {
			$mail->addBCC( $options["email_cci"] );
		  }




		/* ---------------------------------------------------------------------------------- PIÈCES JOINTES */

		if( !empty( $options["attachments"] ) )
		  {
		  	foreach( $options["attachments"] as $attachment )
		  	  {
		  	  	$file		= $attachment[0];
		  	  	$filename	= $attachment[1];

				if( is_file($file) )
				  {
					$mail->AddAttachment( $file , $filename );
				  }			
		  	  }
		  }
		
	
		/* ---------------------------------------------------------------------------------- */
		
		if( $mail->send() )
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

	/* ---------------------------------------------------------------------------------- */

  }


  
/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE UN BOUTON DE CONFIRMATION (oui-non) */
public function btn_confirm( $options = array() )
  {
	/* --------------------------------------------------- */

	$default = array( 
		"id"			=> null,
		"url"			=> "",
		"oui"			=> "OUI",
		"non"			=> "NON",
		"texte"		=> "En &ecirc;tes vous s&ucirc;r ?",
		"btn"			=> "Do it !",
	);

	$options = is_array($options) ? array_merge( $default , $options ) : $default;


	/* --------------------------------------------------- */

  	$html = "
	<div class='btn_confirm'".( !is_null($options["id"]) ? " id='".$options["id"]."'" : "" ).">
		<span class='btn_confirm_span_1' onclick=\"$(this).hide();$(this).parent().find('.btn_confirm_span_2').fadeIn('fast');\">".$options["btn"]."</span>
		<span class='btn_confirm_span_2'>
			".$options["texte"]."
			<a class='btn_confirm_link_oui' href='".$options["url"]."' onclick=\"$('.btn_confirm_span_2').fadeOut();\">".$options["oui"]."</a>
			<span class='btn_confirm_link_non' onclick=\"$('.btn_confirm_span_2').hide();$('.btn_confirm_span_1').fadeIn('fast');\">".$options["non"]."</span>
		</span>
	</div>";
	
	return $html;

	/* --------------------------------------------------- */
  }
	

  
/* --------------------------------------------------------------------------------------------------------------------------------------------- AJOUT LES CODES POUR GOOGLE TAG MANAGER VERSION PAGES AMP */
public function code_google_tag_manager_amp()
  {
  	if( isset($this->html["vars"]["google_tag_manager_amp"]) AND !empty($this->html["vars"]["google_tag_manager_amp"]) )
  	  {
  	  	if( !preg_match( "/amp-analytics/i" , $this->html["header_extras"] ) )
  	  	  {
			$this->html["header_extras"] .= "<script async custom-element='amp-analytics' src='https://cdn.ampproject.org/v0/amp-analytics-0.1.js'></script>";
			$this->html["body_extras"] .= "
			<amp-analytics config='https://www.googletagmanager.com/amp.json?id=".$this->html["vars"]["google_tag_manager_amp"]."&gtm.url=SOURCE_URL' data-credentials='include'>
			<script type='application/json'>
			  {
			    'vars': {
			      'gaTrackingId':'".$this->html["vars"]["google_analytics"]."'
			    }
			  }
			</script>
			</amp-analytics>";
  	  	  }

		return true;
  	  }
	else
	  {
	  	return false;
	  }
  }

  
/* --------------------------------------------------------------------------------------------------------------------------------------------- AJOUT LES CODES POUR GOOGLE TAG MANAGER */
public function code_google_tag_manager()
  {
  	if( isset($this->html["vars"]["google_tag_manager"]) AND !empty($this->html["vars"]["google_tag_manager"]) )
  	  {
  	  	if( !preg_match( "/googletagmanager.com\/gtm/i" , $this->html["header_extras"] ) )
  	  	  {
		$this->html["header_extras"] .= "
		<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','".$this->html["vars"]["google_tag_manager"]."');</script>";
	
			$this->html["body_extras"] .= "<noscript><iframe src='https://www.googletagmanager.com/ns.html?id=".$this->html["vars"]["google_tag_manager"]."' height='0' width='0' style='display:none;visibility:hidden'></iframe></noscript>";
  	  	  }

		return true;
  	  }
	else
	  {
	  	return false;
	  }
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- SCRIPT DE DETECTION D'ADBLOCK + Google Tag Manager */
public function detect_adblock()
  {
  	if( $this->code_google_tag_manager() )
  	  {
	  	$this->html["footer_extras"] .= "<div id='bottomAd' style='font-size:1px;'>&nbsp;</div>";
	
		$this->html["js_ready"] .= "
		window.dataLayer = window.dataLayer || [];
		window.setTimeout( function() 
		  {
			var bottomad = $('#bottomAd');
			if (bottomad.length == 1) 
			  {
				if (bottomad.height() == 0) 	{ dataLayer.push({'event': 'adBlock', 'blocked': 'true' }); } 
				else 					{ dataLayer.push({'event': 'adBlock', 'blocked': 'false' }); }
			  }      
		  }, 1 );";
  	  }
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- CORRECTIF POUR FIREFOX vs TINYMCE */
public function clean_tinymce_firefox( $data )
  {
	return preg_replace( "#<div>(\s+|\xC2\xA0)<\/div>#u" , "" , $data );
  }





/* --------------------------------------------------------------------------------------------------------------------------------------------- ENREGISTRE UN LOG EN BASE DE DONNÉES */
public function log( $text , $type="log" )
  {
	global $app;

	if( $this->html["db"]->table_exists( "logs" ) )
	  {
		$types	= array( "log" , "ajout" , "suppression" , "modif" , "login" );
		$type		= in_array( $type , $types ) ? $type : "log";
		$user_id	= $app->user->connected ? $app->user->infos["id"] : 0;

		$query = "INSERT INTO
		
					 logs

				 SET
					time			= '".time()."',
					microtime		= '".functions::microtime()."',
					type			= '".$type."',
					text 			= ".$this->html["db"]->protect( trim( $text ) ).",
					user_id		= '".$user_id."'";

		if( $this->html["db"]->insert( $query ) )
		  {
			return true;
		  }
		else
		  {
			return false;
		  }

	  }


  }








/* --------------------------------------------------------------------------------------------------------------------------------------------- + */



}



