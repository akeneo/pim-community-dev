<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\ZddMigrations;

use Akeneo\Pim\Automation\IdentifierGenerator\API\Query\UpdateIdentifierPrefixesQuery;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Platform\Bundle\InstallerBundle\Command\ZddMigration;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class V20221205153905FillIdentifierPrefixesZddMigration implements ZddMigration
{
    public const BULK_SIZE = 100;

    public function __construct(
        private readonly Connection $connection,
        private readonly UpdateIdentifierPrefixesQuery $updateIdentifierPrefixesQuery,
        private readonly ProductRepositoryInterface $productRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function migrate(): void
    {
        $lastProductUuidAsBytes = '';
        $productUuids = $this->getProductsByBatch($lastProductUuidAsBytes);

        while (\count($productUuids) > 0) {
            $this->logger->warning(\sprintf('%s products found to fill prefixes', \count($productUuids)));

            $productUuidsAsBytes = \array_map(fn (string $uuid): string => Uuid::fromString($uuid)->getBytes(), $productUuids);
            $products = $this->productRepository->findBy(['uuid' => $productUuidsAsBytes]);

            $this->updateIdentifierPrefixesQuery->updateFromProducts($products);

            $lastProductUuidAsBytes = Uuid::fromString(\end($productUuids))->getBytes();
            $productUuids = $this->getProductsByBatch($lastProductUuidAsBytes);
        }
    }

    public function getName(): string
    {
        return 'FillIdentifierPrefixes';
    }

    /**
     * @param ProductInterface[] $products
     * @return void
     */
    private function fillMissingPrefixes(array $products): void
    {
        $this->updateIdentifierPrefixesQuery->updateFromProducts($products);
    }

    /**
     * @return string[]
     */
    private function getProductsByBatch(string $lastProductUuid): array
    {
        $query = <<<SQL
        SELECT DISTINCT BIN_TO_UUID(pcp.uuid) as uuid from pim_catalog_product as pcp
 LEFT JOIN pim_catalog_identifier_generator pcig on pcp.uuid = pcig.uuid
WHERE pcig.uuid IS NULL
AND pcp.uuid > :lastUuid
ORDER BY uuid
LIMIT :limit
SQL;

        return $this->connection->executeQuery(
            $query,
            ['lastUuid' => $lastProductUuid, 'limit' => self::BULK_SIZE],
            ['lastUuid' => \PDO::PARAM_STR, 'limit' => \PDO::PARAM_INT]
        )->fetchFirstColumn();
    }
}
