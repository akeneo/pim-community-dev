<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\ManagerInterface;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Extension\ExtensionVisitorInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionExtension;

use Pim\Bundle\DataGridBundle\Extension\Filter\OrmFilterExtension;
use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerInterface;

/**
 * Product mass action dispatcher
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductMassActionDispatcher
{
    // TODO: Must be replaced by handler registry
    /** @var ContainerInterface $container */
    protected $container;

    /** @var Manager $manager */
    protected $manager;

    /** @var RequestParameters $requestParams */
    protected $requestParams;

    /**
     * Constructor
     *
     * @param ContainerInterface         $container
     * @param Manager                    $manager
     * @param RequestParameters          $requestParams
     */
    public function __construct(
        ContainerInterface $container,
        ManagerInterface $manager,
        RequestParameters $requestParams
    ) {
        $this->container     = $container;
        $this->manager       = $manager;
        $this->requestParams = $requestParams;
    }

    /**
     * Dispatch datagrid mass action
     *
     * @param string $datagridName
     * @param string $actionName
     * @param array  $parameters
     * @param array  $data
     *
     * @throws \LogicException
     *
     * @return MassActionResponseInterface
     *
     * TODO: Dispatcher can knows the @request to get all these parameters
     */
    public function dispatch($datagridName, $actionName, array $parameters, array $data)
    {
        $inset   = isset($parameters['inset'])   ? $parameters['inset']   : true;
        $values  = isset($parameters['values'])  ? $parameters['values']  : [];
        $filters = isset($parameters['filters']) ? $parameters['filters'] : [];

        if ($inset && empty($values)) {
            throw new \LogicException(sprintf('There is nothing to do in mass action "%s"', $actionName));
        }

        $datagrid   = $this->manager->getDatagrid($datagridName);

        // set filter data
        $this->requestParams->set(OrmFilterExtension::FILTER_ROOT_PARAM, $filters);

        // create datagrid and prepare query
        $qb = $datagrid->getAcceptedDatasource()->getQueryBuilder();

        // Apply mass action parameters on qb
        $massAction = $this->getMassActionByName($actionName, $datagrid);
        $dataLocale = $this->container->get('request')->get('dataLocale', null);
        $scopeCode  = isset($filters['scope']['value']) ? $filters['scope']['value'] : null;
        $identifier = $this->getIdentifierField($massAction);

        $repository = $datagrid->getDatasource()->getRepository();
        $repository->applyMassActionParameters($qb, $identifier, $inset, $values, $dataLocale, $scopeCode);

        // perform mass action
        $handler = $this->getMassActionHandler($massAction);

        return $handler->handle($datagrid, $massAction);
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
     * @param MassActionInterface $massAction
     *
     * @return MassActionHandlerInterface
     * @throws \LogicException
     * @throws UnexpectedTypeException
     *
     * TODO: This method must be replace by an HandlerRegistry
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

        return parent::getMassActionByName($actionName, $datagrid);
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
