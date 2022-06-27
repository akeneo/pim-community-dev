<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\GetProductIdentifiersQueryInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductIdentifiersQuery implements GetProductIdentifiersQueryInterface
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
    public function execute(string $catalogId, ?string $searchAfter = null, int $limit = 100): array
    {
        $pqbOptions = [
            'filters' => $this->findProductSelectionCriteria($catalogId),
            'limit' => $limit,
        ];

        if (null !== $searchAfter) {
            $searchAfterProductIdentifier = $this->findProductIdentifier($searchAfter);

            $pqbOptions['search_after'] = [
                \strtolower($searchAfterProductIdentifier),
                \sprintf('product_%s', $searchAfter),
            ];
        }

        $pqb = $this->productQueryBuilderFactory->create($pqbOptions);
        $pqb->addSorter('identifier', Directions::ASCENDING);

        $results = $pqb->execute();

        return \array_map(
            static fn (IdentifierResult $result) => $result->getIdentifier(),
            \iterator_to_array($results)
        );
    }

    private function findProductIdentifier(string $uuid): string
    {
        $sql = <<<SQL
            SELECT identifier
            FROM pim_catalog_product
            WHERE uuid = :uuid
        SQL;

        $identifier = (string) $this->connection->fetchOne($sql, [
            'uuid' => Uuid::fromString($uuid)->getBytes(),
        ]);

        if (!$identifier) {
            throw new \InvalidArgumentException('Unknown uuid');
        }

        return $identifier;
    }

    /**
     * @return array<mixed>
     */
    private function findProductSelectionCriteria(string $catalogId): array
    {
        $sql = <<<SQL
            SELECT product_selection_criteria
            FROM akeneo_catalog
            WHERE id = :id
        SQL;

        /** @var string|false $raw */
        $raw = $this->connection->fetchOne($sql, [
            'id' => Uuid::fromString($catalogId)->getBytes(),
        ]);

        if (!$raw) {
            throw new \InvalidArgumentException('Unknown catalog');
        }

        if (!\is_array($criteria = \json_decode($raw, true))) {
            throw new \LogicException('Invalid JSON in product_selection_criteria column');
        }

        return $criteria;
    }
}
