<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Application\Supplier\Exception\ContributorDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierFromContributorEmail;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Code;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Exception\ContributorAccountDoesNotExist;
use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\Exception\InvalidSupplierFile;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\StoreProductsFile;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Event\SupplierFileAdded;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Model\SupplierFile;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\SupplierFileRepository;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\ContributorEmail;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Filename;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class CreateSupplierFileHandler
{
    public function __construct(
        private GetSupplierFromContributorEmail $getSupplierFromContributorEmail,
        private SupplierFileRepository $supplierFileRepository,
        private StoreProductsFile $storeProductsFile,
        private ValidatorInterface $validator,
        private TokenStorageInterface $tokenStorage,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(CreateSupplierFile $createSupplierFile): void
    {
        $violations = $this->validator->validate($createSupplierFile);
        if (0 < $violations->count()) {
            throw new InvalidSupplierFile($violations);
        }

        $user = $this->tokenStorage->getToken()?->getUser();
        if (null === $user) {
            throw new ContributorAccountDoesNotExist();
        }

        $supplier = ($this->getSupplierFromContributorEmail)(ContributorEmail::fromString($user->getUserIdentifier()));
        if (null === $supplier) {
            throw new ContributorDoesNotExist();
        }

        $storedProductFilePath = ($this->storeProductsFile)(
            Code::fromString($supplier->code),
            Filename::fromString($createSupplierFile->originalFilename),
            Identifier::generate(),
            $createSupplierFile->uploadedFile->getPathname(),
        );

        $supplierFile = SupplierFile::create(
            $createSupplierFile->originalFilename,
            $storedProductFilePath,
            $user->getUserIdentifier(),
            $supplier->identifier,
        );

        $this->supplierFileRepository->save($supplierFile);

        $this->eventDispatcher->dispatch(new SupplierFileAdded($supplierFile));

        $this->logger->info(
            sprintf('Supplier file "%s" created.', $createSupplierFile->originalFilename),
            [
                'data' => [
                    'filename' => $createSupplierFile->originalFilename,
                    'path' => $storedProductFilePath,
                    'uploaded_by_contributor' => $user->getUserIdentifier(),
                ],
            ],
        );
    }
}
