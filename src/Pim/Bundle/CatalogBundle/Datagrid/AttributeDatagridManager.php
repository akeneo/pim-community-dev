<?php

namespace Pim\Bundle\CatalogBundle\Datagrid;

use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
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
     * get properties
     * @return array
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
        $field->setName('Code');
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('Code'),
                'field_name'   => 'code',
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
                'entity_alias' => 'translation',
                'field_name'   => 'label',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($field);

        $field = $this->createAttributeTypeField();
        $fieldsCollection->add($field);

        $field = new FieldDescription();
        $field->setName('scopable');
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_BOOLEAN,
                'label'       => $this->translate('Scopable'),
                'field_name'  => 'scopable',
                'filter_type' => FilterInterface::TYPE_BOOLEAN,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
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
     * @inheritdoc
     */
    public function getIdentifierField()
    {
        return 'id';
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
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('Type'),
                'field_name'  => 'attributeType',
                'filter_type' => FilterInterface::TYPE_CHOICE,
                'required'    => false,
                'sortable'    => false,
                'filterable'  => true,
                'show_filter' => true,
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
            $choices[$group->getId()] = $group->getName();
        }
        asort($choices);

        $field = new FieldDescription();
        $field->setName('group');
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_OPTIONS,
                'label'       => $this->translate('Group'),
                'field_name'  => 'group',
                'filter_type' => FilterInterface::TYPE_CHOICE,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
                'field_options' => array('choices' => $choices),
            )
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
                'label'   => $this->translate('Delete'),
                'icon'    => 'trash',
                'link'    => 'delete_link'
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
    protected function createQuery()
    {
        $queryBuilder = $this->productManager->getStorageManager()->createQueryBuilder();
        $queryBuilder
            ->select('attribute')
            ->from('PimCatalogBundle:ProductAttribute', 'attribute')
            ->addSelect('translation')
            ->leftJoin('attribute.translations', 'translation', 'with', 'translation.locale = :locale')
            ->setParameter('locale', $this->productManager->getLocale());
        $this->queryFactory->setQueryBuilder($queryBuilder);
        $query = $this->queryFactory->createQuery();

        return $query;
    }
}
