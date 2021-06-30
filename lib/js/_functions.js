
/** --------------------------------------------------------------------------------------------------------------------------------------------
* Author		: @ptibat
* Dev start		: 06/06/2007
* Version		: 19.0
* Last modif	: 10/05/2019 11:37
* Description	: Diverses fonctions en javascript
* Requiert		: jQuery
--------------------------------------------------------------------------------------------------------------------------------------------- */


var _HTML_BODY = ( /webkit/i.test(navigator.userAgent) ) ? "body" : "html";

/**
* OBJET CONTENANT LES FONCTIONS
*/

var _js = {

	/**
	* VARIABLES
	*/

	i 				: 0,
	current_page_title 	: document.title,
	logs			 	: [],
	dnt				: ( navigator.doNotTrack == "yes" || navigator.doNotTrack == "1" || navigator.msDoNotTrack == "1" ),

	/**
	* ALERT( "OK" );
	*/

	ok : function()
				  {
					alert( "OK" );
					console.log( "OK" );
				  },

	/**
	* ECRIT UN TEXTE SUR LA PAGE
	*/

	echo : function( string )
				  {
					document.write( string );
				  },

	/**
	* CHANGE LE TITRE DE LA PAGE
	*/

	change_title : function( title , separator , full )
				  {
					separator = ( separator != undefined ? separator : " - " );
					document.title = ( ( full == "true" ) ? title : this.current_page_title + separator + title );
				  },


	/**
	* CHANGE LE TEXTE DANS LA BARRE DE STATUS DU NAVIGATEUR
	*/

	window_status : function( string )
				  {
					window.status = string;
				  },

	/**
	* REDIRECTION DIRECTE VERS UNE URL (PHP HEADER LOCATION STYLE)
	*/

	redirect : function(  url )
				  {
					window.location.replace( url );
				  },

	/**
	* RENVOIE LE DOMAINE EN COURS
	*/

	get_domain : function()
				  {
					var domain = document.domain;
					if( domain.substring( 0 , 4 ) == "www." )
					  {
						domain = domain.substring( 4 , domain.length );
					  }
					return domain;
				  },

	/**
	* RENVOIE SI UN ELEMENT EXISTE, OU PAS
	*/

	isset : function( element , type )
				  {
					if( (type == "var") && ( typeof(element)!="undefined" ) )
					  {
						return true;
					  }
					else if( $( element ) )
					  {
						return true;
					  }
					else
					  {
						return false;
					  }
				  },

	/**
	* ENREGISTRE LES LOGS
	*/

	log : function( wat )
				  {
					this.logs.push( [ this.microtime(true) , wat ] );
					if( ( typeof( _JS_DEBUG ) !== "undefined" ) && ( _JS_DEBUG === true ) )
					  {
					  	console.log( this.microtime(true) + " : " + wat );
					  }
				  },

	/**
	* RENVOIE LES LOGS
	*/

	get_logs : function( print )
				  {
					if( print == "true" )
					  {
						return this.print_r( this.logs , true );
					  }
					else
					  {
						return this.logs;
					  }
				  },


	/**
	* AFFICHE UN POPUP DE CONFIRMATION ET RETOURNE "TRUE" OU "FALSE"
	*/

	confirmer : function( commentaire )
				  {
					if( confirm( commentaire ) )
					  {
						return true;
					  }
					else
					  {
						return false;
					  }
				  },



	/**
	* INSERTION D'UNE ANNIMATION FLASH DANS LA PAGE
	*/

	annim_flash : function( url_annimation , width , height , name )
				  {
					if( name == undefined )
					  {
						name = "annim_flash";
					  }

					code  = "<object classid='clsid: d27cdb6e-ae6d-11cf-96b8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0' width='" + width + "' height='" + height + "' id='" + name + "'>";
					code += "<param name='movie' value='" + url_annimation + "' /> ";
					code += "<param name='quality' value='high' /> ";
					code += "<param name='wmode' value='transparent' /> ";
					code += "<embed src='" + url_annimation + "' quality='high' wmode='transparent' pluginspage='http://www.macromedia.com/go/getflashplayer' type='application/x-shockwave-flash' width='" + width + "' height='" + height + "'></embed>";
					code += "</object>";

					return code;
				  },


	/**
	* PROTEGE LES ADRESSES EMAIL
	*/

	mailto : function( a , b , c )
				  {
					location.href = "mailto:" + a + "@" + b + "." + c;
				  },

	/**
	* VERIFICATION DE L'EXTENTION DU FICHIER
	*/

	check_file_type : function( filename , extention )
				  {
					/*
					var exp = new RegExp("[a-zA-z0-9]\." + ext.toLowerCase() + "$","g");
					return exp.test(target.toLowerCase());
					*/
					var explode		= filename.split( "." );
					var ext		= explode[ explode.length - 1 ].toLowerCase();

					return ( ( ext == extention ) ? true : false );
				  },

	/**
	* FONCTION DE "PAUSE"
	*/

	pause : function( millisecondes )
				  {
					var now = new Date();
					var exitTime = now.getTime() + millisecondes;
					
					while(true)
					  {
						now = new Date();
						if( now.getTime() > exitTime ) { return; }
					  }
				  },


	/**
	* VERIFICATION D'UNE ADRESSE EMAIL
	*/

	verif_email : function( mail )
				  {
					var regex = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
					return regex.test( mail );
				  },

	/**
	* VERIFIE SI L'EMAIL EST UN MAIL PRO OU PAS
	*/

	check_email_pro : function( email )
				  {
					if( !email.match(/[a-zA-Z0-9-.]+\@(aliceadsl|aol|yahoo|att|belgacom|cegetel|club-internet|dbmail|easynet|free|imp.free|magiccgi|mangoosta|nerim|netpratique|neuf|noos|nordnet|numericable|oleane|orange|bluewin|sympatico|tele2|videotron|hotmail|gmail|laposte|wanadoo|alice|caramail|live|voila|sfr|netcourrier|ifrance|yopmail|online|9online|libertysurf)\.[a-zA-Z0-9-.]+/) )
					  {
						return true;
					  }
					else
					  {
						return false;
					  }

				  },


	/**
	* VERIFICATION SI UNE CHAINE EST BIEN NUMERIQUE
	*/

	is_numeric : function( value )
				  {
					var exp = new RegExp( "^[0-9-.]*$" , "g" );
					return exp.test( value );
					/* return !isNaN( value ); */
				  },

	/**
	* RENVOI SI LE NAVIGATEUR ACCEPTE LES COOKIE OU NON
	*/

	allow_cookies : function()
				  {
					var accepteCookies = (navigator.cookieEnabled) ? true : false;

					if( typeof( navigator.cookieEnabled == "undefined" ) && !cookieEnabled )
					  {
						document.cookie	= "test_js";
						accepteCookies	= (document.cookie.indexOf("test_js") != -1) ? true : false;
					  }


					if( accepteCookies )
					  {
						return true;
					  }
					else
					  {
						return false;
					  }
				  },


	/**
	* CREE UN COOKIE
	*/

	setcookie : function( name , value )
				  {
					var argv 	= this.setcookie.arguments;
					var argc 	= this.setcookie.arguments.length;
					var expires	= (argc > 2) ? argv[2] : null;
					var path	= (argc > 3) ? argv[3] : _ROOT + "/";
					var domain	= (argc > 4) ? argv[4] : null;
					var secure	= (argc > 5) ? argv[5] : false;

					document.cookie = name + "=" + escape(value)+
										((expires==null) ? "" : ("; expires="+expires.toGMTString())) +
										((path==null) ? "" : ("; path="+path)) +
										((domain==null) ? "" : ( "; domain="+domain ) ) +
										((secure==true) ? "; secure" : "");
				  },


	/**
	* RETOURNE LE CONTENU D'UN COOKIE
	*/

	getcookie : function( name )
				  {
					var i, j;
					var cookie = document.cookie;
					i = cookie.indexOf( name );
					if(i==-1)
					  {
						return "";
					  }
					j = cookie.indexOf(";",i);
					if(j==-1)
					  {
						j = cookie.length;
					  }

					return unescape( cookie.substring( i + name.length + 1 , j ) );
				  },


	/**
	* VERIFICATION SI LA CHAINE EST BIEN QUE ALPHANUMERIQUE
	*/

	is_alphanumeric : function( string )
				  {
					var alpha = /^[\w]+$/;
					if( alpha.test(string) )
					  {
						return true;
					  }
					else
					  {
						return false;
					  }
				  },


	/**
	* N'AUTORISE QUE LES CARACTÈRES NUMERIQUE
	*/

	numeric_only : function( data , comma )
				  {
					if( ( comma != undefined ) && comma.match( /^(.|,)$/ ) )
					  {
						data = data.replace( ( comma == "." ? "," : ".") , comma );
					  }
	
					data = data.replace( /^0+/ , "" );
					data = data.replace( /[^0-9.,]/gi , "" );

					return data;
				  },


	/**
	* N'AUTORISE QUE LES CARACTÈRES NUMERIQUE
	*/

	only_numeric : function( id , comma )
				  {
				  	if( $( id ).length )
				  	  {
						$( id ).keyup(function(){
					
							if( ( comma != undefined ) && comma.match( /^(.|,)$/ ) )
							  {
								$(this).val( $(this).val().replace( ( comma == "." ? "," : ".") , comma ) );
							  }
							else
							  {
								$(this).val( $(this).val().replace( "," , "." ) );
							  }
			
							if( $(this).val().length > 1 )
							  {
								$(this).val( $(this).val().replace( /^0+/ , "" ) );

								if( $(this).val().match( /^\./gi ) )
								  {
								  	$(this).val( "0" + $(this).val() );
								  }
							  }
	
							if( $(this).val().match( /[^0-9.,\ ]/gi ) )
							  {
							  	$(this).effect("highlight", { color : "#e8d800" }, 200 );
								$(this).val( $(this).val().replace( /[^0-9.,\ ]/gi , "" ) );
							  }
						});
				  	  }
					
				  },


	/**
	* N'AUTORISE QUE LES CARACTÈRES ALPHANUMERIQUE
	*/

	only_alphanumeric : function( id , spaces )
				  {
				  	if( $( id ).length )
				  	  {
						$( id ).keyup(function(){
	
							var value 	= $(this).val();
							var regex	= ( ( spaces != undefined ) && ( spaces === true ) ) ? /[^a-z0-9\ \-\_]/gi : /[^a-z0-9\-\_]/gi;
			
							if( value.match( regex ) )
							  {
							  	$(this).effect("highlight", { color : "#e8d800" }, 200 );
								$(this).val( value.replace( regex , "" ) );
							  }
						});
				  	  }
				  },


	/**
	* N'AUTORISE QUE LES CARACTÈRES ALPHANUMERIQUE
	*/

	alphanumeric_only : function( text , accents )
				  {
				  	if( ( typeof(accents) != undefined ) && ( accents == true ) )
				  	  {
						return text.replace( /[^a-z0-9àáâãäæçèéêëìíîïñòóôõöœùúûüýÿÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÑÒÓÔÕÖŒÙÚÛÜÝ 0-9_'\-\ \-\_]/gi , "" );
				  	  }
				  	else
				  	  {
						return text.replace( /[^a-z0-9\ \-\_]/gi , "" );
				  	  }
				  },

	/**
	* VERIFICATION D'UN NUMERO DE TELEPHONE
	*/

	check_phone_number : function( numero )
				  {
					var regex = /^[0-9+() .-]{6,32}$/;
					if ( regex.exec( numero ) == null)
					  {
						return false;
					  }
					else
					  {
						return true;
					  }
				  },


	/**
	* LIMITE LE NOMBRE DE CARACTERES DANS UN CHAMP
	*/

	limit_text_length : function( id_element , limit )
				  {
					var length = $( id_element ).val().length;

					if( length> limit )
					  {
						$( id_element ).val() = $( id_element ).val().substr( 0 , limit );
					  }

					var argv = limit_text_length.arguments;

					if( argv[2] )
					  {
						$( argv[2] ).html( limit - length );
					  }
				  },

	/**
	* RENVOYE LE BOUTON QUI EST CLIQUE
	*/

	current_mouse_button : function( e )
				  {
					if ( (e.which == null) || (e.which == undefined) )
					  {
						var button = ( e.button < 2) ? 'left' : ( ( e.button == 4) ? 'middle' : 'right' );
					  }
					else
					  {
						var button = ( e.which < 2) ? 'left' : ( ( e.which == 2) ? 'middle' : 'right' );
					  }

					return button;
				  },


	/**
	* DESACTIVE LE CLICK DROIT
	*/

	disable_right_click : function( wat )
				  {
					wat = ( wat != undefined ) ? wat : document;

					$( wat ).on("contextmenu", function( evt ) {
						
						if( !$(evt.target).is(".rightclickok") )
						  {
							evt.preventDefault();
							return false;
						  }
					});


				  	/* 
				  	* OLD
					$(document).mousedown(function(e){ if( e.which == 3 ) { e.stopPropagation(); return false; } });
					*/

				  },


	/**
	* DESACTIVE LE COPIER COLLER
	*/

	disable_paste : function()
				  {
					$(document).bind("paste", function(e){ 
						e.stopPropagation();
						return false;
					});
				  },


	/**
	* ALERTE SUR TOUS LES LIENS
	*/

	alert_on_links : function( msg )
				  {
					if( msg != undefined )
					  {
						$("a").each(function(i){

							$(this).addClass("link_with_alert");
							$(this).click(function(){
								if( !_js.confirmer( msg ) )
								  {
									return false;
								  }
							});
						});
					  }
				  },
	/**
	* ENLEVE L'ALERTE SUR TOUS LES LIENS
	*/

	remove_alert_on_links : function()
				  {
					$(".link_with_alert").each(function(i){
						$(this).addClass("link_with_alert");
						$(this).unbind();
					});
				  },


	/**
	* RENVOIE L'ENCRE DANS L'URL
	*/

	get_anchor : function()
				  {
					return document.location.hash.substring( 1 );
				  },


	/**
	* MODIFIE L'ENCRE DANS L'URL
	*/

	change_anchor : function( anchor )
				  {
					var url		= "" + window.location;
					url			= this.str_replace( "#" + document.location.hash.substring(1) , "" , url );
					window.location 	= url + "#" + anchor;
				  },


	/**
	* DONNE LA POSITION Y DE LA PAGE
	*/

	current_page_y : function()
				  {
					current_y = 0;

					if( document.all )
					  {
						current_y = document.body.scrollTop;
					  }
					else if( document.body.scrollTop )
					  {
						current_y = document.body.scrollTop;
					  }
					else if( document.documentElement.scrollTop )
					  {
						current_y = document.documentElement.scrollTop;
					  }
					else
					  {
						current_y = window.pageYOffset;
					  }

					return current_y;
				  },


	/**
	* SCROLL VERS LA POSITION D'UN ELEMENT
	*/

	go_to : function( id , speed , margin )
				  {
					if( $(id).length > 0 )
					  {
						var Y = $(id).offset().top;
						
						if( margin != undefined )
						  {
						  	Y = Y - margin;
						  }
						
						$( _HTML_BODY ).animate({ scrollTop : Y }, ( ( speed != undefined ) ? speed : 1000 ) );
					  }
				  },


	/**
	* SCROLL VERS LE HAUT DE LA PAGE
	*/

	scroll_top : function( speed )
				  {
					$( _HTML_BODY ).animate({ scrollTop : 0 } , ( ( speed != undefined ) ? speed : 1000 ) , "swing" );
				  },

	/**
	* TEXTE SELECTIONNE
	*/

	get_selectionned_text : function()
				  {
					var text = "";

					if( window.getSelection )
					  {
						text = window.getSelection();
					  }
					else if( document.getSelection )
					  {
						text = document.getSelection();
					  }
					else if( document.selection )
					  {
						text = document.selection.createRange().text;
					  }

					return text;
				  },


	/**
	* RETOURNE TRUE OU FALSE SI LE NAVIGATEUR EST UN IPHONE OU UN IPAD
	*/

	is_iphone : function( force )
				  {
					if( ( force != undefined ) && ( force == true ) )
					  {
						return ( (navigator.platform.toLowerCase().indexOf('iphone') != -1 ) ? true : false );
					  }
					else
					  {
						return ( (navigator.userAgent.toLowerCase().indexOf('iphone') != -1 ) ? true : false );
					  }
				  },

	is_ipad : function( force )
				  {
					if( ( force != undefined ) && ( force == true ) )
					  {
						return ( (navigator.platform.toLowerCase().indexOf('ipad') != -1 ) ? true : false );
					  }
					else
					  {
						return ( (navigator.userAgent.toLowerCase().indexOf('ipad') != -1 ) ? true : false );
					  }
				  },


	/**
	* EQUIVALENT EREGI DE PHP
	*/

	eregi : function( string , regex )
				  {
					var regex = new RegExp( regex );
					return regex.exec( string )!=null;
				  },


	/**
	* DISTANCE ENTRE 2 POINTS
	*/

	distance_entre_2_points : function( x1 , y1 , x2 , y2 , round )
				  {
					var distance	= 0;
					var x			= x2 - x1;
					var y			= y2 - y1;
					distance 		= Math.sqrt( (x * x) + (y * y) );

					if( ( round != undefined ) && ( round==true ) )
					  {
						distance = Math.ceil( distance );
					  }
					else
					  {
						distance = Math.ceil( distance * 100 ) / 100;
					  }

					return distance;
				  },


	/**
	* PRECHARGE LES IMAGES PASSEES EN PARAMETRES
	*/

	precharger_images : function( images )
				  {
					$( images ).each(function(){
						$("<img/>")[0].src = this;
						_js.log( "Préchargement de l'image : " + this );
					});
				  },

	/**
	* CONVERTISSEUR DECIMAL > HEXA
	*/

	dec2hex : function( value )
				  {
					return ( value<15.5 ? "0" : "" ) + Math.round( value ).toString( 16 );
				  },

	/**
	* CONVERTISSEUR HEXA > DECIMAL
	*/

	hex2dec : function( value )
				  {
					return parseInt( value , 16 );
				  },

	/**
	* CONVERTISSEUR COULEUR HEXA > RVB
	*/

	hex2rgb : function( value )
				  {
					var r = (hex & 0xff0000) >> 16;
					var g = (hex & 0x00ff00) >> 8;
					var b = hex & 0x0000ff ;

					return { R:r, G:g, B:b };
				  },


	/**
	* RETOURNE UN TABLEAU AVEC LES VALEURS DEGRADEE ENTRE 2 COULEURS
	*/

	make_gradient : function( color_start , color_end , steps )
				  {
					steps		= ( steps > 1 ) ? steps-1 : steps;
					var values	= [];
					var i		= parseInt( steps );
					var vn	= i;

					r1 = this.hex2dec( color_start.substring( 0 , 2 ) );
					g1 = this.hex2dec( color_start.substring( 2 , 4 ) );
					b1 = this.hex2dec( color_start.substring( 4 ) );

					rs = ( this.hex2dec( color_end.substring( 0 , 2 ) ) - r1 ) / vn;
					gs = ( this.hex2dec( color_end.substring( 2 , 4 ) ) - g1 ) / vn;
					bs = ( this.hex2dec( color_end.substring( 4 ) ) - b1 ) / vn;

					while( i-- )
					  {
						r1 += rs;
						g1 += gs;
						b1 += bs;

						values[i] = this.dec2hex( r1 ) + this.dec2hex( g1 ) + this.dec2hex( b1 );    
					  }

					values.push( color_start );
					values = values.reverse();

					return values;
				  },


	/**
	* VERIFIE SI LE FOCUS EST FAIT SUR LA PAGE OU NON
	*/

	is_focused : function()
				  {
					if( typeof document.hasFocus != "undefined" )
					  {
						return document.hasFocus();
					  }
					else
					  {
						return false;
					  }
				  },


	/**
	* SUPPRIME LA BARRE D'ADRESSE POUR SAFARI IPHONE
	*/

	iphone_top : function()
				  {
					window.scrollTo( 0 , 1 );
				  },


	/**
	* AFFICHE UN POPUP DE PARTAGE D'URL FACEBOOK
	*/

	facebook_share : function( url , title )
				  {
					window.open( "http://www.facebook.com/sharer.php?u=" + encodeURIComponent( url ) + "&t=" + encodeURIComponent( title ) , "FacebookShare" , "toolbar=0,status=0,width=626,height=436" );
				  },

	/**
	* AFFICHE UNE PAGE EN POPUP
	*/

	popup : function( url , options )
				  {
					var defaults = {
						title		: "Popup",
						width		: ( screen.width / 2 ),
						height 	: ( screen.height / 2 ),
						left 		: ( screen.width - ( screen.width / 2 ) - ( screen.width / 4 ) ),
						top 		: ( screen.height - ( screen.height / 2 ) - ( screen.height / 4 ) ),
						scrollable	: true,
						menubar	: true,
						resizable	: true
					};

					if( typeof( options ) == "object" )
					  {
						options = $.extend( defaults, options );
					  }
					else
					  {
						options = defaults;
					  }


					var opt =	    "width=" 		+ options.width
						 	+ ", height=" 		+ options.height
						 	+ ", left=" 		+ options.left
						 	+ ", top=" 			+ options.top
						 	+ ", screenx=" 		+ options.left
						 	+ ", screenY=" 		+ options.top
						 	+ ", scrollbars=" 	+ ( options.scrollable === true ? "yes" : "no" )
						 	+ ", menubar=" 		+ ( options.menubar === true ? "yes" : "no" )
						 	+ ", resizable=" 		+ ( options.resizable === true ? "yes" : "no" );

					var popup	= window.open( url , options.title , opt );
					if( window.focus )
					  {
						popup.focus();
					  }

				  },


	/**
	* REMPLACE L'URL ET EMPECHE LE REFRESH
	*/

	url_replace : function( url )
				  {
					history.replaceState( {}, "" , url );
				  },

	/**
	* TEST SI L'URL EST BIEN UNE URL...
	*/

	is_valid_url : function( url )
				  {
					var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
					return regexp.test( url );
				  },

	/**
	* GENERE UN TABLEAU AVEC UNE FOURCHETTE DE VALEURS
	*/

	array_range : function( start , end )
				  {
					var range = new Array();
				
					for( var i = start ; i < ( end + 1 ) ; i++ )
					  {
					  	range.push( i ); 
					  }
					  
					return range;
				  },

	/**
	* MÉLANGE UN TABLEAU
	*/

	array_shuffle : function( o )
				  {
					for(var j, x, i = o.length; i; j = Math.floor(Math.random() * i), x = o[--i], o[i] = o[j], o[j] = x);
					return o;
				  },

	/**
	* GENERE UNE VALEUR ALEATOIRE
	*/

	random : function( min , max )
				  {
					return Math.floor( Math.random() * ( max - min + 1 ) ) + min;
				  },

	/**
	* CREE UN TABLEAU DE VALEURS ALEATOIRE
	*/

	random_values_range_array : function( start , end , nb )
				  {
					return this.array_shuffle( this.array_range( start , end ) ).slice( 0 , nb );
				  },

	/**
	* CONVERTIT DES OCTETS AU FORMAT LISIBLE PAR L'HOMME
	*/

	file_size : function( bytes )
				  {
					if(bytes == 0) return "0 octets";
					var k = 1024;
					var sizes = [ "Octets", "Ko", "Mo", "Go", "To", "Po", "Eo", "Zo", "Yo"];
					var i = Math.floor(Math.log(bytes) / Math.log(k));
					return (bytes / Math.pow(k, i)).toPrecision(3) + ' ' + sizes[i];
				  },

	/**
	* CONVERTIT UN TEXTE AU FORMAT URL REWRITE
	*/

	url_rewrite : function( text )
				  {
					text = text.replace( /^\s+|\s+$/g, "" );
					text = text.toLowerCase();
					
					var from = "ãàáäâẽèéëêìíïîõòóöôùúüûñç·/_,:;";
					var to   = "aaaaaeeeeeiiiiooooouuuunc------";
					for( var i=0 , l = from.length ; i<l ; i++)
					  {
						text = text.replace(new RegExp(from.charAt(i), "g" ) , to.charAt(i));
					  }
					
					text = text.replace( /[^a-z0-9 -]/g , "" )
						     .replace(/\s+/g, "-" )
						     .replace(/-+/g, "-" );
					
					return text;
				  },

	/**
	* SUPPRIME LES TAGS HTML D'UN TEXTE
	*/

	remove_html : function( text )
				  {
					return text.replace(/(<([^>]+)>)/ig,"");
				  },

	/**
	* GESTION D'UN OVERLAY AVEC FOND FLOU
	* Les styles CSS sont à configurer...
	*/

	overlay_active : false,

	overlay : function( options )
				  {
					/* ----------------------------------------------- Options / data */
					
					var defaults 	= {
									id			: "overlay",
									parent_id		: "viewport",
									data			: "",
									txt_close		: "Fermer",
									btn_close		: true
								};

					if( typeof( options ) == "object" )
					  {
						options = $.extend( defaults, options );
					  }
					else
					  {
						defaults.data = options;
					  }

					var options = defaults;



					if( options.btn_close === true )
					  {
						options.data = "<span id='" + options.id + "_close' class='bouton rouge small'>" + options.txt_close + "</span>" + options.data;
					  }


					/* ----------------------------------------------- */
					
					if( document.getElementById( options.id ) !== null )
					  {
					  	$( "#" + options.id + "_data" ).html( options.data );
					  }
					else
					  {
					  	var html = "\
						<div id='" + options.id + "'>\
							<div id='" + options.id + "_data'>" + options.data + "</div>\
						</div>";

						$("body").prepend( html );
					  }
					
					this.overlay_active = true;

					/* ----------------------------------------------- BTN close */
					
					$( "#" + options.id + "_close" ).click(function(){
						_js.overlay_close( options );
					});

					$(document).keyup(function(e){
						if( e.keyCode == 27 ){
						  	_js.overlay_close( options );
						}
					});

					/* ----------------------------------------------- Affichage */

				  	$( "#" + options.id ).fadeIn( 200 );
				  	$( "#" + options.parent_id ).addClass("blur");

					/* ----------------------------------------------- */

				  },

	overlay_close : function( options )
				  {
					if( this.overlay_active == true )
					  {
						$( "#" + options.id ).fadeOut( 200 );
						$( "#" + options.parent_id ).removeClass("blur");
						this.overlay_active = false;
					  }
				  },



	/**
	* PREVENT STANDALONE APP LINKS
	* src : https://stackoverflow.com/questions/1173194/select-all-div-text-with-single-mouse-click
	*/

	prevent_standalone_app_links : function ( id )
				   {
						$( document ).on(
							"click",
							"a",
							function( event ){
								event.preventDefault();
								location.href = $( event.target ).attr( "href" );
						});

				   },



	/**
	* DETECT ADOBE FLASH
	*/

	is_flash : function ()
				   {
					if( ( typeof swfobject !== "undefined" && swfobject.getFlashPlayerVersion().major !== 0 ) || ( navigator.plugins.namedItem("Shockwave Flash") ) )
					  {
					  	return true;
					  }
					else
					  {
					  	return false;
					  }
				   },



	/**
	* DESACTIVE LE BOUTON DE RETOUR DE L'HISTORIQUE
	*/

	disable_back_button : function ()
				   {
					window.history.pushState(null,"", window.location.href);
					window.onpopstate = function(){ window.history.pushState(null,"",window.location.href); };
				   },



	/**
	* RENVOI TRUE OU FALSE SI LA COULEUR EST CLAIRE
	*/

	is_claire : function ( couleur )
				   {
					var rgb = parseInt( couleur , 16 );
					var r = (rgb >> 16) & 0xff;
					var g = (rgb >>  8) & 0xff;
					var b = (rgb >>  0) & 0xff;
					var luma = 0.2126 * r + 0.7152 * g + 0.0722 * b;
					return ( luma > 200 ) ? true : false;
				   },



	/**
	* CAPITALIZE TEXT
	*/

	capitalize : function ( txt )
				   {
				   	return txt.charAt(0).toUpperCase() + txt.slice(1);
				   },



	/**
	* CAPTURE LE KONAMI CODE ET LANCE UNE FONCTION
	*/

	konami : function ( func , prevent )
				   {
					if( window.addEventListener )
					  {
						this.konami_func	= func;
						this.konami_keys	= [];
						this.konami_code	= "38,38,40,40,37,39,37,39,66,65";

						window.addEventListener("keydown", function(e){

							if( _js.konami_code.toString().indexOf( e.keyCode ) >= 0 )
							  {
								if( ( typeof( prevent ) != undefined ) && ( prevent == true ) )
								  {
									e.preventDefault();
								  }
							
								_js.konami_keys.push( e.keyCode );
								
								if( _js.konami_keys.toString().indexOf( _js.konami_code ) >= 0 )
								  {
									_js.konami_keys = [];
									_js.konami_func();
								  }
							  }
						} , true );
					  }
				   },



	/**
	* COPIER / COLLER
	*/

	copier : function ( id )
				{

					var data = null;
				
					if( $(id).val() )
					  {
					  	data = $(id).val();
					  }
					else if( $(id).html() )
					  {
					  	data = $(id).html();
					  }
				
					if( data != null )
					  {
					  	data = data.replace( /(<([^>]+)>)/ig , "" );
					  	data = data.replace( /\t/ig , "" );
				
						try
						  {
						  	if( $("#clipboard").length == 0 )
						  	  {
								$("body").append( $("<textarea/>", {
									id 	: "clipboard",
									css 	: {
												"position"		: "absolute",
												"left" 		: "-98765px",
												"background"	: "#0099FF",
												"width"		: "0px",
												"height"		: "0px"
											}
								}));
						  	  }
				
						 	$("#clipboard").show().html( data ).select();
										
							if( document.execCommand("copy") )
							  {
								if( $.isFunction(msg) )
								  {
									msg( "Le texte a été copié." );
								  }
								else {}
								
							  }
							else
							  {
								console.log( "Unable to copy !" );
							  }
							
							$("#clipboard").html("").hide();
						  }
						catch( err )
						  {
							console.log( "Unsupported Browser !" );
						  }
					
						document.getSelection().removeAllRanges();
					  }

				},



	/**
	* VERIFIE LA COMPATIBILITE AVEC LOCAL/SESSION STORAGE
	*/

	check_storage : function ()
				{
					if( typeof(Storage) !== "undefined" )
					  {
						return true;
					  }
					else
					  {
						if( $.isFunction(msg) )
						  {
					  		msg( "Votre appareil ne gère pas la sauvegarde de données" );
						  }
						else
						  {
					  		alert( "Votre appareil ne gère pas la sauvegarde de données" );
						  }
						return false;
					  }

				},



	/**
	* RETOURNE LES ERREURS DE GEOLOCALISATION
	*/

	geo_error : function ( error )
				{

					var txt = "";

					switch(error.code)
					  {
						case error.PERMISSION_DENIED:
							txt = "User denied the request for Geolocation.";
							break;
						case error.POSITION_UNAVAILABLE:
							txt = "Location information is unavailable.";
							break;
						case error.TIMEOUT:
							txt = "The request to get user location timed out.";
							break;
						case error.UNKNOWN_ERROR:
							txt = "An unknown error occurred.";
							break;
					  }

					if( txt != "" )
					  {
						if( $.isFunction(msg) )
						  {
					  		msg( txt );
						  }
						else
						  {
					  		alert( txt );
						  }
					  }
				},



	/**
	* VERIFIE SI L'APP EST EN MODE STANDALONE (webapp)
	*/

	is_standalone_app : function ()
				   {
					return ( window.matchMedia( "(display-mode: standalone)" ).matches );
				   },



	/**
	* VERIFIE SI LA VALEUR EST DANS LE RANGE
	*/

	is_between : function ( x , min , max )
				   {
					return ( x >= min && x <= max );
				   },



	/* -------------------------------------------------------------------------------------------------------------------------------------------- */
	/* ------------------------------------------------------------------------------------------------------------------------------------- EXTRAS */
	/* -------------------------------------------------------------------------------------------------------------------------------------------- */

	/**
	* REMPLACE UN ELEMENT DANS UNE CHAINE DE CARACTERES
	* src : http://www.fobit.com/index.php?article=JavaScript%3A%20str_replace
	*/

	str_replace : function ( search , replace , subject )
				  {
					var result	= "";
					var oldi	= 0;
					for( i=subject.indexOf(search) ; i>-1 ; i=subject.indexOf(search, i) )
					  {
						result += subject.substring( oldi, i );
						result += replace;
						i += search.length;
						oldi = i;
					  }

					return result + subject.substring( oldi, subject.length );
				  },


	/**
	* FONCTION EQUIVALENTE A PRINT_R DE PHP
	* src : http://snipplr.com/view/8378/javascript-equivalent-for-phps-printr/
	*/

	print_r : function( array, return_val )
				  {
					var output = "", pad_char = " ", pad_val = 4;

					var formatArray = function(obj, cur_depth, pad_val, pad_char)
								  {
									if (cur_depth > 0)
									  {
										cur_depth++;
									  }

									var base_pad = repeat_char(pad_val*cur_depth, pad_char);
									var thick_pad = repeat_char(pad_val*(cur_depth+1), pad_char);
									var str = "";

									if (obj instanceof Array || obj instanceof Object)
									  {
										str += "Array\n" + base_pad + "(\n";
										for (var key in obj)
										  {
											if( obj[key] instanceof Array )
											  {
												str += thick_pad + "["+key+"] => "+formatArray(obj[key], cur_depth+1, pad_val, pad_char);
											  }
											else
											  {
												str += thick_pad + "["+key+"] => " + obj[key] + "\n";
											  }
										  }
										str += base_pad + ")\n";
									  }
									else if(obj == null || obj == undefined )
									  {
										str = "";
									  }
									else
									  {
										str = obj.toString();
									  }

									return str;
								};

					var repeat_char = function(len, pad_char)
								  {
									var str = "";
									for(var i=0; i < len; i++)
									  {
										str += pad_char; 
									  };
									return str;
								  };

					output = formatArray(array, 0, pad_val, pad_char);

					if (return_val !== true)
					  {
						document.write("<pre>" + output + "</pre>");
						return true;
					  }
					else
					  {
						return output;
					  }
				  },


	/**
	* FONCTION EQUIVALENTE A TIME DE PHP
	* src : http://kevin.vanzonneveld.net/techblog/article/javascript_equivalent_for_phps_time/
	*/

	time : function()
				  {
					return Math.round(new Date().getTime()/1000);
				  },

	/**
	* FONCTION EQUIVALENTE A MICROTIME DE PHP
	* src : http://phpjs.org/functions/microtime/
	*/

	microtime : function( get_as_float )
				  {
					var now	= new Date().getTime() / 1000;
					var s		= parseInt( now , 10 );
					
					return (get_as_float) ? now :  (Math.round( (now - s) * 1000) / 1000 ) + " " + s;
				  },

	/**
	* NOMBRE DE JOURS ENTRE 2 DATES
	* src : http://stackoverflow.com/questions/2627473/how-to-calculate-the-number-of-days-between-two-dates-using-javascript
	*/

	nb_jours : function( date1 , date2 )
				  {
				  	date1 = date1.split( "-" );
				  	date2 = date2.split( "-" );

					var oneDay		= 24*60*60*1000; /* hours*minutes*seconds*milliseconds */
					var firstDate	= new Date( date1[0] , date1[1] , date1[2] );
					var secondDate	= new Date( date2[0] , date2[1] , date2[2] );
					var diffDays	= Math.round( Math.abs( ( firstDate.getTime() - secondDate.getTime() ) / ( oneDay ) ) );
					
					return diffDays;
				  },


	/**
	* FONCTION PRESQUE EQUIVALENTE A DATE DE PHP
	* src : http://dev.petitchevalroux.net/javascript/date-format-javascript-javascript.67.html
	*/

	date : function( format , date )
				  {
					if (date == undefined)
					  {
						date = new Date();
					  }

					if (typeof date == "number") 
					  {
						time = new Date();
						time.setTime(date);
						date = time;
					  }
					else if (typeof date == "string") 
					  {
						date = new Date(date);
					  }

					var fullYear = date.getYear();
					if (fullYear < 1000) 
					  {
						fullYear = fullYear + 1900;
					  }

					var hour		= date.getHours();
					var day		= date.getDate();
					var month		= date.getMonth() + 1;
					var minute		= date.getMinutes();
					var seconde		= date.getSeconds();
					var milliSeconde	= date.getMilliseconds();

					var reg		= new RegExp("(d|m|Y|H|i|s)", "g");
					var replacement	= new Array();
					replacement["d"]	= day < 10 ? "0" + day : day;
					replacement["m"]	= month < 10 ? "0" + month : month;
					replacement["Y"]	= fullYear;
					replacement["Y"]	= fullYear;
					replacement["H"]	= hour < 10 ? "0" + hour : hour;
					replacement["i"]	= minute < 10 ? "0" + minute : minute;
					replacement["s"]	= seconde < 10 ? "0" + seconde : seconde;
					return format.replace(reg, function($0)
											{
												return ($0 in replacement) ? replacement[$0] : $0.slice(1,$0.length - 1);
											});
				  },


	/**
	* FONCTION EQUIVALENTE UTF8_ENCODE DE PHP
	* src : http://kevin.vanzonneveld.net/techblog/article/javascript_equivalent_for_phps_utf8_encode/
	*/

	utf8_encode : function( string )
				  {
					string = (string+'').replace(/\r\n/g, "\n").replace(/\r/g, "\n");

					var utftext = "";
					var start, end;
					var stringl = 0;
					start = end = 0;
					stringl = string.length;

					for (var n = 0; n < stringl; n++) 
					 {
						var c1 = string.charCodeAt(n);
						var enc = null;

						if (c1 < 128) 
						  {
							end++;
						  } 
						else if((c1 > 127) && (c1 < 2048))
						  {
							enc = String.fromCharCode((c1 >> 6) | 192) + String.fromCharCode((c1 & 63) | 128);
						  }
						else 
						  {
							enc = String.fromCharCode((c1 >> 12) | 224) + String.fromCharCode(((c1 >> 6) & 63) | 128) + String.fromCharCode((c1 & 63) | 128);
						  }
						if (enc != null)
						  {
							if (end > start)
							  {
								utftext += string.substring(start, end);
							  }
							utftext += enc;
							start = end = n+1;
						  }
					  }

					if (end > start) 
					  {
						utftext += string.substring(start, string.length);
					  }

					return utftext;
				  },


	/**
	* FONCTION EQUIVALENTE UTF8_DECODE DE PHP
	* src : http://kevin.vanzonneveld.net/techblog/article/javascript_equivalent_for_phps_utf8_decode/
	*/

	utf8_decode : function( str_data )
				  {
					var tmp_arr = [], i = ac = c1 = c2 = c3 = 0;

					str_data += '';

					while ( i < str_data.length )
					  {
						c1 = str_data.charCodeAt(i);
						if (c1 < 128) 
						  {
							tmp_arr[ac++] = String.fromCharCode(c1);
							i++;
						  } 
						else if ((c1 > 191) && (c1 < 224))
						  {
							c2 = str_data.charCodeAt(i+1);
							tmp_arr[ac++] = String.fromCharCode(((c1 & 31) << 6) | (c2 & 63));
							i += 2;
						  }
						else 
						  {
							c2 = str_data.charCodeAt(i+1);
							c3 = str_data.charCodeAt(i+2);
							tmp_arr[ac++] = String.fromCharCode(((c1 & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
							i += 3;
						  }
					  }

					return tmp_arr.join("");
				  },


	/**
	* FONCTION EQUIVALENTE BASE64_ENCODE DE PHP
	* src : http://kevin.vanzonneveld.net/techblog/article/javascript_equivalent_for_phps_base64_encode/
	*/

	base64_encode : function( data )
				  {
					var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
					var o1, o2, o3, h1, h2, h3, h4, bits, i = ac = 0, enc="", tmp_arr = [];
					data = utf8_encode(data);

					do
					  {
						o1	= data.charCodeAt(i++);
						o2	= data.charCodeAt(i++);
						o3	= data.charCodeAt(i++);
						bits	= o1<<16 | o2<<8 | o3;
						h1	= bits>>18 & 0x3f;
						h2	= bits>>12 & 0x3f;
						h3	= bits>>6 & 0x3f;
						h4	= bits & 0x3f;

						tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
					  }
					while( i < data.length );

					enc = tmp_arr.join("");
					switch( data.length % 3 )
					  {
						case 1 :	enc = enc.slice(0, -2) + '==';	break;
						case 2 :	enc = enc.slice(0, -1) + '=';		break;
					  }

					return enc;
				  },


	/**
	* EFFET DE CLICK COMME GOOGLE MATERIAL
	* src : http://thecodeplayer.com/walkthrough/ripple-click-effect-google-material-design
	*/

	click_ripple : function( options )
				  {
					var defaults = {
						selector		: "",
						parent		: false,
						delay		 	: 150,
						"class"		: "",
						class_ripple	: "click_ripple",
						class_ink 		: "click_ripple_ink",
						class_animate 	: "click_ripple_animate"
					};
				
					if( typeof( options ) == "object" )
					  {
						options = $.extend( defaults, options );
					  }
					else if( typeof( options ) == "string" )
					  {
						var txt		= options;
						options 		= defaults;
						options.selector 	= txt;
					  }
					else
					  {
						return;
					  }				

					if( options.selector !== "" )
					  {
						$( options.selector ).click(function(e){
						
							var element = ( options.parent === true ) ? $(this).parent() : $(this);

							element.addClass( options.class_ripple );
							
							if( element.is("a") && ( element.attr("href") != "" ) )
							  {
								e.preventDefault();
								
								var href	= element.attr("href");
							  	var target	= element.attr("target");
								var regex	= /^(_?blank)$/gmi;

							  	if( ( target != undefined ) && ( regex.exec( target ) != null ) )
							  	  {
									setTimeout( function(){ window.open( href , "_blank" ); return false; } , options.delay );
							  	  }
								else
								  {
									setTimeout( function(){ window.location = href } , options.delay );
								  }						  	
							  }
							

							if( element.find( "." + options.class_ink ).length === 0 )
							  {
								element.prepend( "<span class='" + options.class_ink + ( options["class"] != "" ? ( " " +  options["class"] ) : "" ) + "'></span>" );
							  }
								
							var ink = element.find( "." + options.class_ink ).removeClass( options.class_animate );
							
							if( !ink.height() && !ink.width() )
							  {
							  	var d = Math.max( element.outerWidth() , element.outerHeight() );
								ink.css( { height : d , width : d } );
							  }
							
							var x = e.pageX - element.offset().left - ink.width()/2;
							var y = e.pageY - element.offset().top - ink.height()/2;
							
							ink.css( { top : y + "px", left : x + "px" } ).addClass( options.class_animate );
						});
					  }


				  },



	/**
	* AUTORISE LES TABULATION DANS LES ÉLEMENTS D'ÉDITION
	* src : http://stackoverflow.com/questions/6140632/how-to-handle-tab-in-textarea
	*/

	tabkey : function( selector )
				  {
					$( selector ).keydown(function(e) {
						if(e.keyCode === 9)
						  {
							var start	= this.selectionStart;
							var end	= this.selectionEnd;
							var $this	= $(this);
							var value 	= $this.val();
							
							$this.val( value.substring( 0 , start ) + "\t" + value.substring( end ) );
							this.selectionStart = this.selectionEnd = start + 1;
							e.preventDefault();
						}
					});
				  },



	/**
	* REMET À ZÉRO UN ÉLEMENT DE FORMULAIRE
	* src : http://stackoverflow.com/a/13351234/851728
	*/

	reset_form_element : function( selector )
				  {
					$( selector ).wrap( "<form>" ).closest( "form" ).get(0).reset();
					$( selector ).unwrap();
				  },



	/**
	* FONCTION EQUIVALENTE A NUMBER_FORMAT DE PHP
	* src : http://phpjs.org/functions/number_format/
	*/

	number_format : function( number , decimals , dec_point , thousands_sep )
				  {
					number = (number + '').replace(/[^0-9+\-Ee.]/g, '');

					var n = !isFinite(+number) ? 0 : +number,
					prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
					sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
					dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
					s = '',
					toFixedFix = function(n, prec) {
					var k = Math.pow(10, prec);
					return '' + (Math.round(n * k) / k)
					.toFixed(prec);
					};
					s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
					.split('.');
					if (s[0].length > 3) {
					s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
					}
					if ((s[1] || '')
					.length < prec) {
					s[1] = s[1] || '';
					s[1] += new Array(prec - s[1].length + 1)
					.join('0');
					}

					return s.join(dec);

				  },



	/**
	* FONCTION QUI RECUPERE UN PARAMETRE DE L'URL
	* src : http://www.jquerybyexample.net/2012/06/get-url-parameters-using-jquery.html
	*/

	getUrlParameter : function (sParam)
				   {
					var	sPageURL 		= decodeURIComponent(window.location.search.substring(1)),
						sURLVariables	= sPageURL.split('&'),
						sParameterName,
						i;
					
					for( i = 0; i < sURLVariables.length; i++)
					  {
						sParameterName = sURLVariables[i].split('=');
					
						if( sParameterName[0] === sParam )
						  {
								return sParameterName[1] === undefined ? true : sParameterName[1];
						  }
					  }
				   },



	/**
	* CALCULE LA TAILLE D'UN OBJET
	* src : http://stackoverflow.com/questions/5533192/how-to-get-object-length
	*/

	obj_length : function ( obj )
				   {
					var count = 0;
					var i;
					
					for( i in obj )
					  {
						if( obj.hasOwnProperty(i) )
						  {
							count++;
						  }
					  }

					return count;

				   },



	/**
	* SELECTIONNE LE TEXTE D'UN DIV
	* src : https://stackoverflow.com/questions/1173194/select-all-div-text-with-single-mouse-click
	*/

	select_text : function ( id )
				   {
					if (document.selection)
					  {
						var range = document.body.createTextRange();
						range.moveToElementText(document.getElementById( id ));
						range.select();
					  }
					else if( window.getSelection )
					  {
						var range = document.createRange();
						range.selectNode(document.getElementById( id ));
						window.getSelection().removeAllRanges();
						window.getSelection().addRange(range);
					  }

				   },





	/**
	* FAIT PARLER LE NAVIGATEUR
	*/

	speak : function ( text )
				{
					var message = new SpeechSynthesisUtterance( text );
					message.lang = "fr-FR";
					window.speechSynthesis.speak(message);
				},





	/**
	* FONCTION EQUIVALANTE A PRINT_R EN PHP
	* src : https://gist.github.com/faisalman/879208
	*/

	print_r : function ( obj , t )
				{
				    var tab = t || '';
				    var isArr = Object.prototype.toString.call(obj) === '[object Array]' ? true : false;
				    var str = isArr ? ('Array\n' + tab + '[\n') : ('Object\n' + tab + '{\n');

				    for(var prop in obj){
				        if (obj.hasOwnProperty(prop)) {
				            var val1 = obj[prop];
				            var val2 = '';
				            var type = Object.prototype.toString.call(val1);
				            switch(type){
				                case '[object Array]':
				                case '[object Object]':
				                    val2 = print_r(val1, (tab + '\t'));
				                    break;
				                case '[object String]':
				                    val2 = '\'' + val1 + '\'';
				                    break;
				                default:
				                    val2 = val1;
				            }
				            str += tab + '\t' + prop + ' => ' + val2 + ',\n';
					}
				    }
				    str = str.substring(0, str.length-2) + '\n' + tab;
				    return isArr ? (str + ']') : (str + '}');

				}




};





















