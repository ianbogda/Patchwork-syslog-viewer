<?php

class agent_messages extends agent
{
	public
	
	$get = array(
		'__1__:c:host|input|priority|facility',
		'__2__:c' => -1,
		'start:i:0' => 0,
		'length:i:1' => 25
	);

	protected

	$sqlSysLogTag = "IF('' <> LEFT(`SysLogTag`, LOCATE('[', `SysLogTag`) -1), LEFT(`SysLogTag`, LOCATE('[', `SysLogTag`) -1) , LEFT(`SysLogTag`, LOCATE(':', `SysLogTag`) -1))",
	$sqlSelect    = "ID, ReceivedAt, FromHost, %s, Priority, Facility, Message",
	$sqlWhere     = 1,
	$sqlGroupBy   = '',
	$sqlOrderBy   = 'ORDER BY `ReceivedAt` DESC';

	function control()
	{
		parent::control();

		$this->get->__1__ && $this->prepareSql($this->get->__1__, $this->get->__2__);
	}

	function compose($o)
	{
		$this->sqlSelect = str_replace('%s', $this->sqlSysLogTag . ' AS SysLogTag', $this->sqlSelect);
		$sql = "SELECT {$this->sqlSelect}
				FROM `SystemEvents`
				WHERE {$this->sqlWhere}
				{$this->sqlGroupBy}
				{$this->sqlOrderBy}
				LIMIT {$this->get->start}, {$this->get->length}";

		$o->messages = new loop_sql($sql, array($this, 'filterMessages'));

		$o->severities = new loop_array(syslogViewer::$severities, 'filter_rawArray');
		$o->facilities = new loop_array(syslogViewer::$facilities, 'filter_rawArray');

		$o->start  = $this->get->start;
		$o->length = $this->get->length;

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

			if ('priority' == $this->get->__1__ || 'facility' == $this->get->__1__)
			{
				$sql = "SELECT {$field} AS labelNumeric
						FROM `SystemEvents`
						GROUP BY labelNumeric
						ORDER BY labelNumeric";
				$o->labels = new loop_sql($sql, array($this, 'filterLabel'));
			}
		}
		else
		{
			$sql = "SELECT Facility, Priority, COUNT(`Priority`) AS total
					FROM `SystemEvents`
					WHERE `ReceivedAt` BETWEEN (DATE_SUB(NOW(), INTERVAL 1 DAY)) AND NOW()
					GROUP BY `Facility`
					ORDER BY `Facility`";

			$o->graphData = new loop_sql($sql, array($this, 'filterLabel'));
			$o->labels    = new loop_sql($sql, array($this, 'filterLabel'));
			$o->xlabel    = 'Facility';
			$o->ylabel    = 'Priority';
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
			$sql = $this->getSqlColumn($field) . ('input' === $field ? " LIKE %s%" : " = %s");
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

	function filterMessages($o)
	{
		if (isset($o->Priority))
		{
			$o->severityString      = syslogViewer::$severities[$o->Priority]['severity'];
			$o->severityDescription = syslogViewer::$severities[$o->Priority]['description'];
		}
		if (isset($o->Facility))
		{
			$o->facilityString      = syslogViewer::$facilities[$o->Facility]['facility'];
			$o->facilityDescription = syslogViewer::$facilities[$o->Facility]['description'];
		}

		return $o;
	}

	function filterLabel($o)
	{
		if ('priority' === $this->get->__1__)
		{
			$o->labelString = syslogViewer::$severities[$o->labelNumeric]["{$this->get->__1__}"];
			$o->labelColor  = syslogViewer::$severities[$o->labelNumeric]['color'];
		}
		elseif ('facility' === $this->get->__1__)
		{
			$o->labelString = syslogViewer::$facilities[$o->labelNumeric]["{$this->get->__1__}"];
		}
		else
		{
			$o->severityLabel = syslogViewer::$severities[$o->Priority]['severity'];
			$o->severityColor = syslogViewer::$severities[$o->Priority]['color'];
			$o->facilityLabel = syslogViewer::$facilities[$o->Facility]['facility'];
		}

		return $o;
	}
}
