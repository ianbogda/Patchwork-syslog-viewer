<?php

class syslogViewer
{

	public static

	/* lists from http://wiki.gentoo.org/wiki/Rsyslog */
	$severities = array(
		0 => array(
			'severity'    => 'Emerg',
			'description' => 'system is unusable',
			'color'       => 'Magenta',
		),
		1 => array(
			'severity'    => 'Alert',
			'description' => 'action must be taken immediately',
			'color'       => 'Maroon',
		),
		2 => array(
			'severity'    => 'Critic',
			'description' => 'critical conditions',
			'color'       => 'red',
		),
		3 => array(
			'severity'    => 'Error',
			'description' => 'error conditions',
			'color'       => 'orange',
		),
		4 => array(
			'severity'    => 'Warning',
			'description' => 'warning conditions',
			'color'       => 'yellow',
		),
		5 => array(
			'severity'    => 'Notice',
			'description' => 'normal but significant condition',
			'color'       => 'limegreen',
		),
		6 => array(
			'severity'    => 'Info',
			'description' => 'informational messages',
			'color'       => 'lightSkyBlue',
		),
		7 => array(
			'severity' => 'Debug',
			'description' => 'debug-level messages',
			'color'       => 'grey',
		)
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
}
