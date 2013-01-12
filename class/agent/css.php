<?php

class agent_css extends self
{
	function compose($o)
	{
		$o->severities = new loop_array(syslogViewer::$severities, array($this, 'filter_rawArray'));

		return parent::compose($o);
	}
}
