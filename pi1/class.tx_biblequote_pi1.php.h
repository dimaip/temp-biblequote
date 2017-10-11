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
 * Plugin 'calendar' for the 'biblequote' extension.
 *
 * @author	Dmitri Pisarev <dimaip@gmail.com>
 */
setlocale(LC_ALL, 'ru_RU.utf8');
	
require_once(PATH_tslib.'class.tslib_pibase.php');
$voskresnije = 1;
class tx_biblequote_pi1 extends tslib_pibase {
	var $prefixId = 'tx_biblequote_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_biblequote_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'biblequote';	// The extension key.
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{

		global $voskresnije;
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		$this->local_cObj = t3lib_div::makeInstance('tslib_cObj');
		
		
		
		include('include.php');	
		
		
		$date = $_GET['date']?$_GET['date']:date('Ymd');
		$date_ts = strtotime($date);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_biblequote_days','date='.$date);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		if(!$row){return 'Данного дня еще нет в нашем календаре';}
		$chtenija = '';
		$verses = $row['reading'];
		$verses = str_replace('&#769;','',$verses);
		$verses = explode('#',$verses); //get only first portion of reading
		$verses_more = $verses[1]; //some weird stuff from the second part
		$verses = $verses[0];
		
		$verses = preg_replace('/([а-я,А-Я]+.?: )/u','$1<br/>',$verses);
		$verses = preg_replace('/(\*.+: )/u','<br/>$1<br/>',$verses);
		$verses = preg_replace('#Утр. - Ев. (\d{1,2})-е, #u','<br/>Утр. - ',$verses);
		//$verses = preg_replace('#\(.*?\)#','',$verses);
		$verses = str_replace('Утр. - ','<br/>Утр. - <br/>',$verses);
		$verses = str_replace('Лит. - ','<br/>Лит. - <br/>',$verses);
		
		
		$verses = str_replace(', или свт.: ','<br/>или свт.: ',$verses);
		$verses = str_replace(' или ',' или <br/>',$verses);
		$verses = preg_replace('#(\d)\. #u','$1<br/>',$verses);
		$verses = str_replace('<br/><br/>','<br/>',$verses);
		$verses = explode('<br/>',$verses);

		foreach ($verses as $verse){
			if(!preg_match('#\w{2,5}#u',$verse)){
				$chtenija .= '<p class="type_read"> '.$verse.'';
			}else{
				$chtenija .= '<a href="/biblija/?reading='.rawurlencode(iconv('utf-8','cp1251',$verse)).'">'.$verse.'</a>&nbsp;&nbsp;&nbsp;';
			}
		}
		$chtenija .= $verses_more;
		
		//День недели
		$day .= '<p class="day">'.strftime('%A',strtotime($date)).'</p>';
		$output .= $day; 
		
		//Старый стиль
		$date_c = strtotime("-13 days", $date_ts);
		$output .= '<div class="wrap-top">'; //DIV closed at 125
		$output .= '<p class="topDates" id="old_style"><span class="title">Старый стиль</span><span class="string">'.strftime('%d %b %Y',$date_c).'</span></p>';
		//Новый стиль
		$output .= '<p class="topDates" id="new_style"><span class="title">Новый стиль</span><span class="string">'.strftime('%d %b %Y',$date_ts).'</span></p>';
		$output .= '</div>'; //DIV closed
		
		//Предидущий/Следующий день
		$output .= '<div class="wrapPrevNext">'; //at 133
		$date_prev = strtotime("-1 days", $date_ts);
		$output .= '<a id="prev" class="day_link" href="/pravoslavnyi_kalendar/?date='.strftime('%Y%m%d',$date_prev).'">Предыдущий день</a>';
		$date_next = strtotime("+1 days", $date_ts);
		$output .= '<a id="next" class="day_link" href="/pravoslavnyi_kalendar/?date='.strftime('%Y%m%d',$date_next).'">Следующий день</a>';
		$output .= '</div>'; //closed
		
		$week = preg_replace('#^\. #u','',$row['week']); //Get rid of the . mess
		if($week){$output .= '<h4 class="week">'.$week.'</h4>';}
		if($row['prazdnik']){$output .= '<h4 class="prazdnik">'.$row['prazdnik'].'</h4>';}
		if($row['post']){$output .= '<h4 class="post">'.$row['post'].'</h4>';}
		if($chtenija){$output .= '<h3>Чтения дня</h3><div class="hr calendar_wrap">'.$chtenija.'</div>';}
		if($row['saints']){$output .= '<h3>Святые дня</h3><div class="hr calendar_wrap">'.$row['saints'].'</div>';}
		$weekday = strftime('%u',strtotime($date));
		if($row['prayers']){ //DIV closed at 156
			$output .= '<h3>Тропари и кондаки</h3><div class="hr calendar_wrap">'.$this->local_cObj->parseFunc($row['prayers'], array(), '< lib.parseFunc_RTE');
		}

		if($weekday == '7'){	
				$bib = preg_match('/Глас(.{2})/',$row['week'],$matches);
				$glass = trim($matches['1']);
				if($glass){
					if(!$row['prayers']){ //153
						$output .= '<h3>Тропари и кондаки</h3><div class="hr calendar_wrap">';
					}		
					$output .= '<div class="media"><a href="fileadmin/audio/Bogosluzhebnye%20pesnopenija/Voskresnije%20tropari/glass'.$glass.'.mp3" target="_blank"></a></div>';
					$output .= ' <a href="/audio/bogosluzhebnye_pesnopenija/bp/122/track/voskresnye-tropari/" title="Воскресные тропари">Прослушать воскресные тропари (аудио)</a>';
					$output .= $voskresnije[$glass];
					if(!$row['prayers']){ //close 148
					$output .= '</div>';
					}
				}
			}
		if($row['prayers']){
			$output .= '</div>'; //close 141
		}
		if($row['sermon']){
			$output .= '<h3>Проповедь</h3><div class="hr calendar_wrap">';
			$output .= $this->local_cObj->parseFunc($row['sermon'], array(), '< lib.parseFunc_RTE');
			$output .= '</div>';
		}
		
		return $this->pi_wrapInBaseClass($output);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/biblequote/pi1/class.tx_biblequote_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/biblequote/pi1/class.tx_biblequote_pi1.php']);
}

?>