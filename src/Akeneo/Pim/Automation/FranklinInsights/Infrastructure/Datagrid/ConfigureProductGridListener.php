<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Datagrid;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration;

/**
 * Configures the product datagrid to add filter on Franklin subscription.
 */
class ConfigureProductGridListener
{
    private const PRODUCT_DATAGRID_NAME = 'product-grid';

    /** @var GetConnectionStatusHandler */
    private $connectionStatusHandler;

    /**
     * @param GetConnectionStatusHandler $connectionStatusHandler
     */
    public function __construct(GetConnectionStatusHandler $connectionStatusHandler)
    {
        $this->connectionStatusHandler = $connectionStatusHandler;
    }

    /**
     * @param BuildBefore $event
     */
    public function buildBefore(BuildBefore $event): void
    {
        $datagridConfiguration = $event->getConfig();

        if (!$this->isProductDatagrid($datagridConfiguration) || !$this->isFranklinConnectionsActive()) {
            return;
        }

        $filters = $datagridConfiguration->offsetGet(Configuration::FILTERS_KEY);
        $filters['columns']['franklin_subscription'] = $this->getFranklinSubscriptionFilter();

        $datagridConfiguration->offsetAddToArray(Configuration::FILTERS_KEY, $filters);
    }

    /**
     * @return array
     */
    private function getFranklinSubscriptionFilter(): array
    {
        return [
            'type' => 'franklin_subscription',
            'ftype' => 'choice',
            'label' => 'Franklin subscription',
            'data_name' => 'franklin_subscription',
            'options' => [
                'field_options' => [
                    'choices' => [
                        'Enabled' => 1,
                        'Disabled' => 0,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param DatagridConfiguration $datagridConfiguration
     *
     * @return bool
     */
    private function isProductDatagrid(DatagridConfiguration $datagridConfiguration): bool
    {
        return self::PRODUCT_DATAGRID_NAME === $datagridConfiguration->getName();
    }

    /**
     * @return bool
     */
    private function isFranklinConnectionsActive(): bool
    {
        $connectionStatus = $this->connectionStatusHandler->handle(
            new GetConnectionStatusQuery(false)
        );

        return $connectionStatus->isActive();
    }
}
