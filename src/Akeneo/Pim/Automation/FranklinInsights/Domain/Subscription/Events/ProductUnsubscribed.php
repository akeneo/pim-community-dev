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

use Symfony\Component\EventDispatcher\Event;

/**
 * Dispatched when a product has been unsubscribed.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ProductUnsubscribed extends Event
{
    public const EVENT_NAME = 'franklin_insights.products_unsubscribed';

    /** @var int */
    private $unsubscribedProductId;

    public function __construct(int $unsubscribedProductId)
    {
        $this->unsubscribedProductId = $unsubscribedProductId;
    }

    public function getUnsubscribedProductId(): int
    {
        return $this->unsubscribedProductId;
    }
}
