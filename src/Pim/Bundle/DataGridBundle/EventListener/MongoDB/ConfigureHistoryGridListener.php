<?php

namespace Pim\Bundle\DataGridBundle\EventListener\MongoDB;

use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;

/**
 * History grid listener to reconfigure it for MongoDB
 * TODO: find a way to override or merge grids' configurations to remove this listener
 *
 * @author    Filips Alpe <filips@akeneo.com>
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
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * Reconfigure columns, filters and sorters
     *
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();

        $repositoryParams = [
            'objectClass' => $this->requestParams->get('object_class', $this->request->get('object_class', null)),
            'objectId'    => $this->requestParams->get('object_id', $this->request->get('object_id', null))
        ];
        $config->offsetSetByPath('[source][repository_parameters]', $repositoryParams);

        $config->offsetSetByPath('[columns][author][type]', 'author_property');
    }

    /**
     * Apply request parameters
     *
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $qb = $event->getDatagrid()->getDatasource()->getQueryBuilder();

        $qb->field('resourceName')->equals(str_replace('_', '\\', $this->requestParams->get('object_class', '')));
        $qb->field('resourceId')->equals($this->requestParams->get('object_id', 0));
    }
}
