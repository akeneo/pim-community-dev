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

namespace Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;

/**
 * Handles an UnsubscribeProduct command.
 *
 * It checks that the product is subscribed and unsubscribe it
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class UnsubscribeProductHandler
{
    /** @var ProductSubscriptionRepositoryInterface */
    private $subscriptionRepository;

    /** @var DataProviderInterface */
    private $dataProvider;

    /**
     * @param ProductSubscriptionRepositoryInterface $subscriptionRepository,
     * @param DataProviderFactory $dataProviderFactory
     */
    public function __construct(
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        DataProviderFactory $dataProviderFactory
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->dataProvider = $dataProviderFactory->create();
    }

    /**
     * @param UnsubscribeProductCommand $command
     */
    public function handle(UnsubscribeProductCommand $command): void
    {
        $subscription = $this->subscriptionRepository->findOneByProductId($command->getProductId());
        if (null === $subscription) {
            throw new ProductSubscriptionException(
                sprintf('The product with id "%d" is not subscribed', $command->getProductId())
            );
        }

        $this->dataProvider->unsubscribe($subscription->getSubscriptionId());

        $this->subscriptionRepository->delete($subscription);
    }
}
