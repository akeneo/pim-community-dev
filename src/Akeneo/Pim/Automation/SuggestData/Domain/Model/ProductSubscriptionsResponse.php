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

namespace Akeneo\Pim\Automation\SuggestData\Domain\Model;

/**
 * Represents a standard response from a subscription request
 * Holds a collection of subscription
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProductSubscriptionsResponse
{
    /**
     * @var ProductSubscriptionResponse[]
     */
    private $responses = [];

    /**
     * @param ProductSubscriptionResponse[]
     */
    public function __construct(array $responses = [])
    {
        $this->responses = $responses;
    }

    /**
     * @return ProductSubscriptionResponse[]
     */
    public function responses(): array
    {
        return $this->responses;
    }

    /**
     * @param ProductSubscriptionResponse $response
     */
    public function add(ProductSubscriptionResponse $response): void
    {
        $this->responses[] = $response;
    }
}
