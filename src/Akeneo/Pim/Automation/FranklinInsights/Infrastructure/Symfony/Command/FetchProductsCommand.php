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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FetchProductsCommand extends Command
{
    public const NAME = 'pimee:franklin-insights:fetch-products';

    protected static $defaultName = self::NAME;

    /** @var ScheduleFetchProductsInterface */
    private $scheduleFetchProducts;

    /** @var GetConnectionStatusHandler */
    private $getConnectionStatusHandler;

    public function __construct(
        ScheduleFetchProductsInterface $scheduleFetchProducts,
        GetConnectionStatusHandler $getConnectionStatusHandler
    ) {
        parent::__construct();

        $this->scheduleFetchProducts = $scheduleFetchProducts;
        $this->getConnectionStatusHandler = $getConnectionStatusHandler;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Schedule fetch products from Ask Franklin');
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
