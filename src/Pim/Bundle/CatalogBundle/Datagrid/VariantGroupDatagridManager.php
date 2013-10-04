<?php

namespace Pim\Bundle\CatalogBundle\Datagrid;

use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;
use Oro\Bundle\GridBundle\Property\UrlProperty;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Manager\VariantGroupManager;

/**
 * Variant group datagrid manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupDatagridManager extends DatagridManager
{
    /**
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * @var VariantGroupManager
     */
    protected $variantGroupManager;

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

        $field = new FieldDescription();
        $field->setName('label');
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('Label'),
                'field_name'  => 'variantLabel',
                'expression'  => 'translation.label',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $field->setProperty(
            new TwigTemplateProperty($field, 'PimGridBundle:Rendering:_toString.html.twig')
        );
        $fieldsCollection->add($field);

        $field = $this->createAxisField();
        $fieldsCollection->add($field);
    }

    /**
     * Create an axis field
     *
     * @return \Oro\Bundle\GridBundle\Field\FieldDescription
     */
    protected function createAxisField()
    {
        $choices = $this->variantGroupManager->getAvailableAxisChoices();

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
                'field_options'   => array('choices' => $choices)
            )
        );

        $field->setProperty(
            new TwigTemplateProperty($field, 'PimGridBundle:Rendering:_optionsToString.html.twig')
        );

        return $field;
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
                'label'   => $this->translate('Edit'),
                'icon'    => 'edit',
                'link'    => 'edit_link'
            )
        );

        $clickAction = $editAction;
        $clickAction['name'] = 'rowClick';
        $clickAction['options']['runOnRowClick'] = true;

        $deleteAction = array(
            'name'         => 'delete',
            'type'         => ActionInterface::TYPE_DELETE,
            'acl_resource' => 'root',
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
        $rootAlias = $proxyQuery->getRootAlias();

        $labelExpr = sprintf(
            "(CASE WHEN translation.label IS NULL THEN %s.code ELSE translation.label END)",
            $rootAlias
        );

        $proxyQuery
            ->addSelect($rootAlias)
            ->addSelect(sprintf("%s AS variantLabel", $labelExpr), true)
            ->addSelect('translation.label', true)
            ->addSelect('attribute');

        $proxyQuery
            ->leftJoin($rootAlias .'.translations', 'translation', 'WITH', 'translation.locale = :localeCode')
            ->leftJoin($rootAlias .'.attributes', 'attribute');

        $proxyQuery->setParameter('localeCode', $this->getCurrentLocale());
    }

    /**
     * Set the locale manager
     *
     * @param LocaleManager $localeManager
     *
     * @return VariantGroupDatagridManager
     */
    public function setLocaleManager(LocaleManager $localeManager)
    {
        $this->localeManager = $localeManager;

        return $this;
    }

    /**
     * Set the variant group manager
     *
     * @param VariantGroupManager $variantGroupManager
     *
     * @return \Pim\Bundle\CatalogBundle\Datagrid\VariantGroupDatagridManager
     */
    public function setVariantGroupManager(VariantGroupManager $variantGroupManager)
    {
        $this->variantGroupManager = $variantGroupManager;

        return $this;
    }

    /**
     * Get the current locale code from the locale manager
     *
     * @return string
     */
    protected function getCurrentLocale()
    {
        return $this->localeManager->getUserLocaleCode();
    }
}
