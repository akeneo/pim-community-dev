<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Symfony\Command;

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\FetchProductsCommand
    as AppFetchProductsCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\FetchProductsHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Symfony command that allows to launch data pulling to Franklin
 * It can be used automatically with a CRON but also manually from a specific date (TODO: APAI-170).
 *
 * @author    Romain Monceau <romain@akeneo.com>
 */
class FetchProductsCommand extends ContainerAwareCommand
{
    /** @var string */
    public const NAME = 'pimee:suggest-data:fetch-products';

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $command = new AppFetchProductsCommand();
        $this->getFetchProductsHandler()->handle($command);
    }

    /**
     * @return FetchProductsHandler
     */
    private function getFetchProductsHandler()
    {
        return $this
            ->getContainer()
            ->get('akeneo.pim.automation.suggest_data.product_subscription.handler.fetch_products');
    }
}
