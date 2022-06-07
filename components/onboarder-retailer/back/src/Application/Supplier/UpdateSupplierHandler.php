<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Application\Supplier;

use Akeneo\OnboarderSerenity\Retailer\Application\Supplier\Exception\InvalidData;
use Akeneo\OnboarderSerenity\Retailer\Application\Supplier\Exception\SupplierDoesNotExist;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\ValueObject\Identifier;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UpdateSupplierHandler
{
    public function __construct(
        private Repository $repository,
        private ValidatorInterface $validator,
        private LoggerInterface $onboarderSerenityLogger,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(UpdateSupplier $updateSupplier): void
    {
        $violations = $this->validator->validate($updateSupplier);
        if (0 < $violations->count()) {
            throw new InvalidData($violations);
        }

        $supplier = $this->repository->find(Identifier::fromString($updateSupplier->identifier));

        if (null === $supplier) {
            $this->onboarderSerenityLogger->info(
                'Attempt to update a supplier that does not exist.',
                [
                    'data' => [
                        'new_values' => [
                            'identifier' => $updateSupplier->identifier,
                            'label' => $updateSupplier->label,
                            'contributor_emails' => $updateSupplier->contributorEmails,
                        ],
                    ],
                ],
            );
            throw new SupplierDoesNotExist();
        }

        $supplier->update(
            $updateSupplier->label,
            $updateSupplier->contributorEmails,
        );

        $this->repository->save($supplier);

        foreach ($supplier->events() as $event) {
            $this->eventDispatcher->dispatch($event);
        }

        $this->onboarderSerenityLogger->debug(
            sprintf('Supplier "%s" updated.', $supplier->code()),
            [
                'data' => [
                    'old_values' => [
                        'identifier' => $supplier->identifier(),
                        'code' => $supplier->code(),
                        'label' => $supplier->label(),
                        'contributor_emails' => $supplier->contributors(),
                    ],
                    'new_values' => [
                        'identifier' => $supplier->identifier(),
                        'code' => $supplier->code(),
                        'label' => $supplier->label(),
                        'contributor_emails' => $supplier->contributors(),
                    ],
                ],
            ],
        );
    }
}
