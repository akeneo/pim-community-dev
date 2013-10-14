Extensions
==========

Overview
--------

Datagrid object only take care about converting datasource to result set. All other operations are performed by extensions(e.g. pagination, filtering, etc..).
Here's list of already implemented extensions:

- [Formatter](extensions/formatter.md) - responsible for backend field formatting(e.g generating url using router, translate using symfony translator, etc..).
                                         Also this extension take care about passing columns configuration to view layer.
- [Pager](extensions/pager.md) - responsible for pagination and passing "pager" configuration to view layer.
- [Sorter](extensions/sorter.md) - responsible for sorting

Customization
-------------

To implement your extension you have to do following:

 - Develop class that implements ExtensionVisitorInterface (also there is basic implementation in AbstractExtension class)
 - Register you extension as service with tag { name: oro_grid.extension }
