<?php

namespace Oro\Bundle\GridBundle\Action\MassAction;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\GridBundle\Datagrid\DatagridManagerRegistry;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Datagrid\ResultRecord;
use Oro\Bundle\GridBundle\Action\MassAction\MassActionMediator;

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

        // apply selector parameters
        $identifierFieldExpression = $this->getIdentifierExpression($datagrid);
        /** @var QueryBuilder $proxyQuery */
        $proxyQuery = $datagrid->getQuery();
        if ($values) {
            $valueWhereCondition =
                $inset
                ? $proxyQuery->expr()->in($identifierFieldExpression, $values)
                : $proxyQuery->expr()->notIn($identifierFieldExpression, $values);
            $proxyQuery->andWhere($valueWhereCondition);
        }

        // create mediator
        $massAction = $this->getMassActionByName($datagrid, $massActionName);
        $resultIterator = $this->getResultIterator($proxyQuery);
        $mediator = new MassActionMediator($massAction, $resultIterator);

        // perform mass action
        $handle = $this->getMassActionHandler($massAction);

        return $handle->handle($mediator);
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
     * @return ResultRecord[]|\Iterator
     */
    protected function getResultIterator(ProxyQueryInterface $proxyQuery)
    {
        // TODO use result iterator
        /** @var QueryBuilder $proxyQuery */
        $result = array();
        $rows = $proxyQuery->getQuery()->execute();
        foreach ($rows as $row) {
            $result[] = new ResultRecord($row);
        }

        return $result;
    }

    /**
     * @param DatagridInterface $datagrid
     * @return string
     * @throws \LogicException
     */
    protected function getIdentifierExpression(DatagridInterface $datagrid)
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

        // compute identifier field expression
        $fieldMapping = $fieldDescription->getFieldMapping();
        if (!empty($fieldMapping['fieldExpression'])) {
            $fieldExpression = $fieldMapping['fieldExpression'];
        } elseif (!empty($fieldMapping['entityAlias'])) {
            $fieldExpression = sprintf('%s.%s', $fieldMapping['entityAlias'], $fieldMapping['fieldName']);
        } else {
            $fieldExpression = $fieldMapping['fieldName'];
        }

        return $fieldExpression;
    }


    /**
     * @param MassActionInterface $massAction
     * @return MassActionHandlerInterface
     * @throws \LogicException
     * @throws UnexpectedTypeException
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

        $handler = $this->container->get($handlerServiceId);
        if (!$handler instanceof MassActionHandlerInterface) {
            throw new UnexpectedTypeException($handler, 'MassActionHandlerInterface');
        }

        return $handler;
    }
}
