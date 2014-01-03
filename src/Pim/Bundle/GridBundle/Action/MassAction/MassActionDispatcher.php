<?php

namespace Pim\Bundle\GridBundle\Action\MassAction;

use Oro\Bundle\GridBundle\Action\MassAction\MassActionMediator;
use Oro\Bundle\GridBundle\Action\MassAction\MassActionResponseInterface;
use Oro\Bundle\GridBundle\Action\MassAction\MassActionDispatcher as OroMassActionDispatcher;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;

/**
 * Extends Oro MassActionDispatcher to add category filters
 */
class MassActionDispatcher extends OroMassActionDispatcher
{

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
    public function dispatch($datagridName, $actionName, array $parameters, array $data = array())
    {
        list($inset, $filters, $values) = $this->prepareParameters($parameters, $actionName);

        $datagridManager = $this->managerRegistry->getDatagridManager($datagridName);

        $datagrid = $datagridManager->getDatagrid();
        $datagrid->getParameters()->set(ParametersInterface::FILTER_PARAMETERS, $filters);
        $datagrid->applyFilters();

        $massAction = $this->getMassActionByName($datagrid, $actionName);
        $proxyQuery = $this->getDatagridQuery($datagrid, $inset, $values);
        $resultIterator = $this->getResultIterator($proxyQuery);
        $mediator = new MassActionMediator($massAction, $datagrid, $resultIterator, $data);

        $handle = $this->getMassActionHandler($massAction);
        $result = $handle->handle($mediator);

        return $result;
    }

    /**
     * Prepare parameters to get filters and values
     *
     * @param array  $parameters
     * @param string $actionName
     *
     * @return array
     */
    protected function prepareParameters($parameters, $actionName)
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

        return array($inset, $filters, $values);
    }
}
