<?php

namespace Oro\Bundle\TagBundle\Datagrid;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;

use Oro\Bundle\TagBundle\Entity\Tag;

class TagDatagridManager extends FlexibleDatagridManager
{
    /**
     * {@inheritDoc}
     */
    protected function getProperties()
    {
        return array(
            new UrlProperty('view_link', $this->router, 'oro_tag_view', array('id')),
            new UrlProperty('update_link', $this->router, 'oro_tag_update', array('id')),
            new UrlProperty('delete_link', $this->router, 'oro_tag_delete', array('id')),
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
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

        $fieldScope = new FieldDescription();
        $fieldScope->setName('scope');
        $fieldScope->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_OPTIONS,
                'label'       => $this->translate('Scope'),
                'field_name'  => 'scope',
                'filter_type' => FilterInterface::TYPE_CHOICE,
                'required'    => false,
                'expression'  => 'scope',
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
                'choices'     => array(
//                    Tag::SCOPE_GLOBAL => $this->translate('Global'),
//                    Tag::SCOPE_USER   => $this->translate('User')
                ),
                'multiple'    => false,
            )
        );
        $fieldsCollection->add($fieldScope);
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

//    /**
//     * {@inheritDoc}
//     */
//    protected function prepareQuery(ProxyQueryInterface $query)
//    {
//        /** @var $query QueryBuilder */
//        $query->addSelect($this->getScopeExpression(), true);
//    }
//
//    /**
//     * Transform data from DB to expected option
//     *
//     * @return string
//     */
//    private function getScopeExpression()
//    {
//        return 'CASE WHEN (o.user IS NOT NULL) THEN ' . Tag::SCOPE_USER . ' ELSE ' . Tag::SCOPE_GLOBAL . ' as scope';
//    }
}
