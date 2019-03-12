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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Datagrid;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Datagrid\ConfigureProductGridListener;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConfigureProductGridListenerSpec extends ObjectBehavior
{
    public function let(GetConnectionStatusHandler $connectionStatusHandler): void
    {
        $this->beConstructedWith($connectionStatusHandler);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ConfigureProductGridListener::class);
    }

    public function it_configures_the_product_grid_before_it_is_built_if_the_franklin_connection_is_active(
        GetConnectionStatusHandler $connectionStatusHandler,
        BuildBefore $event,
        DatagridConfiguration $datagridConfiguration
    ): void {
        $event->getConfig()->willReturn($datagridConfiguration);
        $datagridConfiguration->getName()->willReturn('product-grid');

        $connectionStatusHandler
            ->handle(Argument::type(GetConnectionStatusQuery::class))
            ->willReturn(new ConnectionStatus(true, false, false, 0));

        $datagridConfiguration->offsetGet(Configuration::FILTERS_KEY)->willReturn([
            'columns' => [
                'family' => [
                    'type' => 'product_family',
                    'label' => 'Family',
                    'data_name' => 'family',
                ],
            ],
        ]);

        $datagridConfiguration->offsetAddToArray(Configuration::FILTERS_KEY, [
            'columns' => [
                'family' => [
                    'type' => 'product_family',
                    'label' => 'Family',
                    'data_name' => 'family',
                ],
                'franklin_subscription' => [
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
                ],
            ],
        ])->shouldBeCalled();

        $this->buildBefore($event);
    }

    public function it_does_nothing_if_it_is_not_the_product_datagrid(
        GetConnectionStatusHandler $connectionStatusHandler,
        BuildBefore $event,
        DatagridConfiguration $datagridConfiguration
    ): void {
        $event->getConfig()->willReturn($datagridConfiguration);
        $datagridConfiguration->getName()->willReturn('attribute-grid');

        $connectionStatusHandler->handle(Argument::any())->shouldNotBeCalled();
        $datagridConfiguration->offsetAddToArray(Argument::any())->shouldNotBeCalled();

        $this->buildBefore($event);
    }

    public function it_does_nothing_if_the_franklin_connection_is_not_active(
        GetConnectionStatusHandler $connectionStatusHandler,
        BuildBefore $event,
        DatagridConfiguration $datagridConfiguration
    ): void {
        $event->getConfig()->willReturn($datagridConfiguration);
        $datagridConfiguration->getName()->willReturn('product-grid');

        $connectionStatusHandler
            ->handle(Argument::type(GetConnectionStatusQuery::class))
            ->shouldBeCalled()
            ->willReturn(new ConnectionStatus(false, false, false, 0));

        $datagridConfiguration->offsetAddToArray(Argument::any())->shouldNotBeCalled();

        $this->buildBefore($event);
    }
}
