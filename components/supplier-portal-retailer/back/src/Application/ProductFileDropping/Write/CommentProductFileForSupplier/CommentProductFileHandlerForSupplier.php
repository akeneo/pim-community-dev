<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CommentProductFileForSupplier;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Event\ProductFileCommentedBySupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CommentProductFileHandlerForSupplier
{
    public function __construct(
        private ProductFileRepository $productFileRepository,
        private EventDispatcherInterface $eventDispatcher,
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

        $this->eventDispatcher->dispatch(new ProductFileCommentedBySupplier(
            $commentProductFile->productFileIdentifier,
            $commentProductFile->content,
            $commentProductFile->authorEmail,
        ));
    }
}
