<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\ManagerInterface;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Extension\ExtensionVisitorInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionExtension;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponseInterface;
use Pim\Bundle\DataGridBundle\Datasource\ProductDatasource;
use Pim\Bundle\DataGridBundle\Extension\Filter\FilterExtension;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Handler\MassActionHandlerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Mass action dispatcher
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassActionDispatcher
{
    /** @staticvar string */
    const FAMILY_GRID_NAME = 'family-grid';

    /** @var MassActionHandlerRegistry */
    protected $handlerRegistry;

    /** @var ManagerInterface */
    protected $manager;

    /** @var RequestParameters */
    protected $requestParams;

    /** @var MassActionParametersParser */
    protected $parametersParser;

    /** @var array */
    protected $supportedGridNames;

    /**
     * @param MassActionHandlerRegistry  $handlerRegistry
     * @param ManagerInterface           $manager
     * @param RequestParameters          $requestParams
     * @param MassActionParametersParser $parametersParser
     * @param array                      $supportedGridNames
     */
    public function __construct(
        MassActionHandlerRegistry $handlerRegistry,
        ManagerInterface $manager,
        RequestParameters $requestParams,
        MassActionParametersParser $parametersParser,
        array $supportedGridNames
    ) {
        $this->handlerRegistry = $handlerRegistry;
        $this->manager = $manager;
        $this->requestParams = $requestParams;
        $this->parametersParser = $parametersParser;
        $this->supportedGridNames = $supportedGridNames;
    }

    /**
     * Dispatch datagrid mass action
     *
     * @param array $parameters
     *
     * @return MassActionResponseInterface
     */
    public function dispatch(array $parameters)
    {
        $parameters = $this->prepareMassActionParameters($parameters);

        $datagrid = $parameters['datagrid'];
        $massAction = $parameters['massAction'];

        return $this->performMassAction($datagrid, $massAction);
    }

    /**
     * Extract applied filters from the datasource, only implemented for ProductDatasource
     *
     * If Inset is defined, it returns filter on entity ids, else it returns all applied filters on the grid
     *
     * @param array $parameters
     *
     * @return array
     */
    public function getRawFilters(array $parameters)
    {
        $parameters = $this->prepareMassActionParameters($parameters);
        $datagrid = $parameters['datagrid'];
        $datasource = $datagrid->getDatasource();

        if (!$datasource instanceof ProductDatasource) {
            throw new \LogicException('getRawFilters is only implemented for ProductDatasource');
        }

        if (true === $parameters['inset']) {
            $filters = [['field' => 'id', 'operator' => 'IN', 'value' => $parameters['values']]];
        } else {
            $filters = $datasource->getProductQueryBuilder()->getRawFilters();
        }

        $datasourceParams = $datasource->getParameters();
        $contextParams = [];
        if (is_array($datasourceParams)) {
            $contextParams = [
                'locale' => $datasourceParams['dataLocale'],
                'scope'  => $datasourceParams['scopeCode']
            ];
        }

        foreach ($filters as &$filter) {
            if (isset($filter['context'])) {
                $filter['context'] = array_merge($filter['context'], $contextParams);
            } else {
                $filter['context'] = $contextParams;
            }
        }

        return $filters;
    }

    /**
     * Dispatch datagrid mass action
     *
     * @param array $parameters
     *
     * @throws \LogicException
     *
     * @return array
     */
    protected function prepareMassActionParameters(array $parameters)
    {
        $inset = $this->prepareInsetParameter($parameters);
        $values = $this->prepareValuesParameter($parameters);
        $filters = $this->prepareFiltersParameter($parameters);

        if ($inset && empty($values)) {
            throw new \LogicException(sprintf('There is nothing to do in mass action "%s"', $parameters['actionName']));
        }

        $datagrid = $this->manager->getDatagrid($parameters['gridName']);
        $massAction = $this->getMassActionByName($parameters['actionName'], $datagrid);
        $this->requestParams->set(FilterExtension::FILTER_ROOT_PARAM, $filters);

        $qb = $datagrid->getAcceptedDatasource()->getQueryBuilder();
        if (self::FAMILY_GRID_NAME === $parameters['gridName']) {
            $qbLocaleParameter = $qb->getParameter('localeCode');
            if (null !== $qbLocaleParameter && null === $qbLocaleParameter->getValue()) {
                $qb->setParameter('localeCode', $parameters['dataLocale']);
            }
        }

        $datasource = $datagrid->getDatasource();
        if (in_array($parameters['gridName'], $this->supportedGridNames)) {
            $qb = $datasource->getProductQueryBuilder();
        }

        $repository = $datasource->getMassActionRepository();
        $repository->applyMassActionParameters($qb, $inset, $values);

        return [
            'datagrid'   => $datagrid,
            'massAction' => $massAction,
            'inset'      => $inset,
            'values'     => $values
        ];
    }

    /**
     * @param array $parameters
     *
     * @return bool
     */
    protected function prepareInsetParameter(array $parameters)
    {
        return isset($parameters['inset']) ? $parameters['inset'] : true;
    }

    /**
     * @param array $parameters
     *
     * @return array
     */
    protected function prepareValuesParameter(array $parameters)
    {
        return isset($parameters['values']) ? $parameters['values'] : [];
    }

    /**
     * @param array $parameters
     *
     * @return array
     */
    protected function prepareFiltersParameter(array $parameters)
    {
        return isset($parameters['filters']) ? $parameters['filters'] : [];
    }

    /**
     * Prepare query builder, apply mass action parameters and call handler
     *
     * @param DatagridInterface   $datagrid
     * @param MassActionInterface $massAction
     *
     * @return MassActionResponseInterface
     */
    protected function performMassAction(
        DatagridInterface $datagrid,
        MassActionInterface $massAction
    ) {
        $handler = $this->getMassActionHandler($massAction);

        return $handler->handle($datagrid, $massAction);
    }

    /**
     * @param string            $massActionName
     * @param DatagridInterface $datagrid
     *
     * @throws \LogicException
     *
     * @return \Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface
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
     * Get mass action handler from handler registry
     *
     * @param MassActionInterface $massAction
     *
     * @return MassActionHandlerInterface
     */
    protected function getMassActionHandler(MassActionInterface $massAction)
    {
        $handlerAlias = $massAction->getOptions()->offsetGet('handler');
        $handler = $this->handlerRegistry->getHandler($handlerAlias);

        return $handler;
    }

    /**
     * Get mass action from mass action and datagrid names
     *
     * @param string $actionName
     * @param string $datagridName
     *
     * @return \Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface
     *
     * TODO: Need some clean up and optimization
     */
    public function getMassActionByNames($actionName, $datagridName)
    {
        $datagrid = $this->manager->getDatagrid($datagridName);

        return $this->getMassActionByName($actionName, $datagrid);
    }
}
