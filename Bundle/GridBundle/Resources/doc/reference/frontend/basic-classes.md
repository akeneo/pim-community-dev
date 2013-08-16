Basic Classes
-------------

Bundle's JS module extends Backgrid.js and defines the following classes:

* **Oro.PageableCollection**

Provides extended functionality of Backbone.PageableCollection. In particular, this object knows how to encode its state to string, and how to decode the string back to the state. This knowledge required by router of grid module that need representation of grid's collection state as a string.

In addition to everything else, this class holds filtering parameters that are used to request data. State is of collection is an object of next structure:
``` javascript
state: {
    firstPage: Integer, // pager position
    lastPage: Integer, // last available page
    currentPage: Integer, // current page
    pageSize: Integer, // page size
    totalPages: Integer, // total pages
    totalRecords: Integer, // total records in storage
    sortKey: Integer|null, // sort order
    order: -1|1|null, // Sort order: ascending or descending
    filters: Array // Array of applied filters
}
```

When the collection is requests data from storage, it sends a GET request using AJAX. This request contains all criteria based on which data storage is queried. Criteria parameters comes from the state of the collection. An example URL of collection's request to storage:
```
example.com/users/list.json?users[_pager][_page]=1&users[_pager][_per_page]=10
```

* **Oro.Datagrid.Router**

Inherited from Backbone.Router. This object acts as a router. Thanks to this class, user can for example select next page using pagination, change records number per page apply some sorting and then go back to original state using Back button. It also responsible for initializing collection with first state that came from URL that user requests.
An example URL that stores the state of grid:
```
example.com/users/list#g/i=2&p=25&s=email&o=-1
```
This line contains information about the page number (i = 2), the name of the field you are sorting (p = 25) and a ascending sort order (o = -1).

* **Oro.Datagrid.Grid** In addition to basic grid, this class can work with loading mask, toolbar, set of filters, and set of actions.
* **Oro.LoadingMask** Serves to display the loading process to end-user when some request is in progress.
* **Oro.Datagrid.Toolbar** Aggregates control toolbar widgets, including paginator, and page size widgets.
Oro.Datagrid.Pagination** and **Oro.Datagrid.Pagination.Input
Paginator could have one of two possible presentations, using links as page numbers and using input field for entering and displaying page number.
* **Oro.Datagrid.PageSize** This widget is used to control number of records displayed on one grid page.
* **Oro.Datagrid.Row** View extended from Backgrid.Row that allows listening to it's events

Here is an example of code that initializes grid:
``` javascript
var collection = new Oro.PageableCollection({
    inputName: "users",
    url: "/en/grid/users/list.json",
    state:{
        currentPage:1,
        pageSize:25,
        totalRecords:52
    }
});
var grid = new Oro.Datagrid.Grid({
    collection: collection,
    columns:[
        {
            name:"id",
            label:"ID",
            sortable:true,
            editable:false,
            cell:Oro.Datagrid.Cell.IntegerCell.extend({ orderSeparator:'' })
        },
        {
            name:"username",
            label:"Username",
            sortable:true,
            editable:false,
            cell:Oro.Datagrid.Cell.StringCell
        },
        {
            name:"email",
            label:"Email",
            sortable:true,
            editable:false,
            cell:Oro.Datagrid.Cell.StringCell
        }
    ],
    entityHint: "Users",
    noDataHint: "There are no users yet. Try to creating a new ..."
    noResultsHint: "No users were found to match your search. Try modifying your search criteria ..."
});

$('#grid').html(grid.render().$el);
```
