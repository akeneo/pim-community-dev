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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Subscription;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\Subscription;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class SubscriptionsCollection implements \Iterator
{
    /** @var SubscriptionWebService */
    private $subscriptionWebservice;

    /** @var array */
    private $subscriptions;

    /** @var string|null */
    private $nextPageUri;

    /** @var int */
    private $index;

    /** @var int */
    private $total;

    /**
     * @param SubscriptionWebService $subscriptionWebservice
     * @param array $collection
     */
    public function __construct(
        SubscriptionWebService $subscriptionWebservice,
        array $collection
    ) {
        $this->subscriptionWebservice = $subscriptionWebservice;
        $this->subscriptions = $collection['_embedded']['subscription'] ?? [];
        $this->nextPageUri = $collection['_links']['next'][0]['href'] ?? null;
        $this->index = 0;
        $this->total = $collection['total'];
    }

    /**
     * {@inheritdoc}
     */
    public function current(): ?Subscription
    {
        if (!isset($this->subscriptions[$this->index])) {
            return null;
        }

        try {
            return new Subscription($this->subscriptions[$this->index]);
        } catch (\InvalidArgumentException $exception) {
            $this->next();

            return $this->current();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function next(): void
    {
        ++$this->index;
    }

    /**
     * {@inheritdoc}
     */
    public function key(): int
    {
        return $this->index;
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        return isset($this->subscriptions[$this->index]);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->index = 0;
    }

    /**
     * @return bool
     */
    public function hasNextPage(): bool
    {
        return null !== $this->nextPageUri;
    }

    /**
     * @return SubscriptionsCollection|null
     */
    public function getNextPage(): ?SubscriptionsCollection
    {
        if (!$this->hasNextPage()) {
            return null;
        }

        return $this->subscriptionWebservice->fetchProducts($this->nextPageUri);
    }
}
