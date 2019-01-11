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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Subscription\SubscriptionsCollection;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class SubscriptionsCursor implements \Iterator
{
    /** @var SubscriptionsCollection */
    private $currentCollection;

    /** @var int */
    private $mainIndex;

    /**
     * {@inheritdoc}
     */
    public function __construct(SubscriptionsCollection $currentCollection)
    {
        $this->currentCollection = $currentCollection;
        $this->mainIndex = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function current(): ?ProductSubscriptionResponse
    {
        if (null === $subscription = $this->currentCollection->current()) {
            return null;
        }

        $suggestedValues = array_map(
            function (array $data) {
                return [
                    'pimAttributeCode' => $data['name'],
                    'value' => $data['value'],
                ];
            },
            $subscription->getAttributes()
        );

        return new ProductSubscriptionResponse(
            $subscription->getTrackerId(),
            $subscription->getSubscriptionId(),
            $suggestedValues,
            $subscription->isMappingMissing(),
            $subscription->isCancelled()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function next(): void
    {
        ++$this->mainIndex;
        $this->currentCollection->next();

        if (!$this->currentCollection->valid() && $this->currentCollection->hasNextPage()) {
            $this->currentCollection = $this->currentCollection->getNextPage();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function key(): int
    {
        return $this->mainIndex;
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        return $this->currentCollection->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->mainIndex = 0;
        $this->currentCollection->rewind();
    }
}
