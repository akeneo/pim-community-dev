<?php

namespace Oro\Bundle\GridBundle\Action\MassAction;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\GridBundle\Datagrid\DatagridManagerRegistry;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

class MassActionDispatcher
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var DatagridManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @param ContainerInterface $container
     * @param DatagridManagerRegistry $managerRegistry
     */
    public function __construct(ContainerInterface $container, DatagridManagerRegistry $managerRegistry)
    {
        $this->container       = $container;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param string $datagridName
     * @param string $massActionName
     * @param bool $inset
     * @param array $values
     * @param array $filters
     * @return bool
     * @throws \LogicException
     */
    public function dispatch($datagridName, $massActionName, $inset = false, $values = array(), $filters = array())
    {
        if ($inset && empty($values)) {
            throw new \LogicException(sprintf('There is nothing to do in mass action "%s"', $massActionName));
        }

        $datagridManager = $this->managerRegistry->getDatagridManager($datagridName);

        // create datagrid
        $datagrid = $datagridManager->getDatagrid();
        $datagrid->getParameters()->set(ParametersInterface::FILTER_PARAMETERS, $filters);
        $datagrid->applyFilters();

        // get mass action
        $massAction = $this->getMassActionByName($datagrid, $massActionName);

        // apply selector parameters
        $identifierFieldName = $this->getIdentifierFieldName($datagrid);
        /** @var QueryBuilder $proxyQuery */
        $proxyQuery = $datagrid->getQuery();
        if ($values) {
            $valueWhereCondition =
                $inset
                ? $proxyQuery->expr()->in($identifierFieldName, $values)
                : $proxyQuery->expr()->notIn($identifierFieldName, $values);
            $proxyQuery->andWhere($valueWhereCondition);
        }

        // get result iterator
        $resultIterator = $this->getResultIterator($proxyQuery); // TODO implement result iterator

        // get handler
        $handle = $this->getMassActionHandler($massAction);

        // perform mass action
        // TODO implement data container
        $dataContainer = array(
            'resultIterator'  => $resultIterator,
            'massAction'      => $massAction,
            'datagridManager' => $datagridManager,
            'datagrid'        => $datagrid,
        );
        // return $handle->perform($dataContainer);

        return true;
    }

    /**
     * @param DatagridInterface $datagrid
     * @param $massActionName
     * @return MassActionInterface
     * @throws \LogicException
     */
    protected function getMassActionByName(DatagridInterface $datagrid, $massActionName)
    {
        $massAction = null;
        foreach ($datagrid->getMassActions() as $action) {
            if ($action->getName() == $massActionName) {
                $massAction = $action;
            }
        }

        if (!$massAction) {
            throw new \LogicException(sprintf('Can\'t find mass action "%s"', $massActionName));
        }

        return $massAction;
    }

    /**
     * @param ProxyQueryInterface $proxyQuery
     * @return mixed
     */
    protected function getResultIterator(ProxyQueryInterface $proxyQuery)
    {
        /** @var QueryBuilder $proxyQuery */
        return $proxyQuery->getQuery()->iterate();
    }

    /**
     * @param DatagridInterface $datagrid
     * @return string
     * @throws \LogicException
     */
    protected function getIdentifierFieldName(DatagridInterface $datagrid)
    {
        $identifierField = $datagrid->getIdentifierField();

        $fieldDescription = null;
        /** @var FieldDescriptionInterface $column */
        foreach ($datagrid->getColumns() as $column) {
            if ($column->getName() == $identifierField) {
                $fieldDescription = $column;
            }
        }

        if (!$fieldDescription) {
            throw new \LogicException(sprintf('There is no identifier field with name "%s"', $identifierField));
        }

        return $fieldDescription->getFieldName();
    }

    /**
     * @param MassActionInterface $massAction
     * @return object
     * @throws \LogicException
     */
    protected function getMassActionHandler(MassActionInterface $massAction)
    {
        $handlerServiceId = $massAction->getOption('handler');
        if (!$handlerServiceId) {
            throw new \LogicException(sprintf('There is no handler for mass action "%s"', $massAction->getName()));
        }
        if (!$this->container->has($handlerServiceId)) {
            throw new \LogicException(sprintf('Mass action handler service "%s" not exist', $handlerServiceId));
        }

        return $this->container->get($handlerServiceId);
    }
}
