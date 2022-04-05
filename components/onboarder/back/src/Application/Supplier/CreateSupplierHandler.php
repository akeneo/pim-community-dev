<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Application\Supplier;

use Akeneo\OnboarderSerenity\Domain\Read\Supplier\SupplierExists;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Exception\SupplierAlreadyExistsException;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier\ValueObject;

final class CreateSupplierHandler
{
    public function __construct(
        private Supplier\Repository $supplierRepository,
        private SupplierExists $supplierExists,
    ) {
    }

    public function __invoke(CreateSupplier $createSupplier): void
    {
        if ($this->supplierExists->fromCode(ValueObject\Code::fromString($createSupplier->code))) {
            throw new SupplierAlreadyExistsException($createSupplier->code);
        }

        $this->supplierRepository->save(
            Supplier\Model\Supplier::create(
                $createSupplier->identifier,
                $createSupplier->code,
                $createSupplier->label,
                $createSupplier->contributorEmails,
            ),
        );
    }
}
