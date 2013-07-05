<?php

namespace Oro\Bundle\TagBundle\Datagrid;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;

class TagDatagridManager extends DatagridManager
{
    /**
     * {@inheritDoc}
     */
    protected function getProperties()
    {
        return array(
            new UrlProperty('view_link', $this->router, 'oro_tag_view', array('id')),
            new UrlProperty('update_link', $this->router, 'oro_tag_update', array('id')),
            new UrlProperty('delete_link', $this->router, 'oro_api_delete_tag', array('id')),
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldId = new FieldDescription();
        $fieldId->setName('id');
        $fieldId->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                'label'       => $this->translate('ID'),
                'field_name'  => 'id',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'show_column' => false
            )
        );
        $fieldsCollection->add($fieldId);

        $fieldName = new FieldDescription();
        $fieldName->setName('tag');
        $fieldName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('Tag'),
                'field_name'  => 'name',
                'filter_type' => FilterInterface::TYPE_STRING,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldName);

        $fieldName = new FieldDescription();
        $fieldName->setName('usage');
        $fieldName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                'label'       => $this->translate('Usage Count'),
                'field_name'  => 'usage',
                'expression'  => 'usage',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldName);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRowActions()
    {
        $clickAction = array(
            'name'         => 'rowClick',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'oro_tag_grid_and_edit',
            'options'      => array(
                'label'         => $this->translate('View'),
                'link'          => 'view_link',
                'runOnRowClick' => true,
            )
        );

        $viewAction = array(
            'name'         => 'view',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'oro_tag_grid_and_edit',
            'options'      => array(
                'label' => $this->translate('View'),
                'icon'  => 'user',
                'link'  => 'view_link',
            )
        );

        $updateAction = array(
            'name'         => 'update',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'oro_tag_grid_and_edit',
            'options'      => array(
                'label'   => $this->translate('Update'),
                'icon'    => 'edit',
                'link'    => 'update_link',
            )
        );

        $deleteAction = array(
            'name'         => 'delete',
            'type'         => ActionInterface::TYPE_DELETE,
            'acl_resource' => 'oro_tag_delete',
            'options'      => array(
                'label' => $this->translate('Delete'),
                'icon'  => 'trash',
                'link'  => 'delete_link',
            )
        );

        return array($clickAction, $viewAction, $updateAction, $deleteAction);
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareQuery(ProxyQueryInterface $query)
    {
        $alias = $query->getRootAlias();

        $query->leftJoin($alias . '.tagging', 't');
        $query->addSelect($this->getUsageExpression(), true);

        $query->groupBy($alias . '.id');
    }

    /**
     * Add usage count expression
     *
     * @return string
     */
    private function getUsageExpression()
    {
        return 'COUNT(t.id) as usage';
    }
}
