<?php

namespace Akeneo\SupplierPortal\Supplier\Domain;

interface Clock
{
    public function now(): \DateTimeInterface;
}
