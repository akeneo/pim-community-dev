<?php

namespace Pim\Bundle\CatalogBundle\Datagrid;

use Pim\Bundle\GridBundle\Filter\FilterInterface;

use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

use Oro\Bundle\GridBundle\Field\FieldDescription;

use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;

/**
 * Family datagrid manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyDatagridManager extends DatagridManager
{
    /**
     * {@inheritdoc}
     */
    protected function getProperties()
    {
        return array(
            new UrlProperty('edit_link', $this->router, 'pim_catalog_family_edit', array('id')),
            new UrlProperty('delete_llink', $this->router, 'pim_catalog_family_remove', array('id'))
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $this
            ->addCodeField($fieldsCollection)
            ->addLabelField($fieldsCollection)
            ->addAttributeAsLabelField($fieldsCollection);
    }

    /**
     * Create and add field code to field description collection
     *
     * @param FieldDescriptionCollection $fieldsCollection
     *
     * @return \Pim\Bundle\CatalogBundle\Datagrid\FamilyDatagridManager
     */
    protected function addCodeField(FieldDescriptionCollection $fieldsCollection)
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
                'show_filter' => true
            )
        );

        return $this;
    }

    protected function addLabelField(FieldDescriptionCollection $fieldsCollection)
    {
        return $this;
    }

    protected function addAttributeAsLabelField(FieldDescriptionCollection $fieldsCollection)
    {
        return $this;
    }
}
