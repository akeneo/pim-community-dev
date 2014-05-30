<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Datagrid\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;

/**
 * Inject the product id for proposal datagrid
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class InjectProductForProposalSubscriber
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
        $productId = $this->requestParams->get('product');

        $datagridConfig = $event->getConfig();
        $datagridConfig->offsetSetByPath("[source][product]", $productId);
    }
}
