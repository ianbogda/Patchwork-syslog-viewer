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

	$priorities = array( 'Emergency', 'Alert', 'Critical', 'Error', 'Warning', 'Notice', 'Informational', 'Debug'),

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
				$this->sqlSelect = "regex_replace('\\\\\]|\\\\\[|:','',"
					. ( 'input' == $this->get->__1__ ? "regex_replace('([0-9])',''," : "" )
					. "{$this->sqlSelect})"
					. ( 'input' == $this->get->__1__ ? ")" : "" )
					. " AS source, COUNT(*) as `count`";
				$this->sqlGroupBy = "GROUP BY source";
				$this->sqlOrderBy = "ORDER BY `source` ASC";
			}
		}

		0 != $this->get->p || $this->sqlLimit = $this->get->p * $this->results_per_page;
	}

	function compose($o)
	{
		$db = DB();

		$sql = "SELECT ROUTINE_NAME
				FROM INFORMATION_SCHEMA.ROUTINES
				WHERE
					ROUTINE_TYPE='FUNCTION'
					AND ROUTINE_SCHEMA='{$CONFIG['doctrine.dbname']}'
					AND SPECIFIC_NAME ='regex_replace'";
		$sql = $db->query($sql);

		if (false === $sql->fetch()) $udf = $db->exec($this->sqlRegexReplace);

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
