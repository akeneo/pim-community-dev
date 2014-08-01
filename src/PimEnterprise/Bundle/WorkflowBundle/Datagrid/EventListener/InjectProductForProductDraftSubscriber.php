<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Datagrid\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use PimEnterprise\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator;

/**
 * Inject the product id for proposition datagrid
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
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
                sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::REPOSITORY_PARAMETERS_KEY),
                ['product' => $productId]
            );
        }
    }
}
