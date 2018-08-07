<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject;

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
        if (! isset($rawApiResponse['_embedded']['subscription'][0]) || ! is_array($rawApiResponse['_embedded']['subscription'][0])) {
            throw new \InvalidArgumentException('Missing "_embeded" and/or "subscription" keys in API response');
        }
    }
}
