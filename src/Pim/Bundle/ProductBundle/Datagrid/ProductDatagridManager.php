<?php

namespace Pim\Bundle\ProductBundle\Datagrid;

use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\GridBundle\Property\FieldProperty;
use Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Pim\Bundle\GridBundle\Filter\FilterInterface;
use Pim\Bundle\ProductBundle\Manager\CategoryManager;

/**
 * Grid manager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * @staticvar integer
     */
    const UNCLASSIFIED_CATEGORY = 0;

    /**
     * @var Pim\Bundle\ProductBundle\Manager\CategoryManager
     */
    protected $categoryManager;

    /**
     * Filter by tree id, 0 means not tree selected
     * @var integer
     */
    protected $filterTreeId = self::UNCLASSIFIED_CATEGORY;

    /**
     * Filter by category id, 0 means unclassified
     * @var integer
     */
    protected $filterCategoryId = self::UNCLASSIFIED_CATEGORY;

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
     * Configure the category manager
     * @param CategoryManager $manager
     */
    public function setCategoryManager(CategoryManager $manager)
    {
        $this->categoryManager = $manager;
    }

    /**
     * Define the tree to use to filter the product collection
     *
     * @param integer $treeId
     */
    public function setFilterTreeId($treeId)
    {
        $this->filterTreeId = $treeId;
    }

    /**
     * Define the category to use to filter the product collection
     *
     * @param integer $categoryId
     */
    public function setFilterCategoryId($categoryId)
    {
        $this->filterCategoryId = $categoryId;
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
            new UrlProperty(
                'edit_categories_link',
                $this->router,
                'pim_product_product_edit',
                array('id', 'dataLocale' => self::LOCALE_FIELD_NAME),
                false,
                '#categories'
            ),
            new UrlProperty('delete_link', $this->router, 'pim_product_product_remove', array('id')),
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $field = $this->createScopeField();
        $fieldsCollection->add($field);

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

            if (!$attribute->isUseableAsGridColumn() && !$attribute->isUseableAsGridFilter()) {
                continue;
            }

            $field = $this->createFlexibleField($attribute);
            $fieldsCollection->add($field);
        }

        $field = $this->createFamilyField();
        $fieldsCollection->add($field);

        $field = new FieldDescription();
        $field->setName('enabled');
        $field->setOptions(
            array(
                'type'        => false,
                'label'       => $this->translate('Enabled'),
                'field_name'  => 'enabled',
                'filter_type' => FilterInterface::TYPE_BOOLEAN,
                'required'    => false,
                'sortable'    => false,
                'filterable'  => true,
                'show_filter' => true,
                'show_column' => false
            )
        );
        $fieldsCollection->add($field);

        $fieldCreated = new FieldDescription();
        $fieldCreated->setName('created');
        $fieldCreated->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DATETIME,
                'label'       => $this->translate('Created At'),
                'field_name'  => 'created',
                'filter_type' => FilterInterface::TYPE_DATETIME,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldCreated);

        $fieldUpdated = new FieldDescription();
        $fieldUpdated->setName('updated');
        $fieldUpdated->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DATETIME,
                'label'       => $this->translate('Updated At'),
                'field_name'  => 'updated',
                'filter_type' => FilterInterface::TYPE_DATETIME,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldUpdated);

        $field = $this->createCompletenessField();
        $fieldsCollection->add($field);
    }

    /**
     * {@inheritdoc}
     */
    protected function getFlexibleFieldOptions(AbstractAttribute $attribute, array $options = array())
    {
        $result = parent::getFlexibleFieldOptions($attribute, $options);

        $result['filterable'] = $attribute->isUseableAsGridFilter();
        $result['show_filter'] = $attribute->isUseableAsGridFilter();
        $result['show_column'] = $attribute->isUseableAsGridColumn();

        $backendType = $attribute->getBackendType();
        if ($backendType !== AbstractAttributeType::BACKEND_TYPE_OPTION
            && $result['type'] === FieldDescriptionInterface::TYPE_OPTIONS) {
            $result['sortable'] = false;
        }

        if ($result['type'] === FieldDescriptionInterface::TYPE_DECIMAL and !$attribute->isDecimalsAllowed()) {
            $result['type'] = FieldDescriptionInterface::TYPE_INTEGER;
        }

        return $result;
    }

    /**
     * @return AbstractAttribute[]
     */
    protected function getFlexibleAttributes()
    {
        if (null === $this->attributes) {
            /** @var $attributeRepository \Doctrine\Common\Persistence\ObjectRepository */
            $attributeRepository = $this->flexibleManager->getAttributeRepository();
            $attributes = $attributeRepository->findAllWithTranslations();
            $this->attributes = array();
            /** @var $attribute AbstractAttribute */
            foreach ($attributes as $attribute) {
                $this->attributes[$attribute->getCode()] = $attribute;
            }
        }

        return $this->attributes;
    }

    /**
     * Create a family field and filter
     *
     * @return \Oro\Bundle\GridBundle\Field\FieldDescription
     */
    protected function createFamilyField()
    {
        $field = new FieldDescription();
        $field->setName('family');
        $field->setOptions(
            array(
                'type'          => FieldDescriptionInterface::TYPE_TEXT,
                'label'         => $this->translate('Family'),
                'field_name'    => 'familyLabel',
                'expression'    => 'family',
                'filter_type'   => FilterInterface::TYPE_ENTITY,
                'required'      => false,
                'sortable'      => true,
                'filterable'    => true,
                'show_filter'   => true,
                'multiple'      => true,
                'class'         => 'PimProductBundle:Family',
                'property'      => 'label',
                'filter_by_where' => true,
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
                'label'       => $this->translate('Channel'),
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

    protected function createCompletenessField()
    {
        $fieldCompleteness = new FieldDescription();
        $fieldCompleteness->setName('completenesses');
        $fieldCompleteness->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_HTML,
                'label'       => $this->translate('Completeness'),
                'field_name'  => 'completenesses',
                'expression'  => 'pCompleteness',
                'filter_type' => FilterInterface::TYPE_COMPLETENESS,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
                'filter_by_where' => true,
                'sort_field_mapping' => array(
                    'entityAlias' => 'pCompleteness',
                    'fieldName'   => 'ratio'
                )
            )
        );
        $fieldCompleteness->setProperty(
            new TwigTemplateProperty(
                $fieldCompleteness,
                'PimProductBundle:Completeness:_completeness.html.twig',
                array(
                    'localeCode'  => $this->flexibleManager->getLocale(),
                    'channelCode' => $this->flexibleManager->getScope()
                )
            )
        );

        return $fieldCompleteness;
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

        $editCategoriesAction = array(
            'name'         => 'edit_categories',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label'   => $this->translate('Edit categories'),
                'icon'    => 'folder-close',
                'link'    => 'edit_categories_link',
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

        return array(
            $clickAction,
            $editAction,
            $editCategoriesAction,
            $deleteAction
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setFlexibleManager(FlexibleManager $flexibleManager)
    {
        $this->flexibleManager = $flexibleManager;
        $this->flexibleManager->setScope($this->getScopeFilterValue());
        $this->getRouteGenerator()->setRouteParameters(array('dataLocale' => $flexibleManager->getLocale()));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareQuery(ProxyQueryInterface $proxyQuery)
    {
        $rootAlias = $proxyQuery->getRootAlias();

        // @todo : must be THEN CONCAT("[", family.code, "]")
        $selectConcat = "CASE WHEN ft.label IS NULL ".
                        "THEN family.code ".
                        "ELSE ft.label END ".
                        "as familyLabel";

        // prepare query for family
        $proxyQuery
            ->addSelect($selectConcat, true)
            ->leftJoin($rootAlias .'.family', 'family')
            ->leftJoin('family.translations', 'ft', 'WITH', 'ft.locale = :localeCode');

        // prepare query for completeness
        $proxyQuery->leftJoin($rootAlias .'.completenesses', 'pCompleteness')
                   ->leftJoin('pCompleteness.locale', 'locale')
                   ->leftJoin('pCompleteness.channel', 'channel')
                   ->andWhere('locale.code = :localeCode')
                   ->andWhere('channel.code = :channelCode');

        $proxyQuery->setParameter('localeCode', $this->flexibleManager->getLocale());
        $proxyQuery->setParameter('channelCode', $this->flexibleManager->getScope());

        // prepare query for categories
        if ($this->filterTreeId != static::UNCLASSIFIED_CATEGORY) {
            $categoryRepository = $this->categoryManager->getEntityRepository();

            if ($this->filterCategoryId != static::UNCLASSIFIED_CATEGORY) {
                $productIds = $categoryRepository->getLinkedProductIds($this->filterCategoryId, false);
                $productIds = (empty($productIds)) ? array(0) : $productIds;
                $expression = $proxyQuery->expr()->in($rootAlias .'.id', $productIds);
                $proxyQuery->andWhere($expression);
            } else {
                $productIds = $categoryRepository->getLinkedProductIds($this->filterTreeId, true);
                $productIds = (empty($productIds)) ? array(0) : $productIds;
                $expression = $proxyQuery->expr()->notIn($rootAlias .'.id', $productIds);
                $proxyQuery->andWhere($expression);
            }
        }
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
