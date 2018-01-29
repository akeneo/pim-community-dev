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

    /**
     * @param MassActionHandlerRegistry  $handlerRegistry
     * @param ManagerInterface           $manager
     * @param RequestParameters          $requestParams
     * @param MassActionParametersParser $parametersParser
     */
    public function __construct(
        MassActionHandlerRegistry $handlerRegistry,
        ManagerInterface $manager,
        RequestParameters $requestParams,
        MassActionParametersParser $parametersParser
    ) {
        $this->handlerRegistry = $handlerRegistry;
        $this->manager = $manager;
        $this->requestParams = $requestParams;
        $this->parametersParser = $parametersParser;
    }

    /**
     * Dispatch datagrid mass action
     *
     * @param Request $request
     *
     * @throws \LogicException
     *
     * @return MassActionResponseInterface
     */
    public function dispatch(Request $request)
    {
        $parameters = $this->prepareMassActionParameters($request);
        $datagrid = $parameters['datagrid'];
        $massAction = $parameters['massAction'];
        $inset = $parameters['inset'];
        $values = $parameters['values'];

        return $this->performMassAction($datagrid, $massAction, $inset, $values);
    }

    /**
     * Extract applied filters from the datasource, only implemented for ProductDatasource
     *
     * If Inset is defined, it returns filter on entity ids, else it returns all applied filters on the grid
     *
     * @param Request $request
     *
     * @throws \LogicException
     *
     * @return array
     */
    public function getRawFilters(Request $request)
    {
        $parameters = $this->prepareMassActionParameters($request);
        $datagrid = $parameters['datagrid'];
        $datasource = $datagrid->getDatasource();

        if (!$datasource instanceof ProductDatasource) {
            throw new \LogicException('getRawFilters is only implemented for ProductDatasource');
        }

        if (true === $parameters['inset']) {
            $filters = [['field' => 'id', 'operator' => 'IN', 'value' => $parameters['values']]];
        } else {
            if (empty($parameters['values'])) {
                $filters = $datasource->getProductQueryBuilder()->getRawFilters();
            } else {
                $filters = array_merge(
                    $datasource->getProductQueryBuilder()->getRawFilters(),
                    [['field' => 'id', 'operator' => 'NOT IN', 'value' => $parameters['values']]]
                );
            }
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
     * @param Request $request
     *
     * @throws \LogicException
     *
     * @return array
     */
    protected function prepareMassActionParameters(Request $request)
    {
        $parameters = $this->parametersParser->parse($request);
        $inset = $this->prepareInsetParameter($parameters);
        $values = $this->getValues($request);
        $filters = $this->prepareFiltersParameter($parameters);

        $actionName = $request->get('actionName');
        if ($inset && empty($values)) {
            throw new \LogicException(sprintf('There is nothing to do in mass action "%s"', $actionName));
        }

        $datagridName = $request->get('gridName');
        $datagrid = $this->manager->getDatagrid($datagridName);
        $massAction = $this->getMassActionByName($actionName, $datagrid);
        $this->requestParams->set(FilterExtension::FILTER_ROOT_PARAM, $filters);

        $qb = $datagrid->getAcceptedDatasource()->getQueryBuilder();

        if (self::FAMILY_GRID_NAME === $datagridName) {
            $qbLocaleParameter = $qb->getParameter('localeCode');

            if (null !== $qbLocaleParameter && null === $qbLocaleParameter->getValue()) {
                $qb->setParameter('localeCode', $request->query->get('dataLocale'));
            }
        }

        $repository = $datagrid->getDatasource()->getMassActionRepository();
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

    /**
     * @PIM-7132
     *
     * Depending on the context:
     * - the values might be in the request form data (mass edit context)
     * - The values might be in a URL parameter (quick export)
     *
     * @param Request $request
     *
     * @return array
     */
    private function getValues(Request $request)
    {
        $values = $this->getValuesFromRequest($request);
        if (empty($values)) {
            $values = $this->prepareValuesParameter($this->parametersParser->parse($request));
        }

        return $values;
    }

    /**
     * @PIM-7132
     *
     * We get the values (which are the selected row ids) from the request because the selected ids are passed through
     * the form data (POST) via a field called 'itemIds' which is hidden and is not part of any form type.
     *
     * Prior to this fix, the ids would be passed through query parameters. On submit the form would not be processed
     * because the URI would be too long.
     *
     * @param Request $request
     *
     * @return array
     */
    private function getValuesFromRequest(Request $request)
    {
        $all = $request->request->all();

        return isset($all['itemIds']) ? explode(',', $all['itemIds']) : [];
    }
}
