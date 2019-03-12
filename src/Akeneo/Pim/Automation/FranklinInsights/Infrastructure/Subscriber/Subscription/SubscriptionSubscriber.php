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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\Subscription;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Events\ProductSubscribed;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Events\ProductUnsubscribed;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Elasticsearch\ProductSubscriptionUpdater;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class SubscriptionSubscriber implements EventSubscriberInterface
{
    /** @var ProductSubscriptionUpdater */
    private $esIndexUpdater;

    public function __construct(ProductSubscriptionUpdater $esIndexUpdater)
    {
        $this->esIndexUpdater = $esIndexUpdater;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductSubscribed::EVENT_NAME => 'updateSubscribedProduct',
            ProductUnsubscribed::EVENT_NAME => 'updateUnsubscribedProduct',
        ];
    }

    /**
     * On product subscribed it updates the Franklin Insights subscription status in ES.
     *
     * @param ProductSubscribed $event
     */
    public function updateSubscribedProduct(ProductSubscribed $event): void
    {
        $this->esIndexUpdater->updateSubscribedProduct($event->getProductSubscription()->getProductId());
    }

    /**
     * On product unsubscribed it updates the Franklin Insights subscription status in ES.
     *
     * @param ProductUnsubscribed $event
     */
    public function updateUnsubscribedProduct(ProductUnsubscribed $event): void
    {
        $this->esIndexUpdater->updateUnsubscribedProduct($event->getUnsubscribedProductId());
    }
}
