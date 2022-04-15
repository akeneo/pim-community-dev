<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Application\Supplier;

use Akeneo\OnboarderSerenity\Domain\Supplier\Read\SupplierExists;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\Exception\SupplierAlreadyExistsException;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\Model\Supplier;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\Repository;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Code;

final class CreateSupplierHandler
{
    public function __construct(
        private Repository $supplierRepository,
        private SupplierExists $supplierExists,
    ) {
    }

    public function __invoke(CreateSupplier $createSupplier): void
    {
        if ($this->supplierExists->fromCode(Code::fromString($createSupplier->code))) {
            throw new SupplierAlreadyExistsException($createSupplier->code);
        }

        $this->supplierRepository->save(
            Supplier::create(
                $createSupplier->identifier,
                $createSupplier->code,
                $createSupplier->label,
                $createSupplier->contributorEmails,
            ),
        );
    }
}
