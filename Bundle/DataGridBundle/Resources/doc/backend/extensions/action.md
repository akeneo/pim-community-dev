Action Extension
================

Overview:
---------

This extension is responsible for configuring actions for datagrid. Action types could be easily added by developers.
Configuration for actions should be placed under `actions` node.

Actions
-------

`type` is required option for action configuration.
Action access could be controlled by adding `acl_resource` node for each action (this parameter is optional).

### Ajax

Performs ajax call by given url.

``` yml
action_name:
    type: ajax
    link: PROPERTY_WITH_URL # required
```

### Delete

Performs DELETE ajax request by given url.

``` yml
action_name:
    type: delete
    link: PROPERTY_WITH_URL  # required
    confirmation: true|false # should confirmation window be shown
```

### Navigate

Performs redirect by given url.

``` yml
action_name:
    type: navigate
    link: PROPERTY_WITH_URL  # required
```

Row click
----------
If you want to configure action that will executes on row click. You have to set `rowAction` param to true.


Control actions on record level
--------------------------------
To manage(show/hide) some actions by condition(dependent on row) developer should to add `action_configuration` option to datagrid configuration.
This option should conain closure  that will return array of actions that have to be shown/hidden.
Key of this should be action name and true/false  value (show/hide respectively)
