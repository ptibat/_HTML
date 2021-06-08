<?php

/* ---------------------------------------------------------------------------------------------------------------- INIT */

include("lib/app/website/app.php");


/* ---------------------------------------------------------------------------------------------------------------- CONFIG */

$_HTML["page"]			= "home";
$_HTML["title"]		 	= $_HTML["site_name"];


/* ---------------------------------------------------------------------------------------------------------------- CONTENT */

include( DOC_ROOT."/lib/tools/Parsedown.php" );
$Parsedown 	= new Parsedown();
$doc		= $Parsedown->text( file_get_contents( "readme.md" ) );
$doc		= str_replace( "./doc.html" , $app->tools->url( "doc" ) , $doc );
$doc		= str_replace( "doc.html" , "Documentation" , $doc );


$_HTML["data"]["content"] .= "<div class='markdown'>".$doc."</div>";


/* ---------------------------------------------------------------------------------------------------------------- CSS */

$_HTML["css"] .= "
.markdown
	{
		margin :			0;
		line-height :		1.3em;
	}
	
.markdown h1,
.markdown h2,
.markdown h3
	{
		margin :			10px 0;
	}

.markdown h2
	{
		margin-top :		30px;
	}

.markdown p,
.markdown ul
	{
		padding-top :		10px;
		padding-bottom :		10px;
	}

.markdown pre
	{
		padding :			0;
		border :			none;
		background :		none;
	}
";



/* ---------------------------------------------------------------------------------------------------------------- JAVASCRIPT */
/*
$_HTML["js_ready"] .= "setTimeout(function(){ msg('Welcome aboard'); }, 2000 );";
*/



/* ---------------------------------------------------------------------------------------------------------------- DISPLAY */

$_HTML["display"] = true;















