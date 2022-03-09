<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Application\Supplier;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier\ValueObject;

final class CreateSupplierHandler
{
    public function __construct(private Supplier\Repository $supplierRepository)
    {
    }

    public function __invoke(CreateSupplier $createSupplier): void
    {
        if ($this->supplierRepository->find(ValueObject\Identifier::fromString($createSupplier->identifier))) {
            throw new Supplier\Exception\SupplierAlreadyExistsException($createSupplier->code);
        }

        $this->supplierRepository->save(
            Supplier\Model\Supplier::create(
                $createSupplier->identifier,
                $createSupplier->code,
                $createSupplier->label
            )
        );
    }
}
