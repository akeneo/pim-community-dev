<?php

namespace Pim\Bundle\CatalogBundle\Datagrid;

use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;

/**
 * Product attribute grid manager
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeDatagridManager extends DatagridManager
{
    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * @param ProductManager $manager
     */
    public function setProductManager(ProductManager $manager)
    {
        $this->productManager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    protected function getProperties()
    {
        return array(
            new UrlProperty('edit_link', $this->router, 'pim_catalog_productattribute_edit', array('id')),
            new UrlProperty('delete_link', $this->router, 'pim_catalog_productattribute_remove', array('id')),
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
                'field_name'  => 'attributeLabel',
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

        $field = $this->createAttributeTypeField();
        $fieldsCollection->add($field);

        $field = new FieldDescription();
        $field->setName('scopable');
        $field->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_BOOLEAN,
                'label'           => $this->translate('Scopable'),
                'field_name'      => 'scopable',
                'filter_type'     => FilterInterface::TYPE_BOOLEAN,
                'required'        => false,
                'sortable'        => true,
                'filterable'      => true,
                'show_filter'     => true,
                'filter_by_where' => true
            )
        );
        $fieldsCollection->add($field);

        $field = new FieldDescription();
        $field->setName('translatable');
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_BOOLEAN,
                'label'       => $this->translate('Localizable'),
                'field_name'  => 'translatable',
                'filter_type' => FilterInterface::TYPE_BOOLEAN,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($field);

        $field = $this->createGroupField();
        $fieldsCollection->add($field);
    }

    /**
     * Create attribute type field description for datagrid
     *
     * @return FieldDescription
     */
    protected function createAttributeTypeField()
    {
        $field = new FieldDescription();
        $field->setName('attributeType');
        $field->setOptions(
            array(
                'type'          => FieldDescriptionInterface::TYPE_TEXT,
                'label'         => $this->translate('Type'),
                'field_name'    => 'attributeType',
                'filter_type'   => FilterInterface::TYPE_CHOICE,
                'required'      => false,
                'sortable'      => false,
                'filterable'    => true,
                'show_filter'   => true,
                'field_options' => array('choices' => $this->getAttributeTypeFieldOptions(), 'multiple' => true),
            )
        );
        $templateProperty = new TwigTemplateProperty($field, 'PimCatalogBundle:ProductAttribute:_field-type.html.twig');
        $field->setProperty($templateProperty);

        return $field;
    }

    /**
     * Create a group field and filter
     *
     * @return FieldDescription
     */
    protected function createGroupField()
    {
        $em = $this->productManager->getStorageManager();
        $groups = $em->getRepository('PimCatalogBundle:AttributeGroup')->findAllWithTranslations();
        $choices = array();
        foreach ($groups as $group) {
            $choices[$group->getId()] = $group->getLabel();
        }
        asort($choices);

        $field = new FieldDescription();
        $field->setName('group');
        $field->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_HTML,
                'label'           => $this->translate('Group'),
                'field_name'      => 'groupLabel',
                'expression'      => 'attributeGroup.id',
                'filter_type'     => FilterInterface::TYPE_CHOICE,
                'sortable'        => true,
                'filterable'      => true,
                'show_filter'     => true,
                'field_options'   => array('choices' => $choices),
                'filter_by_where' => true
            )
        );

        $field->setProperty(
            new TwigTemplateProperty($field, 'PimGridBundle:Rendering:_toString.html.twig')
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
            'acl_resource' => 'pim_catalog_attribute_edit',
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
            'acl_resource' => 'pim_catalog_attribute_remove',
            'options'      => array(
                'label' => $this->translate('Delete'),
                'icon'  => 'trash',
                'link'  => 'delete_link'
            )
        );

        return array($clickAction, $editAction, $deleteAction);
    }

    /**
     * Get translated attribute types
     *
     * @return array
     */
    protected function getAttributeTypeFieldOptions()
    {
        $translator = $this->translator;
        $attributeTypes = $this->productManager->getAttributeTypes();
        $fieldOptions = empty($attributeTypes) ? array() : array_combine($attributeTypes, $attributeTypes);
        $fieldOptions = array_map(
            function ($type) use ($translator) {
                return $translator->trans($type);
            },
            $fieldOptions
        );
        asort($fieldOptions);

        return $fieldOptions;
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
        $groupExpr = "(CASE WHEN gt.label IS NULL THEN attributeGroup.code ELSE gt.label END)";

        $proxyQuery
            ->addSelect($rootAlias)
            ->addSelect(sprintf("%s AS attributeLabel", $labelExpr), true)
            ->addSelect(sprintf("%s AS groupLabel", $groupExpr), true)
            ->addSelect('translation.label', true);

        $proxyQuery
            ->leftJoin($rootAlias .'.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->leftJoin($rootAlias .'.group', 'attributeGroup')
            ->leftJoin('attributeGroup.translations', 'gt', 'WITH', 'gt.locale = :locale');

        $proxyQuery->setParameter('locale', $this->productManager->getLocale());
    }
}
