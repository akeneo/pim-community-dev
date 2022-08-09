<?php

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

interface Notifier
{
    public function notifyUsersForSupplierFileAdding(string $contributorEmail): void;
}
