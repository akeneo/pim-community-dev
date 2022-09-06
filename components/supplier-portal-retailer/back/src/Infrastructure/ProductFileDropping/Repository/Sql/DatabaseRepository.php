<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Repository\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

final class DatabaseRepository implements ProductFileRepository
{
    public function __construct(private Connection $connection)
    {
    }

    public function save(ProductFile $productFile): void
    {
        $sql = <<<SQL
            REPLACE INTO `akeneo_supplier_portal_supplier_product_file` (
                identifier,
                original_filename,
                path,
                uploaded_by_contributor,
                uploaded_by_supplier,
                uploaded_at
            )
            VALUES (:identifier, :original_filename, :path, :uploaded_by_contributor, :uploaded_by_supplier, :uploaded_at)
        SQL;

        $this->connection->executeStatement(
            $sql,
            [
                'identifier' => $productFile->identifier(),
                'original_filename' => $productFile->originalFilename(),
                'path' => $productFile->path(),
                'uploaded_by_contributor' => $productFile->contributorEmail(),
                'uploaded_by_supplier' => $productFile->supplierIdentifier(),
                'uploaded_at' => $productFile->uploadedAt(),
            ],
        );

        foreach ($productFile->newRetailerComments() as $retailerComment) {
            $sql = <<<SQL
                INSERT INTO `akeneo_supplier_portal_product_file_retailer_comments` (
                    author_email,
                    product_file_identifier,
                    content,
                    created_at
                ) VALUES (:authorEmail, :productFileIdentifier, :content, :createdAt);
            SQL;

            $this->connection->executeQuery(
                $sql,
                [
                    'authorEmail' => $retailerComment->authorEmail(),
                    'productFileIdentifier' => $productFile->identifier(),
                    'content' => $retailerComment->content(),
                    'createdAt' => $retailerComment->createdAt(),
                ],
                [
                    'createdAt' => Types::DATETIME_IMMUTABLE,
                ],
            );
        }

        foreach ($productFile->newSupplierComments() as $supplierComment) {
            $sql = <<<SQL
                INSERT INTO `akeneo_supplier_portal_product_file_supplier_comments` (
                    author_email,
                    product_file_identifier,
                    content,
                    created_at
                ) VALUES (:authorEmail, :productFileIdentifier, :content, :createdAt);
            SQL;

            $this->connection->executeQuery(
                $sql,
                [
                    'authorEmail' => $supplierComment->authorEmail(),
                    'productFileIdentifier' => $productFile->identifier(),
                    'content' => $supplierComment->content(),
                    'createdAt' => $supplierComment->createdAt(),
                ],
                [
                    'createdAt' => Types::DATETIME_IMMUTABLE,
                ],
            );
        }
    }
}
