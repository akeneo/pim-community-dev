<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;

/**
 * Inject the entity with values id for product model draft ddatagrid
 *
 * @author Bryan Stéphan <bryan.stephan@akeneo.com>
 */
class InjectEntityWithValuesForProductModelDraftSubscriber
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
        $entityWithValuesId = $this->requestParams->get('entityWithValues', null);

        if (null !== $entityWithValuesId) {
            $datagridConfig = $event->getConfig();
            $datagridConfig->offsetSetByPath(
                sprintf(ConfiguratorInterface::SOURCE_PATH, ConfiguratorInterface::REPOSITORY_PARAMETERS_KEY),
                ['entityWithValuesId' => $entityWithValuesId]
            );
        }
    }
}
