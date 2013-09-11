<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Routing\Router;

use Oro\Bundle\GridBundle\Builder\DatagridBuilderInterface;
use Oro\Bundle\GridBundle\Builder\ListBuilderInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Property\PropertyInterface;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\GridBundle\Route\RouteGeneratorInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;
use Oro\Bundle\GridBundle\Datagrid\Views\AbstractViewsList;
use Oro\Bundle\GridBundle\Action\MassAction\MassActionInterface;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * TODO: This class should be refactored  (BAP-969).
 */
abstract class DatagridManager implements DatagridManagerInterface
{
    /**
     * @var DatagridBuilderInterface
     */
    protected $datagridBuilder;

    /**
     * @var ListBuilderInterface
     */
    protected $listBuilder;

    /**
     * @var QueryFactoryInterface
     */
    protected $queryFactory;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $translationDomain;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var ParametersInterface
     */
    protected $parameters;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var string
     */
    protected $queryEntityAlias;

    /**
     * @var string
     */
    protected $entityHint;

    /**
     * @var RouteGeneratorInterface
     */
    protected $routeGenerator;

    /**
     * @var FieldDescriptionCollection
     */
    private $fieldsCollection;

    /**
     * @var string|null
     */
    private $identifierField;

    /**
     * @var AbstractViewsList|null
     */
    private $viewsList;

    /**
     * @var array
     */
    protected $toolbarOptions = array();

    /**
     * {@inheritDoc}
     */
    public function setDatagridBuilder(DatagridBuilderInterface $datagridBuilder)
    {
        $this->datagridBuilder = $datagridBuilder;
    }

    /**
     * {@inheritDoc}
     */
    public function setListBuilder(ListBuilderInterface $listBuilder)
    {
        $this->listBuilder = $listBuilder;
    }

    /**
     * {@inheritDoc}
     */
    public function setQueryFactory(QueryFactoryInterface $queryManager)
    {
        $this->queryFactory = $queryManager;
    }

    /**
     * {@inheritDoc}
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function setTranslationDomain($translationDomain)
    {
        $this->translationDomain = $translationDomain;
    }

    /**
     * {@inheritDoc}
     */
    public function setValidator(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritDoc}
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritDoc}
     */
    public function setRouteGenerator(RouteGeneratorInterface $routeGenerator)
    {
        $this->routeGenerator = $routeGenerator;
    }

    /**
     * {@inheritDoc}
     */
    public function setViewsList(AbstractViewsList $list)
    {
        $this->viewsList = $list;
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritDoc}
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * {@inheritDoc}
     */
    public function setQueryEntityAlias($queryEntityAlias)
    {
        $this->queryEntityAlias = $queryEntityAlias;
    }

    /**
     * {@inheritDoc}
     */
    public function setEntityHint($entityHint)
    {
        $this->entityHint = $entityHint;
    }

    /**
     * {@inheritDoc}
     */
    public function setIdentifierField($identifierField)
    {
        $this->identifierField = $identifierField;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifierField()
    {
        if (null == $this->identifierField && $this->entityName && $this->entityManager) {
            $this->identifierField =
                current($this->entityManager->getClassMetadata($this->entityName)->getIdentifierFieldNames());
        }

        return $this->identifierField;
    }

    /**
     * {@inheritDoc}
     */
    public function setParameters(ParametersInterface $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * {@inheritDoc}
     */
    public function getDatagrid()
    {
        // add datagrid fields
        $listCollection = $this->listBuilder->getBaseList();

        /** @var $fieldDescription FieldDescriptionInterface */
        foreach ($this->getFieldDescriptionCollection() as $fieldDescription) {
            $listCollection->add($fieldDescription);
        }

        // merge default parameters
        $parametersArray = $this->parameters->toArray();
        if (empty($parametersArray[$this->name])) {
            foreach ($this->getDefaultParameters() as $type => $value) {
                $this->parameters->set($type, $value);
            }
        }

        // create query
        $query = $this->createQuery();
        $this->applyQueryParameters($query);

        // create datagrid
        $datagrid = $this->datagridBuilder->getBaseDatagrid(
            $query,
            $listCollection,
            $this->routeGenerator,
            $this->parameters,
            $this->name
        );

        $this->configureDatagrid($datagrid);

        return $datagrid;
    }

    /**
     * Process datagrid configuration
     *
     * @param DatagridInterface $datagrid
     */
    protected function configureDatagrid(DatagridInterface $datagrid)
    {
        // add properties
        foreach ($this->getProperties() as $property) {
            $this->datagridBuilder->addProperty($datagrid, $property);
        }

        // add datagrid filters
        /** @var $fieldDescription FieldDescriptionInterface */
        foreach ($this->getFilters() as $fieldDescription) {
            $this->datagridBuilder->addFilter($datagrid, $fieldDescription);
        }

        // add datagrid sorters
        /** @var $sorterField FieldDescriptionInterface */
        foreach ($this->getSorters() as $sorterField) {
            $this->datagridBuilder->addSorter($datagrid, $sorterField);
        }

        // add row actions
        foreach ($this->getRowActions() as $actionParameters) {
            $this->datagridBuilder->addRowAction($datagrid, $actionParameters);
        }

        $massActions = $this->getMassActions();
        // add mass actions
        foreach ($massActions as $massAction) {
            $this->datagridBuilder->addMassAction($datagrid, $massAction);
        }

        // add "selected rows: filter if mass actions exist and identifier is configured
        if (count($massActions) && $this->identifierField) {
            $this->datagridBuilder->addSelectedRowFilter(
                $datagrid,
                $this->getSelectedRowFilterDefaultOptions()
            );
        }

        // add toolbar options
        $datagrid->setToolbarOptions($this->getToolbarOptions());

        // set identifier field name
        if ($this->getIdentifierField()) {
            $datagrid->setIdentifierFieldName($this->getIdentifierField());
        }

        // set identifier field name
        $datagrid->setEntityName($this->entityName);
        $datagrid->setName($this->name);
        $datagrid->setEntityHint($this->entityHint);

        $views = $this->getViewsList();
        if ($views) {
            $defaultParameters = $this->getDefaultParameters();
            $views->applyToDatagrid($datagrid, $defaultParameters);
        }
    }

    /**
     * Provide ability to override default "selected rows" filter setting
     * e.g label, show/hide filter, field name
     *
     * @return array
     */
    protected function getSelectedRowFilterDefaultOptions()
    {
        return array(
            'field_mapping' => array(
                'fieldName' => $this->identifierField
            ),
            'field_name'    => $this->identifierField,
            'show_filter'   => true,
            'label'         => $this->translate('oro.grid.mass_action.selected_rows')
        );
    }

    /**
     * @return ProxyQueryInterface
     */
    protected function createQuery()
    {
        $query = $this->queryFactory->createQuery();
        $this->prepareQuery($query);

        return $query;
    }

    /**
     * @param ProxyQueryInterface $query
     */
    protected function prepareQuery(ProxyQueryInterface $query)
    {
    }

    /**
     * Apply query parameters to query object
     *
     * @param ProxyQueryInterface $query
     */
    protected function applyQueryParameters(ProxyQueryInterface $query)
    {
        foreach ($this->getQueryParameters() as $name => $value) {
            $query->setParameter($name, $value);
        }
    }

    /**
     * Get parameters for query
     *
     * @return array
     */
    protected function getQueryParameters()
    {
        return array();
    }

    protected function getFieldDescriptionCollection()
    {
        if (!$this->fieldsCollection) {
            $this->fieldsCollection = new FieldDescriptionCollection();
            $this->configureFields($this->fieldsCollection);
            $this->configureIdentifierField($this->fieldsCollection);
        }

        return $this->fieldsCollection;
    }

    /**
     * @param FieldDescriptionCollection $fieldCollection
     */
    protected function configureIdentifierField(FieldDescriptionCollection $fieldCollection)
    {
        $identifierField = $this->createIdentifierField();
        if ($identifierField && !$fieldCollection->has($identifierField->getName())) {
            $fieldCollection->add($identifierField);
            $this->identifierField = $identifierField->getName();
        }
    }

    /**
     * @return FieldDescription|null
     */
    protected function createIdentifierField()
    {
        $identifierFieldName = $this->getIdentifierField();

        if (!$identifierFieldName) {
            return null;
        }

        $field = new FieldDescription();
        $field->setName($identifierFieldName);
        $options = array(
            'field_name'   => $identifierFieldName,
            'type'         => FieldDescriptionInterface::TYPE_INTEGER,
            'label'        => $this->translate($this->identifierField),
            'filter_type'  => FilterInterface::TYPE_NUMBER,
            'show_column'  => false
        );

        if ($this->queryEntityAlias) {
            $options['entity_alias'] = $this->queryEntityAlias;
        }

        $field->setOptions($options);

        return $field;
    }

    /**
     * Configure collection of field descriptions
     *
     * @param FieldDescriptionCollection $fieldCollection
     */
    protected function configureFields(FieldDescriptionCollection $fieldCollection)
    {
    }

    /**
     * Get Route generator
     *
     * @return RouteGeneratorInterface
     */
    public function getRouteGenerator()
    {
        return $this->routeGenerator;
    }

    /**
     * Get list of properties
     *
     * @return PropertyInterface[]
     */
    protected function getProperties()
    {
        return array();
    }

    /**
     * Get list of datagrid filters
     *
     * @return FieldDescriptionInterface[]
     */
    protected function getFilters()
    {
        $fields = array();
        /** @var $fieldDescription FieldDescriptionInterface */
        foreach ($this->getFieldDescriptionCollection() as $fieldDescription) {
            if ($fieldDescription->isFilterable()) {
                $fields[] = $fieldDescription;
            }
        }

        return $fields;
    }

    /**
     * Get list of datagrid sorters
     *
     * @return array
     */
    protected function getSorters()
    {
        $fields = array();
        /** @var $fieldDescription FieldDescriptionInterface */
        foreach ($this->getFieldDescriptionCollection() as $fieldDescription) {
            if ($fieldDescription->isSortable()) {
                $fields[] = $fieldDescription;
            }
        }

        return $fields;
    }

    /**
     * Get list of row actions
     *
     * @return array
     */
    protected function getRowActions()
    {
        return array();
    }

    /**
     * Get list of mass actions
     *
     * @return MassActionInterface[]
     */
    protected function getMassActions()
    {
        return array();
    }

    /**
     * Get default parameters
     *
     * @return array
     */
    protected function getDefaultParameters()
    {
        return array(
            ParametersInterface::FILTER_PARAMETERS => $this->getDefaultFilters(),
            ParametersInterface::SORT_PARAMETERS   => $this->getDefaultSorters(),
            ParametersInterface::PAGER_PARAMETERS  => $this->getDefaultPager()
        );
    }

    /**
     * @return array
     */
    protected function getDefaultSorters()
    {
        $sorters = array();

        // get first sortable field
        $fields = $this->getSorters();
        if (!empty($fields)) {
            /** @var $field FieldDescriptionInterface */
            $field = reset($fields);
            $sorters[$field->getName()] = SorterInterface::DIRECTION_ASC;
        }

        return $sorters;
    }

    /**
     * @return array
     */
    protected function getDefaultFilters()
    {
        return array();
    }

    /**
     * @return array
     */
    protected function getDefaultPager()
    {
        $defaultPager = array();
        $options = $this->getToolbarOptions();

        $options = array_merge_recursive(
            array(
                'hide' => false,
                'pageSize' => array(
                    'hide' => false,
                    'items' => array()
                ),
                'pagination' => array(
                    'hide' => false,
                )
            ),
            $options
        );

        // check all label exists
        $zeroItem = array_filter(
            $options['pageSize']['items'],
            function ($item) {
                $item = isset($item['size']) ? $item['size'] : $item;

                return $item == 0;
            }
        );
        $notExists = count($zeroItem) == 0;

        $hidden = in_array(
            true,
            array($options['hide'] , $options['pagination']['hide'] , $options['pageSize']['hide'])
        );

        if ($hidden) {
            $defaultPager['_per_page'] = 0;
        }

        // add 'all' pageSize
        if ($notExists && $hidden) {
            $options['pageSize']['items'][] = array(
                'size' => 0,
                'label' => $this->translate('oro.grid.datagrid.page_size.all')
            );
            $this->toolbarOptions = $options;
        }

        return $defaultPager;
    }

    /**
     * @return null|AbstractViewsList
     */
    public function getViewsList()
    {
        return $this->viewsList;
    }

    /**
     * @param  string $id
     * @param  array  $parameters
     * @param  string $domain
     * @return string
     */
    protected function translate($id, array $parameters = array(), $domain = null)
    {
        if (!$domain) {
            $domain = $this->translationDomain;
        }

        return $this->translator->trans($id, $parameters, $domain);
    }

    /**
     * Define grid toolbar options as assoc array
     *
     * @return array
     */
    public function getToolbarOptions()
    {
        return $this->toolbarOptions;
    }
}
