<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CommentProductFileForSupplier;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Psr\Log\LoggerInterface;

class CommentProductFileHandlerForSupplier
{
    public function __construct(
        private ProductFileRepository $productFileRepository,
        private LoggerInterface $logger,
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

        $this->logger->info(
            sprintf('Contributor "%s" commented a product file.', $commentProductFile->authorEmail),
            [
                'data' => [
                    'identifier' => $productFile->identifier(),
                    'content' => $commentProductFile->content,
                    'metric_key' => 'contributor_product_file_commented',
                ],
            ],
        );
    }
}
