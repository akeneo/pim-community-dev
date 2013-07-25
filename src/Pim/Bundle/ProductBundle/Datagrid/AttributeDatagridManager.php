<?php
namespace Pim\Bundle\ProductBundle\Datagrid;

use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Property\FieldProperty;
use Oro\Bundle\GridBundle\Property\UrlProperty;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;
use Pim\Bundle\ProductBundle\Manager\ProductManager;
use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;

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
        $fieldId = new FieldDescription();
        $fieldId->setName('id');
        $fieldId->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                'required'    => true,
            )
        );

        return array(
            new FieldProperty($fieldId),
            new UrlProperty('edit_link', $this->router, 'pim_product_productattribute_edit', array('id')),
            new UrlProperty('delete_link', $this->router, 'pim_product_productattribute_remove', array('id')),
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
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
     * Create a group field and filter
     *
     * @return \Oro\Bundle\GridBundle\Field\FieldDescription
     */
    protected function createGroupField()
    {
        $em = $this->productManager->getStorageManager();
        $groups = $em->getRepository('PimProductBundle:AttributeGroup')->findAllWithTranslations();
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
        $clickAction = array(
            'name'         => 'rowClick',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label'         => $this->translate('Edit'),
                'icon'          => 'edit',
                'link'          => 'edit_link',
                'runOnRowClick' => true,
                'backUrl'       => true
            )
        );

        $editAction = array(
            'name'         => 'edit',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label'   => $this->translate('Edit'),
                'icon'    => 'edit',
                'link'    => 'edit_link',
                'backUrl' => true
            )
        );

        $deleteAction = array(
            'name'         => 'delete',
            'type'         => ActionInterface::TYPE_DELETE,
            'acl_resource' => 'root',
            'options'      => array(
                'label'   => $this->translate('Delete'),
                'icon'    => 'trash',
                'link'    => 'delete_link',
                'backUrl' => true
            )
        );

        return array($clickAction, $editAction, $deleteAction);
    }

    /**
     * Create attribute type field description for datagrid
     *
     * @return \Oro\Bundle\GridBundle\Field\FieldDescription
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
                'field_options' => array('choices' => $this->getAttributeTypeFieldOptions()),
            )
        );
        $templateProperty = new TwigTemplateProperty($field, 'PimProductBundle:ProductAttribute:_field-type.html.twig');
        $field->setProperty($templateProperty);

        return $field;
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
        $fieldOptions = array_combine($attributeTypes, $attributeTypes);
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
            ->from('PimProductBundle:ProductAttribute', 'attribute')
            ->addSelect('translation')
            ->leftJoin('attribute.translations', 'translation', 'with', 'translation.locale = :locale')
            ->setParameter('locale', TranslatableInterface::FALLBACK_LOCALE);
        $this->queryFactory->setQueryBuilder($queryBuilder);
        $query = $this->queryFactory->createQuery();

        return $query;
    }
}
