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
 * Encapsulates a raw subscription list API response returned by Franklin.
 *
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SubscriptionCollection implements \Countable
{
    /** @var Subscription[] */
    private $collection;

    /**
     * @param array $rawApiResponse
     */
    public function __construct(array $rawApiResponse)
    {
        $this->validateResponseFormat($rawApiResponse);
        $this->collection = $this->buildCollection($rawApiResponse);
    }

    /**
     * @return array
     */
    public function getSubscriptions(): array
    {
        return $this->collection;
    }

    /**
     * @return Subscription|null
     */
    public function getFirst(): ?Subscription
    {
        if (!array_key_exists(0, $this->collection)) {
            return null;
        }

        return $this->collection[0];
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->collection);
    }

    /**
     * @param array $rawApiResponse
     *
     * @return array
     */
    private function buildCollection(array $rawApiResponse): array
    {
        $collection = [];
        foreach ($rawApiResponse['_embedded']['subscription'] as $rawSubscription) {
            $collection[] = new Subscription($rawSubscription);
        }

        return $collection;
    }

    /**
     * @param array $rawApiResponse
     *
     * @throws \InvalidArgumentException
     */
    private function validateResponseFormat(array $rawApiResponse): void
    {
        if (!isset($rawApiResponse['_embedded']['subscription'])
            || !is_array($rawApiResponse['_embedded']['subscription'])
        ) {
            throw new \InvalidArgumentException('Missing "_embeded" and/or "subscription" keys in API response');
        }
    }
}
