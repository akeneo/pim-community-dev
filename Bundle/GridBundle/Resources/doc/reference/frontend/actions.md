Actions
-------

If you need to allow a user to perform an action on records in the grid, this can be achieved by actions. Actions are designed thus that they can be used separately from the grid, but when you need to use actions in the grid, you just need to pass them into configuration. All added actions will be accessible in special actions column.

Action performs using instance of model and usually uses a link to do work on server. User can pass link using parameters:

* **link** (String) - Full link or property name in model where link is located;
* **backUrl** (Boolean or String) - if TRUE then additional parameter will be added to link, this parameter will have value of current window location. If *backUrl* is a String, that it will be used instead.
* **backUrlParameter** (String) - Parameter name used for *backUrl*, by default - "back".

Below is an example of initialization grid with actions:
``` javascript
var grid = new Oro.Datagrid.Grid({
    actions: [
        Oro.Datagrid.Action.NavigateAction.extend({
            label: "Edit",
            icon: edit,
            placeholders: {"{id}":"id"},
            url: "/user/edit/{id}"
        }),
        Oro.Datagrid.Action.DeleteAction.extend({
            label: "Delete",
            icon: "trash",
            placeholders: {"{id}":"id"},
            url: "/api/rest/latest/users/{id}.json"
        })
    ]
    // other configuration
});
```

Main classes and responsibilities:

* **Oro.Datagrid.Grid** - grid contains collection of models and allowed actions that user can perform
* **Oro.Datagrid.Action.Cell** - responsible for rendering grid's actions launchers
* **Oro.Datagrid.Action.AbstractAction** - abstract action that can be performed
* **Oro.Datagrid.Action.Launcher** - renders control that can be used by user to run action, for example a simple link
* **Oro.Datagrid.Action.DeleteAction** - concrete action responsible for model delete
* **Oro.Datagrid.Action.NavigateAction** - concrete action responsible for navigating user to some URL
