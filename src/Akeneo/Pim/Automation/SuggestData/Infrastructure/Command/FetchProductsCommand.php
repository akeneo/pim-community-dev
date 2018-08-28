<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Command;

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\FetchProductsHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class FetchProductsCommand extends ContainerAwareCommand
{
    /** @var string */
    const NAME = 'pimee:suggest-data:fetch-products';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = new \Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\FetchProductsCommand();
        $this->getFetchProductsHandler()->handle($command);
    }

    /**
     * @return FetchProductsHandler
     */
    private function getFetchProductsHandler()
    {
        return $this->getContainer()->get('akeneo.pim.automation.suggest_data.product_subscription.handler.fetch_products');
    }
}
