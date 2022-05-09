<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Application\Supplier;

use Akeneo\OnboarderSerenity\Domain\Supplier\Read\SupplierExists;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\Exception\SupplierAlreadyExistsException;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\Model\Supplier;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\Repository;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Code;
use Psr\Log\LoggerInterface;

final class CreateSupplierHandler
{
    public function __construct(
        private Repository $supplierRepository,
        private SupplierExists $supplierExists,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(CreateSupplier $createSupplier): void
    {
        if ($this->supplierExists->fromCode(Code::fromString($createSupplier->code))) {
            $this->logger->warning(
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

        $this->logger->debug(
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
