<?php

/** --------------------------------------------------------------------------------------------------------------------------------------------
* Author		: @ptibat
* Dev start		: 17/03/2016
* Last modif	: 13/07/2021 15:41
* Description	: Gestion des formulaires
--------------------------------------------------------------------------------------------------------------------------------------------- */

class formulaire {

/*
	FUNCTIONS :

	champ_formulaire( $options = array() )
	clean_tinymce( $data )
	display( $fields = array() , $data = null )
	get_data( $fields = array() , $data = array() )
	get_fields_names( $fields = array() )
	get_html( $data=array() , $type="table" )
	prepare_sql_data( $fields = array() , $post = array() , $sql_data = array() , $clean = true , $multiple_separator = ";" )
*/


/* --------------------------------------------------------------------------------------------------------------------------------------------- VARIABLES */




/* --------------------------------------------------------------------------------------------------------------------------------------------- RENVOYE UNE ERREUR LORS DE L'EXECUTION D'UNE FONCTION INEXISTANTE */
public static function __callStatic( $m , $a )
  {
	echo __CLASS__." : La fonction appelée \"".$m."\" est inexistante.\n";
	exit;
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- */
public static function get_html( $data=array() , $type="table" )
  {
  	/**
  	*
  	* Types :
  	*	- table
  	*	- div
  	*	- label
  	*
  	*/
  }




/* --------------------------------------------------------------------------------------------------------------------------------------------- GERE LES CHAMPS DE FORMULAIRE ET RETOURNE POUR L'AFFICHAGE */
public static function display( $fields = array() , $data = null )
  {
  	$html		= "";
  	$modif	= !is_null($data) ? true : false;

  	if( is_array($fields) AND !empty($fields) )
  	  {
		foreach( $fields as $field_name => $field )
		  {
		  	if(		( !$modif AND ( !isset($field["modif_only"]) OR ( $field["modif_only"] != true ) ) )
				OR  	( $modif AND ( !isset($field["create_only"]) OR ( $field["create_only"] === false ) ) )
			  )
		  	  {
			  	$field["name"] = $field_name;
	
			  	if( $modif AND is_array($data) )
			  	  {
				  	$field["data"] = $data;
			  	  }
	
				$html .= self::champ_formulaire( $field );
		  	  }
		  }
  	  }

	return $html;
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- GENERE LE CODE HTML POUR UNE LIGNE DE FORMULAIRE */
public static function champ_formulaire( $options = array() )
  {
	global $_HTML;
  
	$default = array( 
		"required"			=> false,
		"multilang"			=> false,
		"create_only"		=> false,
		"modif_only"		=> false,
		"lines"			=> array(),
		"type"			=> "text",		/* text, date, password, textarea, select ... */
		"id"				=> null,
		"name"			=> null,
		"titre"			=> "",
		"class"			=> "",
		"pattern"			=> "",
		"extras"			=> "",
		"html_before"		=> "",
		"html_after"		=> "",
		"radio"			=> array(),		/* Valeurs possibles pour des puces radio */
		"select"			=> array(),		/* Valeurs possibles pour un select */
		"value"			=> null,
		"data"			=> null,
		"multiple"			=> false,
		"multiple_max"		=> null,
		"multiple_separator"	=> ";"
	);

	$options	= is_array($options) ? array_merge( $default , $options ) : $default;
  	$html		= "";

	if( $options["name"] !== null )
	  {
		if( $options["id"] == null )
		  {
			$options["id"] = $options["type"]."_".functions::microtime(true);
		  }

		if(       ( $options["multilang"] == true ) 
			AND !is_null($_HTML)
			AND isset($_HTML["multilang"]) AND ( $_HTML["multilang"] == true )
			AND isset($_HTML["langs"]) AND !empty($_HTML["langs"]) AND ( count($_HTML["langs"]) > 0 )
		  )
		  {
		  	foreach( $_HTML["langs"] as $code => $langue )
		  	  {
		  	  	$options["lines"][] = array(
		  	  					"id" 		=> $options["id"]."_".$code,
		  	  					"name" 	=> $options["name"]."_".$code,
		  	  					"titre"	=> $options["titre"]." <span class='tag small bleu_clair'>".$langue."</span>"
		  	  				   );
		  	  }
		  }
		else
		  {
	  	  	$options["lines"][] = array(
	  	  					"id" 		=> $options["id"],
	  	  					"name" 	=> $options["name"],
	  	  					"titre"	=> $options["titre"]
	  	  				   );
		  }


	  	foreach( $options["lines"] as $line )
	  	  {
	  	  	$field 		= "";
	  	  	$type 		= $options["type"];
	  	  	$multiple 		= ( isset($options["multiple"]) AND ( $options["multiple"] == true ) ) ? true : false;
	  	  	$name 		= $line["name"];
	  	  	$id			= $line["id"];
	  	  	$class		= !empty($options["class"]) ? " class='".$options["class"]."'" : "";
	  	  	$pattern		= !empty($options["pattern"]) ? " pattern=\"".$options["pattern"]."\"" : "";
	  	  	$extras		= !empty($options["extras"]) ? " ".$options["extras"] : "";
	  	  	$placeholder	= !empty($options["placeholder"]) ? " placeholder='".$options["placeholder"]."'" : "";
	  	  	$required		= ( $options["required"] === true ) ? " required" : "";
			$value		= ( is_array($options["data"]) AND isset($options["data"][$name]) ) ? $options["data"][$name] : ( ( isset($options["value"]) AND !is_null($options["value"]) ) ? $options["value"] : null );
			$commons		= "name='".$name.( $multiple ? "[]" : "" )."' id='".$id."'".$class.$pattern.$required.$placeholder.$extras;



			if( preg_match( "#^(button|submit|color|date|datetime|datetime-local|email|month|number|password|search|tel|text|time|url|week)$#" , $options["type"] ) )
			  {
				$field .= "<input type='".$options["type"]."' ".$commons.( !is_null($value) ? " value=\"".$value."\"" : "" )." />";
			  }

			else if( $type == "textarea" )
			  {
				$field .= "<textarea ".$commons.">".( !is_null($value) ? $value : "" )."</textarea>"; /* ( preg_match( "#tinymce#" , $class ) ? nl2br( $value ) : $value ) */
			  }
			
			else if( $type == "checkbox" )
			  {
			  	$field .= "
			  	<span class='".$options["type"]."'>
					<input type='".$options["type"]."' name='".$name."' id='".$id."'".$class.( ( !is_null($value) AND ($value == "1" ) ) ? " checked" : "" )." />
					<label for='".$id."'></label>
				</span>";
			  }
			
			else if( $type == "select" )
			  {
			  	$field .= "
			  	<select ".$commons.( $multiple ? " multiple %%%MULTIPLE_SIZE%%%" : "" ).">";
			  
			  	if( !empty($value) AND $multiple )
			  	  {
			  	  	$value = explode( $options["multiple_separator"] , $value );
			  	  }
			  	else
			  	  {
			  	  	$value = array( $value => $value );
			  	  }


			  	if( is_array($options["select"]) AND !empty($options["select"]) )
			  	  {
					if( count($options["select"]) == count( $options["select"] , COUNT_RECURSIVE ) )
					  {
					  	$nb_options = count( $options["select"] , COUNT_RECURSIVE );

				  	  	foreach( $options["select"] as $val => $text )
				  	  	  {
				  	  	  	$field .= "<option value='".$val."'".( ( !is_null($value) AND in_array( $val , $value ) ) ? " selected data-current-selected='true'" : "" ).">".$text."</option>";
				  	  	  }
					  }
					else
					  {

					  	$nb_options = ( count( $options["select"] , COUNT_RECURSIVE ) - count($options["select"]) );

				  	  	foreach( $options["select"] as $group => $data )
				  	  	  {
				  	  	  	if( is_array( $data ) )
				  	  	  	  {
				  	  	  	  	$field .= "<optgroup label=\"".$group."\">";

								foreach( $data as $val => $text )
						  	  	  {
						  	  	  	$field .= "<option value='".$val."'".( ( !is_null($value) AND in_array( $val , $value ) ) ? " selected data-current-selected='true'" : "" ).">".$text."</option>";
						  	  	  }
	
								$field .= "</optgroup>";

				  	  	  	  }
							else
							  {
				  	  	  		$field .= "<option value='".$group."'".( ( !is_null($value) AND in_array( $group , $value ) ) ? " selected data-current-selected='true'" : "" ).">".$data."</option>";
							  }
				  	  	  }
					  }


					$nb_max = ( !is_null($options["multiple_max"]) AND is_numeric($options["multiple_max"]) AND ( $options["multiple_max"] <= $nb_options ) ) ? $options["multiple_max"] : $nb_options;

					$field = str_replace( "%%%MULTIPLE_SIZE%%%" , "size='".$nb_max."'" , $field );

			  	  }

			  	$field .= "
			  	</select>";
			  }
			  
			else if( ( $type == "radio" ) AND isset($options["radio"]) AND is_array($options["radio"]) AND !empty($options["radio"]) )
			  {
			  	$i = 0;

			  	foreach( $options["radio"] as $code => $txt )
			  	  {
			  	  	$i++;

			  	  	$id_tmp = $id."_".$code;

				  	$field .= "
				  	<div>
				  	<span class='".$options["type"]."'>
						<input type='".$options["type"]."' name='".$name."' id='".$id_tmp."' value=\"".$code."\"".$class;

						if( ( !is_null($value) AND ($value == $code ) ) )
						  {
						  	$field .= " checked";
						  }

						else if( is_null($value) AND ( $i == "1" ) )
						  {
						  	$field .= " checked";
						  }
						
						$field .= " />
						<label for='".$id_tmp."'></label>
						".$txt."
					</span>
					</div>";
			  	  }
			  }

			else if( $type == "range" )
			  {
				$range = array( 
					"id"		=> $id."_range_value",
					"min"		=> null,
					"max"		=> null,
					"step"	=> null,
					"unit"	=> null
				);
			
				$range		= ( isset($options["range"]) AND is_array($options["range"]) ) ? array_merge( $range , $options["range"] ) : $range;
				$range["unit"]	= ( !is_null($range["unit"]) ? $range["unit"] : "" );

				$field .= "<input type='".$options["type"]."' ".$commons.( !is_null($value) ? " value=\"".$value."\"" : "" );
				$field .= !is_null($range["min"]) 	? " min='".$range["min"]."'" : "";
				$field .= !is_null($range["max"]) 	? " max='".$range["max"]."'" : "";
				$field .= !is_null($range["step"]) 	? " step='".$range["step"]."'" : "";
				$field .= " oninput=\"_fiches_range( '".$range["id"]."' , this.value , '".$range["unit"]."' );\"";
				$field .= " onchange=\"_fiches_range( '".$range["id"]."' , this.value , '".$range["unit"]."' );\"";
				$field .= " />";
				$field .= " <span id='".$range["id"]."'>".$value.$range["unit"]."</span>";
				$field .= "<script>setTimeout(function(){_fiches_range( '".$range["id"]."' , $('#".$id."').val() , '".$range["unit"]."' ); } , 500 );</script>";

			  }
		  



			/* ------------------------------------------------------- */

			if( !empty($field) )
			  {
				$html .= "
				<div class='ligne".$required."'>
					<div class='label'>".$line["titre"]."</div>
					<div class='field'>"
						.( !empty($options["html_before"]) ? $options["html_before"] : "" )
						.$field
						.( !empty($options["html_after"]) ? $options["html_after"] : "" )
					."</div>
				</div>";
			  }

			else if( ( $type == "separator" ) AND isset($line["titre"]) )
			  {
				$html .= "<div class='ligne_titre'>".$line["titre"]."</div>";
			  }

			/* ------------------------------------------------------- */


	  	  }

	  }

	return $html;
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LES NOMS ET TYPES DE CHAMPS */
public static function get_fields_names( $fields = array() )
  {
  	global $_HTML;

  	$fields_names = array();

  	if( is_array($fields) AND !empty($fields) )
  	  {
		foreach( $fields as $field_name => $field )
		  {
			if(	    isset($field["multilang"]) AND ( $field["multilang"] == true ) 
				AND !is_null($_HTML)
				AND isset($_HTML["multilang"]) AND ( $_HTML["multilang"] == true )
				AND isset($_HTML["langs"]) AND !empty($_HTML["langs"]) AND ( count($_HTML["langs"]) > 0 )
			  )
			  {
			  	foreach( $_HTML["langs"] as $code => $langue )
			  	  {
			  	  	$fields_names[ $field_name."_".$code ] = $field["type"];
			  	  }
			  }
			else
			  {
		  	  	$fields_names[ $field_name ] = $field["type"];
			  }
		  }
  	  }

	return $fields_names;
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LES DONNÉES POUR LA REQUETTE SQL */
public static function get_data( $fields = array() , $data = array() )
  {
  	global $_HTML;

	$sql_data = array();

	if( is_array($fields) AND is_array($data) )
	  {
	  	$fields_names = self::get_fields_names( $fields );

		foreach( $fields_names as $field_name => $type )
		  {
			if( $type == "checkbox" )
			  {
				$sql_data[ $field_name ] = ( isset($data[ $field_name ]) AND ( $data[ $field_name ] == "on" ) ) ? "1" : "0";
			  }
			else if( $type != "separator" )
			  {
				$sql_data[ $field_name ] = isset($data[ $field_name ]) ? trim( $data[ $field_name ] ) : "";
			  }
		  }
	  }

	return $sql_data;
  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LA VARIABLE sql_data AVEC LES DONNÉES DU POST */
public static function prepare_sql_data( $fields = array() , $post = array() , $sql_data = array() , $clean = true , $multiple_separator = ";" )
  {
	$fields_names = self::get_fields_names( $fields );

	foreach( $fields_names as $field_name => $type )
	  {
		if( $type == "checkbox" )
		  {
			$sql_data[ $field_name ] = ( isset($post[ $field_name ]) AND ( $post[ $field_name ] == "on" ) ) ? "1" : "0";
		  }
		else if( $type != "separator" )
		  {

		  	if( isset($post[ $field_name ]) )
		  	  {
		  		if( is_array($post[ $field_name ]) )
		  		  {
		  	  		$data = implode( $multiple_separator , $post[ $field_name ] );
		  		  }
		  		else
		  		  {
		  		  	$data = trim( $post[ $field_name ] );
		  		  }

			  	if( ( $type == "textarea" ) AND ( $clean == true ) )
			  	  {
			  		$data = self::clean_tinymce( $data );
			  	  }

				$sql_data[ $field_name ] = $data;

		  	  }
		  	else
		  	  {
				$sql_data[ $field_name ] = isset($sql_data[ $field_name ]) ? $sql_data[ $field_name ] : "";
		  	  }
		  }
	  }

	return $sql_data;

  }



/* --------------------------------------------------------------------------------------------------------------------------------------------- CORRECTIF POUR FIREFOX vs TINYMCE */
public static function clean_tinymce( $data )
  {
	$data = preg_replace( "#<div>(\s+|\xC2\xA0)<\/div>#u" , "<br />" , $data );
	$data = preg_replace( "#<div><\/div>#u" , "" , $data );

	return $data;
	
  }





}
























