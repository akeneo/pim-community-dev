<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Application\Supplier;

use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Read\SupplierExists;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\Event\ContributorAdded;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\Exception\SupplierAlreadyExistsException;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\Model\Supplier;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\ValueObject\Code;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\ValueObject\Identifier;
use Psr\Log\LoggerInterface;
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
        if ($this->supplierExists->fromCode(Code::fromString($createSupplier->code))) {
            $this->logger->info(
                sprintf('Attempt to create a supplier "%s" that does already exist.', $createSupplier->code),
                [
                    'data' => [
                        'new_values' => [
                            'identifier' => $createSupplier->identifier,
                            'label' => $createSupplier->label,
                            'contributor_emails' => $createSupplier->contributorEmails,
                        ],
                    ],
                ],
            );
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

        foreach ($createSupplier->contributorEmails as $contributorEmail) {
            $this->eventDispatcher->dispatch(new ContributorAdded(
                Identifier::fromString($createSupplier->identifier),
                $contributorEmail,
            ));
        }

        $this->logger->info(
            sprintf('Supplier "%s" created.', $createSupplier->code),
            [
                'data' => [
                    'identifier' => $createSupplier->identifier,
                    'code' => $createSupplier->code,
                    'label' => $createSupplier->label,
                    'contributor_emails' => $createSupplier->contributorEmails,
                ],
            ],
        );
    }
}
