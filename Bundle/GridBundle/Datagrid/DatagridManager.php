<?php

namespace Oro\Bundle\GridBundle\Datagrid;

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
use Oro\Bundle\GridBundle\Sorter\SorterInterface;
use Oro\Bundle\GridBundle\Action\MassAction\MassActionInterface;

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
     * @var MassActionInterface[]
     */
    private $massActions;

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
    public function setName($name)
    {
        $this->name = $name;
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
        foreach ($this->getListFields() as $fieldDescription) {
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
            $this->name,
            $this->entityHint
        );

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

        // add mass actions
        foreach ($this->getMassActions() as $massAction) {
            $this->datagridBuilder->addMassAction($datagrid, $massAction);
        }
        // add toolbar options
        $datagrid->setToolbarOptions($this->getToolBarOptions());

        return $datagrid;
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
        }

        return $this->fieldsCollection;
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
     * Get list of datagrid fields
     *
     * @return FieldDescriptionInterface[]
     */
    protected function getListFields()
    {
        return $this->getFieldDescriptionCollection()->getElements();
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
        $options = $this->getToolBarOptions();

        switch (true) {
            case isset($options['hide']) && $options['hide']:
                $defaultPager['_per_page'] = 0;
                break;
            case isset($options['pagination']['hide']) && $options['pagination']['hide']:
                $defaultPager['_per_page'] = 0;
                break;
            case isset($options['pageSize']['hide']) && $options['pageSize']['hide']:
                $defaultPager['_per_page'] = 0;
                break;
        }

        // add 'all' pageSize
        if (isset($defaultPager['_per_page']) && $defaultPager['_per_page'] == 0) {
            $notExists = true;
            if (isset($options['pageSize']['items']) && is_array($options['pageSize']['items'])) {
                foreach ($options['pageSize']['items'] as $item) {
                    if ($item == 0 || isset($item['size']) && $item['size'] == 0) {
                        $notExists = false;
                        break;
                    }
                }
            }

            if ($notExists) {
                $options['pageSize'] = isset($options['pageSize']) ? $options['pageSize'] : array();
                $options['pageSize']['items'] = isset($options['pageSize']['items']) && is_array($options['pageSize']['items'])
                    ? $options['pageSize']['items']
                    : array();
                $options['pageSize']['items'][] = array(
                    'size' => 0,
                    'label' => $this->translate('oro.grid.datagrid.page_size.all')
                );
                $this->toolbarOptions = $options;
            }
        }

        return $defaultPager;
    }

    /**
     * @param string $id
     * @param array $parameters
     * @param string $domain
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
    public function getToolBarOptions()
    {
        return $this->toolbarOptions;
    }
}
