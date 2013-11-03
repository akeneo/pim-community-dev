Overview
--------

Grid bundle has rich representation of frontend side that is visible to end-user as UI widgets when grid is displayed. Frontend-side serves role of View of grid data. Main goals of grid frontend-side are trivial from perspective of View:

* display grid data
* manipulate grid data

More detailed responsibilities are based on requirements to grid UI. Among those requirements are:

* support of columns sorting
* pagination functionality
* provide row actions
* ability to apply filtering criteria
* use of filter, sorter or pager should change grid state
* able to change grid state without page reload using AJAX
* grid state should be saved in browser history
* apply browser's "Go Back" and "Go Forward" actions onto grid states history
