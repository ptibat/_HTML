<?php

/* ---------------------------------------------------------------------------------------------------------------- INIT */

include("lib/app/website/app.php");


/* ---------------------------------------------------------------------------------------------------------------- CONFIG */

$_HTML["page"]			= "preview";
$_HTML["title"]		 	= "CSS Preview — ".$_HTML["title"];



/* ---------------------------------------------------------------------------------------------------------------- CONTENU */

$_HTML["data"]["content"] .= "

<!-- --------------------------------------------------------------------------------------------- -->

<div class='colonnes'>
		
	<div class='colonne'>

		<h1>Titres</h1>
		<div class='space10'></div>

		<h1>Titre H1</h1>
		<h2>Titre H2</h2>
		<h3>Titre H3</h3>
		<h4>Titre H4</h4>
		<h5>Titre H5</h5>

	</div>

		
	<div class='colonne'>
	
		<h1>Checkboxes</h1>
		<div class='space10'></div>
		
		<span class='checkbox'>
			<input  id='checkbox1' name='checkbox1' type='checkbox' checked='checked' />
			<label for='checkbox1'></label>
		</span> Checked
		
		<br />
		
		<span class='checkbox'>
			<input  id='checkbox2' name='checkbox2' type='checkbox' />
			<label for='checkbox2'></label>
		</span> Unchecked
		
		<br />
		
		<span class='checkbox'>
			<input  id='checkbox3' name='checkbox3' type='checkbox' disabled='disabled' />
			<label for='checkbox3'></label>
		</span> Disabled
		
		<br />

		<span class='checkbox orange'>
			<input  id='checkbox4' name='checkbox4' type='checkbox' checked='checked' />
			<label for='checkbox4'></label>
		</span> Orange
		
		<br />

		<span class='checkbox rouge'>
			<input  id='checkbox5' name='checkbox5' type='checkbox' checked='checked' />
			<label for='checkbox5'></label>
		</span> Rouge
		
		<br />

		<span class='checkbox bleu'>
			<input  id='checkbox6' name='checkbox6' type='checkbox' checked='checked' />
			<label for='checkbox6'></label>
		</span> Bleu
		
		<br />

		<span class='checkbox gris'>
			<input  id='checkbox7' name='checkbox7' type='checkbox' checked='checked' />
			<label for='checkbox7'></label>
		</span> Gris
		
		
		<div class='space40'></div>
		
	</div>

	<div class='colonne'>

		<h1>Boutons radio</h1>
		<div class='space10'></div>
		
		<span class='radio'>
			<input  id='radio1' name='radio[]' value='oui' type='radio' />
			<label for='radio1'></label>
		</span> Oui
		
		<br />
		
		<span class='radio'>
			<input  id='radio2' name='radio[]' value='non' type='radio' />
			<label for='radio2'></label>
		</span> Non
		
		<br />
		
		<span class='radio'>
			<input  id='radio3' name='radio[]' value='peutetre' type='radio' />
			<label for='radio3'></label>
		</span> Peut être
		
		<br />
		
		<span class='radio orange'>
			<input  id='radio4' name='radio[]' value='rouge' type='radio' />
			<label for='radio4'></label>
		</span> Orange
		
		<br />
		
		<span class='radio rouge'>
			<input  id='radio5' name='radio[]' value='orange' type='radio' />
			<label for='radio5'></label>
		</span> Rouge
		
		<br />
		
		<span class='radio bleu'>
			<input  id='radio6' name='radio[]' value='orange' type='radio' checked='checked' />
			<label for='radio6'></label>
		</span> Bleu
		
		<br />
		
		<span class='radio gris'>
			<input  id='radio7' name='radio[]' value='gris' type='radio' />
			<label for='radio7'></label>
		</span> Gris



	</div>

	<div class='colonne'>

		<h1>Listes</h1>
		<div class='space10'></div>
		
		<h3>Virgules (.virgules)</h3>
		<ul class='virgules'>
			<li>Un</li>
			<li>deux</li>
			<li>trois</li>
			<li>quatre</li>
		</ul>
		<div class='space10'></div>


		<h3>Tirets (.tiret)</h3>
		<ul class='tiret'>
			<li>Un</li>
			<li>Deux</li>
			<li>Trois</li>
			<li>Quatre</li>
		</ul>
		<div class='space10'></div>


		<h3>Numéros (.number)</h3>
		<ul class='number'>
			<li>Un</li>
			<li>Deux</li>
			<li>Trois</li>
			<li>Quatre</li>
		</ul>
		<div class='space10'></div>


		<h3>Flèches (.arrows)</h3>
		<ul class='arrows'>
			<li>Un</li>
			<li>Deux</li>
			<li>Trois</li>
			<li>Quatre</li>
		</ul>
		<div class='space10'></div>

	</div>

</div>



<!-- --------------------------------------------------------------------------------------------- -->

<div class='colonnes'>
	
	<div class='colonne2'>
		
		<h1>Boutons & Tags</h1>
		<div class='space20'></div>
			<span class='bouton'>.bouton</span>
		<br /><span class='bouton large'>.bouton .large</span>
		<br /><span class='bouton border'>.bouton .border</span>
		<br /><span class='bouton big'>.bouton .big</span>
		<br /><span class='bouton large animate'>.bouton .large .animate</span>
		<br />
		<br /><span class='bouton2'>.bouton2</span>
		<br /><span class='bouton2 large'>.bouton2 .large</span>
		<br /><button class='bouton2'>.bouton2</button>
		<br /><button class='bouton2 large'>.bouton2 .large</button>
		<br />
		<br /><span class='btn'>.btn</span>
		<br /><span class='btn animate'>.btn .animate</span>
		<br />

		<div class='space40'></div>
		<h1>Bubbles</h1>
		<br /><span class='tag jaune bubble'>.tag .jaune .bubble</span> 
			<span class='tag jaune bubble large'>.bubble .large</span> 
			<span class='tag rouge bubble'>5</span> 
			<span class='tag orange bubble'>84</span> 
			<span class='tag vert bubble'>".date("Y")."</span>
			
		<div class='space40'></div>
		<h1>Arrows</h1>
		<div class='center'>
			<span class='tag bleu left_arrow'>.tag .left_arrow</span>  
			<span class='tag bleu right_arrow'>.tag .right_arrow</span> 
			<div class='space5'></div>
			<span class='tag bleu double_left_arrow'>.tag .double_left_arrow</span>  
			<span class='tag bleu double_right_arrow'>.tag .double_right_arrow</span> 
			<div class='space5'></div> 
			<span class='tag bleu double_left_arrow chained'>1</span>
			<span class='tag bleu double_left_arrow chained'>2</span>
			<span class='tag bleu double_left_arrow chained'>3</span>
			<span class='tag bleu double_left_arrow chained'>4</span>
			<div class='space5'></div> 
			<span class='tag bleu double_right_arrow chained'>1</span>
			<span class='tag bleu double_right_arrow chained'>2</span>
			<span class='tag bleu double_right_arrow chained'>3</span>
			<span class='tag bleu double_right_arrow chained'>4</span>
		</div>




		<div class='space50'></div>
		<h1>Couleurs des boutons et tags</h1>
		<br /><span class='bouton large'>Bouton</span> <span class='tag'>Tag</span>";

		$couleurs = array(
			"vert",
			"orange",
			"rouge",
			"cyan",
			"bleu",
			"violet",
			"jaune",
			"menthe",
			"rose",
			"framboise",
			"gris_fonce",
			"gris",
			
			"gris_clair",
			"vert_clair",
			"bleu_clair",
			"jaune_clair",
			"orange_clair",
			"rouge_clair"
		);

		foreach( $couleurs as $couleur )
		  {
			$_HTML["data"]["content"] .= "<br /><span class='bouton large ".$couleur."'>".$couleur."</span> <span class='tag ".$couleur."'>".$couleur."</span>";
		  }
		
		$_HTML["data"]["content"] .= "
	
	</div>



	<div class='colonne2'>

		<h1>Notices & Messages</h1>
		<div class='space20'></div>
		
			<span class='notice'>.notice</span>
		<br />
		<br /><span class='notice big'>.notice .big</span>
		<br />
		<br /><span class='message'>.message</span>
		
		
		<br /><span class='note'>.note</span>
		<br />
		<br /><span class='note big'>.note .big</span>
		<br />
		<br /><span class='message note'>.message .note</span>
		
		
		<br /><span class='info'>.info</span>
		<br />
		<br /><span class='info big'>.info .big</span>
		<br />
		<br /><span class='message info'>.message .info</span>
		
		
		<br /><span class='erreur'>.erreur</span>
		<br />
		<br /><span class='erreur big'>.erreur .big</span>
		<br />
		<br /><span class='message erreur'>.message .erreur</span>
		
		
		<br /><span class='important'>.important</span>
		<br />
		<br /><span class='important big'>.important .big</span>
		<br />
		<br /><span class='message error'>.message .error</span>
	
	</div>

</div>




<!-- --------------------------------------------------------------------------------------------- -->

<div class='space50'></div>


<h1>Balises de code &lt;pre&gt; /  &lt;xmp&gt; / &lt;code&gt;</h1>
<pre>
body
	{
		font-family :		Sans-serif;
		font-size :		14px;
	}
</pre>

<div class='space20'></div>

Exemple avec une balise span : <span class='code'>&lt;span class=\"code\"&gt;&lt;/span&gt;</span>




<!-- --------------------------------------------------------------------------------------------- -->

<div class='space50'></div>

<h1>Alignement</h1>
<div class='notice big left'>.left</div>
<div class='space5'></div>
<div class='notice big center'>.center</div>
<div class='space5'></div>
<div class='notice big right'>.right</div>




<!-- --------------------------------------------------------------------------------------------- -->

<div class='space50'></div>

<h1>Textes</h1>

<div class='space10'></div>
<h3>Normal</h3>
<div class='space5'></div>
<p class=''>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce magna est, semper a suscipit vitae, tempor eget odio.</p>


<div class='space10'></div>
<h3>.light</h3>
<div class='space5'></div>
<p class='light'>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce magna est, semper a suscipit vitae, tempor eget odio.</p>


<div class='space10'></div>
<h3>.small</h3>
<div class='space5'></div>
<p class='small'>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce magna est, semper a suscipit vitae, tempor eget odio.</p>


<div class='space10'></div>
<h3>.uppercase</h3>
<div class='space5'></div>
<p class='uppercase'>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce magna est, semper a suscipit vitae,<span class='sup'>SUP</span> tempor eget odio.</p>


<div class='space10'></div>
<h3>.strikeout</h3>
<div class='space5'></div>
<span class='strikeout'>C'est faux !</span>




<!-- --------------------------------------------------------------------------------------------- -->

<div class='space50'></div>

<h1>Formulaires</h1>

<div class='space20'></div>
<h3>Input : text</h3>
<input type='text' placeholder='input' /> 
<input type='text' class='vert' placeholder='.vert' /> 
<input type='text' class='orange' placeholder='.orange' /> 
<input type='text' class='rouge' placeholder='.rouge' /> 
<input type='text' class='bleu' placeholder='.bleu' />


<div class='space20'></div>
<h3>Input : range</h3>
<div class='space5'></div>
<input type='range' id='demo_range' min='0' max='100' value='90' step='10' /> <span id='demo_range_value' class='tag bubble large framboise'>90</span>";


$_HTML["js_ready"] .= "
$(document).on('input', '#demo_range', function() {
	$('#demo_range_value').html($(this).val());
});
";




$_HTML["data"]["content"] .= "
<div class='space20'></div>
<h3>Select</h3>
<select>
	<option>Option 1</option>
	<option>Option 2</option>
	<option>Option 3</option>
	<option>Option 4</option>
</select>

<div class='space20'></div>

<h3>Textarea</h3>
<textarea placeholder='no resize (défaut)'></textarea>
<textarea class='resize' placeholder='.resize'></textarea>


<br />
<br />
<br /><b>Bouton de confirmation : </b> <span class='tab'></span> ".$app->tools->btn_confirm(array( "btn" => "Supprimer cet élement" ))."




<!-- --------------------------------------------------------------------------------------------- -->

<div class='space50'></div>";

$tbody = "
	<tbody>
		<tr>
			<td>TD 1</td>
			<td>TD 2</td>
			<td>TD 3</td>
			<td>TD 4</td>
		</tr>
		<tr>
			<td>TD 5</td>
			<td>TD 6</td>
			<td>TD 7</td>
			<td>TD 8</td>
		</tr>
		<tr>
			<td>TD 9</td>
			<td>TD 10</td>
			<td>TD 11</td>
			<td>TD 12</td>
		</tr>
		<tr>
			<td>TD 13</td>
			<td>TD 14</td>
			<td>TD 15</td>
			<td>TD 16</td>
		</tr>
	</tbody>";


$_HTML["data"]["content"] .= "
<h1>Tableaux</h1>

<h3>.tableau</h3>
<div class='space5'></div>
<table class='tableau'>".$tbody."</table>

<div class='space20'></div>

<h3>.tableau .borders</h3>
<div class='space5'></div>
<table class='tableau borders'>".$tbody."</table>

<div class='space20'></div>

<h3>.tableau .lines</h3>
<div class='space5'></div>
<table class='tableau lines'>".$tbody."</table>

<div class='space20'></div>

<h3>.tableau .lines .borders</h3>
<div class='space5'></div>
<table class='tableau lines borders'>".$tbody."</table>

<div class='space20'></div>

<h3>div .table</h3>
<div class='space5'></div>

<div class='table'>
	<div class='table_row'>
		<div class='table_cell'>div.table_cell 1</div>
		<div class='table_cell'>div.table_cell 2</div>
	</div>
	<div class='table_row'>
		<div class='table_cell'>div.table_cell 3</div>
		<div class='table_cell'>div.table_cell 4</div>
	</div>
	<div class='table_row'>
		<div class='table_cell'>div.table_cell 5</div>
		<div class='table_cell'>div.table_cell 6</div>
	</div>
</div>







<!-- --------------------------------------------------------------------------------------------- -->

<div class='space50'></div>


<h1>Lignes HR</h1>
<div class='space20'></div>

<h3>.hr / .hr1</h3>
<hr />

<h3>.hr2</h3>
<hr class='hr2' />

<h3>.hr3</h3>
<hr class='hr3' />

<h3>.hr4</h3>
<hr class='hr4' />

<h3>.hr5</h3>
<hr class='hr5' />






<!-- --------------------------------------------------------------------------------------------- -->

<div class='space50'></div>


<h1>Ombres</h1>
<div class='space20'></div>
<div class='center'>
	<div class='box_ombre ombre'>.ombre</div>
	<div class='box_ombre ombre_left'>.ombre_left</div>
	<div class='box_ombre ombre_top'>.ombre_top</div>
	<div class='box_ombre ombre_right'>.ombre_right</div>
	<div class='box_ombre ombre_bottom'>.ombre_bottom</div>
</div>



<div class='space100'></div>

";





/* ---------------------------------------------------------------------------------------------------------------- DISPLAY */

$_HTML["display"] = true;






