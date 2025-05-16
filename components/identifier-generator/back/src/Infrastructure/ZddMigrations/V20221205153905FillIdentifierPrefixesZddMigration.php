<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\ZddMigrations;

use Akeneo\Pim\Automation\IdentifierGenerator\API\Query\UpdateIdentifierPrefixesQuery;
use Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Platform\Installer\Infrastructure\Command\ZddMigration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class V20221205153905FillIdentifierPrefixesZddMigration implements ZddMigration
{
    /**
     * @var string[]|null
     */
    private ?array $identifierAttributeCodes = null;

    public function __construct(
        private readonly Connection $connection,
        private readonly UpdateIdentifierPrefixesQuery $updateIdentifierPrefixesQuery,
        private readonly WriteValueCollectionFactory $writeValueCollectionFactory,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function migrate(): void
    {
        foreach ($this->getProductsByBatch() as $products) {
            $this->logger->notice(\sprintf('%d products found to fill prefixes', \count($products)));
            $isSuccessfull = $this->updateIdentifierPrefixesQuery->updateFromProducts($products);
            if (!$isSuccessfull) {
                throw new \LogicException('Migration can not be executed as the database is not ready');
            }
        }
    }

    public function migrateNotZdd(): void
    {
        $this->migrate();
    }

    public function getName(): string
    {
        return 'FillIdentifierPrefixes';
    }

    /**
     * @return iterable<ProductInterface[]>
     */
    private function getProductsByBatch(): iterable
    {
        $lastProductUuid = '';

        $query = <<<SQL
        SELECT BIN_TO_UUID(uuid) as uuid_string, raw_values
        FROM pim_catalog_product product
        WHERE NOT EXISTS(
            SELECT * FROM pim_catalog_identifier_generator_prefixes WHERE product_uuid = product.uuid
        )
            AND uuid > :lastUuid
        ORDER BY uuid
        LIMIT 100
SQL;

        while (true) {
            $rows = $this->connection->fetchAllKeyValue(
                $query,
                ['lastUuid' => $lastProductUuid],
                ['lastUuid' => Types::BINARY],
            );

            if (empty($rows)) {
                return;
            }

            $identifierAttributeCodes = $this->getIdentifierAttributeCodes();
            Assert::isArray($rows);
            Assert::allString($rows);
            $values = $this->writeValueCollectionFactory->createMultipleFromStorageFormat(
                \array_map(
                    static function (string $rawValues) use ($identifierAttributeCodes): array {
                        $values = \json_decode($rawValues, true);
                        Assert::isArray($values);

                        return \array_filter(
                            $values,
                            fn (string $key): bool => \in_array($key, $identifierAttributeCodes),
                            ARRAY_FILTER_USE_KEY
                        );
                    },
                    $rows
                )
            );
            $products = [];
            foreach ($values as $uuid => $valueCollection) {
                $product = new Product($uuid);
                $product->setValues($valueCollection);
                $products[] = $product;
            }
            $lastProduct = \end($products);
            if ($lastProduct instanceof ProductInterface) {
                $lastProductUuid = $lastProduct->getUuid()->getBytes();
            }

            yield $products;
        }
    }

    /**
     * @return string[]
     */
    private function getIdentifierAttributeCodes(): array
    {
        if (null === $this->identifierAttributeCodes) {
            $result = $this->connection->fetchFirstColumn(
                "SELECT code FROM pim_catalog_attribute WHERE attribute_type = 'pim_catalog_identifier'"
            );
            Assert::allString($result);
            $this->identifierAttributeCodes = $result;
        }

        return $this->identifierAttributeCodes;
    }
}
