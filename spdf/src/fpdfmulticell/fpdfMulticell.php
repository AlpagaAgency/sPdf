<?php
/**
* FPDF Advanced Multicell - FPDF class addon
* Copyright (c) 2005-2012, http://www.interpid.eu
*
* FPDF Advanced Multicell is licensed under the terms of the GNU Open Source GPL 3.0
* license.
*
* Commercial use is prohibited. Visit <http://www.interpid.eu/fpdf-addons>
* if you need to obtain a commercial license.
*
* This program is free software: you can redistribute it and/or modify it under
* the terms of the GNU General Public License as published by the Free Software
* Foundation, either version 3 of the License, or any later version.
*
* This program is distributed in the hope that it will be useful, but WITHOUT
* ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
* details.
*
* You should have received a copy of the GNU General Public License along with
* this program. If not, see <http://www.gnu.org/licenses/gpl.html>.
*
* 
* Version:             2.0.2
* Date:                2005/12/08
* Last Modification:   2012/03/12
* Author:              Andrei Bintintan <andy@interpid.eu>
*/

require_once(dirname(__FILE__).'/class.stringtags.php');

if (!defined('PARAGRAPH_STRING')) define('PARAGRAPH_STRING', '~~~');

/**
 * multiCell Class, Fpdf extension
 * 
 * 
 * @author     andy@interpid.eu
 * @version 2.0
 * 
 */
class fpdfMulticell{

const DEBUG_CELL_BORDERS    = 0;

/**
 * Valid Tag Maximum Width
 *
 * @access     protected
 * @var        integer
 */
protected $nTagWidthMax = 25;
    
/**
 * The current active tag
 *
 * @access    protected
 * @var        string
 */
protected $sCurrentTag = '';

/**
 * Tags Font Information 
 *
 * @access     protected
 * @var        struct
 */
protected $oFontInfo;

/**
 * Parsed string data info
 *
 * @access     protected
 * @var        struct
 */
protected $aDataInfo;

/**
 * Data Extra Info 
 *
 * @access     protected
 * @var        struct
 */
protected $aDataExtraInfo;

/**
 * Temporary Info
 *
 * @access     protected
 * @var        struct
 */
protected $aTempData;


/**
 * == true if a tag was more times defined.
 *
 * @access     protected
 * @var        boolean
 */
protected $bDoubleTags = false;

/**
* Pointer to the fpdf object
* 
* @access   protected
* @var      object
*/
protected $oFpdf = null;

/**
 * Contains the Singleton Object
 *
 * @var object
 */
private static $_singleton = array();    //implements the Singleton Pattern 

    /**
    * Class constructor. 
    * 
    * @access   public 
    * @param    object $fpdf Instance of the fpdf class
    * @return   fpdfMulticell
    */
    public function __construct($fpdf){
        $this->oFpdf = $fpdf;
    }

    /**
     * Returnes the Singleton Instance of this class.
     *
     * @static 
     * @author  <andy@interpid.eu>
     * @access  public
     * @param   none
     * @return  fpdfMulticell
     */
    static function getInstance($fpdf){
        
        $oInstance = & self::$_singleton[spl_object_hash($fpdf)];
        
        if (!isset($oInstance)) {
            $oInstance = new self($fpdf);
        }

        return $oInstance;
    }




    /**
    * Sets the Tags Maximum width
    * 
    * @access   public
    * @param    numeric $iWidth - the width of the tags
    * @return   void
    */
    public function setTagWidthMax($iWidth = 25){
        $this->nTagWidthMax = $iWidth;
    }


    /**
    * Resets the current class internal variables to default values
    * 
    * @access   protected
    * @param    none
    */
    protected function resetData(){
        $this->sCurrentTag = "";
        $this->aDataInfo = array();
        $this->aDataExtraInfo = array(
            "LAST_LINE_BR" => "",        //CURRENT LINE BREAK TYPE
            "CURRENT_LINE_BR" => "",    //LAST LINE BREAK TYPE
            "TAB_WIDTH" => 10            //The tab WIDTH IS IN mm
        );

        //if another measure unit is used ... calculate your OWN
        $this->aDataExtraInfo["TAB_WIDTH"] *= (72/25.4) / $this->oFpdf->k;
        /*
            $this->oFontInfo - do not reset, once read ... is OK!!!
        */
    }//function resetData(){
    
    /**
    * Sets the current settings for the specified tag name. 
    * The names "ttags" and "pparg" are reserved for tab spaces. 
    * Don't use empty tag names.
    *
    * @access   public
    * @param    string $tag - tag name
    * @param    string $family - text font family name
    * @param    string $style - text font style
    * @param    numeric $size - text font size
    * @param    string $color - text color
    * 
    */
    public function setStyle($tag, $family, $style, $size, $color)
    {
        /**
        * @example
        * $oMulticell->setStyle("p", "times", "", 11, "130,0,30");        
        */

        if ($tag == "ttags") $this->Error (">> ttags << is reserved TAG Name.");
        if ($tag == "") $this->Error ("Empty TAG Name.");

        //use case insensitive tags
        $tag=trim(strtoupper($tag));
        
        if (isset($this->TagStyle[$tag])) $this->bDoubleTags = true;
        
        $this->TagStyle[$tag]['family']=trim($family);
        $this->TagStyle[$tag]['style']=trim($style);
        $this->TagStyle[$tag]['size']=trim($size);
        $this->TagStyle[$tag]['color']=trim($color);
    }//function setStyle

    
    /**
    * Sets current tag to specified style
    *         - if the tag name is not in the tag list then de "DEFAULT" tag is saved.
    *         This includes a fist call of the function saveCurrentStyle()
    *
    * @access  protected
    * @param   string $tag - tag name
    */
    protected function applyStyle($tag){

        //use case insensitive tags
        $tag=trim(strtoupper($tag));

        if ($this->sCurrentTag == $tag) return;

        if (($tag == "") || (! isset($this->TagStyle[$tag]))) $tag = "DEFAULT";

        $this->sCurrentTag = $tag;

        $style = & $this->TagStyle[$tag];

        if (isset($style)){
            $this->oFpdf->SetFont($style['family'], $style['style'], $style['size']);
            //this is textcolor in FPDF format
            if (isset($style['textcolor_fpdf'])) {
                $this->oFpdf->TextColor = $style['textcolor_fpdf'];
                $this->oFpdf->ColorFlag=($this->oFpdf->FillColor != $this->oFpdf->TextColor);
            }else{
                if ($style['color'] <> ""){//if we have a specified color
                    $temp = explode(",", $style['color']);
                    $this->oFpdf->SetTextColor($temp[0], $temp[1], $temp[2]);
                }//fi
            }
            /**/
        }//isset
    }//function applyStyle($tag){

    /**
    * Save the current settings as a tag default style under the DEFAUTLT tag name
    * 
    * @access   protected
    */
    protected function saveCurrentStyle(){
        $this->TagStyle['DEFAULT']['family'] = $this->oFpdf->FontFamily;;
        $this->TagStyle['DEFAULT']['style'] = $this->oFpdf->FontStyle;
        $this->TagStyle['DEFAULT']['size'] = $this->oFpdf->FontSizePt;
        $this->TagStyle['DEFAULT']['textcolor_fpdf'] = $this->oFpdf->TextColor;
        $this->TagStyle['DEFAULT']['color'] = "";
    }//function saveCurrentStyle

    
    /**
    * Divides $this->aDataInfo and returnes a line from this variable
    *
    * @access   protected
    * @param    numeric $w - the width of the text
    * @return   array $aLine - array() -> contains informations to draw a line
    */
    protected function makeLine($w){

        $aDataInfo = & $this->aDataInfo;
        $aExtraInfo = & $this->aDataExtraInfo;

        //last line break >> current line break
        $aExtraInfo['LAST_LINE_BR'] = $aExtraInfo['CURRENT_LINE_BR'];
        $aExtraInfo['CURRENT_LINE_BR'] = "";

        if($w==0)
            $w=$this->oFpdf->w - $this->oFpdf->rMargin - $this->oFpdf->x;

        $wmax = ($w - 2 * $this->oFpdf->cMargin) * 1000;//max width

        $aLine = array();//this will contain the result
        $return_result = false;//if break and return result
        $reset_spaces = false;

        $line_width = 0;//line string width
        $total_chars = 0;//total characters included in the result string
        $space_count = 0;//numer of spaces in the result string
        $fw = & $this->oFpdf->oFontInfo;//font info array

        $last_sepch = ""; //last separator character
        
        foreach ($aDataInfo as $key => $val){
            
            $s = $val['text'];

            $tag = &$val['tag'];

            $bParagraph = false;            
            if (($s == "\t") && ($tag == 'pparg')){
                $bParagraph = true;
                $s = "\t";//place instead a TAB
            }

             $s_lenght=strlen($s);

            $i = 0;//from where is the string remain
            $j = 0;//untill where is the string good to copy -- leave this == 1->> copy at least one character!!!
            $str = "";
            $s_width = 0;    //string width
            $last_sep = -1; //last separator position
            $last_sepwidth = 0;
            $last_sepch_width = 0;
            $ante_last_sep = -1; //ante last separator position
            $spaces = 0;
            
            //parse the whole string
            while ($i < $s_lenght){
                $c = $s[$i];

                   if($c == "\n"){//Explicit line break
                       $i++; //ignore/skip this caracter
                    $aExtraInfo['CURRENT_LINE_BR'] = "BREAK";
                    $return_result = true;
                    $reset_spaces = true;
                    break;
                }

                //space
                   if($c == " "){
                    $space_count++;//increase the number of spaces
                    $spaces ++;
                }

                //    Font Width / Size Array
                if (!isset($fw[$tag]) || ($tag == "") || ($this->bDoubleTags)){
                    //if this font was not used untill now,
                    $this->applyStyle($tag);
                    $fw[$tag]['w'] = $this->oFpdf->CurrentFont['cw'];//width
                    $fw[$tag]['s'] = $this->oFpdf->FontSize;//size
                }

                $char_width = $fw[$tag]['w'][$c] * $fw[$tag]['s'];

                //separators
                if(is_int(strpos(" ,.:;",$c))){

                    $ante_last_sep = $last_sep;
                    $ante_last_sepch = $last_sepch;
                    $ante_last_sepwidth = $last_sepwidth;
                    $ante_last_sepch_width = $last_sepch_width;

                    $last_sep = $i;//last separator position
                    $last_sepch = $c;//last separator char
                    $last_sepch_width = $char_width;//last separator char
                    $last_sepwidth = $s_width;

                }

                if ($c == "\t"){//TAB
                    $c = $s[$i] = "";
                    $char_width = $aExtraInfo['TAB_WIDTH'] * 1000;
                }

                if ($bParagraph == true){
                    $c = $s[$i] = "";
                    $char_width = $this->aTempData['LAST_TAB_REQSIZE']*1000 - $this->aTempData['LAST_TAB_SIZE'];
                    if ($char_width < 0) $char_width = 0;                
                }
                
                

                $line_width += $char_width;

                if($line_width > $wmax){//Automatic line break

                    $aExtraInfo['CURRENT_LINE_BR'] = "AUTO";

                    if ($total_chars == 0) {
                        /* This MEANS that the $w (width) is lower than a char width...
                            Put $i and $j to 1 ... otherwise infinite while*/
                        $i = 1;
                        $j = 1;
                        $return_result = true;//YES RETURN THE RESULT!!!
                        break;
                    }//fi

                    if ($last_sep <> -1){
                        //we have a separator in this tag!!!
                        //untill now there one separator
                        if (($last_sepch == $c) && ($last_sepch != " ") && ($ante_last_sep <> -1)){
                            /*    this is the last character and it is a separator, if it is a space the leave it...
                                Have to jump back to the last separator... even a space
                            */
                            $last_sep = $ante_last_sep;
                            $last_sepch = $ante_last_sepch;
                            $last_sepwidth = $ante_last_sepwidth;
                        }

                        if ($last_sepch == " "){
                            $j = $last_sep;//just ignore the last space (it is at end of line)
                            $i = $last_sep + 1;
                            if ( $spaces > 0 ) $spaces --;
                            $s_width = $last_sepwidth;
                        }else{
                            $j = $last_sep + 1;
                            $i = $last_sep + 1;
                            $s_width = $last_sepwidth + $last_sepch_width;
                        }

                    }elseif(count($aLine) > 0){
                        //we have elements in the last tag!!!!
                        if ($last_sepch == " "){//the last tag ends with a space, have to remove it

                            $temp = & $aLine[ count($aLine)-1 ];

                            if ($temp['text'][strlen($temp['text'])-1] == " "){

                                $temp['text'] = substr($temp['text'], 0, strlen($temp['text']) - 1);
                                $temp['width'] -= $fw[ $temp['tag'] ]['w'][" "] * $fw[ $temp['tag'] ]['s'];
                                $temp['spaces'] --;

                                //imediat return from this function
                                break 2;
                            }else{
                                #die("should not be!!!");
                            }//fi
                        }//fi
                    }//fi else

                    $return_result = true;
                    break;
                }//fi - Auto line break

                //increase the string width ONLY when it is added!!!!
                $s_width += $char_width;

                $i++;
                $j = $i;
                $total_chars ++;
            }//while

            $str = substr($s, 0, $j);

            $sTmpStr = & $aDataInfo[$key]['text'];
            $sTmpStr = substr($sTmpStr, $i, strlen($sTmpStr));

            if (($sTmpStr == "") || ($sTmpStr === FALSE))//empty
                array_shift($aDataInfo);

            if ($val['text'] == $str){
            }
            
            if (!isset($val['href'])) $val['href']='';
            if (!isset($val['ypos'])) $val['ypos']=0;

            //we have a partial result
            array_push($aLine, array(
                'text' => $str,
                'tag' => $val['tag'],
                'href' => $val['href'],
                'width' => $s_width,
                'spaces' => $spaces,
                'ypos' => $val['ypos']
            ));
            
            $this->aTempData['LAST_TAB_SIZE'] = $s_width;
            $this->aTempData['LAST_TAB_REQSIZE'] = (isset($val['size'])) ? $val['size'] : 0;           
            
            if ($return_result) break;//break this for

        }//foreach

        // Check the first and last tag -> if first and last caracters are " " space remove them!!!"

        if ((count($aLine) > 0) && ($aExtraInfo['LAST_LINE_BR'] == "AUTO")){
            //first tag
            $temp = & $aLine[0];
            if ( (strlen($temp['text']) > 0) && ($temp['text'][0] == " ")){
                $temp['text'] = substr($temp['text'], 1, strlen($temp['text']));
                $temp['width'] -= $fw[ $temp['tag'] ]['w'][" "] * $fw[ $temp['tag'] ]['s'];
                $temp['spaces'] --;
            }

            //last tag
            $temp = & $aLine[count($aLine) - 1];
            if ( (strlen($temp['text'])>0) && ($temp['text'][strlen($temp['text'])-1] == " ")){
                $temp['text'] = substr($temp['text'], 0, strlen($temp['text']) - 1);
                $temp['width'] -= $fw[ $temp['tag'] ]['w'][" "] * $fw[ $temp['tag'] ]['s'];
                $temp['spaces'] --;
            }
        }

        if ($reset_spaces){//this is used in case of a "Explicit Line Break"
            //put all spaces to 0 so in case of "J" align there is no space extension
            for ($k=0; $k< count($aLine); $k++) $aLine[$k]['spaces'] = 0;
        }//fi

        return $aLine;
    }//function makeLine


    /**
    * Creates and "Advanced Multicell" which uses as input a TAG based formatted string instead of a simple string. 
    * The use of tags allows to change the font, the style (bold, italic, underline), the size 
    * and the color of characters and many other features.
    *
    * @access  public
    * @param   numeric $w with of the cell
    * @param   numeric $h height of the lines in the cell
    * @param   string $sData TAG based formatted string or formatted data
    * @param   mixed $border Indicates if borders must be drawn around the cell block. 
    *                 The value can be either a number: 
    *                         0 = no border
    *                         1 = frame border
    *                 or a string containing some or all of the following characters (in any order): 
    *                         L: left
    *                         T: top
    *                         R: right
    *                         B: bottom
    * @param   string $align Sets the text alignment
    *                 Possible values:
    *                         L: left
    *                         R: right
    *                         C: center
    *                         J: justified
    * @param   numeric $fill Indicates if the cell background must be painted (1) or transparent (0). 
    * @param   numeric $pad_left Left padding
    * @param   numeric $pad_top Top padding
    * @param   numeric $pad_right Right padding
    * @param   numeric $pad_bottom Bottom padding
    * @param   boolean $pDataIsString
    *              - true if $sData is a string
    *              - false if $sData is an array containing lines formatted with $this->makeLine($w) function
    *                  (the false option is used in relation with stringToLines, to avoid double formatting of a string
    */
    public function multiCell($w, $h, $sData, $border=0, $align='J', $fill=0, $pad_left=0, $pad_top=0, $pad_right=0, $pad_bottom=0, $pDataIsString = true){    
        
        $nStartX = $this->oFpdf->GetX();

        $aRecData = $this->stringToLines($w, $sData);
        $iCounter = 9999; /*avoid infinite loop for any reasons*/
        
        $doBreak = false;
        
        do{ 
            $iLeftHeight = $this->oFpdf->h - $this->oFpdf->bMargin - $this->oFpdf->GetY() - $pad_top - $pad_bottom;
            $bAddNewPage = false;
            
            //Numer of rows that have space on this page:
            $iRows = floor($iLeftHeight / $h);
            if ( count($aRecData) > $iRows){
                $aSendData = array_slice($aRecData, 0, $iRows);
                $aRecData = array_slice($aRecData, $iRows);
                $bAddNewPage = true;
            }else{
                $aSendData = &$aRecData;
                $doBreak = true;    
            }
            
            $this->multiCellSec($w, $h, $aSendData, $border, $align, $fill, $pad_left, $pad_top, $pad_right, $pad_bottom, false);
            
            if (true == $bAddNewPage){
                $this->beforeAddPage();
                $this->oFpdf->AddPage();
                $this->afterAddPage();
                        $this->oFpdf->SetX($nStartX);
            }
        
        }while ((($iCounter--) > 0) && ( false == $doBreak) );
    }//public function multiCell
    
    
    /**
    * Creates and "Advanced Multicell" which uses as input a TAG based formatted string instead of a simple string. 
    * The use of tags allows to change the font, the style (bold, italic, underline), the size 
    * and the color of characters and many other features.
    *
    * @access  public
    * @param   numeric $w with of the cell
    * @param   numeric $h height of the lines in the cell
    * @param   string $sData TAG based formatted string or formatted data
    * @param   mixed $border Indicates if borders must be drawn around the cell block. 
    *                 The value can be either a number: 
    *                         0 = no border
    *                         1 = frame border
    *                 or a string containing some or all of the following characters (in any order): 
    *                         L: left
    *                         T: top
    *                         R: right
    *                         B: bottom
    * @param   string $align Sets the text alignment
    *                 Possible values:
    *                         L: left
    *                         R: right
    *                         C: center
    *                         J: justified
    * @param   numeric $fill Indicates if the cell background must be painted (1) or transparent (0). 
    * @param   numeric $pad_left Left padding
    * @param   numeric $pad_top Top padding
    * @param   numeric $pad_right Right padding
    * @param   numeric $pad_bottom Bottom padding
    * @param   boolean $pDataIsString
    *              - true if $sData is a string
    *              - false if $sData is an array containing lines formatted with $this->makeLine($w) function
    *                  (the false option is used in relation with stringToLines, to avoid double formatting of a string
    */ 
    public function multiCellSec($w, $h, $sData, $border=0, $align='J', $fill=0, $pad_left=0, $pad_top=0, $pad_right=0, $pad_bottom=0, $pDataIsString = true){

        //save the current style settings, this will be the default in case of no style is specified
        $this->saveCurrentStyle();
        $this->resetData();
        
        //if data is string
        if ($pDataIsString === true) $this->divideByTags($sData);

        $b = $b1 = $b2 = $b3 = '';//borders
        
        if($w==0)
            $w = $this->oFpdf->w - $this->oFpdf->rMargin - $this->oFpdf->x;        
        
        /**
         * If the vertical padding is bigger than the width then we ignore it
         * In this case we put them to 0.
         */
        if ( ($pad_left + $pad_right) > $w) {
            $pad_left = 0;
            $pad_right = 0;
        }
        
        $w_text = $w - $pad_left - $pad_right;

        //save the current X position, we will have to jump back!!!!
        $startX = $this->oFpdf->GetX();

        if($border)
        {
            if($border==1)
            {
                $border = 'LTRB';
                $b1 = 'LRT';//without the bottom
                $b2 = 'LR';//without the top and bottom
                $b3 = 'LRB';//without the top
            }
            else
            {
                $b2='';
                if(is_int(strpos($border,'L')))
                    $b2.='L';
                if(is_int(strpos($border,'R')))
                    $b2.='R';
                $b1=is_int(strpos($border,'T')) ? $b2 . 'T' : $b2;
                $b3=is_int(strpos($border,'B')) ? $b2 . 'B' : $b2;
            }

            //used if there is only one line
            $b = '';
            $b .= is_int(strpos($border,'L')) ? 'L' : "";
            $b .= is_int(strpos($border,'R')) ? 'R' : "";
            $b .= is_int(strpos($border,'T')) ? 'T' : "";
            $b .= is_int(strpos($border,'B')) ? 'B' : "";
        }

        $first_line = true;
        $last_line = false;
        
        if ($pDataIsString === true){
            $last_line = !(count($this->aDataInfo) > 0);
        }else {
            $last_line = !(count($sData) > 0);
        }
                                                                      
        while(!$last_line){
            
            if ($first_line && ($pad_top > 0)){
                /**
                 * If this is the first line and there is top_padding
                 */
                $this->oFpdf->MultiCell($w, $pad_top, '', $b1, $align, 1);
                $b1 = str_replace('T', '', $b1);
                $b = str_replace('T', '', $b);
            }
            
            if ($fill == 1){
                //fill in the cell at this point and write after the text without filling
                $this->oFpdf->SetX($startX);//restore the X position
                $this->oFpdf->Cell($w,$h,"",0,0,"",1);
                $this->oFpdf->SetX($startX);//restore the X position
            }

            if ($pDataIsString === true){
                //make a line
                $str_data = $this->makeLine($w_text);
                //check for last line
                $last_line = !(count($this->aDataInfo) > 0);
            }else {
                //make a line
                $str_data = array_shift($sData);
                //check for last line
                $last_line = !(count($sData) > 0);
            }

            if ($last_line && ($align == "J")){//do not Justify the Last Line
                $align = "L";
            }

            /**
             * Restore the X position with the corresponding padding if it exist
             * The Right padding is done automatically by calculating the width of the text
             */
            $this->oFpdf->SetX( $startX + $pad_left );
            $this->printLine($w_text, $h, $str_data, $align);
            
            //check if there is engough space on the current page
            $currentY = $this->oFpdf->getY();
            $restHeight = (int) $this->oFpdf->h - $this->oFpdf->tMargin - $this->oFpdf->bMargin;
            
            //see what border we draw:
            if($first_line && $last_line){
                //we have only 1 line
                $real_brd = $b;
            }elseif($first_line){
                $real_brd = $b1;
            }elseif($last_line){
                $real_brd = $b3;
            }else{
                $real_brd = $b2;
            }
            
            if ($last_line && ($pad_bottom > 0)){
                /**
                 * If we have bottom padding then the border and the padding is outputted
                 */
                $this->oFpdf->SetX($startX);//restore the X
                $this->oFpdf->Cell($w,$h,"",$b2,2);
                $this->oFpdf->SetX($startX);//restore the X
                $this->oFpdf->MultiCell($w, $pad_bottom, '', $real_brd, $align, 1);
            }else{
                //draw the border and jump to the next line
                $this->oFpdf->SetX($startX);//restore the X
                $this->oFpdf->Cell($w,$h,"",$real_brd,2);
            }
            

            if ($first_line) $first_line = false;
        }//while(! $last_line){

        //APPLY THE DEFAULT STYLE
        $this->applyStyle("DEFAULT");

        $this->x=$this->oFpdf->lMargin;
    }//function MultiCellExt


    /**
    * This method divides the string into the tags and puts the result into aDataInfo variable.
    *
    * @access     protected
    * @param      string $pStr - string to be parsed
    * @param      boolean $return - ==TRUE if the result is returned or not
    * @return     struct or void
    */
    protected function divideByTags($pStr, $return = false){

        $pStr = str_replace("\t", "<ttags>\t</ttags>", $pStr);
        $pStr = str_replace(PARAGRAPH_STRING, "<pparg>\t</pparg>", $pStr);
        $pStr = str_replace("\r", "", $pStr);

        //initialize the StringTags class
        $sWork = new StringTags($this->nTagWidthMax);

        //get the string divisions by tags
        $this->aDataInfo = $sWork->get_tags($pStr);
               
        if ($return) return $this->aDataInfo;
    }//function divideByTags($pStr){
    
    /**
    * This method parses the current text and return an array that contains the text information for
    * each line that will be drawed.
    *
    * @access     protected
    * @param      numeric $w - width of the line
    * @param      string $pStr - String to be parsed
    * @return     array $aStrLines - contains parsed text information.
    */
    public function stringToLines($w = 0, $pStr){

        //save the current style settings, this will be the default in case of no style is specified
        $this->saveCurrentStyle();
        $this->resetData();
        
        $this->divideByTags($pStr);
             
        $last_line = !(count($this->aDataInfo) > 0);
        
        $aStrLines = array();

        while (!$last_line){

            //make a line
            $str_data = $this->makeLine($w);
            array_push($aStrLines, $str_data);

            //check for last line
            $last_line = !(count($this->aDataInfo) > 0);
        }//while(! $last_line){

        //APPLY THE DEFAULT STYLE
        $this->applyStyle("DEFAULT");

        return $aStrLines;
    }//function stringToLines    

    
    /**
    * Draws a Tag Based formatted line returned from makeLine function into the pdf document
    *
    * @access     protected
    * @param      numeric $w - width of the text
    * @param      numeric $h - height of a line
    * @param      string $aTxt - text to be draw
    * @param      string $align - align of the text
    */
    protected function printLine($w, $h, $aTxt, $align='J'){

        if($w==0)
            $w = $this->oFpdf->w - $this->oFpdf->rMargin - $this->oFpdf->x;

        $wmax = $w; //Maximum width

        $total_width = 0;    //the total width of all strings
        $total_spaces = 0;    //the total number of spaces

        $nr = count($aTxt);//number of elements

        for ($i=0; $i<$nr; $i++){
            $total_width += ($aTxt[$i]['width']/1000);
            $total_spaces += $aTxt[$i]['spaces'];
        }

        //default
        $w_first = $this->oFpdf->cMargin;

        switch($align){
            case 'J':
                if ($total_spaces > 0)
                    $extra_space = ($wmax - 2 * $this->oFpdf->cMargin - $total_width) / $total_spaces;
                else $extra_space = 0;
                break;
            case 'L':
                break;
            case 'C':
                $w_first = ($wmax - $total_width) / 2;
                break;
            case 'R':
                $w_first = $wmax - $total_width - $this->oFpdf->cMargin;;
                break;
        }

        // Output the first Cell
        if ($w_first != 0){
            $this->oFpdf->Cell($w_first, $h, "", self::DEBUG_CELL_BORDERS, 0, "L", 0);
        }

        $last_width = $wmax - $w_first;

        while (list($key, $val) = each($aTxt)) {
            
            $bYPosUsed = false;
                       
            //apply current tag style
            $this->applyStyle($val['tag']);

            //If > 0 then we will move the current X Position
            $extra_X = 0;
            
            if ($val['ypos'] != 0){
                $lastY = $this->oFpdf->y;
                $this->oFpdf->y = $lastY - $val['ypos'];
                $bYPosUsed = true;
            }

            //string width
            $width = $val['width'] / 1000;

            if ($width == 0) continue;// No width jump over!!!

            if($align=='J'){
                if ($val['spaces'] < 1) $temp_X = 0;
                else $temp_X = $extra_space;

                $this->ws = $temp_X;

                $this->oFpdf->_out(sprintf('%.3f Tw', $temp_X * $this->oFpdf->k));

                $extra_X = $extra_space * $val['spaces'];//increase the extra_X Space

            }else{
                $this->ws = 0;
                $this->oFpdf->_out('0 Tw');
            }//fi

            //Output the Text/Links
            $this->oFpdf->Cell($width, $h, $val['text'], self::DEBUG_CELL_BORDERS, 0, "C", 0, $val['href']);

            $last_width -= $width;//last column width

            if ($extra_X != 0){
                $this->oFpdf->SetX($this->oFpdf->GetX() + $extra_X);
                $last_width -= $extra_X;
            }//fi
            
            if ($bYPosUsed) $this->oFpdf->y = $lastY;
            
        }//while

        // Output the Last Cell
        if ($last_width != 0){
            $this->oFpdf->Cell($last_width, $h, "", 0, 0, "", 0);
        }//fi
    }//function printLine
    
    
    /**
    * Function executed BEFORE a new page is added for further actions on the current page.
    * Usually overwritted. 
    *
    * @access     public
    */
    public function beforeAddPage(){
        /*
            TODO:
            place your code here */
    }// function beforeAddPage

    /**
    * Function executed AFTER a new page is added for pre - actions on the current page.
    * Usually overwritted. 
    *
    * @access     public
    */
    public function afterAddPage(){
        /*
            TODO:
            place your code here */
    }// function afterAddPage

    
}//class

?>
