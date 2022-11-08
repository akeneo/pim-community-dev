<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Repository\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Event\ProductFileDeleted;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Comment;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class DatabaseRepository implements ProductFileRepository
{
    public function __construct(private Connection $connection, private EventDispatcherInterface $eventDispatcher)
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
            WITH retailer_comments AS (
                SELECT product_file_identifier, JSON_ARRAYAGG(
                     CASE WHEN content IS NOT NULL THEN JSON_OBJECT(
                         'content', content,
                         'author_email', author_email,
                         'created_at', created_at
                     ) END
                 ) AS retailer_comments
                FROM akeneo_supplier_portal_product_file_retailer_comments
                WHERE product_file_identifier = :identifier
            ), supplier_comments AS (
                SELECT product_file_identifier, JSON_ARRAYAGG(
                   CASE WHEN content IS NOT NULL THEN JSON_OBJECT(
                           'content', content,
                           'author_email', author_email,
                           'created_at', created_at
                       ) END
               ) AS supplier_comments
                FROM akeneo_supplier_portal_product_file_supplier_comments
                WHERE product_file_identifier = :identifier
            )
            SELECT
                identifier,
                original_filename,
                path,
                uploaded_by_contributor,
                uploaded_by_supplier,
                uploaded_at,
                downloaded,
                rc.retailer_comments,
                sc.supplier_comments
            FROM `akeneo_supplier_portal_supplier_product_file`
            LEFT JOIN retailer_comments rc
                ON identifier = rc.product_file_identifier
            LEFT JOIN supplier_comments sc
                ON identifier = sc.product_file_identifier
            WHERE identifier = :identifier
        SQL;

        $productFile = $this->connection->executeQuery(
            $sql,
            ['identifier' => (string) $identifier],
        )->fetchAssociative();

        if (false === $productFile) {
            return null;
        }

        $retailerComments = $productFile['retailer_comments']
            ? \array_filter(\json_decode(
                $productFile['retailer_comments'],
                true,
            ))
            : [];
        $supplierComments = $productFile['supplier_comments']
            ? \array_filter(\json_decode(
                $productFile['supplier_comments'],
                true,
            ))
            : [];

        return ProductFile::hydrate(
            $productFile['identifier'],
            $productFile['original_filename'],
            $productFile['path'],
            $productFile['uploaded_by_contributor'],
            $productFile['uploaded_by_supplier'],
            $productFile['uploaded_at'],
            (bool) $productFile['downloaded'],
            array_map(
                fn (array $comment) => Comment::hydrate(
                    $comment['content'],
                    $comment['author_email'],
                    new \DateTimeImmutable($comment['created_at']),
                ),
                $retailerComments,
            ),
            array_map(
                fn (array $comment) => Comment::hydrate(
                    $comment['content'],
                    $comment['author_email'],
                    new \DateTimeImmutable($comment['created_at']),
                ),
                $supplierComments,
            ),
        );
    }

    public function deleteProductFileRetailerComments(string $productFileIdentifier): void
    {
        $sql = <<<SQL
            DELETE FROM akeneo_supplier_portal_product_file_retailer_comments
            WHERE product_file_identifier = :productFileIdentifier
        SQL;

        $this->connection->executeStatement(
            $sql,
            ['productFileIdentifier' => $productFileIdentifier],
        );
    }

    public function deleteProductFileSupplierComments(string $productFileIdentifier): void
    {
        $sql = <<<SQL
            DELETE FROM akeneo_supplier_portal_product_file_supplier_comments
            WHERE product_file_identifier = :productFileIdentifier
        SQL;

        $this->connection->executeStatement(
            $sql,
            ['productFileIdentifier' => $productFileIdentifier],
        );
    }

    public function deleteOldProductFiles(): void
    {
        $sql = <<<SQL
            SELECT identifier
            FROM akeneo_supplier_portal_supplier_product_file
            WHERE uploaded_at < :retentionLimit;
        SQL;

        $productFileIdentifiers = array_map(
            fn (array $productFile) => $productFile['identifier'],
            $this->connection->executeQuery(
                $sql,
                [
                    'retentionLimit' => (new \DateTimeImmutable())->add(
                        \DateInterval::createFromDateString(
                            sprintf(
                                '-%d days',
                                self::RETENTION_DURATION_IN_DAYS,
                            ),
                        ),
                    )->format('Y-m-d H:i:s'),
                ],
            )->fetchAllAssociative(),
        );

        if (0 === \count($productFileIdentifiers)) {
            return;
        }

        $sql = <<<SQL
            DELETE FROM akeneo_supplier_portal_supplier_product_file
            WHERE identifier IN (:productFileIdentifiers)
        SQL;

        $this->connection->executeStatement(
            $sql,
            [
                'productFileIdentifiers' => $productFileIdentifiers,
            ],
            [
                'productFileIdentifiers' => Connection::PARAM_STR_ARRAY,
            ],
        );

        foreach ($productFileIdentifiers as $productFileIdentifier) {
            $this->eventDispatcher->dispatch(new ProductFileDeleted($productFileIdentifier));
        }
    }
}
