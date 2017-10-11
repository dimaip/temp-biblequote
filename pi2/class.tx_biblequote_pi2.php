<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Dmitri Pisarev <dimaip@gmail.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Plugin 'bible' for the 'biblequote' extension.
 *
 * @author	Dmitri Pisarev <dimaip@gmail.com>
 */

// Convert Roman numerals into Arabic numerals.
function arabic($roman)
{
$result = 0;
// Remove subtractive notation.
$roman = str_replace("CM", "DCCCC", $roman);
$roman = str_replace("CD", "CCCC", $roman);
$roman = str_replace("XC", "LXXXX", $roman);
$roman = str_replace("XL", "XXXX", $roman);
$roman = str_replace("IX", "VIIII", $roman);
$roman = str_replace("IV", "IIII", $roman);
// Calculate for each numeral.
$result += substr_count($roman, 'M') * 1000;
$result += substr_count($roman, 'D') * 500;
$result += substr_count($roman, 'C') * 100;
$result += substr_count($roman, 'L') * 50;
$result += substr_count($roman, 'X') * 10;
$result += substr_count($roman, 'V') * 5;
$result += substr_count($roman, 'I');
return $result;
}

function do_reg($text, $regex)
{
	preg_match_all($regex, $text, $result);
	return($result['0']);
}

require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_biblequote_pi2 extends tslib_pibase {
	var $prefixId = 'tx_biblequote_pi2';		// Same as class name
	var $scriptRelPath = 'pi2/class.tx_biblequote_pi2.php';	// Path to this script relative to the extension dir.
	var $extKey = 'biblequote';	// The extension key.

	
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;
		
		$ver = iconv('cp1251','utf-8',$_GET['reading']);// win2utf(urldecode($_GET['reading']));//Кол., 249 зач. (от полу'), I, 3-6.  
		$orig_ver = $ver;
		$ver = preg_replace('/,.*зач.*?,/','',$ver); //Евр. V, 11 - VI, 8.  Remove zach 
		$ver = preg_replace('/(\.$)/','',$ver); //Евр. V, 11 - VI, 8 remove last dot
		//$ver = preg_replace('#(\d{1,3}),(\s\w{1,4})#u','$1;$2',$ver); //VII, 37-52, VIII,12 TEMPORARY DISABLED
		$ver = preg_replace('#(\d{1,3}),(\s\d{1,3})#u','$1;$2',$ver); //VII, 37-52, 12-15
		$ver = preg_replace('#(\d{1,3}-\d{1,3})\s-(\s\w{1,4})#u','$1;$2',$ver); //VII, 37-52 - VIII,12
		$verse = explode('.',$ver); //Евр | V, 11 - VI, 8 split book from verse
		$v_parts = explode(';',$verse['1']); //V, 11 - VI, 8 split verse on parts(if multipart verse)
		$i=0;
		$print_b=1000;
		$print_e=0;
		foreach($v_parts as $v_part){ //II, 23 - III, 5   
			$part_be = explode('-',$v_part); //II, 23 | III, 5 
			$part_b = explode(',',$part_be['0']); //II| 23 
			if(!$part_b['1']){ //hard to imagine this
				$part_b['1'] = $part_b['0']; 
				if($saved_chap){
					$part_b['0'] = $saved_chap; //Get previous chapter
				}else{
					return ("Этого дня в календаре нет!");
				}
			}else{
				$part_b['0'] = arabic($part_b['0']); //Convert chpter to arabic	//II		
			}
			if($part_b['0']<$print_b){
				$print_b=$part_b['0']; //Begining of reading chap
			}
			if($part_b['0']>$print_e){
				$print_e=$part_b['0']; //Ending of reading chap
			}
			$chtenije[$i]['chap_b'] = trim($part_b['0']);
			$saved_chap = $part_b['0'];
			$chtenije[$i]['stih_b'] = trim($part_b['1']);
			
			if(!$part_be['1']){$part_be['1']=$part_be['0'];} //just a single verse, set the ending to begining
			$part_e = explode(',',$part_be['1']);  //III, 5 FLAW
			if(!$part_e['1']){ //if doesn't span across few chapters
				$part_e['1'] = $part_e['0'];
				$part_e['0'] = $part_b['0'];
			}else{
				$part_e['0'] = arabic($part_e['0']); //Convert chpter to arabic	
			}
			$saved_chap = $part_e['0'];
			if($part_e['0']<$print_b){
				$print_b=$part_e['0'];
			}
			if($part_e['0']>$print_e){
				$print_e=$part_e['0'];
			}
			$chtenije[$i]['chap_e'] = trim($part_e['0']);
			$chtenije[$i]['stih_e'] = trim($part_e['1']);
			$i++;
		}
		
		$book_key = $verse['0'];
		$book_key = str_replace(' ','',$book_key);
		$i='0';
		$dir = scandir($_SERVER["DOCUMENT_ROOT"].'/bible');
		foreach($dir as $folder){
			if(!(($folder=='.')||($folder=='..'))){
				////
				$settings = file("bible/".$folder."/Bibleqt.ini");
				foreach($settings as $key=>$setting){
					$setting = iconv('cp1251','utf-8',$setting);
					$comm = preg_match('{^\s*//}',$setting);
					if(!$comm){		
						$bib = preg_match('{^\s*BibleName\s*=\s*(.+)$}',$setting,$matches);
						if($bib){$bi = trim($matches['1']);}
						
						$reg = '{^\s*ShortName\s*=.*(\s+'.$book_key.'\s+).*$}';
						$short_name = preg_match($reg,$setting);
						if($short_name){
							$avail_trans[$i]['path'] = trim($folder);
							$avail_trans[$i]['name'] = trim($bi);
							$i++;
						}
					}
				}
			}
		}
		$trans = $_GET['trans']?$_GET['trans']:$avail_trans['0']['path'];
		
		$output .= '<div class="trans">Перевод: ';
		foreach ($avail_trans as $tr){
			if($tr['path']==$trans){
				$output .= '<a href="/biblija/?reading='.urlencode(iconv('utf-8','cp1251',$ver)).'&trans='.urlencode($tr['path']).'"><span class="active">'.$tr['name'].'</span></a> / ';
			}else{
				$output .= '<a href="/biblija/?reading='.urlencode(iconv('utf-8','cp1251',$ver)).'&trans='.urlencode($tr['path']).'">'.$tr['name'].'</a> / ';
			}
		}
		$output .= '</div>';
		$settings = file("bible/".$trans."/Bibleqt.ini");
		foreach($settings as $key=>$setting){
			$setting = iconv('cp1251','utf-8',$setting);
			$comm = preg_match('{^\s*//}',$setting);
			if(!$comm){			
				$chap = preg_match('{^\s*ChapterSign\s*=\s*(.+)$}',$setting,$matches);
				if($chap){$token = trim($matches['1']);}
				
				$path_name = preg_match('{^\s*PathName\s*=\s*(.+)$}',$setting,$matches);
				if($path_name){
					$pa = $matches['1'];
				}				
				
				$fname = preg_match('{^\s*FullName\s*=\s*(.+)$}',$setting,$matches);
				if($fname){$fn = $matches['1'];}
				
				$reg = '{^\s*ShortName\s*=.*(\s+'.$book_key.'\s+).*$}';
				$short_name = preg_match($reg,$setting);
				if($short_name){
					$path = trim($pa);
					$full_name = trim($fn);
				}				
			}
		}
		$output .= '<h1>'.$full_name.'</h1>';
		$text = file_get_contents('bible/'.$trans.'/'.$path);
		$text = iconv('cp1251','utf-8',$text);
		$text = preg_replace('/<p>([0-9]{1,2})/','<p><sup>$1</sup>',$text);
		$chapters = explode($token,$text);
		foreach($chapters as $i=>$chapter){
			$chapters[$i] = $token.$chapter;
		}
		//unset($chtenije['1']);
		//var_dump($chtenije);
		foreach($chtenije as $int)
		{
			$chapters[$int['chap_b']] = str_replace('<p><sup>'.$int['stih_b'].'</sup>','</p><span class="quote"><p><sup>'.$int['stih_b'].'</sup>',$chapters[$int['chap_b']]);
			$chapters[$int['chap_e']] = preg_replace('#(<p><sup>'.($int['stih_e']).'</sup>.*)#u','$1</p></span>',$chapters[$int['chap_e']]);
			
		}
		for($i=$print_b;$i<=$print_e;$i++){
			$outputc .= $chapters[$i];
		}
		

		//
		if($trans=='RstStrong'){
			$outputc = preg_replace('/Глава\s*([0-9]{1,2})/','Глава$1',$outputc);
			$outputc = preg_replace('/\s+[0-9]{1,6}/','',$outputc);
			$outputc = preg_replace('/Глава([0-9]{1,2})/','Глава $1',$outputc);
		}
		//
		preg_match_all('#<span class="quote">.*?</span>#us',$outputc,$matches);
		if(strlen($matches[0][0])<10){
			//preg_match_all('#<span class="quote">.*#us',$outputc,$matches);
		}
		$outputc = implode('(...)<br/>',$matches['0']);		
		$output = '<h4>'.$orig_ver.'</h4>'.$output.$outputc;
		return $this->pi_wrapInBaseClass($output);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/biblequote/pi2/class.tx_biblequote_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/biblequote/pi2/class.tx_biblequote_pi2.php']);
}

?>