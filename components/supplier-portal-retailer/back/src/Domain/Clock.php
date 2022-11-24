<?php

namespace Akeneo\SupplierPortal\Retailer\Domain;

interface Clock
{
    public function now(): \DateTimeInterface;
}
