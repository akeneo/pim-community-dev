<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductIdentifiersQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\Infrastructure\Service\FormatProductSelectionCriteria;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductIdentifiersQuery implements GetProductIdentifiersQueryInterface
{
    public function __construct(
        private ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        private Connection $connection,
    ) {
    }

    /**
     * This implementation is coupled temporarly to the PQB,
     * we are waiting for the ServiceAPI version that should be available soon.
     *
     * @return array<string>
     */
    public function execute(Catalog $catalog, ?string $searchAfter = null, int $limit = 100): array
    {
        $pqbOptions = [
            'filters' => FormatProductSelectionCriteria::toPQBFilters($catalog->getProductSelectionCriteria()),
            'limit' => $limit,
        ];

        if (null !== $searchAfter) {
            $searchAfterProductIdentifier = $this->findProductIdentifier($searchAfter);

            $pqbOptions['search_after'] = [
                \strtolower($searchAfterProductIdentifier),
            ];
        }

        $pqb = $this->productQueryBuilderFactory->create($pqbOptions);
        $pqb->addSorter('identifier', Directions::ASCENDING);

        $results = $pqb->execute();

        return \array_map(
            fn (IdentifierResult $result) => $result->getIdentifier() ?:
                $this->getUuidFromIdentifierResult($result->getId()),
            \iterator_to_array($results),
        );
    }

    private function findProductIdentifier(string $uuid): string
    {
        $sql = <<<SQL
            SELECT identifier
            FROM pim_catalog_product
            WHERE uuid = :uuid
        SQL;

        /** @var mixed|false $identifier */
        $identifier = $this->connection->fetchOne($sql, [
            'uuid' => Uuid::fromString($uuid)->getBytes(),
        ]);

        if (false === $identifier) {
            throw new \InvalidArgumentException('Unknown uuid');
        }

        return (string) $identifier;
    }

    private function getUuidFromIdentifierResult(string $esId): string
    {
        $matches = [];
        if (!\preg_match(
            '/^product_(?P<uuid>[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})$/',
            $esId,
            $matches,
        )) {
            throw new \InvalidArgumentException(\sprintf('Invalid Elasticsearch identifier %s', $esId));
        }

        return $matches['uuid'];
    }
}
