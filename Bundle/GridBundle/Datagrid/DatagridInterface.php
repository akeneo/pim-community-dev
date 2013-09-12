<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Symfony\Component\Form\Form;

use Oro\Bundle\GridBundle\Property\PropertyInterface;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;
use Oro\Bundle\GridBundle\Route\RouteGeneratorInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Action\MassAction\MassActionInterface;
use Oro\Bundle\GridBundle\Datagrid\PagerInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

interface DatagridInterface
{
    /**
     * @return PagerInterface
     */
    public function getPager();

    /**
     * @return ProxyQueryInterface
     */
    public function getQuery();

    /**
     * @return ResultRecordInterface[]
     */
    public function getResults();

    /**
     * @return FilterInterface[]
     */
    public function getFilters();

    /**
     * @return FieldDescriptionInterface[]
     */
    public function getColumns();

    /**
     * @return Form
     */
    public function getForm();

    /**
     * @param string $name
     *
     * @return FilterInterface
     */
    public function getFilter($name);

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasFilter($name);

    /**
     * @param string $name
     */
    public function removeFilter($name);

    /**
     * @return boolean
     */
    public function hasActiveFilters();

    /**
     * @param FilterInterface $filter
     * @param boolean         $prepend
     */
    public function addFilter(FilterInterface $filter, $prepend = false);

    /**
     * @param  PropertyInterface $property
     * @return void
     */
    public function addProperty(PropertyInterface $property);

    /**
     * @param  SorterInterface $sorter
     * @return void
     */
    public function addSorter(SorterInterface $sorter);

    /**
     * @param  ActionInterface $action
     * @return void
     */
    public function addRowAction(ActionInterface $action);

    /**
     * @param  MassActionInterface $action
     * @return void
     */
    public function addMassAction(MassActionInterface $action);

    /**
     * @return SorterInterface[]
     */
    public function getSorters();

    /**
     * @return ActionInterface[]
     */
    public function getRowActions();

    /**
     * @return MassActionInterface[]
     */
    public function getMassActions();

    /**
     * @param  string               $name
     * @return null|SorterInterface
     */
    public function getSorter($name);

    /**
     * @return RouteGeneratorInterface
     */
    public function getRouteGenerator();

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getEntityName();

    /**
     * @param string $entityName
     */
    public function setEntityName($entityName);

    /**
     * @return string
     */
    public function getEntityHint();

    /**
     * @param string $entityHint
     */
    public function setEntityHint($entityHint);

    /**
     * @return DatagridView
     */
    public function createView();

    /**
     * @return ParametersInterface
     */
    public function getParameters();

    /**
     * @return array
     */
    public function getProperties();

    /**
     * @return array
     */
    public function getToolbarOptions();

    /**
     * @param $options
     * @return $this
     */
    public function setToolbarOptions($options);

    /**
     * Apply filter data to ProxyQuery
     */
    public function applyFilters();

    /**
     * Get identifier field name
     *
     * @return string
     */
    public function getIdentifierFieldName();

    /**
     * Set identifier field name
     *
     * @param string $identifierFieldName
     */
    public function setIdentifierFieldName($identifierFieldName);

    /**
     * Set identifier field
     *
     * @return FieldDescriptionInterface
     * @throws \RuntimeException         If there is no identifier field
     */
    public function getIdentifierField();

    /**
     * Set multiple sorting flag
     *
     * @param boolean $multipleSorting
     */
    public function setMultipleSorting($multipleSorting);

    /**
     * Get multiple sorting flag
     *
     * @return boolean
     */
    public function getMultipleSorting();
}
