<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction;

use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\ManagerInterface;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Extension\ExtensionVisitorInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionExtension;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;

use Pim\Bundle\DataGridBundle\Extension\Filter\OrmFilterExtension;

/**
 * Mass action dispatcher
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassActionDispatcher
{
    /** @var MassActionHandlerRegistry $handlerRegistry */
    protected $handlerRegistry;

    /** @var Manager $manager */
    protected $manager;

    /** @var RequestParameters $requestParams */
    protected $requestParams;

    /** @var MassActionParametersParser $parametersParser */
    protected $parametersParser;

    /**
     * Constructor
     *
     * @param MassActionHandlerRegistry  $handlerRegistry
     * @param Manager                    $manager
     * @param RequestParameters          $requestParams
     * @param MassActionParametersParser $parametersParser
     */
    public function __construct(
        MassActionHandlerRegistry $handlerRegistry,
        ManagerInterface $manager,
        RequestParameters $requestParams,
        MassActionParametersParser $parametersParser
    ) {
        $this->handlerRegistry  = $handlerRegistry;
        $this->manager          = $manager;
        $this->requestParams    = $requestParams;
        $this->parametersParser = $parametersParser;
    }

    /**
     * Dispatch datagrid mass action
     *
     * @throws \LogicException
     *
     * @return MassActionResponseInterface
     */
    public function dispatch($request)
    {
        $parameters   = $this->parametersParser->parse($request);
        $datagridName = $request->get('gridName');
        $actionName   = $request->get('actionName');

        $inset   = isset($parameters['inset'])   ? $parameters['inset']   : true;
        $values  = isset($parameters['values'])  ? $parameters['values']  : [];
        $filters = isset($parameters['filters']) ? $parameters['filters'] : [];

        if ($inset && empty($values)) {
            throw new \LogicException(sprintf('There is nothing to do in mass action "%s"', $actionName));
        }

        $datagrid = $this->manager->getDatagrid($datagridName);
        $this->requestParams->set(OrmFilterExtension::FILTER_ROOT_PARAM, $filters);

        // create datagrid, prepare query and apply mass action parameters
        $qb = $datagrid->getAcceptedDatasource()->getQueryBuilder();
        $massAction = $this->getMassActionByName($actionName, $datagrid);
        $identifier = $this->getIdentifierField($massAction);

        $repository = $datagrid->getDatasource()->getRepository();
        $repository->applyMassActionParameters($qb, $identifier, $inset, $values);

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
     *
     * @throws UnexpectedTypeException
     */
    protected function getMassActionHandler(MassActionInterface $massAction)
    {
        $handlerAlias = $massAction->getOptions()->offsetGet('handler');
        $handler      = $this->handlerRegistry->getHandler($handlerAlias);

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

        return $this->getMassActionByName($actionName, $datagrid);
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
