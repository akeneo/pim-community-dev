Listeners
---------

Datagrid Listener is an entity that used to listen to Datagrid events and perform some actions with
external components (f.e. synchronization with form inputs).

Datagrid Listener receives datagrid name, column name and field ID to listen.
Specific datagrid instance is extracted from Oro.Registry.

**Main classes and responsibilities:**

* **Backbone.Model** - basic Backbone class which provides container functionality;
* **Oro.Datagrid.Listener.AbstractListener** - abstract listener that allows developer to work with datagrid,
rows and cells.
* **Oro.Datagrid.Listener.ColumnFormListener** - listener that synchronize datagrid with entity form fields.

