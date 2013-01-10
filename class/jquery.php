<?php

class jquery extends self 
{
	static function __init()
	{
		self::$uiLoad .= ' ui.tabs ui.dialog ui.sortable';

		parent::__init();
	}
}
