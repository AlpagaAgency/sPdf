<?php

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
		// ->setMargin(10,10,10,10)
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
		->addText(3.5,'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse tincidunt arcu in enim luctus sed suscipit risus eleifend. Maecenas vitae felis nunc, eget congue metus. Donec fringilla egestas pellentesque. Etiam elementum tincidunt sapien vel laoreet. Integer consectetur varius enim vel porttitor. Donec venenatis tincidunt lectus, eget tincidunt tortor bibendum vitae. Phasellus vehicula mauris elit, at interdum nisl. Nulla varius elit eget augue faucibus interdum. Nunc mollis pretium venenatis.

Aliquam leo metus, dictum vel egestas vitae, congue vel tortor. Praesent tempor lectus viverra felis interdum tempor. Sed sed ipsum sit amet eros vulputate ullamcorper at in sem. Praesent at libero sit amet velit posuere pulvinar nec a augue. Aliquam lorem ante, scelerisque ac porta eu, posuere ut purus. Nullam eu diam ipsum, eu sagittis ante. Praesent nisi erat, volutpat eget condimentum at, mollis eget lorem. Praesent vitae ipsum in elit rhoncus hendrerit. Integer varius augue in enim ultrices imperdiet rutrum turpis pellentesque. In eleifend mollis lacus et aliquam.

Sed viverra sem ut mauris consectetur hendrerit. Sed vel eros a felis vehicula consectetur. Sed vitae dui vitae ante convallis adipiscing. Phasellus vehicula justo placerat sapien aliquet viverra. Etiam eget dignissim metus. Aenean id felis sit amet odio interdum consectetur tempus in neque. Maecenas suscipit lorem eget erat interdum ac commodo tortor vestibulum. Morbi gravida varius elit eu convallis. Aenean porta enim et arcu blandit porttitor ac in dolor. '
		)
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
		->generate('example-1.pdf','D')
	;