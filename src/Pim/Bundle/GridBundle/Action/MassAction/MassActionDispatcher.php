<?php

namespace Pim\Bundle\GridBundle\Action\MassAction;

use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\GridBundle\Datagrid\ORM\IterableResult;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Datagrid\IterableResultInterface;
use Oro\Bundle\GridBundle\Action\MassAction\MassActionMediator;
use Oro\Bundle\GridBundle\Action\MassAction\MassActionResponseInterface;
use Oro\Bundle\GridBundle\Action\MassAction\MassActionDispatcher as OroMassActionDispatcher;
use Pim\Bundle\CatalogBundle\Datagrid\ProductDatagridManager;

/**
 * Extends Oro MassActionDispatcher to add category filters
 */
class MassActionDispatcher extends OroMassActionDispatcher
{
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

        if ($datagridManager instanceof ProductDatagridManager) {
            if (isset($data['treeId'])) {
                $datagridManager->setFilterTreeId($data['treeId']);
            }
            if (isset($data['categoryId'])) {
                $datagridManager->setFilterCategoryId($data['categoryId']);
            }
        }
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

}
