<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject;

class Subscription
{
    private $rawSubscription;

    public function __construct(array $rawSubscription)
    {
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
}
