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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class SubscriptionId
{
    /** @var string */
    private $subscriptionId;

    /**
     * @param string $subscriptionId
     */
    public function __construct(string $subscriptionId)
    {
        $this->subscriptionId = $subscriptionId;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->subscriptionId;
    }
}
