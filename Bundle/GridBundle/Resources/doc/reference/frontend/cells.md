Cells
-----

Cells are entities that are used for rendering of cell content and providing editor to edit cell content.
Datagrid bundle extends Backgrid Cells and Cell Formatters to add additional functionality.

**Main classes and responsibilities:**

* **Oro.Datagrid.Cell.BooleanCell** - cell representation of boolean values extended from Backgrid.BooleanCell;
* **Oro.Datagrid.Cell.IntegerCell** - cell representation of integer values extended from Backgrid.IntegerCell;
* **Oro.Datagrid.Cell.NumberCell** - cell representation of number values extended from Backgrid.NumberCell;
* **Oro.Datagrid.Cell.StringCell** - cell representation of string values extended from Backgrid.StringCell;
* **Oro.Datagrid.Cell.HtmlCell** - string cell that renders as HTML code;
* **Oro.Datagrid.Cell.SelectCell** - cell representation of options values extended from Backgrid.SelectCell;
* **Oro.Datagrid.Cell.MomentCell** - cell representation of date and datetime values extended from Backgrid.Extension.MomentCell;
* **Oro.Datagrid.Cell.Formatter.CellFormatter** - custom formatter for string values extended from Backgrid.CellFormatter;
* **Oro.Datagrid.Cell.Formatter.MomentFormatter** - custom formatter for date and datetime values extended from Backgrid.Extension.MomentFormatter.
