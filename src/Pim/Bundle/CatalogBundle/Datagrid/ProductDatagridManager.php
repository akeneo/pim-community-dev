<?php

namespace Pim\Bundle\CatalogBundle\Datagrid;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

use Oro\Bundle\GridBundle\Action\MassAction\Ajax\DeleteMassAction;
use Oro\Bundle\GridBundle\Action\MassAction\Redirect\RedirectMassAction;
use Oro\Bundle\GridBundle\Builder\DatagridBuilderInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;

use Pim\Bundle\GridBundle\Action\ActionInterface;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\GridBundle\Filter\FilterInterface;
use Pim\Bundle\GridBundle\Action\Export\ExportCollectionAction;

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
     * @var Pim\Bundle\CatalogBundle\Manager\CategoryManager
     */
    protected $categoryManager;

    /**
     * @var Pim\Bundle\CatalogBundle\Manager\LocaleManager
     */
    protected $localeManager;

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
     *
     * @param CategoryManager $manager
     */
    public function setCategoryManager(CategoryManager $manager)
    {
        $this->categoryManager = $manager;
    }

    /**
     * Configure the locale manager
     *
     * @param LocaleManager $manager
     */
    public function setLocaleManager(LocaleManager $manager)
    {
        $this->localeManager = $manager;
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
     * {@inheritdoc}
     */
    protected function getProperties()
    {
        return array(
            new UrlProperty(
                'edit_link',
                $this->router,
                'pim_catalog_product_edit',
                array('id', 'dataLocale' => self::LOCALE_FIELD_NAME)
            ),
            new UrlProperty(
                'edit_categories_link',
                $this->router,
                'pim_catalog_product_edit',
                array('id', 'dataLocale' => self::LOCALE_FIELD_NAME),
                false,
                '#categories'
            ),
            new UrlProperty('delete_link', $this->router, 'pim_catalog_product_remove', array('id')),
        );
    }

    /**
     * {@inheritdoc}
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
        $result['show_filter'] = $attribute->isUseableAsGridFilter()
            && $attribute->getAttributeType() === 'pim_catalog_identifier';
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
     * @return FieldDescription
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
                'class'         => 'PimCatalogBundle:Family',
                'property'      => 'label',
                'filter_by_where' => true,
            )
        );

        return $field;
    }

    /**
     * Create scope field description for datagrid
     *
     * @return FieldDescription
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

    /**
     * Create the completeness field
     *
     * @return FieldDescription
     */
    protected function createCompletenessField()
    {
        $fieldCompleteness = new FieldDescription();
        $fieldCompleteness->setName('completenesses');
        $fieldCompleteness->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_HTML,
                'label'       => $this->translate('Complete'),
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
                'PimCatalogBundle:Completeness:_completeness.html.twig',
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
        $editAction = array(
            'name'         => 'edit',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label'   => $this->translate('Edit attributes of the product'),
                'icon'    => 'edit',
                'link'    => 'edit_link'
            )
        );

        $clickAction = $editAction;
        $clickAction['name'] = 'rowClick';
        $clickAction['options']['runOnRowClick'] = true;

        $editCategoriesAction = array(
            'name'         => 'edit_categories',
            'type'         => ActionInterface::TYPE_TAB_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label'     => $this->translate('Classify the product'),
                'tab'       => '#categories',
                'icon'      => 'folder-close',
                'className' => 'edit-categories-action',
                'link'      => 'edit_categories_link'
            )
        );

        $deleteAction = array(
            'name'         => 'delete',
            'type'         => ActionInterface::TYPE_DELETE,
            'acl_resource' => 'root',
            'options'      => array(
                'label'   => $this->translate('Delete the product'),
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
    protected function getMassActions()
    {
        $deleteMassActions = new DeleteMassAction(
            array(
                'name'  => 'delete',
                'label' => $this->translate('Delete'),
                'icon'  => 'trash'
            )
        );

        $redirectMassAction = new RedirectMassAction(
            array(
                'name'  => 'redirect',
                'label' => $this->translate('Mass Edition'),
                'icon' => 'edit',
                'route' => 'pim_catalog_mass_edit_action_choose',
            )
        );

        return array($redirectMassAction, $deleteMassActions);
    }

    /**
     * Get list of export actions
     *
     * @return \Pim\Bundle\GridBundle\Action\Export\ExportActionInterface[]
     */
    protected function getExportActions()
    {
        $exportCsv = new ExportCollectionAction(
            array(
                'acl_resource' => 'root',
                'baseUrl' => $this->router->generate('pim_catalog_product_index', array('_format' => 'csv')),
                'name' =>  'exportCsv',
                'label' => $this->translate('CSV export'),
                'icon'  => 'icon-download',
                'keepParameters' => true
            )
        );

        return array($exportCsv);
    }

    /**
     * {@inheritdoc}
     *
     * Add export actions
     */
    protected function configureDatagrid(DatagridInterface $datagrid)
    {
        parent::configureDatagrid($datagrid);

        $exportActions = $this->getExportActions();
        foreach ($exportActions as $exportAction) {
            $this->datagridBuilder->addExportAction($datagrid, $exportAction);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDatagrid()
    {
        $datagrid = parent::getDatagrid();

        $datagrid->removeFilter(DatagridBuilderInterface::SELECTED_ROW_FILTER_NAME);

        return $datagrid;
    }

    /**
     * {@inheritdoc}
     */
    public function setFlexibleManager(FlexibleManager $flexibleManager)
    {
        $this->flexibleManager = $flexibleManager;
        $this->flexibleManager->setScope($this->getScopeFilterValue());
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
            ->leftJoin('family.translations', 'ft', 'WITH', 'ft.locale = :localeCode')
            ->leftJoin($rootAlias.'.values', 'values')
            ->leftJoin('values.prices', 'valuePrices');

        // prepare query for completeness
        $this->prepareQueryForCompleteness($proxyQuery, $rootAlias);

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
     * Prepare query for completeness field
     * @param ProxyQueryInterface $proxyQuery
     * @param string              $rootAlias
     */
    protected function prepareQueryForCompleteness(ProxyQueryInterface $proxyQuery, $rootAlias)
    {
        $exprLocaleAndScope      = $proxyQuery->expr()->andX(
            'locale.code = :localeCode',
            'channel.code = :channelCode'
        );
        $exprWithoutCompleteness = $proxyQuery->expr()->isNull('pCompleteness');
        $exprFamilyIsNull        = $proxyQuery->expr()->isNull($rootAlias .'.family');
        $proxyQuery
            ->leftJoin($rootAlias .'.completenesses', 'pCompleteness')
            ->leftJoin('pCompleteness.locale', 'locale')
            ->leftJoin('pCompleteness.channel', 'channel')
            ->orWhere($exprLocaleAndScope, $exprWithoutCompleteness, $exprFamilyIsNull);
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

    /**
     * Get the product availables from the selected products (clause in proxy query)
     *
     * @param ProxyQueryInterface $proxyQuery
     *
     * @return array
     */
    protected function getAvailableAttributes(ProxyQueryInterface $proxyQuery)
    {
        $qb = clone $proxyQuery;
        $qb
            ->leftJoin('values.attribute', 'attribute')
            ->groupBy('attribute.id')
            ->select('attribute.id, attribute.code, attribute.translatable, attribute.attributeType');

        return $qb
            ->getQuery()
            ->execute(array(), \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * Get the available attribute codes
     *
     * @param ProxyQueryInterface $proxyQuery
     *
     * @return string[]
     */
    public function getAvailableAttributeCodes(ProxyQueryInterface $proxyQuery)
    {
        $results = $this->getAvailableAttributes($proxyQuery);

        $attributesList = array();
        foreach ($results as $attribute) {
            if ($attribute['translatable'] == 1) {
                foreach ($this->localeManager->getActiveCodes() as $code) {
                    $attributesList[] = sprintf('%s-%s', $attribute['code'], $code);
                }
                // @todo : Use constant for pim_catalog_identifier
            } elseif ($attribute['attributeType'] === 'pim_catalog_identifier') {
                array_unshift($attributesList, $attribute['code']);
            } else {
                $attributesList[] = $attribute['code'];
            }
        }

        return $attributesList;
    }

    /**
     * Get the available attribute ids
     *
     * @param ProxyQueryInterface $proxyQuery
     *
     * @return int[]
     */
    protected function getAvailableAttributeIds(ProxyQueryInterface $proxyQuery)
    {
        $results = $this->getAvailableAttributes($proxyQuery);

        $attributeIds = array();
        foreach ($results as $attribute) {
            $attributeIds[] = $attribute['id'];
        }

        return $attributeIds;
    }

    /**
     * Optimize the query for the export
     *
     * @param ProxyQueryInterface $proxyQuery
     */
    public function prepareQueryForExport(ProxyQueryInterface $proxyQuery)
    {
        $attributeIds = $this->getAvailableAttributeIds($proxyQuery);

        $proxyQuery
            ->resetDQLPart('groupBy')
            ->resetDQLPart('orderBy');

        // join tables
        $proxyQuery
            ->leftJoin('values.options', 'valueOptions')
            ->leftJoin('o.categories', 'categories');

        // select datas
        $proxyQuery
            ->select($proxyQuery->getRootAlias())
            ->addSelect('values')
            ->addSelect('family')
            ->addSelect('valuePrices')
            ->addSelect('valueOptions')
            ->addSelect('categories');

        // where clause on attributes
        $exprIn = $proxyQuery->expr()->in('attribute', $attributeIds);
        $proxyQuery
            ->leftJoin('values.attribute', 'attribute')
            ->andWhere($exprIn);
    }
}
