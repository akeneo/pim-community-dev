<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction;

use Oro\Bundle\FilterBundle\Grid\Extension\OrmFilterExtension;

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
     * @param ContainerInterface $container
     * @param Manager $manager
     * @param RequestParameters $requestParams
     */
    public function __construct(ContainerInterface $container, Manager $manager, RequestParameters $requestParams)
    {
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

        // set filter data
        $this->request->set(OrmFilterExtension::FILTER_ROOT_PARAM, $filters);

        // create datagrid and get mass action
        $datagrid   = $this->manager->getDatagrid($datagridName);
        $massAction = $this->getMassActionByName($actionName, $datagrid);

        // perform mass action
        $handler = $this->getMassActionHandler($massAction);

        return $handler->handler($datagrid);
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
}
