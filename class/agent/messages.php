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
	),

	/* from http://techras.wordpress.com/2011/06/02/regex-replace-for-mysql/ */
	$sqlRegexReplace = "CREATE FUNCTION  `regex_replace`(pattern VARCHAR(1000),replacement VARCHAR(1000),original VARCHAR(1000))
						RETURNS VARCHAR(1000)
						DETERMINISTIC
						BEGIN 
							DECLARE temp VARCHAR(1000); 
							DECLARE ch VARCHAR(1); 
							DECLARE i INT;
							SET i = 1;
							SET temp = '';
							IF original REGEXP pattern THEN 
								loop_label: LOOP 
									IF i>CHAR_LENGTH(original) THEN
										LEAVE loop_label;  
									END IF;
									SET ch = SUBSTRING(original,i,1);
									IF NOT ch REGEXP pattern THEN
										SET temp = CONCAT(temp,ch);
									ELSE
										SET temp = CONCAT(temp,replacement);
									END IF;
									SET i=i+1;
								END LOOP;
							ELSE
								SET temp = original;
							END IF;
							RETURN temp;
						END;";

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
/*				$this->sqlSelect = "regex_replace('\\\\\]|\\\\\[|:','',"
					. ( 'input' == $this->get->__1__ ? "regex_replace('([0-9])',''," : "" )
					. "{$this->sqlSelect})"
					. ( 'input' == $this->get->__1__ ? ")" : "" )
					. " AS source, COUNT(*) as `count`";
*/
				$this->sqlSelect .= " AS source, COUNT(*) as `count`";
				$this->sqlGroupBy = "GROUP BY source";
				$this->sqlOrderBy = "ORDER BY `source` ASC";
/*
				$sql = "SELECT ROUTINE_NAME
						FROM INFORMATION_SCHEMA.ROUTINES
						WHERE
							ROUTINE_TYPE='FUNCTION'
							AND ROUTINE_SCHEMA='{$CONFIG['doctrine.dbname']}'
							AND SPECIFIC_NAME ='regex_replace'";
				$sql = $db->query($sql);

				if (false === $sql->fetch()) $udf = DB()->exec($this->sqlRegexReplace);
*/
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

//		$o->messages = new loop_sql($sql);

		$o->severities = new loop_array($this->severities, 'filter_rawArray');
		$o->facilities = new loop_array($this->facilities, 'filter_rawArray');

		$o->results_per_page = $this->results_per_page;
		$o->page  = $this->get->p + 1;

		switch ($this->get->__1__)
		{
			case 'host'     : $this->sqlWhere = "`FromHost`";  break;
			case 'input'    : $this->sqlWhere = "`SysLogTag`"; break;
			case 'priority' : $this->sqlWhere = "`Priority`";  break;
			case 'facility' : $this->sqlWhere = "`Facility`";  break;
		}
		$this->sqlWhere .= " AS data,
			COUNT({$this->sqlWhere}) as total,
			UNIX_TIMESTAMP(`ReceivedAt`) AS timestamped";

		$sql = "SELECT {$this->sqlWhere}
				FROM `SystemEvents`
				GROUP BY data, YEAR(`ReceivedAt`), MONTH(`ReceivedAt`), DAY(`ReceivedAt`)
				ORDER BY timestamped";
		$o->graphData = new loop_sql($sql);

		return $o;
	}
}
