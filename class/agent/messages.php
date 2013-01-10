<?php

class agent_messages extends agent
{
	public
	
	$get = array(
		'__1__:c' => 'host|input|priority|facilityi',
		'__2__:c' => -1
	);

	protected

	$results_per_page = 50,
	$results_nb,
	$filter = 1,
	$groupBy = '',

	$priorities = array( 'Emergency', 'Alert', 'Critical', 'Error', 'Warning', 'Notice', 'Informational', 'Debug');

	function control()
	{
		parent::control();

		if ($this->get->__1__)
		{
			switch ($this->get->__1__)
			{
				case 'host'     : $this->filter = "`FromHost`";  break;
				case 'input'    : $this->filter = "`SysLogTag`"; break;
				case 'priority' : $this->filter = "`Priority`";  break;
				case 'facility' : $this->filter = "`Facility`";  break;
			}


			if (-1 != $this->get->__2__)
			{
				switch ($this->get->__1__)
				{
					case 'host':
					case 'priority' :
					case 'facility' : $this->filter .= " = '%s'";     break;
					case 'input'    : $this->filter .= " LIKE '%s%'"; break;
				}
				$this->filter = str_replace('%s', $this->get->__2__, $this->filter);
			}
			else
			{
				$this->filter = 1;
			}
		}
	}

	function compose($o)
	{
		$sql = "SELECT
					ID,
					ReceivedAt,
					FromHost,
					SysLogTag,
					Priority,
					Facility,
					Message
				FROM `SystemEvents`
				WHERE {$this->filter}
				ORDER BY `ReceivedAt` DESC";

		$o->messages = new loop_sql($sql);
E($sql);
		$o->results_per_page = $this->results_per_page;

		return $o;
	}
}
