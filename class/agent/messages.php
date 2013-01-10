<?php

class agent_messages extends agent
{
	public
	
	$get = array(
		'__1__:c:host|input|priority|facility',
		'__2__:c' => -1,
		'p:i' => 0
	);

	protected

	$results_per_page = 50,

	$sqlSelect = 'ID, ReceivedAt, FromHost, SysLogTag, Priority, Facility, Message',
	$sqlWhere = 1,
	$sqlGroupBy = '',
	$sqlOrderBy = 'ORDER BY `ReceivedAt` DESC',
	$sqlLimit = 0,

	$priorities = array( 'Emergency', 'Alert', 'Critical', 'Error', 'Warning', 'Notice', 'Informational', 'Debug');

	function control()
	{
		parent::control();

		if ($this->get->__1__)
		{
			switch ($this->get->__1__)
			{
				case 'host'     : $this->sqlWhere = "`FromHost`";  break;
				case 'input'    : $this->sqlWhere = "`SysLogTag`"; break;
				case 'priority' : $this->sqlWhere = "`Priority`";  break;
				case 'facility' : $this->sqlWhere = "`Facility`";  break;
			}


			if (-1 != $this->get->__2__)
			{
				switch ($this->get->__1__)
				{
					case 'host':
					case 'priority' :
					case 'facility' : $this->sqlWhere .= " = '%s'";     break;
					case 'input'    : $this->sqlWhere .= " LIKE '%s%'"; break;
				}
				$this->sqlWhere = str_replace('%s', $this->get->__2__, $this->sqlWhere);
			}
			else
			{
				$this->sqlWhere = 1;

				switch ($this->get->__1__)
				{
					case 'host'     : $this->sqlSelect = "`FromHost`";  break;
					case 'priority' : $this->sqlSelect = "`Priority`";  break;
					case 'facility' : $this->sqlSelect = "`Facility`";  break;
					case 'input'    : $this->sqlSelect = "`SysLogTag`"; break;
				}
				$this->sqlGroupBy = "GROUP BY {$this->sqlSelect} ASC";
				$this->sqlOrderBy = "ORDER BY {$this->sqlSelect} ASC";
				$this->sqlSelect .= " AS source, COUNT(*) as `count`";
			}
		}

		0 != $this->get->p || $this->sqlLimit = $this->get->p * $this->results_per_page;
	}

	function compose($o)
	{
		$sql = "SELECT {$this->sqlSelect}
				FROM `SystemEvents`
				WHERE {$this->sqlWhere}
				{$this->sqlGroupBy}
				{$this->sqlOrderBy}
				LIMIT {$this->sqlLimit}, {$this->results_per_page}";

		$o->messages = new loop_sql($sql);

		$o->results_per_page = $this->results_per_page;
		$o->page  = $this->get->p + 1;

		return $o;
	}
}
