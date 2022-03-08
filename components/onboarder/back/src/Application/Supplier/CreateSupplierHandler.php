<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Application\Supplier;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier;

final class CreateSupplierHandler
{
    public function __construct(private Supplier\Repository $supplierRepository)
    {
    }

    public function __invoke(CreateSupplier $createSupplier): void
    {
        $this->supplierRepository->save(
            Supplier\Supplier::create(
                $createSupplier->identifier,
                $createSupplier->code,
                $createSupplier->label
            )
        );
    }
}
