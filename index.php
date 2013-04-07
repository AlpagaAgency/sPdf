<?php
	if (isset($_GET['createPdf']) && isset($_GET['example'])) {
		include ('spdf/src/sPdf.php');
		if (file_exists('examples/example-'.$_GET['example'].'.php')) {
			include ('examples/example-'.$_GET['example'].'.php');
		}
	}
?>
<!DOCTYPE html>
<html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8" />
	<link rel="icon" href="favicon.ico" />
	<meta name="viewport" content="width=device-width" />
	<title>sPdf | Alpaga Studio</title>

	<link rel="stylesheet" href="components/foundation/stylesheets/foundation.min.css">
	<link rel="stylesheet" href="static/styles/app.css">

	<!-- IE Fix for HTML5 Tags -->
	<!--[if lt IE 9]>
	<script src="components/polyfill/html5shiv.min.js" type="text/javascript"></script>
	<![endif]-->

	<script src="components/highlight/highlight.pack.js" type="text/javascript"></script>
	<script src="components/foundation/javascripts/modernizr.foundation.js" type="text/javascript"></script>
	<script src="components/jquery/jquery.min.js" type="text/javascript"></script>
	<script src="components/foundation/javascripts/foundation.min.js" type="text/javascript"></script>
	<script src="static/scripts/main.js" type="text/javascript"></script>
</head>
<body class="dark">
	<section class="container row">
		<div id="content-wrapper">
			<h1>sPdf - générateur de pdf</h1>
			<h2>Installation</h2>
			<p>Il suffit simplement d'inclure la librairie dans vos dossier et y faire référence avec un include.</p>
			<p>Cependant, cette librairie nécessite l'utilisation de <strong>php 5.3.3 au minimum</strong></p>
			<h2>Création de l'objet</h2>
			<p>Les paramètres sont facultatifs.<br />Le premier comprend le type de vue (P => Portrait, L => paysage), le format du document (mm,cm,pc, ...) et la taille du document (en fonction du format). Par défaut, les valeurs sont 'P|mm|210,297'.<br />Le second paramètre comprend les marges du document, par défaut, [0,0,0,0].
			<pre><code class="php">
				$pdf = new sPdf('P|mm|210,297', [0,0,0,0]);
			</code></pre>
			
			<h2>Fonctions utilitaires</h2>
			<p>Ces fonctions permettent, entre autre, l'utilisation de la numérotation automatique, de fixer un template de référence, de l'utilisation d'un template de référence en tant que background, etc.</p>
			<pre><code class="php">
				// Fonction à appeler avant la première page, permet de démarrer le compteur à 0
				$pdf->usePageNumerotation();
				
				// Définir le lien vers le template
				// Il faut fournir le lien relatif/absolu vers le pdf de référence
				$pdf->defineTemplatePath('pathToThePdf');
				
				// Définir un template comme background sur toutes les pages
				// Il faut fournir le numéro de la page
				$pdf->usePageFromTemplateAsBg($pageNum);
				
				// Créer une nouvelle page
				$pdf->newPage();
				
				// Utiliser une page de référence particulière en tant que background
				// Il faut fournir le numéro de la page
				$pdf->usePageFromTemplate($pageNum);
			</code></pre>
			<h2>Fonctions de mesure</h2>
			<p>Récupérer une position, la largeur du document, etc.</p>
			<pre><code>
				// définir une position x ou y avec un nombre flottant
				$pdf->setPosition($axe,$float);
				
				// Récupérer une position x ou y, retourne un nombre flottant
				$pdf->getPosition($axe);
				
				// Récupérer les informations du document, width ou height en paramètre
				$pdf->getPdfSize('width');
			</code></pre>
			<h2>Définition des marges</h2>
			<p>Basée sur le format de document (mm, cm, pc), les marges doivent être des nombres flottants.</p>
			<pre><code class="php">
				// Définis toutes les marges => top, right, bottom, left
				$pdf->setMargin($unit, $unit, $unit,$unit);

				$pdf->setMarginTop($value);
				$pdf->setMarginRight($value);
				$pdf->setMarginLeft($value);
				$pdf->setMarginBottom($value);
				
				// Récupérer les marges
				// Si aucune valeur n'est admise, l'objet retourne un tableau
				// Si une des valeurs suivantes <strong>top|right|bottom|left</strong> est admise, l'objet retourne un nombre
				$pdf->getMargin();
			</code></pre>
			<h2>Typographie</h2>
			<p>ces fonctions permettent l'ajout de typographie (passée au préalable dans le transformateur php), l'ajout de style, l'utilisation de la typo, ...</p>
			<pre><code class="php">
				// Ajouter une police de caractère au document. 
				// Le fichier doit être placé dans le dossier font à coté de la librairie
				// Nom de la typo, type et nom du fichier
				$pdf->addFontFace('hero','R','hero.php');
				
				// Définir un style utilisable dans les multiCell
				// tag, nom de la police, type de la police, taille de caractère et couleur (r,g,b)
				$pdf->setStyleRule("h2","hero","R","9","110,110,110");
				
				// Définir la typo utilisée dans les champs qui suivent sa déclaration
				$pdf->useFont('hero','R','9')
			</code></pre>
			<h2>Ajouter une ligne (visuelle)</h2>
			<p>Ajouter une ligne et les différents styles qui la compose. Le style doit être paramétré avant son ajout</p>
			<pre><code class="php">
				// couleur de la ligne
				// Convient aussi au ligne entourant les cellules
				$pdf->setBorderColor('200,200,200');
				
				// Ligne pointillé
				// taille de la ligne, espace entre les pointillés
				$pdf->setDash($lineWidth, $space);
				
				// Ajouter une ligne
				$pdf->addLine($x1,$y1,$x2,$y2);
			</code></pre>
			<h2>Ajouter une cellule, image, ...</h2>
			<p>Ajout de cellules, simple ou multiligne, d'images ou de tableaux.</p>
			<pre><code class="php">
				// Ajouter une cellule simple
				$pdf->addCell(
					'value'  // valeur obligatoire
					/*
						Ces valeurs-ci sont facultatives
					*/
						,[
							'width' => ($pdf->getPdfSize('width')/2-35), // => Par défaut, la taille du document moins les marges
							'height' => 4, // => Par défaut: 4
							'pos' => [null,null], // si une des valeurs est null, celle-ci est basé sur la position courante
							'align' => 'L',
							'border' => 1, //=> flaggé à 0,1 ou selon l'endroit LTRB (Left, Top, Right, Bottom)
							'fill' => false,
							'ln' => 1, // renvoi à la ligne automatiquement, flaggé à 0 permet la continuité sur une même ligne 
						]
				);
				
				// Si la valeur ln de addCell est placée à 0, la fonction pour revenir est la ligne est:
				// La valeur est facultative, correspondante à un nombre flottant
				$pdf->newLine(/*$value*/);
				
				// Ajouter une cellule multiligne
				$pdf->addMultiCell(
				'value'
				/*
					Ces valeurs sont facultatives
				*/
					,[
							'width' => 0, // set to 0 = pdf->getPdfSize('witdh')-margin; default 0;
							'height'=> 10,
							'border'=> 0,
							'align' => 'L',
							'fill' => false,
							'padding' => [0,0,0,0],
					]
				);
				
				// Ajouter une image
				$pdf->addImage('pathToImage'
					/*
						Ces valeurs sont facultatives
					*/
					,[
						'pos' => [x,y],
						'dpi' => 72|150|300, // L'image est redimensionnée au besoin
						'width' => mm, // Si le dpi est défini, la largeur jouera uniquement sur la largeur de cellule
						'height' => mm,
						'type' => JPEG|PNG|GIF,
						'link' => string,
						'ln' => 1, // define newLine automatically to 1
					]
				);
				
				// Ajouter un tableau				
				$pdf->addTable([
					'column' => 3,
					'pos' => [14,null],
					'columnWidth' => [145,12,25],
					// Facultatif!
					'header' => ['Objet','Qté','Montants'],
					'headerStyle' => [
						// bordertype: 1 (all), T (top), B (bottom), L (left), R (right), vous pouvez les combiner: TRB or RBL
						/* exemple complet
							[
								'border' => 'TB',
								'align' => 'L',
								'height' => 20,
							]
						*/
						['border' => 'B'],
						['border' => '1','align'=>'R'],
						['border' => '1','align'=>'R'],
					],
					// Facultatif!
					'content' => [
						['value1','value2','value3'],
						['value1','value2','value3'],
						['value1','value2','value3'],
						['value1','value2','value3'],
					], 
					'columnStyle' => [
						// le même que headerStyle
						['border' => 'LR'],
						['border' => 'LR','align'=>'R'],
						['border' => 'LR','align'=>'R'],
					],
				]);
				
				// Ajouter un flux de texte
				$pdf->addtext($lineHeight, $value);
			</code></pre>
			<h2>Ajouter du style à votre pdf</h2>
			<p>Manipulation des blocs, rotation, skew, scale, ...</p>
			<pre><code class="php">
				// Tjs débute une transformation par
				$pdf->startTransform();
				
				// Tjs terminer une transformation par
				$pdf->stopTransform();
				
				// Type (x,y,xy), angle (-90>90), position x et y de départ 
				// comme ancre de transformation par rapport 
				// au coin supérieur gauche de l'objet
				// Dans le cas d'un type xy, un tableau [x,y] peut être donné pour l'angle
				$pdf->skew($type,$angle,$x="",$y="");
				
				// angle (float), position de départ x/y en fonction du point d'origine (coin supérieur gauche)
				$pdf->rotate($angle, $x='', $y='');
				
				//  type (x,y,xy), value (peut être un tableau dans le cas xy
				$pdf->translate($type,$value);
				
				// type (x,y), position x de départ
				$pdf->mirror($type,$x='');
				
				// Type (x,y,xy), value (float), position x et y de départ 
				// comme ancre de transformation par rapport 
				// au coin supérieur gauche de l'objet
				// Dans le cas d'un type xy, un tableau [x,y] peut être donné pour l'angle
				$pdf->scale($type,$value,$x='',$y='');
			</code></pre>
			<h2>Transparence</h2>
			<p>Ajouter de la transparence à votre pdf</p>
			<pre><code class="php">
				// définir la valeur, comprise en 0 et 1, nombre flottant
				// si vous passez la valeur à 0.5, la transparence sera active pour
				// tous les éléments suivants
				$pdf->setAlpha(1);
			</code></pre>
			<h2>Générer le pdf</h2>
			<p>Pour générer le pdf, vous disposez de la méthode generate.<br />
			Le premier paramètre correspond au nom ou lien et nom du fichier<br />
			Le second paramètre définit l'action: I,D,F,S<br />
			<strong>I</strong> => Envoyer le pdf au navigateur, il sera interpreté par celui-ci via un plugin<br />
			<strong>D</strong> => Force le téléchargement<br />
			<strong>F </strong>=> Enregistre le fichier en suivant le nom de celui-ci (et du lien)<br />
			<strong>S</strong> => retourne l'objet sous forme de chaine</p>
			<pre><code class="php">
				$pdf->generate('nameOrPathAndLinkFolder+Name', 'D');
			</code></pre>
			<h2>Quelques exemples</h2>
			<p>Premier exemple - <a href="index.php?createPdf=true&example=1">Démo en direct</a></p>
			<pre><code class="php">
				$pdf = new sPdf('P|mm|105,150',[10,10,10,10]);
	
				$pdf
					->newPage()
					->addFontFace('hero','','hero.php')
					->addFontFace('hero','R','hero.php')
					->addFontFace('hero','L','hero_light.php')
					->setStyleRule("h2","hero","R","9","110,110,110")
					->setStyleRule("em","hero","R","8","50,50,50")
					->useFont('hero','R', '9')
					->setPosition('y',40)
					->addCell(
						'Jean François Du Moulin',
						[
							'width' => ($pdf->getPdfSize('width')/2-35),
							'height' => 4,
						]
					)
					->addCell('Rue du moulin blanc')
					->addCell('1020 Laeken')
					->newLine(5)
					->addText(3.5,'Lorem ... ac in dolor. ')
					->newPage()
					->addTable([
							'column' => 3,
							'columnWidth' => [50,12,25],
							'header' => ['Objet','Qté','Montants'],
							'headerStyle' => [
								['border' => 'B'],
								['border' => '1','align'=>'R'],
								['border' => '1','align'=>'R'],
							],
							'content' => [
								['value1','value2','value3'],
								['value1','value2','value3'],
								['value1','value2','value3'],
								['value1','value2','value3'],
								['value1','value2','value3'],
								['value1','value2','value3'],
								['value1','value2','value3'],
							], 
							'columnStyle' => [
								['border' => 'LR'],
								['border' => 'LR','align'=>'R'],
								['border' => 'LR','align'=>'R'],
							],
						])
					->generate('test.pdf','D')
				;
			</code></pre>
			<p>&nbsp;</p>
			<p>Second exemple - <a href="index.php?createPdf=true&example=2">Démo en direct</a></p>
			<pre><code class="php">
				$pdf = new sPdf('P|mm|210,297',[10,10,40,10]);
				
				$pdf
					->defineTemplatePath('tpl/tmpl.pdf')
					->usePageNumerotation()
					->usePageFromTemplateAsBg(4)
					->newPage()
					->usePageFromTemplate(1)
					->addFontFace('hero','','hero.php')
					->addFontFace('hero','R','hero.php')
					->addFontFace('hero','L','hero_light.php')
					->setStyleRule("h2","hero","R","9","255,255,255")
					->setStyleRule("em","hero","R","8","50,50,50")
					->useFont('hero','R', '9')
					->setPosition('y',140)
					->addImage('gfx/koala.jpg',[
						'width'=> 95,
					])
					->addCell('Hello World')
					->addCell('In a barbie world')
					->newPage()
					->newLine()
					->startTransform()
					->rotate(20, 50, 60)
					->addCell('Ma belle cellule')
					->addImage('gfx/koala.jpg',[
						'width'=> 95,
					])
					->addCell('hello')
					->stopTransform()
					->generate('test.pdf','D')
				;
			</code></pre>
		</div>
	</section>
	<footer class="center">Copyright Camelidae Group - 2012</footer>
</body>
</html>