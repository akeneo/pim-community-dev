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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\SubscriptionCollection;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\WarningCollection;

/**
 * Represents a response from Franklin to a subscription request, with a list of subscriptions and a list of warnings
 * (both lists can be empty).
 *
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
final class ApiResponse
{
    /** @var SubscriptionCollection */
    private $subscriptions;

    /** @var WarningCollection */
    private $warnings;

    /**
     * @param SubscriptionCollection $subscriptions
     * @param WarningCollection $warnings
     */
    public function __construct(SubscriptionCollection $subscriptions, WarningCollection $warnings)
    {
        $this->subscriptions = $subscriptions;
        $this->warnings = $warnings;
    }

    /**
     * @return SubscriptionCollection
     */
    public function subscriptions(): SubscriptionCollection
    {
        return $this->subscriptions;
    }

    /**
     * @return WarningCollection
     */
    public function warnings(): WarningCollection
    {
        return $this->warnings;
    }
}
