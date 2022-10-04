<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\InvalidProductFile;
use Akeneo\SupplierPortal\Retailer\Application\Supplier\Exception\ContributorDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\StoreProductsFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Filename;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierFromContributorEmail;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Code;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CreateProductFileHandler
{
    public function __construct(
        private GetSupplierFromContributorEmail $getSupplierFromContributorEmail,
        private ProductFileRepository           $productFileRepository,
        private StoreProductsFile               $storeProductsFile,
        private ValidatorInterface              $validator,
        private EventDispatcherInterface        $eventDispatcher,
        private LoggerInterface                 $logger,
    ) {
    }

    public function __invoke(CreateProductFile $createProductFile): void
    {
        $violations = $this->validator->validate($createProductFile);
        if (0 < $violations->count()) {
            throw new InvalidProductFile($violations);
        }

        $supplier = ($this->getSupplierFromContributorEmail)($createProductFile->uploadedByContributor);
        if (null === $supplier) {
            throw new ContributorDoesNotExist();
        }

        $storedProductFilePath = ($this->storeProductsFile)(
            Code::fromString($supplier->code),
            Filename::fromString($createProductFile->originalFileName),
            Identifier::fromString(Uuid::uuid4()->toString()),
            $createProductFile->temporaryFilePath,
        );

        $productFileIdentifier = Identifier::fromString(Uuid::uuid4()->toString());
        $productFile = ProductFile::create(
            (string) $productFileIdentifier,
            $createProductFile->originalFileName,
            $storedProductFilePath,
            $createProductFile->uploadedByContributor,
            $supplier,
        );

        $this->productFileRepository->save($productFile);

        foreach ($productFile->events() as $event) {
            $this->eventDispatcher->dispatch($event);
        }

        $this->logger->info(
            sprintf('Product file "%s" created.', $createProductFile->originalFileName),
            [
                'data' => [
                    'identifier' => (string) $productFileIdentifier,
                    'supplier_identifier' => $supplier->identifier,
                    'supplier_code' => $supplier->code,
                    'filename' => $createProductFile->originalFileName,
                    'path' => $storedProductFilePath,
                    'uploaded_by_contributor' => $createProductFile->uploadedByContributor,
                    'metric_key' => 'supplier_file_dropped',
                ],
            ],
        );
    }
}
