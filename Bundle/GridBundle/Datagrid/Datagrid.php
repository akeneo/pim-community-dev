<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Sonata\AdminBundle\Filter\FilterInterface as SonataFilterInterface;

use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Property\PropertyCollection;
use Oro\Bundle\GridBundle\Property\PropertyInterface;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;
use Oro\Bundle\GridBundle\Route\RouteGeneratorInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\EventDispatcher\ResultDatagridEvent;

class Datagrid implements DatagridInterface
{
    /**
     * @var ProxyQueryInterface
     */
    protected $query;

    /**
     * @var PropertyCollection
     */
    protected $properties;

    /**
     * @var FieldDescriptionCollection
     */
    protected $columns;

    /**
     * @var PagerInterface
     */
    protected $pager;

    /**
     * @var FormBuilderInterface
     */
    protected $formBuilder;

    /**
     * Parameters applied flag
     *
     * @var bool
     */
    protected $parametersApplied = false;

    /**
     * Pager applied flag
     *
     * @var bool
     */
    protected $pagerApplied = false;

    /**
     * @var RouteGeneratorInterface
     */
    protected $routeGenerator;

    /**
     * Parameters binded flag
     *
     * @var bool
     */
    protected $parametersBinded = false;

    /**
     * @var ParametersInterface
     */
    protected $parameters;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var array
     */
    protected $filters = array();

    /**
     * @var SorterInterface[]
     */
    protected $sorters = array();

    /**
     * @var Form
     */
    protected $form;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $entityHint;

    /**
     * @var ActionInterface[]
     */
    protected $rowActions;

    /**
     * @var
     */
    protected $toolbarOptions;

    /**
     * @param ProxyQueryInterface $query
     * @param FieldDescriptionCollection $columns
     * @param PagerInterface $pager
     * @param FormBuilderInterface $formBuilder
     * @param RouteGeneratorInterface $routeGenerator
     * @param ParametersInterface $parameters
     * @param EventDispatcherInterface $eventDispatcher
     * @param string $name
     * @param string $entityHint
     */
    public function __construct(
        ProxyQueryInterface $query,
        FieldDescriptionCollection $columns,
        PagerInterface $pager,
        FormBuilderInterface $formBuilder,
        RouteGeneratorInterface $routeGenerator,
        ParametersInterface $parameters,
        EventDispatcherInterface $eventDispatcher,
        $name,
        $entityHint = null
    ) {
        $this->query           = $query;
        $this->columns         = $columns;
        $this->pager           = $pager;
        $this->formBuilder     = $formBuilder;
        $this->routeGenerator  = $routeGenerator;
        $this->parameters      = $parameters;
        $this->eventDispatcher = $eventDispatcher;
        $this->name            = $name;
        $this->entityHint      = $entityHint;
        $this->properties      = new PropertyCollection();

        /** @var $field FieldDescriptionInterface */
        if (count($this->columns)) {
            foreach ($this->columns as $field) {
                $this->addProperty($field->getProperty());
            }
        }
    }

    /**
     * Add property
     *
     * @param PropertyInterface $property
     */
    public function addProperty(PropertyInterface $property)
    {
        $this->properties->add($property);
    }

    /**
     * Get properties
     *
     * @return PropertyCollection
     */
    public function getProperties()
    {
        return $this->properties->getElements();
    }

    /**
     * @param SonataFilterInterface $filter
     * @return void
     */
    public function addFilter(SonataFilterInterface $filter)
    {
        $name = $filter->getName();
        $this->filters[$name] = $filter;
        list($formType, $formOptions) = $filter->getRenderSettings();
        $this->formBuilder->add($name, $formType, $formOptions);
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param string $name
     *
     * @return SonataFilterInterface
     */
    public function getFilter($name)
    {
        return $this->hasFilter($name) ? $this->filters[$name] : null;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasFilter($name)
    {
        return isset($this->filters[$name]);
    }

    /**
     * @param string $name
     */
    public function removeFilter($name)
    {
        unset($this->filters[$name]);
    }

    /**
     * @return boolean
     */
    public function hasActiveFilters()
    {
        /** @var $filter FilterInterface */
        foreach ($this->filters as $filter) {
            if ($filter->isActive()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param SorterInterface $sorter
     * @return void
     */
    public function addSorter(SorterInterface $sorter)
    {
        $this->sorters[$sorter->getName()] = $sorter;
    }

    /**
     * @return array
     */
    public function getSorters()
    {
        return $this->sorters;
    }

    /**
     * @param $name
     * @return null|SorterInterface
     */
    public function getSorter($name)
    {
        if (isset($this->sorters[$name])) {
            return $this->sorters[$name];
        }

        return null;
    }

    /**
     * @return PagerInterface
     */
    public function getPager()
    {
        $this->applyPager();
        return $this->pager;
    }

    /**
     * Apply parameters
     */
    protected function applyParameters()
    {
        if ($this->parametersApplied) {
            return;
        }

        $this->applyFilters();
        $this->applyPager();
        $this->applySorters();

        $this->parametersApplied = true;
    }

    /**
     * Apply filter data to ProxyQuery
     */
    protected function applyFilters()
    {
        $form = $this->getForm();

        /** @var $filter FilterInterface */
        foreach ($this->getFilters() as $filter) {
            $filterName = $filter->getName();
            $filterForm = $form->get($filterName);
            if ($filterForm->isValid()) {
                $data = $filterForm->getData();
                $filter->apply($this->query, $data);
            }
        }
    }

    /**
     * Add sorters on grid and apply requested sorting
     */
    protected function applySorters()
    {
        $sortBy = $this->parameters->get(ParametersInterface::SORT_PARAMETERS);

        foreach ($sortBy as $fieldName => $direction) {
            if (isset($this->sorters[$fieldName])) {
                $this->sorters[$fieldName]->apply($this->query, $direction);
            }
        }
    }

    /**
     * Apply pager parameters
     */
    protected function applyPager()
    {
        if ($this->pagerApplied) {
            return;
        }

        $pagerParameters = $this->parameters->get(ParametersInterface::PAGER_PARAMETERS);
        $this->pager->setPage(isset($pagerParameters['_page']) ? $pagerParameters['_page'] : 1);
        $this->pager->setMaxPerPage(isset($pagerParameters['_per_page']) ? (int)$pagerParameters['_per_page'] : 10);
        $this->pager->init();

        $this->pagerApplied = true;
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        if (!$this->form) {
            $this->form = $this->formBuilder->getForm();
            $this->form->submit($this->parameters->get(ParametersInterface::FILTER_PARAMETERS));
        }

        return $this->form;
    }

    /**
     * @return ProxyQueryInterface
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return ResultRecord[]
     */
    public function getResults()
    {
        $this->applyParameters();
        $rows = $this->getQuery()->execute();

        // dispatch result event
        $event = new ResultDatagridEvent($this);
        $event->setRows($rows);
        $this->eventDispatcher->dispatch(ResultDatagridEvent::NAME, $event);
        $rows = $event->getRows();

        $result = array();
        foreach ($rows as $row) {
            $result[] = new ResultRecord($row);
        }

        return $result;
    }

    /**
     * @deprecated Use applyParameters instead
     */
    public function buildPager()
    {
        $this->applyParameters();
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns->getElements();
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters->toArray();
    }

    /**
     * @param string $name
     * @param string $operator
     * @param mixed $value
     * @deprecated Grid parameters are read-only
     */
    public function setValue($name, $operator, $value)
    {
    }

    /**
     * @return array
     * @deprecated Use getParameters instead
     */
    public function getValues()
    {
        return $this->getParameters();
    }

    /**
     * @return RouteGeneratorInterface
     */
    public function getRouteGenerator()
    {
        return $this->routeGenerator;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEntityHint()
    {
        return $this->entityHint;
    }

    /**
     * @param ActionInterface $action
     * @return void
     */
    public function addRowAction(ActionInterface $action)
    {
        $this->rowActions[] = $action;
    }

    /**
     * @return ActionInterface[]
     */
    public function getRowActions()
    {
        return $this->rowActions;
    }

    /**
     * @return DatagridView
     */
    public function createView()
    {
        return new DatagridView($this);
    }

    /**
     * @return array
     */
    public function getToolbarOptions()
    {
        return $this->toolbarOptions;
    }

    /**
     * @param $options
     * @return $this
     */
    public function setToolbarOptions($options)
    {
        $this->toolbarOptions = $options;
    }
}
