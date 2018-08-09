<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\SubscriptionCollection;
use Symfony\Component\HttpFoundation\Response;

/**
 * Represents the whole PIM.ai API response, with the HTTP code and a list of subscriptions (can be an empty list)
 *
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
final class ApiResponse
{
    private $responseCode;

    private $subscriptionCollection;

    public function __construct(int $responseCode, SubscriptionCollection $subscriptionCollection)
    {
        $this->responseCode = $responseCode;
        $this->subscriptionCollection = $subscriptionCollection;
    }

    public function code(): int
    {
        return $this->responseCode;
    }

    public function isSuccess(): bool
    {
        return $this->code() === Response::HTTP_OK;
    }

    public function content(): SubscriptionCollection
    {
        return $this->subscriptionCollection;
    }

    public function hasSubscriptions()
    {
        return count($this->subscriptionCollection) > 0;
    }
}
