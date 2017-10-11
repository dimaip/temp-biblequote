<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_biblequote_days"] = Array (
	"ctrl" => $TCA["tx_biblequote_days"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,date,week,saints,prazdnik,post,reading,prayers,sermon"
	),
	"feInterface" => $TCA["tx_biblequote_days"]["feInterface"],
	"columns" => Array (
		'sys_language_uid' => array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages',-1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value',0)
				)
			)
		),
		'l18n_parent' => Array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('', 0),
				),
				'foreign_table' => 'tx_biblequote_days',
				'foreign_table_where' => 'AND tx_biblequote_days.pid=###CURRENT_PID### AND tx_biblequote_days.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => Array (		
			'config' => Array (
				'type' => 'passthrough'
			)
		),
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.xml:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"date" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:biblequote/locallang_db.xml:tx_biblequote_days.date",		
			"config" => Array (
				"type" => "text",	
				"size" => "30",
			)
		),
		"week" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:biblequote/locallang_db.xml:tx_biblequote_days.week",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"saints" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:biblequote/locallang_db.xml:tx_biblequote_days.saints",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"prazdnik" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:biblequote/locallang_db.xml:tx_biblequote_days.prazdnik",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"post" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:biblequote/locallang_db.xml:tx_biblequote_days.post",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"reading" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:biblequote/locallang_db.xml:tx_biblequote_days.reading",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"prayers" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:biblequote/locallang_db.xml:tx_biblequote_days.prayers",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"sermon" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:biblequote/locallang_db.xml:tx_biblequote_days.sermon",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, date,sermon;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts_css]")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
//week, saints, prazdnik, post, reading,
?>