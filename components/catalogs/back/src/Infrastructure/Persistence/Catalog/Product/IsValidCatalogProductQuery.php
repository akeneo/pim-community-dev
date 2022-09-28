<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogProductSelectionCriteriaQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\IsValidCatalogProductQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsValidCatalogProductQuery implements IsValidCatalogProductQueryInterface
{
    public function __construct(
        private ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        private GetCatalogProductSelectionCriteriaQueryInterface $getCatalogProductSelectionCriteriaQuery,
        private Connection $connection,
    ) {
    }

    public function execute(string $catalogId, string $productUuid): bool
    {
        $productIdentifier = $this->findProductIdentifier($productUuid);
        if (null === $productIdentifier) {
            return false;
        }

        $pqb = $this->productQueryBuilderFactory->create([
            'filters' => $this->getFilters($catalogId),
            'limit' => 1,
        ]);

        $pqb->addFilter('identifier', Operators::EQUALS, $productIdentifier);

        return $pqb->execute()->count() > 0;
    }

    /**
     * @return array<mixed>
     */
    private function getFilters(string $catalogId): array
    {
        $filters = [];
        $productSelectionCriteria = $this->getCatalogProductSelectionCriteriaQuery->execute($catalogId);
        foreach ($productSelectionCriteria as $criterion) {
            $filter = $criterion;

            if (isset($criterion['scope'])) {
                $filter['context']['scope'] = $criterion['scope'];
            }

            if (isset($criterion['locale'])) {
                $filter['context']['locale'] = $criterion['locale'];
            }

            unset($filter['scope'], $filter['locale']);

            $filters[] = $filter;
        }

        return $filters;
    }

    private function findProductIdentifier(string $uuid): ?string
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
            return null;
        }

        return (string) $identifier;
    }
}
