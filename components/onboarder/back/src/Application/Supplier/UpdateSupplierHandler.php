<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Application\Supplier;

use Akeneo\OnboarderSerenity\Application\Supplier\Exception\InvalidDataException;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier\ValueObject\Identifier;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UpdateSupplierHandler
{
    public function __construct(
        private Supplier\Repository $repository,
        private ValidatorInterface $validator,
    ) {
    }

    public function __invoke(UpdateSupplier $updateSupplier): void
    {
        $violations = $this->validator->validate($updateSupplier);
        if ($violations->count() > 0) {
            throw new InvalidDataException($violations);
        }

        $storageSupplier = $this->repository->getByIdentifier(Identifier::fromString($updateSupplier->identifier));

        if (!$storageSupplier instanceof Supplier\Model\Supplier) {
            return;
        }

        $supplier = $storageSupplier->update($updateSupplier->label, $updateSupplier->contributorEmails);

        $this->repository->save($supplier);
    }
}
