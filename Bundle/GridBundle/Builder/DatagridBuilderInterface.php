<?php

namespace Oro\Bundle\GridBundle\Builder;

use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Property\PropertyInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\GridBundle\Route\RouteGeneratorInterface;
use Oro\Bundle\GridBundle\Action\MassAction\MassActionInterface;

interface DatagridBuilderInterface
{
    /**
     * Add property to datagrid
     *
     * @param DatagridInterface $datagrid
     * @param PropertyInterface $property
     * @return void
     */
    public function addProperty(DatagridInterface $datagrid, PropertyInterface $property);

    /**
     * @param DatagridInterface $datagrid
     * @param FieldDescriptionInterface $fieldDescription
     * @return void
     */
    public function addFilter(
        DatagridInterface $datagrid,
        FieldDescriptionInterface $fieldDescription = null
    );

    /**
     * @param DatagridInterface $datagrid
     * @param FieldDescriptionInterface $field
     * @return void
     */
    public function addSorter(DatagridInterface $datagrid, FieldDescriptionInterface $field);

    /**
     * @param DatagridInterface $datagrid
     * @param array $parameters
     * @return void
     */
    public function addRowAction(DatagridInterface $datagrid, array $parameters);

    /**
     * @param DatagridInterface $datagrid
     * @param MassActionInterface $massAction
     */
    public function addMassAction(DatagridInterface $datagrid, MassActionInterface $massAction);

    /**
     * @param ProxyQueryInterface $query
     * @param FieldDescriptionCollection $fieldCollection
     * @param RouteGeneratorInterface $routeGenerator,
     * @param ParametersInterface $parameters
     * @param string $name
     * @param string $entityHint
     *
     * @return DatagridInterface
     */
    public function getBaseDatagrid(
        ProxyQueryInterface $query,
        FieldDescriptionCollection $fieldCollection,
        RouteGeneratorInterface $routeGenerator,
        ParametersInterface $parameters,
        $name,
        $entityHint = null
    );
}
