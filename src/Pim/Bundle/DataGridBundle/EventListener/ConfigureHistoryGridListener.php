<?php

namespace Pim\Bundle\DataGridBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ContextConfigurator;
use Symfony\Component\HttpFoundation\Request;

/**
 * Listener to configure history grids.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigureHistoryGridListener
{
    /** @staticvar string */
    const GRID_PARAM_CLASS = 'object_class';

    /** @staticvar string */
    const GRID_PARAM_OBJECT_ID = 'object_id';

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
            'objectClass' => str_replace(
                '_',
                '\\',
                $this->requestParams->get(self::GRID_PARAM_CLASS, '')
            ),
            'objectId' => $this->requestParams->get(self::GRID_PARAM_OBJECT_ID, 0),
        );

        $config->offsetSetByPath(
            sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::REPOSITORY_PARAMETERS_KEY),
            $repositoryParameters
        );
    }
}
