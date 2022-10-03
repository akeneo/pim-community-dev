<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;

final class CommentProductFileHandlerForSupplier
{
    public function __construct(
        private ProductFileRepository $productFileRepository,
    ) {
    }

    public function __invoke(CommentProductFileForSupplier $commentProductFile): void
    {
        $productFile = $this->productFileRepository->find(
            Identifier::fromString($commentProductFile->productFileIdentifier),
        );

        if (null === $productFile) {
            throw new ProductFileDoesNotExist();
        }

        $productFile->addNewSupplierComment(
            $commentProductFile->content,
            $commentProductFile->authorEmail,
            $commentProductFile->createdAt,
        );

        $this->productFileRepository->save($productFile);
    }
}
