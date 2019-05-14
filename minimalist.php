<?php

/* ------------------------------------------------------------------------------- INIT */

include("lib/app/minimalist/app.php");


/* ------------------------------------------------------------------------------- CONFIG */

$_HTML["page"]    = "app_minimalist";
$_HTML["title"]   = "APP Minimalist";


/* ------------------------------------------------------------------------------- CONTENT */

$_HTML["data"]["content"] .= "

		<h1>APP minimaliste du framework _HTML.</h1>
		<div class='space20'></div>
		<h2>Code source pour générer cette page.</h2>
		<div class='space20'></div>
		".highlight_file( __FILE__ , true )."

		<div class='space20'></div>
		<p>".functions::lorem_ipsum( rand( 30 , 200 ) )."</p>
		<p>".functions::lorem_ipsum( rand( 30 , 200 ) )."</p>
		<p>".functions::lorem_ipsum( rand( 30 , 200 ) )."</p>";


/* ------------------------------------------------------------------------------- DISPLAY */

$_HTML["display"] = true;
