<?php

/*	Copyright 2013 Fr?d?ric Faltin <frederic.faltin@alpagastudio.be>
 * 	This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 * 
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 * 
 *     You should have received a copy of the GNU General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
	
	include("fpdf/FPDF.php");
	include("fpdi/fpdi.php");
	include("fpdfmulticell/fpdfMulticell.php");

	class sPdf extends FPDI {
		
			protected $defaultSize;
			protected $templateFile;
			protected $this = null;
			protected $styler;
			protected $margin;
			protected $currentTemplate='';
			protected $alreadyImported=false;
			protected $backgroundTemplateFromPg=null;
			protected $useNum=false;
			protected $extgstates=array();
			protected $pageCount = 1;
			protected $addingTableRow = false;
			
			public function __construct($defaultSize='P|mm|210,297',$margin = array(0,0,0,0)) {
			
				// retrieve true values
				$defaultSize = explode('|',$defaultSize);
				$defaultSize[2] = explode(',',$defaultSize[2]);

				// declare a new pdf
				parent::__construct($defaultSize[0],$defaultSize[1],$defaultSize[2]);
				
				// reset margins
				$this->SetTopMargin($margin[0]);
				$this->SetLeftMargin($margin[3]);
				$this->SetRightMargin($margin[1]);
				$this->SetAutoPageBreak(true, $margin[2]);
				// add document
				// $this->Open();
				$this->state = 1;
				
				// define multicell class
				$styler = new \fpdfMulticell($this);
				
				// map to class vars
				$this->styler = $styler;
				$this->defaultSize = $defaultSize;
				$this->margin = $margin;
				
				// count pages
				$this->AliasNbPages();
				
				return $this;
			}
			
			public function getFontFamily()
			{
				return $this->FontFamily;
			}

			public function defineTemplatePath($templateFile) {
				$this->templateFile = $templateFile;
				return $this;
			}
		
			protected function useTmpl ($page2import) {
				// get the template's path
				if (!$this->alreadyImported || $this->currentTemplate!=$this->templateFile) {
					$this->currentTemplate = $this->templateFile;
					$this->pageCount = $this->setSourceFile($this->templateFile); 
				}
				// import page 
				$tplidx = $this->importPage($page2import); 
				// map with template
				$this->useTemplate($tplidx, 0, 0, $this->defaultSize[2][0]);
				
				$this->alreadyImported = true;
			}
			
			public function getPageCount() {
				return $this->setSourceFile($this->templateFile);
			}

			public function setMargin($top,$right,$bottom,$left) {
				$this->margin = array($top,$right,$bottom,$left);
				$this->SetMargins($top,$left,($right!=null?$right:$left));
				$this->SetAutoPageBreak(true,$bottom);
				return $this;
			}
			
			public function setMarginTop($value) {
				$this->margin[0] = $value;
				$this->SetTopMargin($value);
				return $this;
			}
			
			public function setMarginLeft($value) {
				$this->margin[3] = $value;
				$this->SetLeftMargin($value);
				return $this;
			}
			
			public function setMarginRight($value) {
				$this->margin[1] = $value;
				$this->SetRightMargin($value);
				return $this;
			}
			
			public function setMarginBottom($value) {
				$this->margin[2] = $value;
				$this->SetAutoPageBreak(true,$value);
				return $this;
			}
			
			public function newPage($orientation ="", $size="") {
				$this->addPage($orientation ="", $size="");
				return $this;
			}
			
			public function usePageFromTemplate($page2import) {
				if ($this->templateFile != null) $this->useTmpl($page2import);
				return $this;
			}
			
			public function usePageFromTemplateAsBg($page2Import) {
				$this->backgroundTemplateFromPg = $page2Import;
				return $this;
			}
			
			public function usePageNumerotation() {
				$this->useNum = true;
				return $this;
			}
			
			public function Header() {
				if ($this->backgroundTemplateFromPg != null) $this->usePageFromTemplate($this->backgroundTemplateFromPg);
			}
			
			public function Footer() {
				if ($this->useNum) {
					$this->SetTextColor(100,100,100);
					$this->addCell(
						"Page: ".$this->PageNo()."/{nb}",
						array (
							'pos' => array(null,-18),
							'align' => 'R',
							'width' => 0,
							'height' => 10,
						)
					);
				}
			}
			
			//*********************************************************************
			// Element's function
			//*********************************************************************
		
			protected function verboseCell ($arr,$type) {
				if (isset($arr['pos'])) {
					$this->makePosition($arr['pos']);
				}
				
				if (isset($arr["fillColor"]))
				{
					$colors = explode(',', $arr["fillColor"]);
					$this->SetFillColor($colors[0], $colors[1], $colors[2]);
					$arr["fill"] = true;
				}

				if ($type == "simple") {
					$this->Cell(
						isset($arr['width'])?$arr['width']:$this->getPdfSize('width')-$this->margin[1]-$this->margin[3]-(isset($arr['pos'])?$arr['pos'][0]:$this->getPosition('x')-$this->margin[3]),
						isset($arr['height'])?$arr['height']:4,
						$this->escapeString($arr['value']),
						isset($arr['border'])?$arr['border']:0,
						isset($arr['ln'])?$arr['ln']:1,
						isset($arr['align'])?$arr['align']:'L',
						isset($arr['fill'])?$arr['fill']:false
					);
				} elseif ($type == "multi") {
					$this->styler->MultiCell(
						isset($arr['width'])?$arr['width']:$this->getPdfSize('width')-$this->margin[1]-$this->margin[3]-(isset($arr['pos'])?$arr['pos'][0]:0),
						isset($arr['height'])?$arr['height']:7,
						$this->escapeString($arr['value']),
						isset($arr['border'])?$arr['border']:0,
						isset($arr['align'])?$arr['align']:'L',
						isset($arr['fill'])?$arr['fill']:false,
						isset($arr['padding'])?$arr['padding'][0]:0,
						isset($arr['padding'])?$arr['padding'][1]:0,
						isset($arr['padding'])?$arr['padding'][2]:0,
						isset($arr['padding'])?$arr['padding'][3]:0
					);
				}
			}
		
			public function addCell($value,$arr=array()) {
				$arr['value'] = $value;
				$this->verboseCell($arr, "simple");
				return $this;
			}
		
			public function addMultiCell($value,$arr=array()) {
				$arr['value'] = $value;
				$this->verboseCell($arr, "multi");
				return $this;
			}

			protected function computeNbLines($w,$txt)
			{
				//Computes the number of lines a MultiCell of width w will take
				$cw=&$this->CurrentFont['cw'];
				if($w==0)
					$w=$this->w-$this->rMargin-$this->x;
				$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
				$s=str_replace("\r",'',$txt);
				$nb=strlen($s);
				if($nb>0 and $s[$nb-1]=="\n")
					$nb--;
				$sep=-1;
				$i=0;
				$j=0;
				$l=0;
				$nl=1;
				while($i<$nb)
				{
					$c=$s[$i];
					if($c=="\n")
					{
						$i++;
						$sep=-1;
						$j=$i;
						$l=0;
						$nl++;
						continue;
					}
					if($c==' ')
						$sep=$i;
					$l+=$cw[$c];
					if($l>$wmax)
					{
						if($sep==-1)
						{
							if($i==$j)
								$i++;
						}
						else
							$i=$sep+1;
						$sep=-1;
						$j=$i;
						$l=0;
						$nl++;
					}
					else
						$i++;
				}
				return $nl;
			}
		
			public function checkPageBreakTrigger($h)
			{
				if($this->GetY()+$h>$this->PageBreakTrigger - $this->margin[2]) {
        			$this->newPage();
					return true;
				}
				return false;
			}

			public function addTable($arr)	{
			
				$column = $arr['column'];
				$size = $arr['columnWidth'];

				$startPos = $arr["pos"];
				if (is_null($startPos[0])) $startPos[0] = $this->getPosition("x");
				if (is_null($startPos[1])) $startPos[1] = $this->getPosition("y");

				
				if (isset($arr['header'])) {
					$header = $arr['header'];
					$headerStyle = $arr['headerStyle'];
					
					if (isset($arr['pos'])) {
						$this->makePosition($arr['pos']);
					}
					
					$posY = $this->getPosition("y");
					$posX = $this->getPosition("x");
					$nextPosY = $posY;

					for($i=0;$i<$column;$i++) {
						$this->setPosition("y",$posY);
						$this->setPosition("x",$posX);

						$headerStyle[$i] = isset($headerStyle[$i])?$headerStyle[$i]:array();
						$this->verboseCell(array (
							'width' => $size[$i],
							'height' => isset($headerStyle[$i]['height'])?$headerStyle[$i]['height']:7,
							'value' => $header[$i],
							'border' => isset($headerStyle[$i]['border'])?$headerStyle[$i]['border']:0,
							'align' => isset($headerStyle[$i]['align'])?$headerStyle[$i]['align']:'L',
							'ln' => 0,
						),'multi');

						$posX = $posX + $size[$i];
						$nextPosY = $this->getPosition("y") > $nextPosY ? $this->getPosition("y") : $nextPosY;
					}
					$this->setPosition("y", $nextPosY);
				}

				if (isset($arr['startLine']) && $arr['startLine'] == true) {					
					if (isset($arr['pos'])) {
						$this->makePosition($arr['pos']);
					}
					// Trait de terminaison
					$this->Cell(array_sum($size),0,'','T');
					$this->Ln();	
				}
				
				$dataIsModulo = isset($arr["modulo"]) ? true : false;
				$bordersColor = isset($arr["borders"]) ? $this->SetDrawColor($arr["borders"]) : false;
				if ($dataIsModulo)
					$moduloColors = explode(",", $arr["modulo"]);

				if (isset($arr['content'])) {
					$data = $arr['content'];
					$columnStyle = $arr['columnStyle'];
					$j = 0;
					$this->SetAutoPageBreak(false);
					$this->addingTableRow = true;
					foreach($data as $row) {
						//
						$isModulo = false;
						if ($dataIsModulo) {
							$isModulo = $j % 2 === 1;
							$j++;
							if ($isModulo) {
								$this->SetFillColor($moduloColors[0],$moduloColors[1],$moduloColors[2]);
							}
						}
						//
						if (isset($arr['pos'])) {
							$this->makePosition($arr['pos']);
						}

						$startPosX = $this->getPosition("x");
						$posY = $this->getPosition("y");
						$posX = $this->getPosition("x");
						$nextPosY = $posY;
						$borders = [];
						// Verify with the height if a new page is needed
						$isNewPage = $this->checkIfnewPageIsNeededForTable($row,$arr);
						if ($isNewPage) {
							$posY = $this->margin[0];
							$nextPosY = $this->margin[0];
							$posX = $startPos[0];
						}
						// s($nextPosY);
						for($i=0;$i<$column;$i++) {
							$this->setPosition("y",$posY);
							$this->setPosition("x",$posX);
							$border = [
								"border" => isset($columnStyle[$i]['border'])?$columnStyle[$i]['border']:0,
								"x" => [ $posX ]
							];
							$this->verboseCell(array(
								'width' => $size[$i],
								'height' => isset($columnStyle[$i]['height'])?$columnStyle[$i]['height']:6,
								'value' => $row[$i],
								'border' => 0,
								'fill' => $isModulo,
								'align' => isset($columnStyle[$i]['align'])?$columnStyle[$i]['align']:'L',
								'ln' => 0,
							),'multi');

							$posX = $posX + $size[$i];
							$border["x"][] = $posX;
							$nextPosY = $this->getPosition("y") > $nextPosY ? $this->getPosition("y") : $nextPosY;
							$borders[] = $border;
						}
						$this->newLine(0);

						$this->drawBorders($borders, $posY, $nextPosY);
						$this->setPosition("x",$startPosX);
						$this->setPosition("y",$nextPosY);
					}
					
					if (!isset($arr['closeLine']) || $arr['closeLine'] == true) {					
						if (isset($arr['pos'])) {
							$this->makePosition($arr['pos']);
						}
						// Trait de terminaison
						$this->Cell(array_sum($size),0,'','T');
						$this->Ln();	
					}
					$this->SetAutoPageBreak(true);
					$this->addingTableRow = false;
				}
				
				return $this;
			}

			private function checkIfnewPageIsNeededForTable($data, $arr)
			{
				$size = $arr['columnWidth'];
				$columnStyle = $arr['columnStyle'];
				//Calculate the height of the row
				$nb=0;
				$ln=0;
				for($i=0;$i<count($data);$i++) {
					$nb=max($nb,$this->computeNbLines($size[$i],$data[$i]));
					$ln=max($ln, isset($columnStyle[$i]['height'])?$columnStyle[$i]['height']:6);
				}
				$h=$ln*$nb;
				return $this->checkPageBreakTrigger($h);
			}

			public function checkIfNewPageIsNeeded($data, $width, $height)
			{
				if (!$this->addingTableRow) {
					$nb = $this->computeNbLines($width,$data);
					return $this->checkPageBreakTrigger($nb*$height);
				}

				return false;
			}

			private function drawBorders($borders, $y1, $y2)
			{
				foreach ($borders as $border):
					foreach(str_split($border["border"]) as $letter):
						switch($letter):
							case "T":
								$this->addLine($border["x"][0],$y1,$border["x"][1],$y1);
							break;
							case "L":
								$this->addLine($border["x"][0],$y1,$border["x"][0],$y2);
							break;
							case "B":
								$this->addLine($border["x"][0],$y2,$border["x"][1],$y2);
							break;
							case "R":
								$this->addLine($border["x"][1],$y1,$border["x"][1],$y2);
							break;
						endswitch;
					endforeach;
				endforeach;
			}
			
			public function addImage($file, $a=array()) {
			
				if (!isset($a['pos'])): 
					$a['pos'] = array(null,null);
				else:
					if (!isset($a['pos'][0])) $a['pos'][0] = null;
					if (!isset($a['pos'][1])) $a['pos'][1] = null;
				endif;
				
				$arr = array (
					'pos' => $a['pos'],
					'width' => isset($a['width'])?$a['width']:0,
					'height' => isset($a['height'])?$a['height']:0,
					'type' => isset($a['type'])?$a['type']:'',
					'link' => isset($a['link'])?$a['link']:'',
					'ln' => isset($a['ln'])?$a['ln']:1,
				);
				
				$this->addCell(
					$this->Image($file, $arr['pos'][0], $arr['pos'][1], (isset($a['dpi'])?-$a['dpi']:$arr['width']), $arr['height'], $arr['type'], $arr['link']),
					array (
						'pos' => $a['pos'],
						'width' => isset($a['width'])?$a['width']:false,
						'fill' => isset($a['fill'])?$a['fill']:false,
						'height' => $arr['height'],
						'ln' => $arr['ln'],
					)
				);
				return $this;
			}
			
			public function newLine($value=null) {
				if ($value != null) $this->Ln($value);
				else $this->Ln();
				return $this;
			}
			
			public function addText($height,$value,$link='') {
				$this->Write($height,$this->escapeString($value),$link);
				return $this;
			}
			
			//*********************************************************************
			// End of element's function
			//*********************************************************************
			
			//*********************************************************************
			// Font's function
			//*********************************************************************
			
			public function addFontFace ($name,$type,$fileRef) {
				$this->addFont($name,$type,$fileRef);
				return $this;
			}
		
			public function useFont ($name,$type,$size) {
				$this->SetFont($name,$type,$size);
				return $this;
			}
			
			public function setStyleRule($tag,$font="helvetica",$style="",$size="10",$color="0,0,0") {
				$this->styler->setStyle($tag,$font,$style,$size,$color);
				return $this;
			}
			
			//*********************************************************************
			// End of font's function
			//*********************************************************************
			
			//*********************************************************************
			// Dimension's function
			//*********************************************************************
			
			public function getPdfSize($type= null) {
				$info = array (
					"width" => (float) $this->defaultSize[2][0], 
					"height" => (float) $this->defaultSize[2][1]
				);
				
				if ($type != null) return $info[$type];
				return $info;
				
			}
			
			public function getMargin($value=null) {
				$arr = array('top','right','bottom','left');
				$arr = array_flip($arr);
				if ($value!= null) return $arr[$value];
				return $this->margin;
			}
			
			public function getPosition($type=null) {
				if ($type == "x") return $this->GetX();
				if ($type == "y") return $this->GetY();
			}
			
			public function setPosition($type, $value) {
				if ($type == "x") $this->SetX($value);
				if ($type == "y") $this->SetY($value);
				if ($type == "xy") $this->SetXY($value[0],$value[1]);
				return $this;
			}

			public function resetPosition($type, $value=false)
			{
				$method = sprintf("Set%s", strtoupper($type));
				// s($value ? $value : $this->margin[ $type == "x" ? 3 : 0 ]);
				$this->$method($value ? $value : $this->margin[ $type == "x" ? 3 : 0 ]);
				return $this;
			}
			
			//*********************************************************************
			// End of dimension's function
			//*********************************************************************
			
			//*********************************************************************
			// Helpers
			//*********************************************************************
			
			public function escapeString ($str) {
				$str = preg_replace(':\xe2\x80\x89:', ' ', $str);
				try {
					return iconv("utf-8", "windows-1252//TRANSLIT",$str);
				} catch (\Exception $e) {
					header('Content-Type: text/plain; charset=utf-8');
					$chars = array();
					$invalidChars = array();
					for ($i = 0; $i < strlen($str); $i++) {
						$char = $str[$i];
						$chars[ord($char)] = $char;
						try {
							iconv("utf-8", "windows-1252//TRANSLIT", $char);
						} catch (\Exception $e) {
							$invalidChars[ord($char)] = $char;
						}
					}
					print "$str\n\n";
					foreach (array($chars, $invalidChars) as $set) {
						foreach ($set as $hex => $char) printf("`%s` (%x) ", $char, $hex);
						print "\n\n";
					}
				}
				die;
			}
			
			protected function makePosition($pos = array(null,null)) {
				if ($pos[0] != null) $this->SetX($pos[0]);
				if ($pos[1] != null) $this->SetY($pos[1]);
				if ($pos[0] != null && $pos[1] != null) $this->SetXY($pos[0],$pos[1]);
			}
			
			//*********************************************************************
			// End of Helpers
			//*********************************************************************
			
			//*********************************************************************
			// Display's function
			//*********************************************************************
			
			public function setBorderColor ($color) {
				if (strpos($color,',')==false) {
					$color = array($color,$color,$color);
				} else {
					$color = explode(',',$color);
				}
				$this->SetDrawColor($color[0],$color[1],$color[2]);
				return $this;
			}
			
			public function addLine ($x1=null,$y1=null,$x2=null,$y2=null) {
				if (is_null($x1)) $x1 = $this->margin[1];
				if (is_null($y1)) {
					$this->setPosition("y", $this->getPosition("y") + 3);
					$y1 = $this->getPosition("y");
				}
				if (is_null($x2)) $x2 = $this->getPdfSize("width") - $this->margin[1];
				if (is_null($y2)) {
					$y2 = $this->getPosition("y");
					$this->setPosition("y", $this->getPosition("y") + 3);
				}

				$this->Line($x1,$y1,$x2,$y2);
				return $this;
			}
			
			public function setDash($black=false, $white=false) {
				if($black and $white)
					$s=sprintf('[%.3f %.3f] 0 d', $black*$this->k, $white*$this->k);
				else
					$s='[] 0 d';
				$this->_out($s);
				return $this;
			}
			
			//*********************************************************************
			// End of display's function
			//*********************************************************************
			
			//*********************************************************************
			// Transformation
			//*********************************************************************
			
			public function startTransform (){
				$this->_out('q');
				return $this;
			}

			public function scale ($type,$value,$x='',$y='') {
				if ($type=='x') $this->scaleProcess($value, 100, $x, $y);
				if ($type=='y') $this->scaleProcess(100, $value, $x, $y);
				if ($type=='xy') {
					if (is_array($value)): 
						$this->scaleProcess($value[0], $value[1], $x, $y);
					else:
						$this->scaleProcess($value, $value, $x, $y);
					endif;
				}
				return $this;
			}
			
			public function mirror ($type,$x=''){
				if ($type == 'x') $this->scaleProcess(-100, 100, $x);
				if ($type == 'y') $this->scaleProcess(100, -100, $x);
				return $this;
			}
			
			protected function scaleProcess ($s_x, $s_y, $x='', $y=''){
				if($x === '')
					$x=$this->x;
				if($y === '')
					$y=$this->y;
				if($s_x == 0 || $s_y == 0)
					$this->Error('Please use values unequal to zero for Scaling');
				$y=($this->h-$y)*$this->k;
				$x*=$this->k;
				//calculate elements of transformation matrix
				$s_x/=100;
				$s_y/=100;
				$tm[0]=$s_x;
				$tm[1]=0;
				$tm[2]=0;
				$tm[3]=$s_y;
				$tm[4]=$x*(1-$s_x);
				$tm[5]=$y*(1-$s_y);
				//scale the coordinate system
				$this->transformProcess($tm);
				return $this;
			}

			public function translate ($type,$value) {
				if ($type=='x') $this->translateProcess($value, 0);
				if ($type=='y') $this->translateProcess(0, $value);
				if ($type=='xy') {
					if (is_array($value)): 
						$this->translateProcess($value[0], $value[1]);
					else:
						$this->translateProcess($value, $value);
					endif;
				}
				return $this;
			}
			
			protected function translateProcess ($t_x, $t_y){
				//calculate elements of transformation matrix
				$tm[0]=1;
				$tm[1]=0;
				$tm[2]=0;
				$tm[3]=1;
				$tm[4]=$t_x;
				$tm[5]=-$t_y;
				//translate the coordinate system
				$this->transformProcess($tm);
				return $this;
			}

			public function rotate ($angle, $x='', $y=''){
				if($x === '')
					$x=$this->x;
				if($y === '')
					$y=$this->y;
				$y=($this->h-$y)*$this->k;
				$x*=$this->k;
				//calculate elements of transformation matrix
				$tm[0]=cos(deg2rad($angle));
				$tm[1]=sin(deg2rad($angle));
				$tm[2]=-$tm[1];
				$tm[3]=$tm[0];
				$tm[4]=$x+$tm[1]*$y-$tm[0]*$x;
				$tm[5]=$y-$tm[0]*$y-$tm[1]*$x;
				//rotate the coordinate system around ($x, $y)
				$this->transformProcess($tm);
				return $this;
			}

			public function skew ($type,$angle,$x="",$y="") {
				if ($type=='x') $this->skewProcess($angle, 0, $x, $y);
				if ($type=='y') $this->skewProcess(0, $angle, $x, $y);
				if ($type=='xy') {
					if (is_array($angle)): 
						$this->skewProcess($angle[0], $angle[1], $x, $y);
					else:
						$this->skewProcess($angle, $angle, $x, $y);
					endif;
				}
				return $this;
			}
			
			protected function skewProcess ($angle_x, $angle_y, $x='', $y=''){
				if($x === '')
					$x=$this->x;
				if($y === '')
					$y=$this->y;
				if($angle_x <= -90 || $angle_x >= 90 || $angle_y <= -90 || $angle_y >= 90)
					$this->Error('Please use values between -90? and 90? for skewing');
				$x*=$this->k;
				$y=($this->h-$y)*$this->k;
				//calculate elements of transformation matrix
				$tm[0]=1;
				$tm[1]=tan(deg2rad($angle_y));
				$tm[2]=tan(deg2rad($angle_x));
				$tm[3]=1;
				$tm[4]=-$tm[2]*$y;
				$tm[5]=-$tm[1]*$x;
				//skew the coordinate system
				$this->transformProcess($tm);
				return $this;
			}

			protected function transformProcess ($tm){
				$this->_out(sprintf('%.3f %.3f %.3f %.3f %.3f %.3f cm', $tm[0], $tm[1], $tm[2], $tm[3], $tm[4], $tm[5]));
			}

			public function stopTransform (){
				//restore previous graphic state
				$this->_out('Q');
				return $this;
			}

			function textWithRotation($x, $y, $txt, $txt_angle, $font_angle=0) {
				$font_angle+=90+$txt_angle;
				$txt_angle*=M_PI/180;
				$font_angle*=M_PI/180;

				$txt_dx=cos($txt_angle);
				$txt_dy=sin($txt_angle);
				$font_dx=cos($font_angle);
				$font_dy=sin($font_angle);

				$s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',$txt_dx,$txt_dy,$font_dx,$font_dy,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
				if ($this->ColorFlag)
				$s='q '.$this->TextColor.' '.$s.' Q';
				$this->_out($s);
				return $this;
			}
			
			//*********************************************************************
			// End of transfroamtion
			//*********************************************************************
			
			public function generate ($name="",$dest="") {
				return $this->Output($name,$dest);
			}

			/*
			 * Save the document to a path
			 *
			 * @param string $path
			 */
			public function saveTo($path) {
				return $this->generate($path, 'F');
			}
			
			//*********************************************************************
			// alpha function
			//*********************************************************************
			
			public function setAlpha($alpha, $bm='Normal') {
				// set alpha for stroking (CA) and non-stroking (ca) operations
				$gs = $this->AddExtGState(array('ca'=>$alpha, 'CA'=>$alpha, 'BM'=>'/'.$bm));
				$this->SetExtGState($gs);
				return $this;
			}

			public function AddExtGState($parms) {
				$n = count($this->extgstates)+1;
				$this->extgstates[$n]['parms'] = $parms;
				return $n;
			}

			public function SetExtGState($gs) {
				$this->_out(sprintf('/GS%d gs', $gs));
			}

			public function _enddoc() {
				if(!empty($this->extgstates) && $this->PDFVersion<'1.4')
					$this->PDFVersion='1.4';
				parent::_enddoc();
			}

			public function _putextgstates() {
				for ($i = 1; $i <= count($this->extgstates); $i++)
				{
					$this->_newobj();
					$this->extgstates[$i]['n'] = $this->n;
					$this->_out('<</Type /ExtGState');
					foreach ($this->extgstates[$i]['parms'] as $k=>$v)
						$this->_out('/'.$k.' '.$v);
					$this->_out('>>');
					$this->_out('endobj');
				}
			}

			public function _putresourcedict() {
				parent::_putresourcedict();
				$this->_out('/ExtGState <<');
				foreach($this->extgstates as $k=>$extgstate)
					$this->_out('/GS'.$k.' '.$extgstate['n'].' 0 R');
				$this->_out('>>');
			}

			public function _putresources() {
				$this->_putextgstates();
				parent::_putresources();
			}
			

			public function __call($method, $arguments) {
				if (! preg_match('/^(fget|fset|fis)([A-Z][a-z0-9]*)$/i', $method, $matches)) {
					throw new \BadMethodCallException(sprintf('Method %s does not exist', $method));
				}

				$action = $matches[1];
				$property = $matches[2];

				if (!array_key_exists($property, get_object_vars($this))) {
					throw new \BadMethodCallException(sprintf('Property %s does not exist', $property));
				}

				switch ($action) {
					case 'fget':
						return $this->$property;
					case 'fis':
						if (!preg_match('/^is[A-Z0-9]/i', $property)) {
							throw new \BadMethodCallException('Property used with this getter must start with is*');
						}
						return (bool) $this->$property;
					case 'fset':
						$value = $arguments[0];
						$this->$property = $value;
						return $this;
				}
			}
			
	}