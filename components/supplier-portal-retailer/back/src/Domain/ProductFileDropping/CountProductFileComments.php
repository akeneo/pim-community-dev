<?php

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

interface CountProductFileComments
{
    public function __invoke(string $productFileIdentifier): int;
}
