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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Symfony\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionIsActiveHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Service\ScheduleFetchProductsInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FetchProductsCommandSpec extends ObjectBehavior
{
    public function let(
        ContainerInterface $container,
        GetConnectionIsActiveHandler $getConnectionIsActiveHandler,
        ScheduleFetchProductsInterface $scheduleFetchProducts
    ): void {
        $container->get('akeneo.pim.automation.franklin_insights.connector.job_launcher.schedule_fetch_products')
            ->willReturn($scheduleFetchProducts);
        $container->get('akeneo.pim.automation.franklin_insights.application.configuration.query.get_connection_is_active_handler')
            ->willReturn($getConnectionIsActiveHandler);

        $this->setContainer($container);
    }

    public function it_is_a_command(): void
    {
        $this->shouldBeAnInstanceOf(ContainerAwareCommand::class);
    }

    public function it_has_a_name(): void
    {
        $this->getName()->shouldReturn('pimee:franklin-insights:fetch-products');
    }

    public function it_launches_the_job_when_the_connection_is_active(
        $getConnectionIsActiveHandler,
        ScheduleFetchProductsInterface $scheduleFetchProducts,
        InputInterface $input,
        OutputInterface $output
    ): void {
        $getConnectionIsActiveHandler->handle(Argument::any())->willReturn(true);

        $scheduleFetchProducts->schedule()->shouldBeCalled();

        $this->run($input, $output);
    }

    public function it_stops_when_the_connection_is_inactive(
        $getConnectionIsActiveHandler,
        ScheduleFetchProductsInterface $scheduleFetchProducts,
        InputInterface $input,
        OutputInterface $output
    ): void {
        $getConnectionIsActiveHandler->handle(Argument::any())->willReturn(false);

        $scheduleFetchProducts->schedule()->shouldNotBeCalled();

        $this->run($input, $output);
    }
}
