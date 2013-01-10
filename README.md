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

pChart
------
pChart was to provide an easy interface for drawing anti-aliased pictures using the GD library.

[pChart](http://www.pchart.net/)
