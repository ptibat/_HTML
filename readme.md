# _HTML


**_HTML**, c'est un micro-framework **PHP**.  
C’est une boîte à outils qui vous permet de mettre en place rapidement et facilement un site internet.
Pourquoi "_HTML" ? ... tout simplement parce qu'au final on produit du code HTML pour un navigateur... ;)



## Philosophie du projet
Ce projet à été crée dans le but de simplifier la mise en oeuvre d’un site web de petite et moyenne envergure.  
Le principe est de séparer les parties communes d’un site (*les fonctions, les connexions à aux bases de données, les templates, les menus, …* ) des pages.  
Ainsi, la création d’une page est très rapide.


## Ce dont vous avez besoin

- Un serveur web (Apache,…)
- Un serveur SQL (*optionnel*)
- Une version de PHP supérieure à 7


## Compatibilité
_HTML est prévu pour fonctionner sur la plupart des plateformes et sans ajout de composants.  
Vous pouvez, par exemple, l’installer directement sur un hébergement mutualisé OVH.  
Compatible avec PHP 7.


## Exemple de page

	<?php
	
	include("lib/app/website/app.php");
	
	$_HTML['page']				 = 'page_exemple';
	$_HTML['title']				 = 'Page EXEMPLE';
	$_HTML['data']['content']	.= 'Contenu de la page';
	$_HTML['display'] 			 = true;

  
That’s all


## Documentation

voir la page « [doc.html](./doc.html) »

