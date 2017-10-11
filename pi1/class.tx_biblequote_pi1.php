<?php
ini_set('display_errors', 1); 
ini_set('log_errors', 1); 
//error_reporting(E_ALL);
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

		
require_once(PATH_tslib.'class.tslib_pibase.php');

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
		$date_begin_str = '20140101';
		$date_end_str = '20141231';
			
		for($date_str = $date_begin_str; $date_str <= $date_end_str; ){
			$out .= $this->process($date_str);
			$date = strtotime($date_str);
			$date_n = strtotime("+1 days", $date);
			$date_str = strftime('%Y%m%d',$date_n);
		}
		return $out;
	}
	
	function process($date_str){
		$date = strtotime($date_str);
		$date_c_str = strftime('%m%d',$date);
		echo($date_c_str);
		
		
		$json = file_get_contents("http://script.days.ru/php.php?var=varname&php=1&date=".$date_c_str);
		$vars = unserialize(stripslashes($json));
		
		
		
		// INSERT:
		$insertArray = array(
			'pid' => '1',
			'date' => $date_str,
			'reading' => strip_tags(iconv('cp1251','utf-8',$vars["chten"]))
		);
		
		$res = $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_biblequote_days', 'date='.$date_str);
		$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_biblequote_days', $insertArray);
		$content = print_r($insertArray,1);
		return $content;
	}
}
?>