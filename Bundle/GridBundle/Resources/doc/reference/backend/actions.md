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
        arguments: ["@oro_user.acl_manager"]
        tags:
            - { name: oro_grid.action.type, alias: oro_grid_action_redirect }

    oro_grid.action.type.delete:
        class: %oro_grid.action.type.delete.class%
        arguments: ["@oro_user.acl_manager"]
        tags:
            - { name: oro_grid.action.type, alias: oro_grid_action_delete }
```

#### Example of Datagrid Actions

``` php

class UserDatagridManager extends FlexibleDatagridManager
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
