<?php

namespace Oro\Bundle\DataGridBundle\Extension\MassAction;

use Doctrine\ORM\QueryBuilder;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;

use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\Orm\IterableResult;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Extension\ExtensionVisitorInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Oro\Bundle\FilterBundle\Grid\Extension\OrmFilterExtension;

class MassActionDispatcher
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Manager
     */
    protected $manager;

    /** @var RequestParameters */
    protected $requestParams;

    public function __construct(ContainerInterface $container, Manager $manager, RequestParameters $requestParams)
    {
        $this->container     = $container;
        $this->manager       = $manager;
        $this->requestParams = $requestParams;
    }

    /**
     * @param string $datagridName
     * @param string $actionName
     * @param array  $parameters
     * @param array  $data
     *
     * @throws \LogicException
     *
     * @return MassActionResponseInterface
     */
    public function dispatch($datagridName, $actionName, array $parameters, array $data = [])
    {
        $inset = true;
        if (isset($parameters['inset'])) {
            $inset = $parameters['inset'];
        }

        $values = [];
        if (isset($parameters['values'])) {
            $values = $parameters['values'];
        }

        $filters = [];
        if (isset($parameters['filters'])) {
            $filters = $parameters['filters'];
        }

        if ($inset && empty($values)) {
            throw new \LogicException(sprintf('There is nothing to do in mass action "%s"', $actionName));
        }

        // create datagrid
        $datagrid = $this->manager->getDatagrid($datagridName);

        // set filter data
        $this->requestParams->set(OrmFilterExtension::FILTER_ROOT_PARAM, $filters);

        // create mediator
        $massAction     = $this->getMassActionByName($actionName, $datagrid);
        $identifier     = $this->getIdentifierField($massAction);
        $qb             = $this->getDatagridQuery($datagrid, $identifier, $inset, $values);
        $resultIterator = $this->getResultIterator($qb);
        $mediator       = new MassActionMediator($massAction, $datagrid, $resultIterator, $data);

        // perform mass action
        $handle = $this->getMassActionHandler($massAction);
        $result = $handle->handle($mediator);

        return $result;
    }

    /**
     * @param DatagridInterface $datagrid
     * @param string            $identifierField
     * @param bool              $inset
     * @param array             $values
     *
     * @return QueryBuilder
     * @throws \LogicException
     */
    protected function getDatagridQuery(
        DatagridInterface $datagrid,
        $identifierField = 'id',
        $inset = true,
        $values = []
    ) {
        $datasource = $datagrid->getDatasource();
        if (!$datasource instanceof OrmDatasource) {
            throw new \LogicException("Mass actions applicable only for datagrids with ORM datasource.");
        }

        /** @var QueryBuilder $qb */
        $qb = $datagrid->getAcceptedDatasource()->getQueryBuilder();
        if ($values) {
            $valueWhereCondition =
                $inset
                    ? $qb->expr()->in($identifierField, $values)
                    : $qb->expr()->notIn($identifierField, $values);
            $qb->andWhere($valueWhereCondition);
        }

        return $qb;
    }

    /**
     * @param string            $massActionName
     * @param DatagridInterface $datagrid
     *
     * @return \Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface
     * @throws \LogicException
     */
    protected function getMassActionByName($massActionName, DatagridInterface $datagrid)
    {
        $massAction = null;
        $extensions = array_filter(
            $datagrid->getAcceptor()->getExtensions(),
            function (ExtensionVisitorInterface $extension) {
                return $extension instanceof MassActionExtension;
            }
        );

        /** @var MassActionExtension|bool $extension */
        $extension = reset($extensions);
        if ($extension === false) {
            throw new \LogicException("MassAction extension is not applied to datagrid.");
        }

        $massAction = $extension->getMassAction($massActionName, $datagrid);

        if (!$massAction) {
            throw new \LogicException(sprintf('Can\'t find mass action "%s"', $massActionName));
        }

        return $massAction;
    }

    /**
     * @param QueryBuilder $qb
     * @param null         $bufferSize
     *
     * @return IterableResult
     */
    protected function getResultIterator(QueryBuilder $qb, $bufferSize = null)
    {
        $result = new IterableResult($qb);

        if ($bufferSize) {
            $result->setBufferSize($bufferSize);
        }

        return $result;
    }

    /**
     * @param MassActionInterface $massAction
     *
     * @return MassActionHandlerInterface
     * @throws \LogicException
     * @throws UnexpectedTypeException
     */
    protected function getMassActionHandler(MassActionInterface $massAction)
    {
        $handlerServiceId = $massAction->getOptions()->offsetGet('handler');
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

    /**
     * @param Actions\MassActionInterface $massAction
     *
     * @throws \LogicException
     *
     * @return string
     */
    protected function getIdentifierField(MassActionInterface $massAction)
    {
        $identifier = $massAction->getOptions()->offsetGet('data_identifier');
        if (!$identifier) {
            throw new \LogicException(sprintf('Mass action "%s" must define identifier name', $massAction->getName()));
        }

        return $identifier;
    }
}
