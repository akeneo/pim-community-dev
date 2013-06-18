<?php
namespace Pim\Bundle\ProductBundle\Datagrid;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

use Pim\Bundle\GridBundle\Property\CurrencyProperty;

use Oro\Bundle\GridBundle\Property\FixedProperty;
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
     * Define constructor to add new price type
     */
    public function __construct()
    {
        self::$typeMatches['prices'] = array(
            'field'  => FieldDescriptionInterface::TYPE_OPTIONS,
            'filter' => FilterInterface::TYPE_CURRENCY
        );
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
        // TODO : until we'll have related backend type in grid bundle
        $excludedBackend = array(
            AbstractAttributeType::BACKEND_TYPE_MEDIA
        );
        // create flexible columns
        foreach ($this->getFlexibleAttributes() as $attribute) {
            $backendType = $attribute->getBackendType();
            if (in_array($backendType, $excludedBackend)) {
                continue;
            }

            if (!$attribute->getUseableAsGridColumn() && !$attribute->getUseableAsGridFilter()) {
                continue;
            }

            $field = $this->createFlexibleField($attribute);
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
     * {@inheritdoc}
     */
    protected function getFlexibleFieldOptions(AbstractAttribute $attribute, array $options = array())
    {
        $result = parent::getFlexibleFieldOptions($attribute, $options);

        $result['filterable'] = $attribute->getUseableAsGridFilter();
        $result['show_filter'] = $attribute->getUseableAsGridFilter();
        $result['show_column'] = $attribute->getUseableAsGridColumn();

        return $result;
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
                'label'       => $this->translate('Family'),
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
                'label'       => $this->translate('Categories'),
                'field_name'  => 'categories',
                'filter_type' => FilterInterface::TYPE_CATEGORY,
                'required'    => false,
                'sortable'    => false, //TODO To enable when PIM-603 is fixed
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
                'label'       => $this->translate('Data Locale'),
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
                'label'       => $this->translate('Scope'),
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
                'label'         => $this->translate('Edit'),
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
    protected function getLocaleFilterValue()
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
    protected function getScopeFilterValue()
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
