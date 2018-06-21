<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\ValueObjects;

class SubscriptionId
{
    private $id;

    public function __construct(string $id)
    {
        $id = trim($id);
        $this->validate($id);

        $this->id = trim($id);
    }

    public function value(): string
    {
        return $this->id;
    }

    private function validate(string $id)
    {
        if (empty($id)) {
            throw new \InvalidArgumentException('Subscription id must not be empty');
        }
    }
}
