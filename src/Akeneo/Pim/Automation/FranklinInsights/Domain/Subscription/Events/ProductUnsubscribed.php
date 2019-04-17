<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Events;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Symfony\Component\EventDispatcher\Event;

/**
 * Dispatched when a product has been unsubscribed.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ProductUnsubscribed extends Event
{
    public const EVENT_NAME = 'franklin_insights.products_unsubscribed';

    /** @var ProductId */
    private $unsubscribedProductId;

    public function __construct(ProductId $unsubscribedProductId)
    {
        $this->unsubscribedProductId = $unsubscribedProductId;
    }

    public function getUnsubscribedProductId(): ProductId
    {
        return $this->unsubscribedProductId;
    }
}
