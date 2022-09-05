<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Subscriber;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Notifier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Event\ProductFileAdded;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class NotifyUsersOnProductFileAdded implements EventSubscriberInterface
{
    public function __construct(
        private Notifier $notifier,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductFileAdded::class => 'notifyUsers',
        ];
    }

    public function notifyUsers(ProductFileAdded $event): void
    {
        $this->notifier->notifyUsersForProductFileAdding(
            $event->contributorEmail(),
            $event->supplierLabel(),
        );
    }
}
