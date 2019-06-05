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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\SubscriptionProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Family;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductNotSubscribedException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class UpdateSubscriptionFamilyHandler
{
    /** @var ProductSubscriptionRepositoryInterface */
    private $productSubscriptionRepository;

    /** @var SubscriptionProviderInterface */
    private $subscriptionProvider;

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    public function __construct(
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository,
        SubscriptionProviderInterface $subscriptionProvider,
        FamilyRepositoryInterface $familyRepository
    ) {
        $this->productSubscriptionRepository = $productSubscriptionRepository;
        $this->subscriptionProvider = $subscriptionProvider;
        $this->familyRepository = $familyRepository;
    }

    /**
     * @param UpdateSubscriptionFamilyCommand $command
     *
     * @throws ProductNotSubscribedException
     */
    public function handle(UpdateSubscriptionFamilyCommand $command): void
    {
        $subscription = $this->productSubscriptionRepository->findOneByProductId($command->productId());
        if (null === $subscription) {
            throw ProductNotSubscribedException::notSubscribed();
        }

        $family = $this->familyRepository->findOneByIdentifier($command->familyCode());
        if (!$family instanceof Family) {
            throw new \RuntimeException(sprintf('The family "%s" was not found.', $command->familyCode()));
        }

        $this->subscriptionProvider->updateFamilyInfos($subscription->getSubscriptionId(), $family);
        // TODO: empty suggested_data? misses_mapping? dispatch an event?
    }
}
