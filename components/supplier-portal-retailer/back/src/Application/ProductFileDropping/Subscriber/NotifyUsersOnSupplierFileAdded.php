<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Subscriber;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Notifier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierLabelFromIdentifier;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Event\SupplierFileAdded;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class NotifyUsersOnSupplierFileAdded implements EventSubscriberInterface
{
    public function __construct(
        private Notifier $notifier,
        private GetSupplierLabelFromIdentifier $getSupplierLabelFromIdentifier,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SupplierFileAdded::class => 'notifyUsers',
        ];
    }

    public function notifyUsers(SupplierFileAdded $event): void
    {
        $supplierIdentifier = $event->supplierFile->uploadedBySupplier();
        $supplierLabel = ($this->getSupplierLabelFromIdentifier)($supplierIdentifier);

        $this->notifier->notifyUsersForSupplierFileAdding(
            $event->supplierFile->uploadedByContributor(),
            $supplierLabel,
        );
    }
}
