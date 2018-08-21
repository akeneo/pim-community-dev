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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Query\Memory;

use Akeneo\Pim\Automation\SuggestData\Domain\Query\GetSubscriptionStatusForProductInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Repository\Memory\InMemoryProductSubscriptionRepository;

/**
 * Fake implementation to check if a product was subscribed to Franklin.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetSubscriptionStatusForProduct implements GetSubscriptionStatusForProductInterface
{
    /** @var InMemoryProductSubscriptionRepository */
    private $productSubscriptionRepository;

    /**
     * @param InMemoryProductSubscriptionRepository $productSubscriptionRepository
     */
    public function __construct(InMemoryProductSubscriptionRepository $productSubscriptionRepository)
    {
        $this->productSubscriptionRepository = $productSubscriptionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function query(int $productId): bool
    {
        return isset($this->productSubscriptionRepository->subscriptions[$productId]);
    }
}
