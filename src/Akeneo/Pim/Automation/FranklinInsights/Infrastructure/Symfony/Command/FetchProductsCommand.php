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

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionIsActiveHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionIsActiveQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Service\ScheduleFetchProductsInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FetchProductsCommand extends ContainerAwareCommand
{
    public const NAME = 'pimee:franklin-insights:fetch-products';

    /** @var ScheduleFetchProductsInterface */
    private $scheduleFetchProducts;

    /** @var GetConnectionIsActiveHandler */
    private $getConnectionIsActiveHandler;

    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Schedule fetch products from Ask Franklin');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->scheduleFetchProducts = $this->getContainer()->get('akeneo.pim.automation.franklin_insights.connector.job_launcher.schedule_fetch_products');
        $this->getConnectionIsActiveHandler = $this->getContainer()->get(
            'akeneo.pim.automation.franklin_insights.application.configuration.query.get_connection_is_active_handler'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $connectionIsActive = $this->getConnectionIsActiveHandler->handle(new GetConnectionIsActiveQuery());
        if (false === $connectionIsActive) {
            return;
        }

        $this->scheduleFetchProducts->schedule();
    }
}
