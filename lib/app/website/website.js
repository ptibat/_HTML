/**
* @ptibat
* 
*/

/* ----------------------------------------------------------------------------------- CONFIG */

var _HTML_BODY				= ( /webkit/i.test(navigator.userAgent) ) ? "body" : "html";
var _DEBUG					= false;
var _WEBAPP					= window.navigator.standalone ? true : false;


/* ----------------------------------------------------------------------------------- INIT */

$(document).ready(function(){
});




/* ----------------------------------------------------------------------------------- AFFICHAGE D'UN MESSAGE DANS LA CONSOLE */

_MSG_TIMEOUT = null;

msg = function( options ){

	var defaults = {
		text		: "",
		delay 		: 5000,
		color 		: "#aa4f03",
		bgcolor 	: "#ffecb2",
		scroll 	: false,
		exit		: true,
	};

	if( typeof( options ) == "object" )
	  {
		options = $.extend( defaults, options );
	  }
	else if( typeof( options ) == "string" )
	  {
		var txt		= options;
		options 		= defaults;
		options.text 	= txt;
	  }
	else
	  {
		return;
	  }

	$( "#msg" ).css({
				"color"		: options.color,
				"background-color"	: options.bgcolor 
			})
			.html( options.text )
			.fadeIn( 300 );

	if( options.scroll !== false )
	  {
		var Y = $("#msg").offset().top;
		$( _HTML_BODY ).animate({ scrollTop : 0 }, 500 );
	  }

	if( options.delay !== false )
	  {
		delay = ( !isNaN( options.delay ) ? options.delay : 15000 );
		_MSG_TIMEOUT = setTimeout( "$( '#msg' ).fadeOut( 500 );" , delay );
	  }

	if( options.exit === true )
	  {
		$( "#msg" ).click(function(){
			window.clearTimeout( _MSG_TIMEOUT );
		}).dblclick(function(){
			msg_hide();
		});
	  }

};

msg_hide = function(){
	window.clearTimeout( _MSG_TIMEOUT );
	$( "#msg" ).hide();
};



/* ----------------------------------------------------------------------------------- RETOURNE EN HAUT DE LA PAGE */

gotop = function(){
	$(window).scrollTop(0);
};

/* ----------------------------------------------------------------------------------- SCROLL VERS LA POSITION D'UN ELEMENT */

go_to = function( id , margin ){
	var Y = $( id ).position().top - ( margin != undefined ? margin : 0 );
	$(window).scrollTop( Y );
};


/* ----------------------------------------------------------------------------------- */















