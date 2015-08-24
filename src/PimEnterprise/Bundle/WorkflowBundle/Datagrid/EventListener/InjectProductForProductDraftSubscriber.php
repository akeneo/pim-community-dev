<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Datagrid\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface;

/**
 * Inject the product id for product draft datagrid
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class InjectProductForProductDraftSubscriber
{
    /** @var RequestParameters $requestParams */
    protected $requestParams;

    /**
     * Constructor
     *
     * @param RequestParameters $requestParameters
     */
    public function __construct(RequestParameters $requestParameters)
    {
        $this->requestParams = $requestParameters;
    }

    /**
     * Method calls on build before event
     *
     * @param BuildBefore $event
     */
    public function buildBefore(BuildBefore $event)
    {
        $productId = $this->requestParams->get('product', null);

        if (null !== $productId) {
            $datagridConfig = $event->getConfig();
            $datagridConfig->offsetSetByPath(
                sprintf(ConfiguratorInterface::SOURCE_PATH, ConfiguratorInterface::REPOSITORY_PARAMETERS_KEY),
                ['product' => $productId]
            );
        }
    }
}
