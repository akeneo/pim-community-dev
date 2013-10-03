<?php

namespace Pim\Bundle\GridBundle\Action\MassAction;

use Oro\Bundle\GridBundle\Action\MassAction\MassActionMediator;
use Oro\Bundle\GridBundle\Action\MassAction\MassActionResponseInterface;
use Oro\Bundle\GridBundle\Action\MassAction\MassActionDispatcher as OroMassActionDispatcher;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\UserBundle\Acl\ManagerInterface as AclManagerInterface;

use Pim\Bundle\CatalogBundle\Datagrid\ProductDatagridManager;

/**
 * Extends Oro MassActionDispatcher to add category filters
 */
class MassActionDispatcher extends OroMassActionDispatcher
{
    /**
     * @var AclManagerInterface
     */
    protected $aclManager;

    /**
     * Set the ACL Manager
     * 
     * @param AclManagerInterface $aclManager
     */
    public function setAclManager(AclManagerInterface $aclManager)
    {
        $this->aclManager = $aclManager;
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
            if (!$this->aclManager->isResourceGranted('pim_catalog_product_remove')) {
                throw new \RuntimeException('Mass delete is not allowed by ACLs');
            }
            $datagridManager->setFilterTreeId(isset($data['treeId']) ? $data['treeId'] : 0);
            $datagridManager->setFilterCategoryId(isset($data['categoryId']) ? $data['categoryId'] : 0);
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
