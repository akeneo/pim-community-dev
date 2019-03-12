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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Events;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Symfony\Component\EventDispatcher\Event;

/**
 * A product has been subscribed.
 *
 * @author Willy MESNAGE <willy.mesnage@akeneo.com>
 */
class ProductSubscribed extends Event
{
    public const EVENT_NAME = 'franklin_insights.product_subscribed';

    /** @var ProductSubscription */
    private $productSubscription;

    public function __construct(ProductSubscription $productSubscription)
    {
        $this->productSubscription = $productSubscription;
    }

    public function getProductSubscription(): ProductSubscription
    {
        return $this->productSubscription;
    }
}
