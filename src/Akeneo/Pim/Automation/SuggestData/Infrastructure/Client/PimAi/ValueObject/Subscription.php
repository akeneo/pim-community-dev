<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject;

class Subscription
{
    private $rawSubscription;

    public function __construct(array $rawSubscription)
    {
        $this->validateSubscription($rawSubscription);
        $this->rawSubscription = $rawSubscription;
    }

    public function getSubscriptionId()
    {
        return $this->rawSubscription['id'];
    }

    public function getAttributes()
    {
        return $this->rawSubscription['identifiers'] + $this->rawSubscription['attributes'];
    }

    private function validateSubscription(array $rawSubscription)
    {
        $expectedKeys = [
            'id',
            'identifiers',
            'attributes',
        ];

        foreach ($expectedKeys as $key) {
            if (! array_key_exists($key, $rawSubscription)) {
                throw new \InvalidArgumentException(sprintf('Missing key "%s" in raw subscription data', $key));
            }
        }
    }
}
