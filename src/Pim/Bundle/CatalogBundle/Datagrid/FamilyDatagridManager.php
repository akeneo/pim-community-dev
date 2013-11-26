<?php

namespace Pim\Bundle\CatalogBundle\Datagrid;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;

use Oro\Bundle\GridBundle\Property\UrlProperty;

use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;

use Pim\Bundle\CatalogBundle\Manager\LocaleManager;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

use Pim\Bundle\GridBundle\Action\ActionInterface;

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
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * @var ProductManager
     */
    protected $productManager;

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
            ->createCodeField($fieldsCollection)
            ->createLabelField($fieldsCollection)
            ->createAttributeAsLabelField($fieldsCollection);
    }

    /**
     * Create and add code to field description collection
     *
     * @param FieldDescriptionCollection $fieldsCollection
     *
     * @return \Pim\Bundle\CatalogBundle\Datagrid\FamilyDatagridManager
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
                'show_filter' => true
            )
        );

        $fieldsCollection->add($field);

        return $this;
    }

    /**
     * Create and add label to field description collection
     *
     * @param FieldDescriptionCollection $fieldsCollection
     *
     * @return \Pim\Bundle\CatalogBundle\Datagrid\FamilyDatagridManager
     */
    protected function createLabelField(FieldDescriptionCollection $fieldsCollection)
    {
        $field = new FieldDescription();
        $field->setName('label');
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('Label'),
                'field_name'  => 'familyLabel',
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

        return $this;
    }

    /**
     * Create and add attribute as label to field description collection
     *
     * @param FieldDescriptionCollection $fieldsCollection
     *
     * @return \Pim\Bundle\CatalogBundle\Datagrid\FamilyDatagridManager
     */
    protected function createAttributeAsLabelField(FieldDescriptionCollection $fieldsCollection)
    {
        $choices = $this
            ->productManager
            ->getAttributeRepository()
            ->getAvailableAttributesAsLabelChoice();

        $field = new FieldDescription();
        $field->setName('attributeAsLabel');
        $field->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_TEXT,
                'label'           => $this->translate('Attribute as label'),
                'field_name'      => 'attributeAsLabel',
                'filter_type'     => FilterInterface::TYPE_CHOICE,
                'required'        => false,
                'sortable'        => true,
                'filterable'      => true,
                'show_filter'     => true,
                'field_options'   => array('choices' => $choices),
                'filter_by_where' => true,
                'multiple'        => true
            )
        );

        $fieldsCollection->add($field);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRowActions()
    {
        $editAction = array(
            'name'         => 'edit',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'pim_catalog_family_edit',
            'options'      => array(
                'label' => $this->translate('Edit'),
                'icon'  => 'edit',
                'link'  => 'edit_link'
            )
        );

        $clickAction = $editAction;
        $clickAction['name'] = 'rowClick';
        $clicAction['options']['runOnRowClick'] = true;

        $deleteAction = array(
            'name'         => 'delete',
            'type'         => ActionInterface::TYPE_DELETE,
            'acl_resource' => 'pim_catalog_family_remove',
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
            ->select('f')
            ->from('PimCatalogBundle:Family', 'f');

        $familyLabelExpr = "(CASE WHEN translation.label IS NULL THEN f.code ELSE translation.label END)";

        $proxyQuery
            ->addSelect(sprintf("%s AS familyLabel", $familyLabelExpr), true)
            ->addSelect('translation.label', true);

        $proxyQuery
            ->leftJoin('f.translations', 'translation', 'WITH', 'translation.locale = :localeCode')
            ->leftJoin('f.attributeAsLabel', 'a');

        $proxyQuery->setParameter('localeCode', $this->getCurrentLocale());
    }

    /**
     * Set the locale manager
     *
     * @param LocaleManager $localeManager
     *
     * @return \Pim\Bundle\CatalogBundle\Datagrid\FamilyDatagridManager
     */
    public function setLocaleManager(LocaleManager $localeManager)
    {
        $this->localeManager = $localeManager;

        return $this;
    }

    /**
     * Set the product manager
     *
     * @param ProductManager $productManager
     *
     * @return \Pim\Bundle\CatalogBundle\Datagrid\FamilyDatagridManager
     */
    public function setProductManager(ProductManager $productManager)
    {
        $this->productManager = $productManager;

        return $this;
    }

    /**
     * Get the current locale code from the locale manager
     *
     * @return string
     */
    protected function getCurrentLocale()
    {
        return $this->localeManager->getUserLocale()->getCode();
    }
}
