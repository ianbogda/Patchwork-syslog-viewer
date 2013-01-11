Syslog Viewer
=============

Patchwork-syslog-viewer is a Syslog-viewer for Patchwork

Install rsyslog 
--------------

Optimize MySQL
--------------
```sql
ALTER TABLE `SystemEvents` ADD INDEX(`Facility`);
ALTER TABLE `SystemEvents` ADD INDEX(`Priority`);
ALTER TABLE `SystemEvents` ADD INDEX(`FromHost`);
ALTER TABLE `SystemEvents` ADD INDEX(`SysLogTag`);
ALTER TABLE `SystemEvents` ADD INDEX(`DeviceReportedTime`);
```

flot
----
flot is an attractive JavaScript charts for jQuery

[flot](http://www.flotcharts.org/)
[flot on github](https://github.com/flot/flot)
