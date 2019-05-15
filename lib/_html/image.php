<?php

/** ----------------------------------------------------------------------------------------------------------------------------
* Contact		: @ptibat
* Dev start		: 07/05/2007
* Last modif	: 14/02/2019 17:00
* Description	: Classe de fonctions pour la manipulation d'images
-------------------------------------------------------------------------------------------------------------------------------- */

class image {

/*
	FONCTIONS :

	cadrage( $width , $height=0 )
	couleurs_dominantes( $ressource , $ecart=4 )
	exif( $file , $tags="essentials" )
	fix_orientation( $file )
	flip(&$image, $x = 0, $y = 0, $width = null, $height = null)
	get_image( $file )
	get_image_info( $url , $wat=null )
	graph( $datas , $show_values=false , $sizes="400x150" , $output="screen" , $couleur_barres="808080" , $couleur_fond="F6F6F6" , $couleur_texte="333333" , $font=null )
	grille_gd($espacement, $image)
	imagettfbbox_fixed( $size, $angle, $font, $text)
	is_animated_gif( $filename )
	palette_de_couleurs( $ressource , $espacement=null , $distinct=true , $output=false , $show_matrix=false , $pixelize=false )
	pixelize( $file , $destination=null , $nb_pixel_width=30 )
	resize( $ressource , $size , $destination=null , $crop=false , $quality=100 , $square=false , $imageinterlace=true , $copyright=null , $noise_correction=true , $fix_orientation=true )
	resize_get_sizes( $src_largeur , $src_hauteur , $size , $square=null )
	rotate( $file , $angle=null , $output=null )
	square_image( $src , $out=null , $size=null )
	svg_sizes( $data )


*/


/* ----------------------------------------------------------------------------------------------------------------------------- CONSTRUCTEUR */
public function __construct()
  {
  }

/* ----------------------------------------------------------------------------------------------------------------------------- RENVOYE UNE ERREUR LORS DE L'EXECUTION D'UNE FONCTION INEXISTANTE */
public function __call( $m , $a )
  {
	echo __CLASS__." : La fonction appelée \"".$m."\" est inexistante.\n";
	exit;
  }

/* ----------------------------------------------------------------------------------------------------------------------------- RENVOYE UNE ERREUR LORS DE L'EXECUTION D'UNE FONCTION INEXISTANTE */
public static function __callStatic( $m , $a )
  {
	echo __CLASS__." : La fonction appelée \"".$m."\" est inexistante.\n";
	exit;
  }


/* ----------------------------------------------------------------------------------------------------------------------------- RENVOIE UNE RESSOURCE IMAGE */
public static function get_image( $file )
  {
	$ressource = null;

	if( is_file( $file ) )
	  {
		$ext = strtolower( substr( strrchr( $file , "." ) , 1 ) );
		switch( $ext )
		  {
			case "jpg" :	$ressource	= imagecreatefromjpeg( $file );	break;
			case "jpeg" :	$ressource	= imagecreatefromjpeg( $file );	break;
			case "png" :	$ressource	= imagecreatefrompng( $file );		break;
			case "webp" :	$ressource	= imagecreatefromwebp( $file );	break;
			case "gif" :	$ressource	= imagecreatefromgif( $file );		break;
			case "xbm" :	$ressource	= imagecreatefromxbm( $file );		break;
			case "xpm" :	$ressource	= imagecreatefromxpm( $file );		break;
			case "gd" :		$ressource	= imagecreatefromgd( $file );		break;
			case "gd2" :	$ressource	= imagecreatefromgd2( $file );		break;
			case "image" :	$ressource	= imagecreatefromwbmp( $file );	break;
		  }
	  }
	else
	  {
		if( $ressource = imagecreatefromstring( $file ) )
		  {
		  }
		else
		  {
			$ressource = null;
		  }
	  }

	return $ressource;
  }


/* ----------------------------------------------------------------------------------------------------------------------------- REDIMENSIONNE UNE IMAGE */
public static function resize( $ressource , $size , $destination=null , $crop=false , $quality=100 , $square=false , $imageinterlace=true , $copyright=null , $noise_correction=true , $fix_orientation=true )
  {
	/*$output		= ( $destination === null ) ? $ressource : $destination; */
	$file			= null;
	$ext			= strtolower(substr(strrchr($ressource,"." ),1));
	

	/* -------------------------------------------------------------- Ressource */
	
	if( !is_resource($ressource) )
	  {
		if( !is_file( $ressource ) ) { return false; }
		
		$file = $ressource;
		
		switch( $ext )
		  {
			case "jpg" :	$ressource	= imagecreatefromjpeg( $ressource );	break;
			case "jpeg" :	$ressource	= imagecreatefromjpeg( $ressource );	break;
			case "png" :	$ressource	= imagecreatefrompng( $ressource );		break;
			case "webp" :	$ressource	= imagecreatefromwebp( $ressource );	break;
			case "gif" :	$ressource	= imagecreatefromgif( $ressource );		break;
			default :		return false; 							break;
		  }
	  }


	/* -------------------------------------------------------------- Orientation */

	if( preg_match("#^(jpg|jpeg|pjpeg)$#" , $ext ) AND !is_null($file) AND $fix_orientation )
	  {
		$exif = exif_read_data( $file );
		
		if( !empty($exif["Orientation"]) )
		  {	
			switch ($exif["Orientation"])
			  {
				case 3 :	$ressource = imagerotate( $ressource, 180, 0 );	break;
				case 6 :	$ressource = imagerotate( $ressource, -90, 0 );	break;
				case 8 :	$ressource = imagerotate( $ressource, 90, 0 );	break;
			  }
		  }
	  }

		
	/* -------------------------------------------------------------- Sizes */

	$src_largeur		= imagesx($ressource);
	$src_hauteur	= imagesy($ressource);

	if( preg_match( "#^([0-9]+)%$#" , $size ) )					/* Pourcentage 			000% */
	  {
		$largeur	= ( $src_largeur * $size / 100 );
		$hauteur	= ( $src_hauteur * $size / 100 );
	  }
	else if( preg_match( "#^l:([0-9]+)$#" , $size ) )				/* Largeur définie		l:00 */
	  {
		$largeur	= str_replace( "l:" , "" , $size );
		$hauteur	= $largeur * $src_hauteur / $src_largeur;
	  }
	else if( preg_match( "#^h:([0-9]+)$#" , $size ) )				/* Hauteur définie		h:00 */
	  {
		$hauteur	= str_replace( "h:" , "" , $size );
		$largeur	= $hauteur * $src_largeur / $src_hauteur;
	  }
	else if( preg_match( "#^([0-9]+)x([0-9]+)$#" , $size ) )			/* Taille définie			00x00 */
	  {
		$size		= explode( "x" , $size );
		$largeur	= $size[0];
		$hauteur	= $size[1];
	  }
	else if( preg_match( "#^([0-9]+)$#" , $size ) )					/* Hauteur = Largeur */
	  {
		$largeur	= $size;
		$hauteur	= $size;
	  }
	else if( preg_match( "#^m:([0-9]+)$#" , $size ) )				/* Hauteur ou Largeur maxi	m:00 */
	  {
		$taille	= str_replace( "m:" , "" , $size );

		if( $src_largeur > $src_hauteur )
		  {
			$largeur	= $taille;
			$hauteur	= $largeur * $src_hauteur / $src_largeur;
		  }
		else if( $src_largeur < $src_hauteur )
		  {
			$hauteur	= $taille;
			$largeur	= $hauteur * $src_largeur / $src_hauteur;
		  }
		else
		  {
			$largeur	= $taille;
			$hauteur	= $taille;
		  }
	  }
	else
	  {
		$largeur	= $src_largeur;
		$hauteur	= $src_hauteur;
	  }


	$largeur = round( $largeur );
	$hauteur = round( $hauteur );


	/* -------------------------------------------------------------- Square image */
	if( $square === true )
	  {
		if( $largeur < $hauteur )
		  {
			$hauteur = $largeur;
		  }
		else if( $largeur > $hauteur )
		  {
			$largeur = $hauteur;
		  }
	  }

	/* -------------------------------------------------------------- Limit memory size */
	/*
	$ini		= trim( ini_get( "memory_limit" ) );
	$type		= strtolower( $ini[ strlen( $ini ) - 1 ] );
	$memory_set	= str_replace( $type , "" , $ini );

	switch( $type )
	  {
		case "g" : $memory_set *= 1024;
		case "m" : $memory_set *= 1024;
		case "k" : $memory_set *= 1024;
	  }


	$memory_needed = ( $largeur * $hauteur );
	$memory_needed = $memory_needed * 1.8;
	*/

	ini_set( "memory_limit" , "1024M" );

	/* -------------------------------------------------------------- New image */

	$image = imagecreatetruecolor( $largeur , $hauteur );

	/* -------------------------------------------------------------- Transparence PNG */

	if( $ext == "png" )
	  {
		imagealphablending( $image , false );
		imagesavealpha( $image , true );
	 	$transparent = imagecolorallocatealpha( $image , 255, 255, 255, 127 );
		imagefilledrectangle( $image, 0, 0 , $largeur, $hauteur, $transparent );
	  }
	/*
	else
	  {
		$blanc = imagecolorallocate($image, 255, 255, 255);
		imagefill( $image , 0 , 0 , $blanc );
	  }
	*/


	/* -------------------------------------------------------------- Noise correction ( 1/2 ) */
	/*
		imagefilter( $image , IMG_FILTER_BRIGHTNESS , 1 );
		A tester : imagescale()
	*/

	if( $noise_correction === true )
	  {
		imagefilter( $ressource , IMG_FILTER_NEGATE );
	  }

	/* -------------------------------------------------------------- Resize */

	if( $crop === true )
	  {
		$cx			= 0;
		$cy			= 0;
		$ratio_largeur	= $src_largeur / $largeur;
		$ratio_hauteur	= $src_hauteur / $hauteur;

		if( $ratio_hauteur < $ratio_largeur )
		  {
			$width 		= $src_largeur;
			$src_largeur	= $largeur * $ratio_hauteur;
			$cx			= ( $width - $src_largeur ) / 2;
		  }

		if( $ratio_largeur < $ratio_hauteur )
		  {
			$height		= $src_hauteur;
			$src_hauteur	= $hauteur * $ratio_largeur;
			$cy			= ( $height - $src_hauteur ) / 2;
		  }

		imagecopyresampled( $image , $ressource , 0 , 0 , $cx , $cy , $largeur , $hauteur , $src_largeur , $src_hauteur );
	  }
	else
	  {
		imagecopyresampled( $image , $ressource , 0 , 0 , 0 , 0 , $largeur , $hauteur , $src_largeur , $src_hauteur );
	  }


	/* -------------------------------------------------------------- Noise correction ( 2/2 ) */

	if( $noise_correction === true )
	  {
		imagefilter( $image , IMG_FILTER_NEGATE );
	  }



	/* -------------------------------------------------------------- Copyright */

	if( is_array($copyright) )
	  {	
		$default = array( 
			"color"	=> "0,0,0",
			"alpha" 	=> 0,
			"size" 	=> 14,
			"text" 	=> "",
			"font" 	=> "",
			"position" 	=> "br"
		);

		$copyright = array_merge( $default , $copyright );

		if( !empty($copyright["text"]) AND is_file($copyright["font"]) AND preg_match( "#^(ttf|otf)$#i" , functions::ext( $copyright["font"] ) ) )
		  {
			$color			= explode( "," , $copyright["color"] );
			$color	 		= imagecolorallocatealpha( $image, $color[0] , $color[1] , $color[2] , $copyright["alpha"] );
			$sizes			= imagettfbbox( $copyright["size"] , 0 , $copyright["font"] , $copyright["text"] );
			$txt_largeur		= abs( $sizes[2] - $sizes[0] );
			$txt_hauteur		= abs( $sizes[1] - $sizes[7] );
			$X				= $largeur - $txt_largeur - 10;
			$Y				= $hauteur - $txt_hauteur - 10;

			imagettftext( $image , $copyright["size"] , 0 , $X , ( $Y + $txt_hauteur ) , $color , $copyright["font"] , $copyright["text"] );
		  }
	  }



	/* -------------------------------------------------------------- Output */

	if( ( $destination == "screen" ) AND isset($ext) )
	  {
		switch( $ext )
		  {
			case "jpg" :
			case "jpeg" :
						if( $imageinterlace === true )
	  					  {
							imageinterlace( $image , true );
	  					  }

						header( "Content-type: image/jpeg" );
						imagejpeg( $image , NULL , $quality );
						break;

			case "png" :
						header( "Content-type: image/png" );
						imagepng( $image );
						break;

			case "webp" :
						header( "Content-type: image/webp" );
						imagewebp( $image );
						break;

			case "gif" :
						header( "Content-type: image/gif" );
						imagegif( $image );
						break;

			default :
						header( "Content-type: image/png" );
						imagepng( $image );
						break;
		  }
	  }
	else if( $destination !== null )
	  {
		$ext = strtolower(substr(strrchr($destination,"." ),1));
		if( isset($ext) )
		  {
			switch( $ext )
			  {
				case "jpg" :
				case "jpeg" :	
							if( $imageinterlace === true )
	  					   	  {
								imageinterlace( $image , true );
	  					  	  }

							imagejpeg( $image , $destination , $quality );
							break;

				case "png" :	imagepng( $image , $destination );				break;
				case "webp" :	imagewebp( $image , $destination , $quality );		break;
				case "gif" :	imagegif( $image , $destination );				break;
				default :		imagepng( $image , $destination );				break;
			  }
		  }
	  }
	else
	  {
		return $image;
	  }
  }


/* ----------------------------------------------------------------------------------------------------------------------------- RETOURNE LES DIMENSIONS POUR LE REDIMENSIONNE D'UNE IMAGE */
public static function resize_get_sizes( $src_largeur , $src_hauteur , $size , $square=null )
  {
	if( preg_match( "#^([0-9]+)%$#" , $size ) )					/* Pourcentage 			000% */
	  {
		$largeur	= ( $src_largeur * $size / 100 );
		$hauteur	= ( $src_hauteur * $size / 100 );
	  }
	else if( preg_match( "#^l:([0-9]+)$#" , $size ) )				/* Largeur définie		l:00 */
	  {
		$largeur	= str_replace( "l:" , "" , $size );
		$hauteur	= $largeur * $src_hauteur / $src_largeur;
	  }
	else if( preg_match( "#^h:([0-9]+)$#" , $size ) )				/* Hauteur définie		h:00 */
	  {
		$hauteur	= str_replace( "h:" , "" , $size );
		$largeur	= $hauteur * $src_largeur / $src_hauteur;
	  }
	else if( preg_match( "#^([0-9]+)x([0-9]+)$#" , $size ) )			/* Taille définie			00x00 */
	  {
		$size		= explode( "x" , $size );
		$largeur	= $size[0];
		$hauteur	= $size[1];
	  }
	else if( preg_match( "#^([0-9]+)$#" , $size ) )					/* Hauteur = Largeur */
	  {
		$largeur	= $size;
		$hauteur	= $size;
	  }
	else if( preg_match( "#^m:([0-9]+)$#" , $size ) )				/* Hauteur ou Largeur maxi	m:00 */
	  {
		$taille	= str_replace( "m:" , "" , $size );

		if( $src_largeur > $src_hauteur )
		  {
			$largeur	= $taille;
			$hauteur	= $largeur * $src_hauteur / $src_largeur;
		  }
		else if( $src_largeur < $src_hauteur )
		  {
			$hauteur	= $taille;
			$largeur	= $hauteur * $src_largeur / $src_hauteur;
		  }
		else
		  {
			$largeur	= $taille;
			$hauteur	= $taille;
		  }
	  }
	else
	  {
		$largeur	= $src_largeur;
		$hauteur	= $src_hauteur;
	  }


	$largeur = round( $largeur );
	$hauteur = round( $hauteur );


	/* -------------------------------------------------------------- Square image */
	if( $square === true )
	  {
		if( $largeur < $hauteur )
		  {
			$hauteur = $largeur;
		  }
		else if( $largeur > $hauteur )
		  {
			$largeur = $hauteur;
		  }
	  }

	/* -------------------------------------------------------------- No squared image */
	if( $square === false )
	  {
		if( $src_largeur > $src_hauteur )
		  {
			$hauteur	= $largeur * $src_hauteur / $src_largeur;
		  }
		else
		  {
			$largeur	= $hauteur * $src_largeur / $src_hauteur;
		  }
	  }


	/* -------------------------------------------------------------- End / Return */
	
	$largeur = round( $largeur );
	$hauteur = round( $hauteur );

	return array( "width" => $largeur , "height" => $hauteur );
  }


/* ----------------------------------------------------------------------------------------------------------------------------- RENVOI LE TYPE DE CADRAGE D'UNE IMAGE */
public static function cadrage( $width , $height=0 )
  {

	if( is_resource($width) )
	  {
		$image	= $width;
		$width	= imagesx( $image );
		$height	= imagesy( $image );
	  }
	else if( !is_numeric($width) AND is_file($width) )
	  {
		$ext = strtolower(substr(strrchr($width,"." ),1));
		switch( $ext )
		  {
			case "jpg" :
			case "jpeg" :	$image	= imagecreatefromjpeg( $width );	break;
			case "png" :	$image	= imagecreatefrompng( $width );	break;
			case "webp" :	$image	= imagecreatefromwebp( $width );	break;
			case "gif" :	$image	= imagecreatefromgif( $width );	break;
			default :		return false; 						break;
		  }

		$width	= imagesx( $image );
		$height	= imagesy( $image );
	  }


	if( $width == $height )
	  {
		return "carre";
	  }
	else if( $width < $height )
	  {
		return "portrait";
	  }
	else if( $width > $height )
	  {
		return "paysage";
	  }
	else
	  {
		return "error";
	  }
  }


/* ----------------------------------------------------------------------------------------------------------------------------- RENVOIE UNE RESSOURCE IMAGE */
public static function pixelize( $file , $destination=null , $nb_pixel_width=30 )
  {
	if( is_resource($file) )
	  {
		$src = $file;
	  }
	else
	  {
		$src = functions::get_image( $file );
	  }

	$largeur		= imagesx( $src );
	$hauteur		= imagesy( $src );
	$pixel_size 	= ceil( $largeur / $nb_pixel_width );
	$px_pointer 	= round( $pixel_size / 2 );
	$nb_pixel_height 	= ceil( $hauteur / $pixel_size );
	$output		= imagecreatetruecolor( $largeur , $hauteur );

	for( $h=0 ; $h<$nb_pixel_height ; $h++ )
	  {
			for( $w=0 ; $w<$nb_pixel_width ; $w++ )
			  {
				$x		= ( $w * $pixel_size );
				$y		= ( $h * $pixel_size );
				$pointer_x	= ( $x + $px_pointer );
				$pointer_y	= ( $y + $px_pointer );

				if( ( $pointer_x < $largeur ) AND ( $pointer_y < $hauteur ) )
				  {
					$couleur		= imagecolorat( $src , $pointer_x , $pointer_y );
					$R			= ( $couleur >> 16 ) & 0xFF;
					$G			= ( $couleur >> 8 ) & 0xFF;
					$B			= $couleur & 0xFF;
					$couleur_hexa	= sprintf( "%02X%02X%02X" , $R , $G , $B );

					$pixel_color = imagecolorallocate( $output , $R , $G ,$B );
					imagefilledrectangle( $output , $x , $y , $x+$pixel_size , $y+$pixel_size , $pixel_color );
				  }
			  }
	  }

	if( $destination == "screen" )
	  {
		$ext = functions::ext( $file );

		switch( $ext )
		  {
			case "jpg" :
			case "jpeg" :
						header( "Content-type: image/jpeg" );
						imagejpeg( $output , NULL , 100 );
						break;

			case "png" :
						header( "Content-type: image/png" );
						imagepng( $output );
						break;

			case "webp" :
						header( "Content-type: image/webp" );
						imagewebp( $output );
						break;

			case "gif" :
						header( "Content-type: image/gif" );
						imagegif( $output );
						break;

			default :
						header( "Content-type: image/png" );
						imagepng( $output );
						break;
		  }
	  }
	else if( $destination !== null )
	  {
		$ext = functions::ext( $file );

		switch( $ext )
		  {
			case "jpg" :
			case "jpeg" :	imagejpeg( $output , $destination , 100 );		break;
			case "png" :	imagepng( $output , $destination );				break;
			case "webp" :	imagewebp( $output , $destination , 100 );		break;
			case "gif" :	imagegif( $output , $destination );				break;
			default :		imagepng( $output , $destination );				break;
		  }
	  }
	else
	  {
		return $output;
	  }
  }


/* ----------------------------------------------------------------------------------------------------------------------------- RENVOI LA DOMINANTE DES COULEURS D'UNE IMAGE */
public static function couleurs_dominantes( $ressource , $ecart=4 )
  {
	if( !is_resource($ressource) )
	  {
		if( !is_file( $ressource ) ) { return false; }
		$ext = strtolower(substr(strrchr($ressource,"." ),1));

		switch( $ext )
		  {
			case "jpg" :	$ressource	= imagecreatefromjpeg( $ressource );	break;
			case "jpeg" :	$ressource	= imagecreatefromjpeg( $ressource );	break;
			case "png" :	$ressource	= imagecreatefrompng( $ressource );		break;
			case "webp" :	$ressource	= imagecreatefromwebp( $ressource );	break;
			case "gif" :	$ressource	= imagecreatefromgif( $ressource );		break;
			default :		return false; 							break;
		  }
	  }

	$largeur	= imagesx( $ressource );
	$hauteur	= imagesy( $ressource );

	$color_list	= array();
	for( $x = $ecart ; $x < $largeur ; $x = $x + $ecart )
	  {
		for( $y = $ecart ; $y < $hauteur ; $y = $y + $ecart )
		  {
			$rgb	= imagecolorat( $ressource , $x , $y );

			if( isset($color_list[$rgb]) AND in_array( $color_list[$rgb] , $color_list ) )
			  {
				$color_list[$rgb]++;
			  }
			else
			  {
				$color_list[$rgb]	= 0;
			  }
		  }
	  }

	natsort( $color_list );

	$sombres	= 0;
	$clairs	= 0;
	foreach ( $color_list as $key => $val )
	  {
		if( $key < 14145495 )
		  {
			$sombres += $val;
		  }
		else
		  {
			$clairs += $val;
		  }
	  }

	if( $sombres > $clairs )
	  {
		return 0;
	  }
	else
	  {
		return 1;
	  }


  }


/* ----------------------------------------------------------------------------------------------------------------------------- RENVOI LA PALETTE DE COULEURS D'UNE IMAGE */
public static function palette_de_couleurs( $ressource , $espacement=null , $distinct=true , $output=false , $show_matrix=false , $pixelize=false )
  {
	if( !is_resource($ressource) )
	  {
		if( !is_file( $ressource ) ) { return false; }
		$ext = strtolower( substr( strrchr( $ressource , "." ) , 1 ) );
		switch( $ext )
		  {
			case "jpg" :	$ressource	= imagecreatefromjpeg( $ressource );	break;
			case "jpeg" :	$ressource	= imagecreatefromjpeg( $ressource );	break;
			case "png" :	$ressource	= imagecreatefrompng( $ressource );		break;
			case "gif" :	$ressource	= imagecreatefromgif( $ressource );		break;
			default :		return false; 							break;
		  }
	  }

	if( $show_matrix == true )
	  {
		$matrix_color	= imagecolorallocate( $ressource , 240 , 70 , 70 );
		$matrix_black	= imagecolorallocate( $ressource , 0 , 0 , 0 );
	  }

	$color_list		= array();
	$largeur		= imagesx( $ressource );
	$hauteur		= imagesy( $ressource );

	if( preg_match( "#([0-9]+)%(([0-9]+)%)?#" , $espacement , $pourcentage ) )
	  {
		$espacement_x = floor( $largeur * $pourcentage[1] / 100 );

		if( isset($pourcentage[2]) )
		  {
			$espacement_y = floor( $hauteur * $pourcentage[2] / 100 );
		  }
		else
		  {
			$espacement_y = $espacement_x;
		  }
	  }
	else if( preg_match( "#([0-9]+)px(([0-9]+)px)?#" , $espacement , $pix ) )
	  {
		$espacement_x = $pix[1];

		if( isset($pix[2]) )
		  {
			$espacement_y = $pix[2];
		  }
		else
		  {
			$espacement_y = $pix[1];
		  }
	  }
	else
	  {
		$espacement_x = floor( $largeur * 5 / 100 );
		$espacement_y = floor( $hauteur * 5 / 100 );
	  }


	for( $y = $espacement_y ; $y < $hauteur ; $y = $y + $espacement_y )
	  {
		for( $x = $espacement_x ; $x < $largeur ; $x = $x + $espacement_x )
		  {
			$couleur		= imagecolorat( $ressource , $x , $y );
			$R			= ( $couleur >> 16 ) & 0xFF;
			$G			= ( $couleur >> 8 ) & 0xFF;
			$B			= $couleur & 0xFF;
			$couleur_hexa	= sprintf( "%02X%02X%02X" , $R , $G , $B );

			if( ($distinct==false) OR !in_array( $couleur_hexa , $color_list ) )
			  {
				$color_list[] = $couleur_hexa;
			  }

			if( $pixelize === true )
			  {
				/* -- A REVOIR -- */
				$pixel_color = imagecolorallocate( $ressource , $R , $G ,$B );
				imagefilledrectangle( $ressource , $x-$espacement_x , $y-$espacement_y , $x , $y , $pixel_color );
			  }
			else if( $show_matrix === true )
			  {
				imagefilledrectangle( $ressource , $x-1 , $y-1 , $x+1 , $y+1 , $matrix_color );
			  }
		  }
	  }


	if( $output == true )
	  {
		if( isset($ext) )
		  {
			switch( $ext )
			  {
				case "jpg" :
				case "jpeg" :	header( "Content-type: image/jpeg" );	imagejpeg( $ressource , null , 100 );	break;
				case "png" :	header( "Content-type: image/png" );	imagepng( $ressource );				break;
				case "webp" :	header( "Content-type: image/webp" );	imagewebp( $ressource , null , 100 );	break;
				case "gif" :	header( "Content-type: image/gif" );	imagegif( $ressource );				break;
				default :		header( "Content-type: image/png" );	imagepng( $ressource );				break;
			  }

			exit;
		  }
		else if( $output === true )
		  {
			header( "Content-type: image/png" );
			imagepng( $ressource );
			exit;
		  }
		else
		  {
			$ext = strtolower( substr( strrchr( $output , "." ) , 1 ) );

			switch( $ext )
			  {
				case "jpg" :
				case "jpeg" :	imagejpeg( $ressource , $output , 100 );	break;
				case "png" :	imagepng( $ressource , $output );		break;
				case "webp" :	imagewebp( $ressource , $output , 100 );	break;
				case "gif" :	imagegif( $ressource , $output );		break;
				default :		imagepng( $ressource , $output );		break;
			  }
		  }
	  }

	return $color_list;

  }



/* ----------------------------------------------------------------------------------------------------------------------------- ROTATE IMAGE */
public static function rotate( $file , $angle=null , $output=null )
  {
	if( ( $angle === null ) OR !is_numeric($angle) )
	  {
		$angle = 90;
	  }

	$src		= functions::get_image( $file );
	$largeur	= imagesx( $src );
	$hauteur	= imagesy( $src );
	$rotate	= imagerotate( $src , $angle , 0 );

	if( $output == "screen" )
	  {
		$ext = functions::ext( $file );

		switch( $ext )
		  {
			case "jpg" :
			case "jpeg" :
						header( "Content-type: image/jpeg" );
						imagejpeg( $rotate , NULL , 100 );
						break;

			case "png" :
						header( "Content-type: image/png" );
						imagepng( $rotate );
						break;

			case "webp" :
						header( "Content-type: image/webp" );
						imagewebp( $rotate );
						break;

			case "gif" :
						header( "Content-type: image/gif" );
						imagegif( $rotate );
						break;

			default :
						header( "Content-type: image/png" );
						imagepng( $rotate );
						break;
		  }
	  }
	else if( $output !== null )
	  {
		$ext = functions::ext( $output );

		switch( $ext )
		  {
			case "jpg" :
			case "jpeg" :	imagejpeg( $rotate , $output , 100 );		break;
			case "png" :	imagepng( $rotate , $output );			break;
			case "webp" :	imagewebp( $rotate , $output , 100 );		break;
			case "gif" :	imagegif( $rotate , $output );			break;
			default :		imagepng( $rotate , $output );			break;
		  }
	  }
	else
	  {
		return $rotate;
	  }
  }


/* ----------------------------------------------------------------------------------------------------------------------------- RENVOIE LES INFOS SUR UN IMAGE */
public static function get_image_info( $url , $wat=null )
  {
	$return = "";

	switch( $wat )
	  {
		case "width" : 		$wat = 0;	break;
		case "height" : 		$wat = 1;	break;
		case "img" : 		$wat = 3;	break;
	  }

	if( functions::check_url( $url ) )
	  {
		$infos = getimagesize( $url );

		if( $wat !== null )
		  {
			if( $wat === "size" )
			  {
				$return = array( "width" => $infos[ 0 ] , "height" => $infos[ 1 ] );
			  }
			else
			  {
				$return = $infos[ $wat ];
			  }
		  }
		else
		  {
			$return = $infos;
		  }
	  }

	return $return;
	exit;
  }


/* ----------------------------------------------------------------------------------------------------------------------------- CREE UN GRAPHIQUE */
public static function graph( $datas , $show_values=false , $sizes="400x150" , $output="screen" , $couleur_barres="808080" , $couleur_fond="F6F6F6" , $couleur_texte="333333" , $font=null )
  {
	/* -------------------------------------------------------------------- SETTINGS */

	$font			= ( ( $font !== null ) AND is_file($font) ) ? $font : dirname( __FILE__ )."/files/AndaleMono.ttf";

	$sizes		= explode( "x" , $sizes );
	$largeur		= $sizes[0];
	$hauteur		= $sizes[1];
	$padding		= 5;
	$margin		= 1;
	$footer		= ( $show_values === true ) ? 20 : 0;
	$R			= hexdec( substr( $couleur_barres , 0 , 2 ) );
	$G			= hexdec( substr( $couleur_barres , 2 , 2 ) );
	$B			= hexdec( substr( $couleur_barres , 4 , 2 ) );
	$back_R		= hexdec( substr( $couleur_fond , 0 , 2 ) );
	$back_G		= hexdec( substr( $couleur_fond , 2 , 2 ) );
	$back_B		= hexdec( substr( $couleur_fond , 4 , 2 ) );
	$font_R		= hexdec( substr( $couleur_texte , 0 , 2 ) );
	$font_G		= hexdec( substr( $couleur_texte , 2 , 2 ) );
	$font_B		= hexdec( substr( $couleur_texte , 4 , 2 ) );
	$nb_valeurs		= count( $datas );
	$valeure_max	= max( $datas );
	$start_x		= $padding;
	$start_y		= ( $hauteur - $padding - $footer );
	$max_hauteur	= ( $hauteur - ( 2 * $padding ) - $footer );
	$bar_size		= floor( ( $largeur - ( 2 * $padding ) - ( ( $nb_valeurs - 1 ) * $margin ) ) / $nb_valeurs );

	/* -------------------------------------------------------------------- IMG */

	$image	= imagecreatetruecolor( $largeur , $hauteur );
	$blanc	= imagecolorallocate($image , 255 , 255 , 255);
	$noir		= imagecolorallocate($image , 0 , 0 , 0);
	$background	= imagecolorallocate($image , $back_R , $back_G , $back_B );
	$couleur_txt= imagecolorallocate($image , $font_R , $font_G , $font_B );
	$couleur	= imagecolorallocate($image , $R , $G , $B );

	imagefilledrectangle( $image , 0 , 0 , $largeur , $hauteur , $background );

	/* -------------------------------------------------------------------- BARS */

	$n=0;
	foreach( $datas AS $data => $value )
	  {
		$n++;
		$hauteur_barre = ( $valeure_max > 0 ) ? round( ( $value * $max_hauteur ) / $valeure_max ) : 0;

		$x1 = $start_x + ( ( $n-1 ) * $margin ) + ( ( $n-1 ) * $bar_size );
		$y1 = $start_y;

		$x2 = $x1 + $bar_size - 1;
		$y2 = $start_y - $hauteur_barre;

		/* -------- BAR */
		imagefilledrectangle( $image , $x1 , $y1 , $x2 , $y2 , $couleur );


		if( $show_values === true )
		  {
			$font_infos = self::imagettfbbox_fixed( 8 , 0 , $font, $value);
			if( ( $font_infos["width"] + 4 ) < $bar_size )
			  {
				$fx = $x1 + ( ( $bar_size - $font_infos["width"] ) / 2 );
				$fy = $y1 - $font_infos["height"] + 2;
				imagettftext( $image , 8 , 0 , $fx , $fy , $couleur_txt , $font , $value );
			  }

			$font_infos = self::imagettfbbox_fixed( 8 , 0 , $font, $data);
			if( ( $font_infos["width"] + 4 ) < $bar_size )
			  {
				$fx = $x1 + ( ( $bar_size - $font_infos["width"] ) / 2 );
				$fy = $y1 + $font_infos["height"] + 5;
				imagettftext( $image , 8 , 0 , $fx , $fy , $couleur_txt , $font , $data );
			  }
		  }

	  }

	/* -------------------------------------------------------------------- OUTPUT */

	if( $output == "screen" )
	  {
		header( "Content-type: image/png" );
		imagepng( $image );
	  }
	else
	  {
		imagepng( $image , $output );	
	  }

  }



/* ----------------------------------------------------------------------------------------------------------------------------- RETAILLE UNE IMAGE AU FORMAT CARRE */
public static function square_image( $src , $out=null , $size=null )
  {
	return self::resize( $src , $size , $out , true , 100 , true );
  }


/* ----------------------------------------------------------------------------------------------------------------------------- DIMENSIONS D'UN TEXTE EN GD
http://fr2.php.net/manual/fr/function.imagettfbbox.php#39720
*/
public static function imagettfbbox_fixed( $size, $angle, $font, $text)
   {
	/* Get the boundingbox from imagettfbbox(), which is correct when angle is 0 */
	$bbox = imagettfbbox($size, 0, $font, $text);

	/* Rotate the boundingbox */
	$angle = pi() * 2 - $angle * pi() * 2 / 360;
	for ($i=0; $i<4; $i++)
	  {
		$x = $bbox[$i * 2];
		$y = $bbox[$i * 2 + 1];
		$bbox[$i * 2] = cos($angle) * $x - sin($angle) * $y;
		$bbox[$i * 2 + 1] = sin($angle) * $x + cos($angle) * $y;
	  }

	/* Variables which tells the correct width and height */
	$bbox['width'] = $bbox[0] + $bbox[4];
	$bbox['height'] = $bbox[1] - $bbox[5];

	return $bbox;
  }


/* ----------------------------------------------------------------------------------------------------------------------------- AFFICHE UNE GRILLE SUR UN IMAGE GD
http://phpfonctions.fr/fonction-php.php?fonction=nth_imagesreperes
*/

public static function grille_gd($espacement, $image)
  {
	$couleur		= imagecolorallocate( $image, 220, 220 ,220 );
	$largeur_image	= imagesx( $image );
	$hauteur_image	= imagesy( $image );

	for($repere_vertical = $espacement; $repere_vertical <= $largeur_image; $repere_vertical)
	  {
		imageline($image, $repere_vertical, 0, $repere_vertical, $hauteur_image, $couleur);
		imagestring($image, 3, $repere_vertical+5, 0, $repere_vertical, $couleur);
		$repere_vertical = $repere_vertical+$espacement;
	  }
	for($repere_horizontal = $espacement; $repere_horizontal <= $hauteur_image; $repere_horizontal)
	  {
		imageline($image, 0, $repere_horizontal, $largeur_image, $repere_horizontal, $couleur);
		imagestring($image, 3, 0, $repere_horizontal, $repere_horizontal, $couleur);
		$repere_horizontal = $repere_horizontal+$espacement;
	  }
  }


/* ----------------------------------------------------------------------------------------------------------------------------- FLIP (MIRROR) AN IMAGE LEFT TO RIGHT.
http://maettig.com/?page=PHP/imageflip
*/

public static function flip(&$image, $x = 0, $y = 0, $width = null, $height = null)
  {
	if ($width  < 1) $width  = imagesx($image);
	if ($height < 1) $height = imagesy($image);
	/* Truecolor provides better results, if possible. */
	if (function_exists('imageistruecolor') && imageistruecolor($image))
	  {
		$tmp = imagecreatetruecolor(1, $height);
	  }
	else
	  {
		$tmp = imagecreate(1, $height);
	  }
	$x2 = $x + $width - 1;
	for ($i = (int)floor(($width - 1) / 2); $i >= 0; $i--)
	  {
		/* Backup right stripe. */
		imagecopy($tmp, $image, 0, 0, $x2 - $i, $y, 1, $height);
		/* Copy left stripe to the right. */
		imagecopy($image, $image, $x2 - $i, $y, $x + $i, $y, 1, $height);
		/* Copy backuped right stripe to the left. */
		imagecopy($image, $tmp, $x + $i, $y, 0, 0, 1, $height);
	  }
	imagedestroy($tmp);
	return true;
  }



/* ----------------------------------------------------------------------------------------------------------------------------- CHECK SI LE GIF EST ANIMÉ */

public static function is_animated_gif( $filename )
  {
	/* http://www.codigomanso.com/en/2009/06/detect-an-animated-gif-in-php/ */
	return (bool)preg_match('#(\x00\x21\xF9\x04.{4}\x00\x2C.*){2,}#s', file_get_contents($filename));
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LES INFOS EXIFS */
public static function exif( $file , $tags="essentials" )
  {
	if( is_file( $file ) AND function_exists("exif_read_data") )
	  {
		$exif = exif_read_data( $file , 0 , true );

		if( $exif !== false )
		  {
			if( $tags == "essentials" )
			  {
				$marque		= ( isset( $exif["IFD0"]["Make"] ) AND !empty($exif["IFD0"]["Make"]) ) 					? $exif["IFD0"]["Make"] : "";
				$modele		= ( isset( $exif["IFD0"]["Model"] ) AND !empty($exif["IFD0"]["Model"]) ) 				? $exif["IFD0"]["Model"] : "";
				$focale		= ( isset( $exif["EXIF"]["FocalLength"] ) AND !empty($exif["EXIF"]["FocalLength"]) ) 		? $exif["EXIF"]["FocalLength"] : "";
				$exposition		= ( isset( $exif["EXIF"]["ExposureTime"] ) AND !empty($exif["EXIF"]["ExposureTime"]) ) 		? $exif["EXIF"]["ExposureTime"] : "";
				$ouverture		= ( isset( $exif["EXIF"]["FNumber"] ) AND !empty($exif["EXIF"]["FNumber"]) ) 				? $exif["EXIF"]["FNumber"] : "";
				$flash			= ( isset( $exif["EXIF"]["Flash"] ) AND !empty($exif["EXIF"]["Flash"]) ) 				? $exif["EXIF"]["Flash"] : "";
				$date			= ( isset( $exif["EXIF"]["DateTimeOriginal"] ) AND !empty($exif["EXIF"]["DateTimeOriginal"]) ) 	? $exif["EXIF"]["DateTimeOriginal"] : "";
				$software		= ( isset( $exif["IFD0"]["Software"] ) AND !empty($exif["IFD0"]["Software"]) ) 			? $exif["IFD0"]["Software"] : "";
				$orientation	= ( isset( $exif["IFD0"]["Orientation"] ) AND !empty($exif["IFD0"]["Orientation"]) ) 		? $exif["IFD0"]["Orientation"] : "";

				if( $focale != "" )
				  {
					$focale = explode( "/" , $focale );
					$focale = ( $focale[0] / $focale[1] );
				  }

				if( $exposition != "" )
				  {
					$exposition = explode( "/" , $exposition );
					$exposition = ( $exposition[0] / 10 )."/".( $exposition[1] / 10 );
				  }

				if( $ouverture != "" )
				  {
					$ouverture = explode( "/" , $ouverture );
					$ouverture = "f/".( $ouverture[0] / $ouverture[1] );
				  }

				if( functions::is_in_string( $marque , $modele ) )
				  {
					$apn = $modele;
				  }
				else
				  {
					$apn = $marque." ".$modele;
				  }

				$return["marque"]		= $marque;
				$return["modele"]		= $modele;
				$return["apn"]		= $apn;
				$return["focale"]		= $focale;
				$return["exposition"]	= $exposition;
				$return["ouverture"]	= $ouverture;
				$return["flash"]		= $flash;
				$return["date"]		= $date;
				$return["software"]	= $software;
				$return["orientation"]	= $orientation;

				if( isset($exif["GPS"]) AND isset($exif["GPS"]["GPSLatitude"]) AND is_array($exif["GPS"]["GPSLatitude"])
								AND isset($exif["GPS"]["GPSLongitude"]) AND is_array($exif["GPS"]["GPSLongitude"])
				  )
				  {
					$GPS				= $exif["GPS"];

					$latitude_d			= explode( "/" , $GPS["GPSLatitude"][0] );
					$latitude_d			= ( $latitude_d[0] / $latitude_d[1] );
					$latitude_m			= explode( "/" , $GPS["GPSLatitude"][1] );
					$latitude_m			= ( $latitude_m[0] / $latitude_m[1] );
					$latitude_s			= explode( "/" , $GPS["GPSLatitude"][2] );
					$latitude_s			= ( $latitude_s[0] / $latitude_s[1] );

					$latitude			= round( functions::degres_to_decimal( $GPS["GPSLatitudeRef"] , $latitude_d , $latitude_m , $latitude_s ), 5 );
					$lat				= $latitude_d."° ".$latitude_m."' ".$latitude_s."\" ".$GPS["GPSLatitudeRef"];



					$longitude_d		= explode( "/" , $GPS["GPSLongitude"][0] );
					$longitude_d		= ( $longitude_d[0] / $longitude_d[1] );
					$longitude_m		= explode( "/" , $GPS["GPSLongitude"][1] );
					$longitude_m		= ( $longitude_m[0] / $longitude_m[1] );
					$longitude_s		= explode( "/" , $GPS["GPSLongitude"][2] );
					$longitude_s		= ( $longitude_s[0] / $longitude_s[1] );

					$longitude			= round( functions::degres_to_decimal( $GPS["GPSLongitudeRef"] , $longitude_d , $longitude_m , $longitude_s ), 5 );
					$long				= $longitude_d."° ".$longitude_m."' ".$longitude_s."\" ".$GPS["GPSLongitudeRef"];


					$return["latitude"]	= $latitude;
					$return["longitude"]	= $longitude;
					$return["lat"]		= $lat;
					$return["long"]		= $long;
				  }
			  }
			else
			  {
				if( $tags == "all" )
				  {
					$tags = "";
				  }

				$tags		= ( $tags==NULL ) ? NULL : explode( ";" , $tags );
				$nb_tags	= count( $tags );
				$return	= ( ($tags==NULL) AND ($nb_tags>1) ) ? array() : "";

				foreach( $exif as $key => $section )
				  {
					foreach( $section as $name => $val)
					  {
						if( $tags == NULL )
						  {
							$return[ $name ] = $val;
						  }
						else
						  {
							if( ( $nb_tags==1 ) AND in_array( $name , $tags ) )
							  {
								$return = $val;
							  }
							else if( $nb_tags>1 )
							  {
								$return[ $name ] = $val;
							  }
						  }
					  }
				  }
			  }
		  }
		else
		  {
			$return = "Error #2";
		  }
	  }
	else
	  {
		$return = "Error #1";
	  }

	return $return;
  }


/* --------------------------------------------------------------------------------------------------------------------------------------------- RETOURNE LES DIMENSIONS D'UNE IMAGE SVG */
public static function svg_sizes( $data )
  {
	$sizes = array( "width" => 0 , "height" => 0 );

	if( is_file( $data ) )
	  {
		$data = file_get_contents( $data );
	  }

	if( $data = @simplexml_load_string( $data ) )
	  {
		$attr	= $data->attributes();
	
		if( isset($attr->width) AND isset($attr->width) )
		  {
			$sizes["width"]	= preg_replace( "/\D/", "" , ( (string) $attr->width ) );
			$sizes["height"]	= preg_replace( "/\D/", "" , ( (string) $attr->height ) );
		  }
		else if( isset($attr->viewBox) )
		  {
			$viewbox = explode( " " , $attr->viewBox );
			
			if( count( $viewbox )  == 4 )
			  {
				$sizes["width"]	= $viewbox[2];
				$sizes["height"]	= $viewbox[3];
			  }
			
		  }
	  }
	
	return $sizes;
  }




/* --------------------------------------------------------------------------------------------------------------------------------------------- FIX L'ORIENTATION D'UNE IMAGE */
public static function fix_orientation( $file )
  {
	$exif = exif_read_data( $file );
	
	if( !empty($exif["Orientation"]) )
	  {
		$image = self::get_image( $file );

		switch ($exif["Orientation"])
		  {
			case 3 :	$image = imagerotate( $image, 180, 0 );	break;
			case 6 :	$image = imagerotate( $image, -90, 0 );	break;
			case 8 :	$image = imagerotate( $image, 90, 0 );	break;
			default :	$image = null;
		  }

		$ext = strtolower(substr(strrchr($file,"." ),1));

		if( !is_null($image) AND isset($ext) )
		  {
			switch( $ext )
			  {
				case "jpg" :
				case "jpeg" :	imagejpeg( $image , $file , 100 );		break;
				case "png" :	imagepng( $image , $file );			break;
				case "webp" :	imagewebp( $image , $file , 100 );		break;
				case "gif" :	imagegif( $image , $file );			break;
				default :		imagepng( $image , $file );			break;
			  }
		  }
	  }
  }









/* ----------------------------------------------------------------------------------------------------------------------------- DESTRUCTEUR */
public function __destruct()
  {
  }

}






