<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\EventListener\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;

/**
 * Grid listener to configure the parameters of the rule grid in case it is displayed for a resource context
 *
 * @author Clement Gautier <clement.gautier@akeneo.com>
 */
class ConfigureRuleRelationGridListener
{
    /** @var RequestParameters */
    protected $requestParams;

    /**
     * @param RequestParameters $requestParams
     */
    public function __construct(RequestParameters $requestParams)
    {
        $this->requestParams = $requestParams;
    }

    /**
     * @param BuildAfter $event
     */
    public function configure(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();

        if (null !== $this->requestParams->get('resourceName', null)) {
            $datasource->getQueryBuilder()->joinResource(
                $this->requestParams->get('resourceName'),
                $this->requestParams->get('resourceId')
            );
        }
    }
}
