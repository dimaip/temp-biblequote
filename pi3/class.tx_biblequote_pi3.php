<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Dmitri Pisarev <dimaip@gmail.com>
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
 * Plugin 'calendar_left' for the 'biblequote' extension.
 *
 * @author	Dmitri Pisarev <dimaip@gmail.com>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_biblequote_pi3 extends tslib_pibase {
	var $prefixId = 'tx_biblequote_pi3';		// Same as class name
	var $scriptRelPath = 'pi3/class.tx_biblequote_pi3.php';	// Path to this script relative to the extension dir.
	var $extKey = 'biblequote';	// The extension key.
	//var $pi_checkCHash = TRUE;
	
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
			
		$date = $_GET['date']?$_GET['date']:date('Ymd');
		$timestamp = strtotime($date);
		$year = date('Y', $timestamp);
		$month = date('m', $timestamp);
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('date','tx_biblequote_days','post<>"Поста нет." AND post<>"Поста нет" AND post<>""');
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			$timestamp2 = strtotime($row['date']);
			$cal[] = date('Y-m-d', $timestamp2);
		}
		
		$date_prev = strtotime("-1 month", $timestamp);
		$output .= '<a class="day_link" href="/pravoslavnyi_kalendar/?date='.strftime('%Y%m%d',$date_prev).'">Предыдущий месяц</a>';
		
		
		require_once('calendar.class.php');
		$calendar = new Calendar();
		//$cal[] = date('Y-m-d', $timestamp);
		$calendar->formatted_link_to = '/pravoslavnyi_kalendar/?date=%Y%m%d';
		$calendar->week_start = '1';
		$calendar->highlighted_dates = $cal;
		//12holidays - no move
		$cal2[] = '2012-01-07';//christmas
		$cal2[] = '2012-01-19';//baptism
		$cal2[] = '2012-02-15';//Candlemas
		$cal2[] = '2012-04-07';//anounciation
		$cal2[] = '2012-08-19';//transfiguration
		$cal2[] = '2012-08-28';//assumption
		$cal2[] = '2012-09-21';//Nativity of st.Virgin
		$cal2[] = '2012-09-27';//Rise of cross
		$cal2[] = '2012-12-04';//Introduction to tempel
		//12 0 move
		$cal2[] = '2012-04-08';//Entrance to Jerusalim
		$cal2[] = '2012-04-15';//easter
		$cal2[] = '2012-05-24';//ascension
		$cal2[] = '2012-06-03';//pentecost
		
		//great
		$cal2[] = '2012-01-14';//circumcision of the Lord
		$cal2[] = '2012-07-07';//birth of John
		$cal2[] = '2012-07-12';//Peter and Pole
		$cal2[] = '2012-09-11';//the beheading of John the baptist
		$cal2[] = '2012-10-14';//intercession of st.Virgin
		
		$calendar->highlighted_dates2 = $cal2;
		$out .= $calendar->output_calendar($year,$month);
		$output .= $out;
		$date_next = strtotime("+1 month", $timestamp);
		$output .= '<a class="day_link" href="/pravoslavnyi_kalendar/?date='.strftime('%Y%m%d',$date_next).'">Следующий месяц</a>';
		
		return $this->pi_wrapInBaseClass($output);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/biblequote/pi3/class.tx_biblequote_pi3.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/biblequote/pi3/class.tx_biblequote_pi3.php']);
}

?>
