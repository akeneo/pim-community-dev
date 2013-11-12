<?php

namespace Pim\Bundle\CatalogBundle\Datagrid;

use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;

/**
 * Group type datagrid manager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeDatagridManager extends DatagridManager
{
    /**
     * {@inheritdoc}
     */
    protected function getProperties()
    {
        return array(
            new UrlProperty('edit_link', $this->router, 'pim_catalog_group_type_edit', array('id')),
            new UrlProperty('delete_link', $this->router, 'pim_catalog_group_type_remove', array('id'))
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $this->createCodeField($fieldsCollection);
    }

    /**
     * Create code field
     *
     * @param FieldDescriptionCollection $fieldsCollection
     */
    protected function createCodeField(FieldDescriptionCollection $fieldsCollection)
    {
        $field = new FieldDescription();
        $field->setName('code');
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('Code'),
                'field_name'  => 'code',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($field);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRowActions()
    {
        $editAction = array(
            'name'         => 'edit',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'pim_catalog_group_type_edit',
            'options'      => array(
                'label' => $this->translate('Edit'),
                'icon'  => 'edit',
                'link'  => 'edit_link'
            )
        );

        $clickAction = $editAction;
        $clickAction['name'] = 'rowClick';
        $clickAction['options']['runOnRowClick'] = true;

        $deleteAction = array(
            'name'         => 'delete',
            'type'         => ActionInterface::TYPE_DELETE,
            'acl_resource' => 'pim_catalog_group_type_remove',
            'options'      => array(
                'label' => $this->translate('Delete'),
                'icon'  => 'trash',
                'link'  => 'delete_link'
            )
        );

        return array($clickAction, $editAction, $deleteAction);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareQuery(ProxyQueryInterface $proxyQuery)
    {
        $proxyQuery
            ->select('g')
            ->from('PimCatalogBundle:GroupType', 'g');
    }
}

