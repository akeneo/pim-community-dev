<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\InvalidComment;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CommentProductFileHandler
{
    public function __construct(
        private ProductFileRepository $productFileRepository,
        private ValidatorInterface $validator,
    ) {
    }

    public function __invoke(CommentProductFile $commentProductFile): void
    {
        $violations = $this->validator->validate($commentProductFile);

        if (0 < $violations->count()) {
            throw new InvalidComment($violations);
        }

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
