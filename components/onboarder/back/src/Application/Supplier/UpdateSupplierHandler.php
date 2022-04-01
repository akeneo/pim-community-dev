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
//        private ValidatorInterface $validator,
    ) {
    }

    public function __invoke(UpdateSupplier $updateSupplier): void
    {
//        $violations = $this->validator->validate($updateSupplier);
//        if ($violations->count() > 0) {
//            throw new InvalidDataException($violations);
//        }

        $supplier = $this->repository->find(Identifier::fromString($updateSupplier->identifier));

        if (!$supplier instanceof Supplier\Model\Supplier) {
            return;
        }

        $updatedSupplier = $supplier->update(
            \substr($updateSupplier->label, 0, Supplier\ValueObject\Label::MAX_LENGTH),
            \array_filter(
                $updateSupplier->contributorEmails,
                fn($contributorEmail) => \filter_var($contributorEmail, FILTER_VALIDATE_EMAIL)
            )
        );

        $this->repository->save($updatedSupplier);
    }
}
