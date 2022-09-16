<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Repository\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

final class DatabaseRepository implements ProductFileRepository
{
    public function __construct(private Connection $connection)
    {
    }

    public function save(ProductFile $productFile): void
    {
        $this->connection->beginTransaction();

        $sql = <<<SQL
            INSERT IGNORE INTO `akeneo_supplier_portal_supplier_product_file` (
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
                'uploaded_by_supplier' => $productFile->uploadedBySupplier(),
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

            $this->connection->executeStatement(
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

            $this->connection->executeStatement(
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

        $this->connection->commit();
    }

    public function find(Identifier $identifier): ?ProductFile
    {
        $sql = <<<SQL
            SELECT
                identifier,
                original_filename,
                path,
                uploaded_by_contributor,
                uploaded_by_supplier,
                uploaded_at,
                downloaded
            FROM `akeneo_supplier_portal_supplier_product_file`
            WHERE identifier = :identifier
        SQL;

        $productFile = $this->connection->executeQuery($sql, ['identifier' => (string) $identifier])->fetchAssociative();

        if (false === $productFile) {
            return null;
        }

        return ProductFile::hydrate(
            $productFile['identifier'],
            $productFile['original_filename'],
            $productFile['path'],
            $productFile['uploaded_by_contributor'],
            $productFile['uploaded_by_supplier'],
            $productFile['uploaded_at'],
            (bool) $productFile['downloaded'],
        );
    }
}
