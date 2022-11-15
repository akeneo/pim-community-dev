<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\MarkCommentsAsReadBySupplier;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\MarkCommentsAsReadBySupplier as MarkCommentsAsReadBySupplierQuery;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Event\MarkingCommentsAsRead;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MarkCommentsAsReadBySupplierHandler
{
    public function __construct(
        private readonly MarkCommentsAsReadBySupplierQuery $markCommentsAsReadBySupplier,
        private readonly ProductFileRepository $productFileRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(MarkCommentsAsReadBySupplier $markCommentsAsReadBySupplier): void
    {
        $productFile = $this->productFileRepository->find(
            Identifier::fromString($markCommentsAsReadBySupplier->productFileIdentifier),
        );

        if (null === $productFile) {
            throw new ProductFileDoesNotExist();
        }

        if (!$productFile->hasComments()) {
            return;
        }

        $this->eventDispatcher->dispatch(new MarkingCommentsAsRead(new \DateTimeImmutable(), $markCommentsAsReadBySupplier->productFileIdentifier));

        ($this->markCommentsAsReadBySupplier)($markCommentsAsReadBySupplier->productFileIdentifier, $markCommentsAsReadBySupplier->lastReadAt);
    }
}
