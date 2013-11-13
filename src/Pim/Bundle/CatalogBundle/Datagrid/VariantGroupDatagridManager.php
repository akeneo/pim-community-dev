<?php

namespace Pim\Bundle\CatalogBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;
use Oro\Bundle\GridBundle\Property\UrlProperty;

use Pim\Bundle\GridBundle\Filter\FilterInterface;

/**
 * Variant group datagrid manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupDatagridManager extends GroupDatagridManager
{
    /**
     * {@inheritdoc}
     */
    protected function getProperties()
    {
        return array(
            new UrlProperty('edit_link', $this->router, 'pim_catalog_variant_group_edit', array('id')),
            new UrlProperty('delete_link', $this->router, 'pim_catalog_variant_group_remove', array('id'))
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        parent::configureFields($fieldsCollection);

        $this->createAxisField($fieldsCollection);
    }

    /**
     * {@inheritdoc}
     *
     * Override to remove create type field
     */
    protected function createTypeField(FieldDescriptionCollection $fieldsCollection)
    {
    }

    /**
     * Create an axis field
     *
     * @param FieldDescriptionCollection $fieldsCollection
     */
    protected function createAxisField(FieldDescriptionCollection $fieldsCollection)
    {
        $choices = $this->groupManager->getAvailableAxisChoices();

        $field = new FieldDescription();
        $field->setName('attribute');
        $field->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_HTML,
                'label'           => $this->translate('Axis'),
                'field_name'      => 'attributes',
                'expression'      => 'attribute.id',
                'filter_type'     => FilterInterface::TYPE_CHOICE,
                'required'        => true,
                'multiple'        => true,
                'filterable'      => true,
                'show_filter'     => true,
                'field_options'   => array('choices' => $choices),
                'filter_by_where' => true
            )
        );

        $field->setProperty(
            new TwigTemplateProperty($field, 'PimGridBundle:Rendering:_optionsToString.html.twig')
        );

        $fieldsCollection->add($field);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareQuery(ProxyQueryInterface $proxyQuery)
    {
        parent::prepareQuery($proxyQuery);

        $proxyQuery
            ->leftJoin($proxyQuery->getRootAlias() .'.attributes', 'attribute')
            ->groupBy($proxyQuery->getRootAlias());
    }

    /**
     * {@inheritdoc}
     */
    protected function applyJoinOnGroupType(ProxyQueryInterface $proxyQuery)
    {
        $joinExpr = $proxyQuery->expr()->eq('type.code', ':group');
        $proxyQuery
            ->innerJoin($proxyQuery->getRootAlias() .'.type', 'type', 'WITH', $joinExpr)
            ->setParameter('group', 'VARIANT');
    }
}
