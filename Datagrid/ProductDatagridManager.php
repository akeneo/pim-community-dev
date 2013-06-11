<?php
namespace Pim\Bundle\ProductBundle\Datagrid;

use Pim\Bundle\ConfigBundle\Manager\CurrencyManager;

use Oro\Bundle\GridBundle\Property\FixedProperty;

use Pim\Bundle\ConfigBundle\Manager\ChannelManager;
use Pim\Bundle\ConfigBundle\Manager\LocaleManager;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\GridBundle\Property\FieldProperty;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Pim\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

/**
 * Grid manager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductDatagridManager extends FlexibleDatagridManager
{

    /**
     * @staticvar string
     */
    const LOCALE_FIELD_NAME = 'locale';

    /**
     * @staticvar string
     */
    const SCOPE_FIELD_NAME  = 'scope';

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
                'type'     => FieldDescriptionInterface::TYPE_INTEGER,
                'required' => true,
            )
        );

        return array(
            new FieldProperty($fieldId),
            new UrlProperty(
                'edit_link',
                $this->router,
                'pim_product_product_edit',
                array('id', 'dataLocale' => self::LOCALE_FIELD_NAME)
            ),
            new UrlProperty('delete_link', $this->router, 'pim_product_product_remove', array('id')),
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldSku = new FieldDescription();
        $fieldSku->setName('sku');
        $fieldSku->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translator->trans('Sku'),
                'field_name'  => 'sku',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldSku);

        $field = $this->createCurrencyField();
        $fieldsCollection->add($field);

        // TODO : until we'll have related backend type in grid bundle
        $excludedBackend = array(
            AbstractAttributeType::BACKEND_TYPE_MEDIA,
            AbstractAttributeType::BACKEND_TYPE_METRIC,
            AbstractAttributeType::BACKEND_TYPE_PRICE,
            'prices'
        );

        foreach ($this->getFlexibleAttributes() as $attribute) {
            $backendType = $attribute->getBackendType();
            if (in_array($backendType, $excludedBackend)) {
                continue;
            }

            if (!$attribute->getUseableAsGridColumn()) {
                continue;
            }

            $attributeType = $this->convertFlexibleTypeToFieldType($backendType);
            $filterType    = $this->convertFlexibleTypeToFilterType($backendType);

            $field = new FieldDescription();
            $field->setName($attribute->getCode());
            $field->setOptions(
                array(
                    'type'          => $attributeType,
                    'label'         => $attribute->getLabel(),
                    'field_name'    => $attribute->getCode(),
                    'filter_type'   => $filterType,
                    'required'      => false,
                    'sortable'      => true,
                    'filterable'    => $attribute->getUseableAsGridFilter(),
                    'flexible_name' => $this->flexibleManager->getFlexibleName(),
                    'show_filter'   => $attribute->getUseableAsGridFilter()
                )
            );

            if ($attributeType == FieldDescriptionInterface::TYPE_OPTIONS) {
                $field->setOption('multiple', true);
            }

            if (!$attribute->getUseableAsGridFilter()) {
                $field->setOption('filter_type', false);
                $field->setOption('filterable', false);
            }

            $fieldsCollection->add($field);
        }

        $field = $this->createLocaleField();
        $fieldsCollection->add($field);

        $field = $this->createScopeField();
        $fieldsCollection->add($field);

        $field = $this->createCategoryField();
        $fieldsCollection->add($field);

        $field = $this->createFamilyField();
        $fieldsCollection->add($field);
    }

    /**
     * Create a currency field and filter
     *
     * @return \Oro\Bundle\GridBundle\Field\FieldDescription
     */
    protected function createCurrencyField()
    {
        $field = new FieldDescription();
        $field->setName('currency');
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DECIMAL,
                'label'       => $this->translator->trans('Currency'),
                'field_name'  => 'currency',
                'filter_type' => FilterInterface::TYPE_CURRENCY,
                'required'    => false,
                'sortable'    => true,
                'show_column' => false,
                'filterable'  => true,
                'show_filter' => true
            )
        );

        return $field;
    }

    /**
     * Create a family field and filter
     *
     * @return \Oro\Bundle\GridBundle\Field\FieldDescription
     */
    protected function createFamilyField()
    {
        // get families
        $em = $this->flexibleManager->getStorageManager();
        $families = $em->getRepository('PimProductBundle:ProductFamily')->findAll();
        $choices = array();
        foreach ($families as $family) {
            $choices[$family->getId()] = $family->getLabel();
        }

        $field = new FieldDescription();
        $field->setName('family');
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translator->trans('Family'),
                'field_name'  => 'productFamily',
                'filter_type' => FilterInterface::TYPE_CHOICE,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
                'field_options' => array(
                    'choices'  => $choices,
                    'multiple' => true
                ),
            )
        );

        return $field;
    }

    /**
     * Create a category field
     *
     * @return \Oro\Bundle\GridBundle\Field\FieldDescription
     */
    protected function createCategoryField()
    {
        // get categories
        $em = $this->flexibleManager->getStorageManager();
        $categories = $em->getRepository('PimProductBundle:Category')->findAll();
        $choices = array();
        foreach ($categories as $category) {
            $choices[$category->getId()] = $category->getTitle();
        }

        $field = new FieldDescription();
        $field->setName('categories');
        $field->setProperty(new FixedProperty('categories', 'categoryTitlesAsString'));
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_OPTIONS,
                'label'       => $this->translator->trans('Categories'),
                'field_name'  => 'categories',
                'filter_type' => FilterInterface::TYPE_ENTITY,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
                'field_options' => array(
                    'choices' => $choices,
                ),
            )
        );

        return $field;
    }

    /**
     * Create locale field description for datagrid
     *
     * @return \Oro\Bundle\GridBundle\Field\FieldDescription
     */
    protected function createLocaleField()
    {
        $field = new FieldDescription();
        $field->setName(self::LOCALE_FIELD_NAME);
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_OPTIONS,
                'label'       => $this->translator->trans('Data Locale'),
                'field_name'  => 'data_locale',
                'filter_type' => FilterInterface::TYPE_LOCALE,
                'required'    => false,
                'filterable'  => true,
                'show_column' => false,
                'show_filter' => true
            )
        );

        return $field;
    }

    /**
     * Create scope field description for datagrid
     *
     * @return \Oro\Bundle\GridBundle\Field\FieldDescription
     */
    protected function createScopeField()
    {
        $field = new FieldDescription();
        $field->setName(self::SCOPE_FIELD_NAME);
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_OPTIONS,
                'label'       => $this->translator->trans('Scope'),
                'field_name'  => 'scope',
                'filter_type' => FilterInterface::TYPE_SCOPE,
                'required'    => false,
                'filterable'  => true,
                'show_column' => false,
                'show_filter' => true
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
                'label'         => $this->translator->trans('Edit'),
                'icon'          => 'edit',
                'link'          => 'edit_link',
                'backUrl'       => true,
                'runOnRowClick' => true
            )
        );

        $editAction = array(
            'name'         => 'edit',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label'   => $this->translator->trans('Edit'),
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
                'label'   => $this->translator->trans('Delete'),
                'icon'    => 'trash',
                'link'    => 'delete_link'
            )
        );

        return array($clickAction, $editAction, $deleteAction);
    }

    /**
     * {@inheritdoc}
     */
    public function setFlexibleManager(FlexibleManager $flexibleManager)
    {
        $this->flexibleManager = $flexibleManager;

        $this->flexibleManager->setLocale($this->getLocaleFilterValue());
        $this->flexibleManager->setScope($this->getScopeFilterValue());
    }

    /**
     * Get data locale value from parameters
     *
     * @return string
     */
    public function getLocaleFilterValue()
    {
        $filtersArray = $this->parameters->get(ParametersInterface::FILTER_PARAMETERS);
        if (isset($filtersArray[self::LOCALE_FIELD_NAME]) && isset($filtersArray[self::LOCALE_FIELD_NAME]['value'])) {
            $dataLocale = $filtersArray[self::LOCALE_FIELD_NAME]['value'];
        } else {
            $dataLocale = $this->flexibleManager->getLocale();
        }

        return $dataLocale;
    }

    /**
     * Get scope value from parameters
     *
     * @return string
     */
    public function getScopeFilterValue()
    {
        $filtersArray = $this->parameters->get(ParametersInterface::FILTER_PARAMETERS);
        if (isset($filtersArray[self::SCOPE_FIELD_NAME]) && isset($filtersArray[self::SCOPE_FIELD_NAME]['value'])) {
            $dataScope = $filtersArray[self::SCOPE_FIELD_NAME]['value'];
        } else {
            $dataScope = $this->flexibleManager->getScope();
        }

        return $dataScope;
    }
}
