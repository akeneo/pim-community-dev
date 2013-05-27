Backgrid Developer Introduction
-------------------------------

Detailed information on the library is here http://wyuenho.github.com/backgrid/. The main types of this library are:

* **Backgrid.Grid** - central object of Backgrid library, aggregate object that connects all grid's views and models together. Client should create instance of this object to have grid in it's UI. The default grid is HTML tag table. Grid has the ability to be configured with such data as:
collection - data source model, it's models will be displayed in grid
columns - information about what columns and in what way should be displayed in grid
* **Backgrid.Header** - header section of grid, responsible for outputting columns labels in cells of Backgrid.HeaderRow. By default represented with HTML tag thead.
* **Backgrid.Body** - body section of grid, responsible for outputting collection's models in it's rows (Backgrid.Row), each row in it's turn, consists of cells that match the corresponding grid columns. By default represented with HTML tag tbody.
* **Backgrid.Footer** - footer section of grid, responsible for output additional information of grid in footer section. By default represented with HTML tag tfoot.
* **Backgrid.Columns** - collection of grid columns
* **Backgrid.Column** - encapsulates model of модель grid column. Column module has next attributes:
 * **name** - unique column identifier. This identifier must be same as attribute of model
 * **label** - label of column displayed in grid header section
 * **sortable** - is allow sorting by column values
 * **editable** - is allow inline edit for column's cell
 * **renderable** - should column be rendered
 * **formatter** - instance of Backgrid.Formatter, this object responsible for converting corresponding model attribute to value that will be displayed in column cell
 * **cell** - instance of Backgrid.Cell, responsible for presentation of corresponding model attribute in column's cell of Backbone.Row
headerCell - instance of Backgrid.Cell, responsible for presentation of column cell in Backbone.HeaderRow
* **Backgrid.Row** - this object encapsulates representation of model in grid row. Row has embeds as many cells as available columns in grid. By default row represented with HTML tag tr.
* **Backgrid.HeaderRow** - encapsulates number of header cells. Extends from Backgrid.Row but unlike parent aggregates * Backgrid.HeaderCell's. As parent by default represented with HTML tag tr.
* **Backgrid.Cell** - is responsible for presenting model property in a row. Cell aggregates Backgrid.CellFormatter and Backgrid.CellEditor. By default cell represented with HTML tag td.
* **Backgrid.HeaderCell** - unlike Backgrid.Cell it doesn't have editor and formatter. Header cell displays column label and also provides UI controls for column sorting. By default cell represented with HTML tag th.
* **Backgrid.CellFormatter** - has one responsibility - convert value of model property with same name as in related column and return this value. Backgrid has formatters for main data types:
* **Backgrid.NumberFormatter** - for dealing with properties of number types
* **Backgrid.DatetimeFormatter** - for dealing with properties of date time types

Backbone.Grid is a class from backbone's View category. Any standard backbone's collection could be used together with grid. But to able to use the paginator in grid, you must first declare your collections to be a Backbone.PageableCollection, which is a simple subclass of the Backbone.js Collection with added pagination behavior.
