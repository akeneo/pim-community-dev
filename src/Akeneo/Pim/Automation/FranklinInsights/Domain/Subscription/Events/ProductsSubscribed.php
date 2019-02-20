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

use Symfony\Component\EventDispatcher\Event;

/**
 * Several products have been subscribed.
 *
 * @author Willy MESNAGE <willy.mesnage@akeneo.com>
 */
class ProductsSubscribed extends Event
{
    public const EVENT_NAME = 'franklin_insights.products_subscribed';

    /** @var []ProductInterface */
    private $subscribedProducts;

    public function __construct(array $subscribedProducts)
    {
        $this->subscribedProducts = $subscribedProducts;
    }

    public function getSubscribedProducts(): array
    {
        return $this->subscribedProducts;
    }
}
