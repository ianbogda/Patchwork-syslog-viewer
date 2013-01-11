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

	$sqlSysLogTag = "IF('' <> LEFT(`SysLogTag`, LOCATE('[', `SysLogTag`) -1), LEFT(`SysLogTag`, LOCATE('[', `SysLogTag`) -1) , LEFT(`SysLogTag`, LOCATE(':', `SysLogTag`) -1))",
	$sqlSelect    = "ID, ReceivedAt, FromHost, %s, Priority, Facility, Message",
	$sqlWhere     = 1,
	$sqlGroupBy   = '',
	$sqlOrderBy   = 'ORDER BY `ReceivedAt` DESC',
	$sqlLimit     = 0,

	/* lists from http://wiki.gentoo.org/wiki/Rsyslog */
	$severities = array(
		0 => array('severity' => 'Emerg',   'description' => 'system is unusable'),
		1 => array('severity' => 'Alert',   'description' => 'action must be taken immediately'),
		2 => array('severity' => 'Critic',  'description' => 'critical conditions'),
		3 => array('severity' => 'Error',   'description' => 'error conditions'),
		4 => array('severity' => 'Warning', 'description' => 'warning conditions'),
		5 => array('severity' => 'Notice',  'description' => 'normal but significant condition'),
		6 => array('severity' => 'Info',    'description' => 'informational messages'),
		7 => array('severity' => 'Debug',   'description' => 'debug-level messages')
	),
	$facilities = array(
		 0 => array('facility' => 'kern',     'description' => 'kernel messages'),
		 1 => array('facility' => 'user',     'description' => 'user-level messages'),
		 2 => array('facility' => 'mail',     'description' => 'mail system'),
		 3 => array('facility' => 'daemon',   'description' => 'system daemons'),
		 4 => array('facility' => 'auth',     'description' => 'security/authorization messages'),
		 5 => array('facility' => 'syslog',   'description' => 'messages generated internally by syslogd'),
		 6 => array('facility' => 'lpr',      'description' => 'line printer subsystem'),
		 7 => array('facility' => 'news',     'description' => 'network news subsystem'),
		 8 => array('facility' => 'uucp',     'description' => 'UUCP subsystem'),
		 9 => array('facility' => 'cron',     'description' => 'clock daemon'),
		10 => array('facility' => 'security', 'description' => 'security/authorization messages'),
		11 => array('facility' => 'ftp',      'description' => 'FTP daemon'),
		12 => array('facility' => 'ntp',      'description' => 'NTP subsystem'),
		13 => array('facility' => 'logaudit', 'description' => 'log audit'),
		14 => array('facility' => 'logalert', 'description' => 'log alert'),
		15 => array('facility' => 'clock',    'description' => 'clock daemon (note 2)'),
		16 => array('facility' => 'local0',   'description' => 'local use 0 (local0)'),
		17 => array('facility' => 'local1',   'description' => 'local use 1 (local1)'),
		18 => array('facility' => 'local2',   'description' => 'local use 2 (local2)'),
		19 => array('facility' => 'local3',   'description' => 'local use 3 (local3)'),
		20 => array('facility' => 'local4',   'description' => 'local use 4 (local4)'),
		21 => array('facility' => 'local5',   'description' => 'local use 5 (local5)'),
		22 => array('facility' => 'local6',   'description' => 'local use 6 (local6)'),
		23 => array('facility' => 'local7',   'description' => 'local use 7 (local7)'),
	);

	function control()
	{
		parent::control();

		$this->get->__1__ && $this->prepareSql($this->get->__1__, $this->get->__2__);

		0 != $this->get->p || $this->sqlLimit = $this->get->p * $this->results_per_page;
	}

	function compose($o)
	{
		$this->sqlSelect = str_replace('%s', $this->sqlSysLogTag . ' AS SysLogTag', $this->sqlSelect);
		$sql = "SELECT {$this->sqlSelect}
				FROM `SystemEvents`
				WHERE {$this->sqlWhere}
				{$this->sqlGroupBy}
				{$this->sqlOrderBy}
				LIMIT {$this->sqlLimit}, {$this->results_per_page}";

		$o->messages = new loop_sql($sql);

		$o->severities = new loop_array($this->severities, 'filter_rawArray');
		$o->facilities = new loop_array($this->facilities, 'filter_rawArray');

		$o->results_per_page = $this->results_per_page;
		$o->page  = $this->get->p + 1;

		if ($this->get->__1__)
		{
			$field = $this->getSqlColumn($this->get->__1__);
			$this->sqlSelect = "{$field} AS data,
				COUNT(" . $field . ") as total,
				UNIX_TIMESTAMP(`ReceivedAt`) AS timestamped";

			$sql = "SELECT {$this->sqlSelect}
					FROM `SystemEvents`
					GROUP BY data, YEAR(`ReceivedAt`), MONTH(`ReceivedAt`), DAY(`ReceivedAt`)
					ORDER BY timestamped";
			$o->graphData = new loop_sql($sql);
		}

		return $o;
	}

	protected function getSqlColumn($case)
	{
		switch ($case)
		{
			case 'host'     : return "`FromHost`";        break;
			case 'input'    : return $this->sqlSysLogTag; break;
			case 'priority' : return "`Priority`";        break;
			case 'facility' : return "`Facility`";        break;
		}
	}

	protected function prepareSql($field, $value)
	{
		if (-1 != $value)
		{
			$sql = $this->getSqlColumn($field) . ('input' === $field) ? " = %s" : "LIKE %s%";
			$this->sqlWhere = str_replace('%s', $value, $sql);
		}
		else
		{
			$this->sqlSelect  = $this->getSqlColumn($field) . " AS source, COUNT(*) as `count`";
			$this->sqlWhere   = 1;
			$this->sqlGroupBy = "GROUP BY source";
			$this->sqlOrderBy = "ORDER BY `source` ASC";
		}
	}
}
