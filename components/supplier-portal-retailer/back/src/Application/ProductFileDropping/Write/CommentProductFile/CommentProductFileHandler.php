<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CommentProductFile;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;

final class CommentProductFileHandler
{
    public function __construct(
        private ProductFileRepository $productFileRepository,
    ) {
    }

    public function __invoke(CommentProductFile $commentProductFile): void
    {
        $productFile = $this->productFileRepository->find(
            Identifier::fromString($commentProductFile->productFileIdentifier),
        );

        if (null === $productFile) {
            throw new ProductFileDoesNotExist();
        }

        $productFile->addNewRetailerComment(
            $commentProductFile->content,
            $commentProductFile->authorEmail,
            $commentProductFile->createdAt,
        );

        $this->productFileRepository->save($productFile);
    }
}
