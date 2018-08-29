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
class ProductSubscriptionsResponse implements \Countable
{
    /**
     * @var ProductSubscriptionResponse
     */
    private $collection = [];

    /**
     * @param array $subscriptions
     */
    public function __construct(array $subscriptions)
    {
        foreach ($subscriptions as $subscription) {
            $this->collection[] = new ProductSubscriptionResponse(
                42, // @TODO: Use tracker id (See APAI-153)
                $subscription->getSubscriptionId(),
                $subscription->getAttributes()
            );
        }
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->collection);
    }
}
