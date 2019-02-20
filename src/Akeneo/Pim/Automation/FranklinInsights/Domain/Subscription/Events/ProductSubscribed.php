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

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * A product has been subscribed.
 *
 * @author Willy MESNAGE <willy.mesnage@akeneo.com>
 */
class ProductSubscribed extends Event
{
    public const EVENT_NAME = 'franklin_insights.product_subscribed';

    /** @var ProductInterface */
    private $subscribedProduct;

    public function __construct(ProductInterface $subscribedProduct)
    {
        $this->subscribedProduct = $subscribedProduct;
    }

    public function getSubscribedProduct(): ProductInterface
    {
        return $this->subscribedProduct;
    }
}
