Pager extension:
=======

Overview
--------
This extension provides pagination, also it responsible for passing "pager" settings to view layer.
Now implemented only paging for ORM datasource.

Settings
---------
Pager setting should be placed under `pager` tree node. It's enabled by default for grids that used ORM datasource

 - `enabled` - (bool) enable extension for the datagrid
 - `default_per_page` - (int) default per page value

