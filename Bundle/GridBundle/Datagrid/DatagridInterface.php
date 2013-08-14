<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Sonata\AdminBundle\Datagrid\DatagridInterface as BaseDatagridInterface;

use Oro\Bundle\GridBundle\Property\PropertyInterface;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;
use Oro\Bundle\GridBundle\Route\RouteGeneratorInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;

interface DatagridInterface extends BaseDatagridInterface
{
    /**
     * @param PropertyInterface $property
     * @return void
     */
    public function addProperty(PropertyInterface $property);

    /**
     * @param SorterInterface $sorter
     * @return void
     */
    public function addSorter(SorterInterface $sorter);

    /**
     * @param ActionInterface $action
     * @return void
     */
    public function addRowAction(ActionInterface $action);

    /**
     * @return SorterInterface[]
     */
    public function getSorters();

    /**
     * @return ActionInterface[]
     */
    public function getRowActions();

    /**
     * @param string $name
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
     * @return string
     */
    public function getEntityHint();

    /**
     * @return DatagridView
     */
    public function createView();

    /**
     * @return array
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
}
