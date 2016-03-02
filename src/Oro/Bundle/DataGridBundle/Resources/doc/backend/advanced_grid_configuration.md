Advanced grid configuration
===========================

This page contains basic examples of advanced datagrid configuration. More detailed explanation for each extension could be found [here](./extensions.md)


## Problems and solutions

#### Problem:
Datagrid should show data dependent on some param
For example "_grid should show users for group that currently editing_"
#### Solution:
Macros that renders datagrid could retrieve parameters that will be used for generating URL for data retrieving.

Example:
``` twig
[dataGrid.renderGrid(gridName, {groupId: entityId})]
```
This param will be passed to request and could be applied to datasource in build.after event listener. There is base implementation of listener in Datagrid bundle, so just need to configure it as service

``` yml
    some.service.name:
        class: %oro_datagrid.event_listener.base_orm_relation.class%
        arguments:
          - groupId # param name
          - @oro_datagrid.datagrid.request_params
          - false   # edit mode disabled
        tags:
          - { name: kernel.event_listener, event: oro_datagrid.datgrid.build.after.GRID_NAME, method: onBuildAfter }
```
#### Problem:
Let's take previous problem, but in additional we need to fill some form field dependent on grid state.
For example "_grid should show users for group that currently editing and user should be able to add/remove users from group_"
#### Solution:
For solving this problem we have to modify query. We'll add additional field that will show value of "assigned state".
``` yml
datagrid:
    acme-demo-grid:
        source:
            type: orm
            query:
                select:
                    - u.id
                    - u.username
                    - >
                        (CASE WHEN (:groupId IS NOT NULL) THEN
                              CASE WHEN (:groupId
                                     MEMBER OF u.groups OR u.id IN (:data_in)) AND u.id NOT IN (:data_not_in)
                              THEN true ELSE false END
                         ELSE
                              CASE WHEN u.id IN (:data_in) AND u.id NOT IN (:data_not_in)
                              THEN true ELSE false END
                         END) as isAssigned
                from:
                    { table: AcmeDemoBundle:User, alias:u }
        columns:
            isAssigned: # column has name correspond to data_name
                label: Assigned
                frontend_type: boolean
                editable: true # put cell in editable mod
            username:
                label: Username
        properties:
            id: ~  # Identifier property must be passed to frontend
```

When this done we have to create form fields that wil contain assigned/removed user ids and process it on backend
For example fields are:
``` twig
    form_widget(form.appendUsers, {'id': 'groupAppendUsers'}),
    form_widget(form.removeUsers, {'id': 'groupRemoveUsers'}),

```

Last step: need to register column listener that will fill values of those fields:
``` yml
datagrid:
    acme-demo-grid:
        ... # previous configuration
        options:
            entityHint: account
            requireJSModules:
              - oro/datagrid/column-form-listener
            columnListener:
                dataField: id
                columnName: isAssigned    # frontend column name
                selectors:
                    included: '#groupAppendUsers'  # field selectors
                    excluded: '#groupRemoveUsers'
```

#### Problem:
*I'm developing some extension for grid, how can I add my frontend builder (some class that should show my widget) ?*
#### Solution:
Any builders could be passed under gridconfig[options][requireJSModules] node. Your builder should have method `init`, it will be called when grid-builder finish building grid.

Example:
``` yml
datagrid:
    acme-demo-grid:
        ... # some configuration
        options:
            requireJSModules:
              - your/builder/amd/module/name
```

#### Problem:
*I'm developing grid that should be shown in modal window, so I don't need "grid state url"*
#### Solution:
Grid states processed using Backbone.Router, and it could be easily disabled in configuration by setting `routerEnabled` option to false.

Example:
``` yml
datagrid:
    acme-demo-grid:
        ... # some configuration
        options:
            routerEnabled: false
```