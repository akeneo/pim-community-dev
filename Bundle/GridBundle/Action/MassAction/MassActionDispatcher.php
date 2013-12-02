<?php

namespace Oro\Bundle\GridBundle\Action\MassAction;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\GridBundle\Datagrid\ORM\IterableResult;
use Oro\Bundle\GridBundle\Datagrid\DatagridManagerRegistry;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Datagrid\IterableResultInterface;
use Oro\Bundle\GridBundle\Datagrid\ResultRecord;
use Oro\Bundle\GridBundle\Action\MassAction\MassActionMediator;
use Oro\Bundle\GridBundle\Action\MassAction\MassActionResponseInterface;

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
     * @param string $actionName
     * @param array $parameters
     * @param array $data
     * @throws \LogicException
     *
     * @return MassActionResponseInterface
     */
    public function dispatch($datagridName, $actionName, array $parameters, array $data = array())
    {
        $inset = true;
        if (isset($parameters['inset'])) {
            $inset = $parameters['inset'];
        }

        $values = array();
        if (isset($parameters['values'])) {
            $values = $parameters['values'];
        }

        $filters = array();
        if (isset($parameters['filters'])) {
            $filters = $parameters['filters'];
        }

        if ($inset && empty($values)) {
            throw new \LogicException(sprintf('There is nothing to do in mass action "%s"', $actionName));
        }

        $datagridManager = $this->managerRegistry->getDatagridManager($datagridName);

        // create datagrid
        $datagrid = $datagridManager->getDatagrid();
        $datagrid->getParameters()->set(ParametersInterface::FILTER_PARAMETERS, $filters);
        $datagrid->applyFilters();

        // create mediator
        $massAction = $this->getMassActionByName($datagrid, $actionName);
        $proxyQuery = $this->getDatagridQuery($datagrid, $inset, $values);
        $resultIterator = $this->getResultIterator($proxyQuery);
        $mediator = new MassActionMediator($massAction, $datagrid, $resultIterator, $data);

        // perform mass action
        $handle = $this->getMassActionHandler($massAction);
        $result = $handle->handle($mediator);

        return $result;
    }

    /**
     * @param DatagridInterface $datagrid
     * @param bool $inset
     * @param array $values
     * @return ProxyQueryInterface
     */
    protected function getDatagridQuery(DatagridInterface $datagrid, $inset = true, $values = array())
    {
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

        return $proxyQuery;
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
     * @param int|null $bufferSize
     * @return IterableResultInterface
     */
    protected function getResultIterator(ProxyQueryInterface $proxyQuery, $bufferSize = null)
    {
        $result = new IterableResult($proxyQuery);

        if ($bufferSize) {
            $result->setBufferSize($bufferSize);
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
        $fieldMapping = $identifierField->getFieldMapping();

        if (!empty($fieldMapping['fieldExpression'])) {
            return $fieldMapping['fieldExpression'];
        }

        return sprintf('%s.%s', $datagrid->getQuery()->getRootAlias(), $identifierField->getFieldName());
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
