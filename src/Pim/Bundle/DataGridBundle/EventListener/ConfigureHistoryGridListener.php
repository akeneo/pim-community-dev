<?php

namespace Pim\Bundle\DataGridBundle\EventListener;

use Oro\Bundle\DataAuditBundle\EventListener\AuditHistoryGridListener;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator;
use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;

/**
 * Listener to configure history grids.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigureHistoryGridListener
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var RequestParameters
     */
    protected $requestParams;

    /**
     * @param RequestParameters $requestParams
     */
    public function __construct(RequestParameters $requestParams)
    {
        $this->requestParams = $requestParams;
    }

    /**
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();

        $repositoryParameters = array(
            'objectClass' => str_replace('_', '\\', $this->requestParams->get(AuditHistoryGridListener::GRID_PARAM_CLASS, '')),
            'objectId' => $this->requestParams->get(AuditHistoryGridListener::GRID_PARAM_OBJECT_ID, 0),
        );

        $config->offsetSetByPath(
            sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::REPOSITORY_PARAMETERS_KEY),
            $repositoryParameters
        );
    }
}
