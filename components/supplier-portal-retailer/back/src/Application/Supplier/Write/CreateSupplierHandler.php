<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\Supplier\Write;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\SupplierExists;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorAdded;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Exception\SupplierAlreadyExistsException;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Identifier;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class CreateSupplierHandler
{
    public function __construct(
        private Repository $supplierRepository,
        private SupplierExists $supplierExists,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(CreateSupplier $createSupplier): void
    {
        if ($this->supplierExists->fromCode($createSupplier->code)) {
            $this->logger->info(
                sprintf('Attempt to create a supplier "%s" that does already exist.', $createSupplier->code),
                [
                    'data' => [
                        'new_values' => [
                            'label' => $createSupplier->label,
                            'contributor_emails' => $createSupplier->contributorEmails,
                        ],
                    ],
                ],
            );
            throw new SupplierAlreadyExistsException($createSupplier->code);
        }

        $supplierIdentifier = Uuid::uuid4()->toString();
        $this->supplierRepository->save(
            Supplier::create(
                $supplierIdentifier,
                $createSupplier->code,
                $createSupplier->label,
                $createSupplier->contributorEmails,
            ),
        );

        foreach ($createSupplier->contributorEmails as $contributorEmail) {
            $this->eventDispatcher->dispatch(new ContributorAdded(
                Identifier::fromString($supplierIdentifier),
                $contributorEmail,
                $createSupplier->code,
            ));
        }

        $this->logger->info(
            sprintf('Supplier "%s" created.', $createSupplier->code),
            [
                'data' => [
                    'identifier' => $supplierIdentifier,
                    'supplier_code' => $createSupplier->code,
                    'contributor_emails' => $createSupplier->contributorEmails,
                    'metric_key' => 'supplier_created',
                ],
            ],
        );
    }
}
