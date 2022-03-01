<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Application\Supplier;

use Akeneo\OnboarderSerenity\Domain\Supplier\Supplier;
use Akeneo\OnboarderSerenity\Domain\Supplier\SupplierRepository;

final class CreateSupplierHandler
{
    public function __construct(private SupplierRepository $supplierRepository){}

    public function __invoke(CreateSupplier $createSupplier): void
    {
        $this->supplierRepository->save(
            Supplier::create(
                $createSupplier->identifier,
                $createSupplier->code,
                $createSupplier->label
            )
        );
    }
}
