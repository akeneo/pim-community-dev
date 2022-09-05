<?php

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

interface Notifier
{
    public function notifyUsersForProductFileAdding(string $contributorEmail, string $supplierLabel): void;
}
