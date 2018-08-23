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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject;

/**
 * Encapsulates a raw subscription list API response returned by PIM.ai
 *
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SubscriptionCollection implements \Countable
{
    private $collection;

    public function __construct(array $rawApiResponse)
    {
        $this->validateResponseFormat($rawApiResponse);
        $this->collection = $this->buildCollection($rawApiResponse);
    }

    public function getSubscriptions(): iterable
    {
        return $this->collection;
    }

    public function getFirst(): ?Subscription
    {
        if (! array_key_exists(0, $this->collection)) {
            return null;
        }

        return $this->collection[0];
    }

    public function count()
    {
        return count($this->collection);
    }

    private function buildCollection(array $rawApiResponse): array
    {
        $collection = [];

        foreach ($rawApiResponse['_embedded']['subscription'] as $rawSubscription) {
            $collection[] = new Subscription($rawSubscription);
        }

        return $collection;
    }

    private function validateResponseFormat(array $rawApiResponse): void
    {
        if (! isset($rawApiResponse['_embedded']['subscription']) || ! is_array($rawApiResponse['_embedded']['subscription'])) {
            throw new \InvalidArgumentException('Missing "_embeded" and/or "subscription" keys in API response');
        }
    }
}
