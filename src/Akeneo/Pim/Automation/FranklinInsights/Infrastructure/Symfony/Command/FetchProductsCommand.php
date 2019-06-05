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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Symfony\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Service\ScheduleFetchProductsInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FetchProductsCommand extends ContainerAwareCommand
{
    public const NAME = 'pimee:franklin-insights:fetch-products';

    /** @var ScheduleFetchProductsInterface */
    private $scheduleFetchProducts;

    /** @var GetConnectionStatusHandler */
    private $getConnectionStatusHandler;

    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Schedule fetch products from Ask Franklin');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->scheduleFetchProducts = $this->getContainer()->get('akeneo.pim.automation.franklin_insights.connector.job_launcher.schedule_fetch_products');
        $this->getConnectionStatusHandler = $this->getContainer()->get(
            'akeneo.pim.automation.franklin_insights.application.configuration.query.get_connection_status_handler'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $connectionStatus = $this->getConnectionStatusHandler->handle(new GetConnectionStatusQuery(false));
        if (false === $connectionStatus->isActive()) {
            return;
        }

        $this->scheduleFetchProducts->schedule();
    }
}
