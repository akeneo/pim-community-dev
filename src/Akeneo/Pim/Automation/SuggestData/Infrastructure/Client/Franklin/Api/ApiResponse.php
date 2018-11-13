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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\ValueObject\SubscriptionCollection;
use Symfony\Component\HttpFoundation\Response;

/**
 * Represents the whole Franklin API response, with the HTTP code and a list of subscriptions (can be an empty list).
 *
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
final class ApiResponse
{
    /** @var int */
    private $responseCode;

    /** @var SubscriptionCollection */
    private $subscriptionCollection;

    /**
     * @param int $responseCode
     * @param SubscriptionCollection $subscriptionCollection
     */
    public function __construct(int $responseCode, SubscriptionCollection $subscriptionCollection)
    {
        $this->responseCode = $responseCode;
        $this->subscriptionCollection = $subscriptionCollection;
    }

    /**
     * @return int
     */
    public function code(): int
    {
        return $this->responseCode;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return Response::HTTP_OK === $this->code();
    }

    /**
     * @return SubscriptionCollection
     */
    public function content(): SubscriptionCollection
    {
        return $this->subscriptionCollection;
    }

    /**
     * @return bool
     */
    public function hasSubscriptions()
    {
        return count($this->subscriptionCollection) > 0;
    }
}
