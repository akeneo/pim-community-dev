Actions
-------

Action is an entity that represents grid action in some specific context - for example, row action. Actions are created by Action Factory.

#### Class Description

* **Action / ActionInterface** - basic interface for Action entity;
* **Action / AbstracAction** - abstract implementation of Action entity, includes route processing;
* **Action / RedirectAction** - redirect action implementation;
* **Action / DeleteAction** - delete action implementation;
* **Action / ActionFactoryInterface** - basic interface for Action Factory;
* **Action / ActionFactory** - Action Factory interface implementation to create Action entities.

#### Configuration

**Configuration of Services**

```
parameters:
    oro_grid.action.factory.class:       Oro\Bundle\GridBundle\Action\ActionFactory

services:
    oro_grid.action.factory:
        class:     %oro_grid.action.factory.class%
        arguments: ["@service_container", ~]
```

**Configuration of Action Types**

```
parameters:
    oro_grid.action.type.redirect.class: Oro\Bundle\GridBundle\Action\RedirectAction
    oro_grid.action.type.delete.class:   Oro\Bundle\GridBundle\Action\DeleteAction

services:
    oro_grid.action.type.redirect:
        class: %oro_grid.action.type.redirect.class%
        arguments: ["@oro_security.security_facade"]
        tags:
            - { name: oro_grid.action.type, alias: oro_grid_action_redirect }

    oro_grid.action.type.delete:
        class: %oro_grid.action.type.delete.class%
        arguments: ["@oro_security.security_facade"]
        tags:
            - { name: oro_grid.action.type, alias: oro_grid_action_delete }
```

#### Example of Datagrid Actions

``` php

class UserDatagridManager extends DatagridManager
{
    /**
     * {@inheritdoc}
     */
    protected function getRowActions()
    {
        $clickAction = array(
            'name'         => 'rowClick',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label'         => 'Show',
                'link'          => 'show_link',
                'route'         => 'oro_user_view',
                'runOnRowClick' => true,
            )
        );
        $showAction = array(
            'name'         => 'show',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label' => 'Show',
                'icon'  => 'user',
                'link'  => 'show_link',
            )
        );
        $editAction = array(
            'name'         => 'edit',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label'   => 'Edit',
                'icon'    => 'edit',
                'link'    => 'edit_link',
                'backUrl' => true,
            )
        );
        $deleteAction = array(
            'name'         => 'delete',
            'type'         => ActionInterface::TYPE_DELETE,
            'acl_resource' => 'root',
            'options'      => array(
                'label' => 'Delete',
                'icon'  => 'trash',
                'link'  => 'delete_link',
            )
        );
        return array($clickAction, $showAction, $editAction, $deleteAction);
    }
    // other methods
}
```

#### Control actions on record level
To manage(show/hide) some actions by condition(dependent on row) developer should to add ActionConfigurationProperty to datagrid.
This property needs closure as required param that will return array of actions that have to be shown/hidden.
Key of this should be action name and true/false  value (show/hide respectively)

#### Example

``` php
    protected function getProperties()
    {
        return array(
            //... Some properties ...
            new ActionConfigurationProperty(
                function (ResultRecordInterface $record) {
                    if ($record->getValue('someField') == true) {
                        // do not render delete action if row field someField equals true
                        return array('delete' => false);
                    }
                }
            )
        );
    }
```

### Mass actions

#### Class Description
* ** MassAction / Ajax / AjaxMassAction ** - ajax action implementation (confirmation enabled by default)
* ** MassAction / Ajax / DeleteMassAction ** - ajax delete action implementation
* ** MassAction /Redirect / RedirectMassAction ** - redirects to route with checked rows ids (GET method)
* ** MassAction / Widget / WidgetMassAction ** - basic widget mass action implementation open specifed widget with checked rows ids (GET method)
* ** MassAction / Widget / WindowMassAction ** - open window widget with specific url and with checked rows ids (GET method)


#### Examples

``` php
    /**
     * {@inheritDoc}
     */
    protected function getMassActions()
    {
        $deleteMassAction = new DeleteMassAction(
            array(
                'name'         => 'delete',
                'acl_resource' => 'oro_user_user_delete',
                'label'        => $this->translate('orocrm.contact.datagrid.delete'),
                'icon'         => 'trash',
            )
        );

        $redirectMassAction = new RedirectMassAction(
            array(
                'name'             => 'redirect',
                'acl_resource'     => 'oro_user_user_delete',
                'label'            => 'Redirect',
                'route'            => 'oro_user_view',
                'route_parameters' => array('id' => 1)
            )
        );

        $windowMassAction = new WindowMassAction(
            array(
                'name'             => 'window',
                'label'            => 'Window',
                'acl_resource'     => 'oro_user_user_delete',
                'route'            => 'oro_user_view',
                'route_parameters' => array('id' => 1),
            )
        );

        return array($deleteMassAction, $redirectMassAction, $windowMassAction);
    }
```

** NOTE: **  _All ajax massaction performed via OroGridBundle:MassActionController using specified handlers (ref: DeleteMassAction).
 Developer should specify 'handler' options contains service id that should
 handle current mass action and implements MassActionHandlerInterface_
