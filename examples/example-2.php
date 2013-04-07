<?php

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
		->generate('example-2.pdf','D')
	;