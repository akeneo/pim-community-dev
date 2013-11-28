Datagrid Managers
-----------------

Datagrid Managers provides inner interface for developer to work with grid. They receive dependencies through setter methods, store configuration and build datagrid entity.

Datagrid Manager works with regular Doctrine entities and flat arrays as source data.

#### Class Description

* **Datagrid \ DatagridManagerInterface** - general interface for all Datagrid Managers, provides setter method to inject dependencies through Symfony Container;
* **Datagrid \ DatagridManager** - abstract Datagrid Manager which implements basic method to get Datagrid, contains methods to specify grid configuration;

#### Configuration

Following example shows configuration of two datagrid managers.

```
parameters:
    acme_demo_grid.user_grid.manager.class: Acme\Bundle\DemoGridBundle\Datagrid\UserDatagridManager
    acme_demo_grid.product_grid.manager.class: Acme\Bundle\DemoGridBundle\Datagrid\ProductDatagridManager

services:
    acme_demo_grid.user_grid.manager:
        class: %acme_demo_grid.user_grid.manager.class%
        tags:
            - name: oro_grid.datagrid.manager
              datagrid_name: users
              entity_name: Oro\Bundle\UserBundle\Entity\User
              entity_hint: users
              route_name: acme_demo_gridbundle_user_list

    acme_demo_grid.product_grid.manager:
        class: %acme_demo_grid.product_grid.manager.class%
        tags:
            - name: oro_grid.datagrid.manager
              datagrid_name: product
              entity_hint: products
              route_name: acme_demo_gridbundle_product_list
```

#### Code Example

Following example shows simple Datagrid Manager with two fields, filters, sorters and row action.

``` php
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;

class ProductDatagridManager extends DatagridManager
{
    /**
     * @return array
     */
    protected function getProperties()
    {
        return array(
            new UrlProperty('edit_link', $this->router, 'oro_product_edit', array('id'))
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldManufacturerId = new FieldDescription();
        $fieldManufacturerId->setName('id');
        $fieldManufacturerId->setOptions(
            array(
                'type'         => FieldDescriptionInterface::TYPE_INTEGER,
                'label'        => 'ID',
                'entity_alias' => 'm',
                'field_name'   => 'id',
                'filter_type'  => FilterInterface::TYPE_NUMBER,
                'required'     => false,
                'sortable'     => true,
                'filterable'   => true,
                'show_filter'  => true,
            )
        );
        $fieldsCollection->add($fieldManufacturerId);

        $fieldManufacturerName = new FieldDescription();
        $fieldManufacturerName->setName('name');
        $fieldManufacturerName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'        => 'Name',
                'entity_alias' => 'm',
                'field_name'   => 'name',
                'filter_type'  => FilterInterface::TYPE_STRING,
                'required'     => false,
                'sortable'     => true,
                'filterable'   => true,
                'show_filter'  => true,
            )
        );
        $fieldsCollection->add($fieldManufacturerName);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRowActions()
    {
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
        return array($editAction);
    }
}
```
