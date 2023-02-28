<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Application\Exception\ProductMappingSchemaNotFoundException;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductUuidsQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\Infrastructure\Persistence\ProductMappingSchema\GetProductMappingSchemaQuery;
use Akeneo\Catalogs\Infrastructure\PqbFilters\ProductMappingRequiredFilters;
use Akeneo\Catalogs\Infrastructure\PqbFilters\ProductSelectionCriteria;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductUuidsQuery implements GetProductUuidsQueryInterface
{
    public function __construct(
        private readonly ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        private readonly GetProductMappingSchemaQuery $productMappingSchemaQuery,
    ) {
    }

    /**
     * This implementation is coupled temporarly to the PQB,
     * we are waiting for the ServiceAPI version that should be available soon.
     *
     * @return array<string>
     */
    public function execute(
        Catalog $catalog,
        ?string $searchAfter = null,
        int $limit = 100,
        ?string $updatedAfter = null,
        ?string $updatedBefore = null,
    ): array {
        $filters = \array_merge(
            $this->getUpdatedFilters($updatedAfter, $updatedBefore),
            ProductSelectionCriteria::toPQBFilters($catalog->getProductSelectionCriteria()),
        );

        try {
            $productMappingSchema = $this->productMappingSchemaQuery->execute($catalog->getId());
            $filters = \array_merge(
                $filters,
                ProductMappingRequiredFilters::toPQBFilters($catalog->getProductMapping(), $productMappingSchema),
            );
        } catch (ProductMappingSchemaNotFoundException) {
        }

        $pqbOptions = [
            'filters' => $filters,
            'limit' => $limit,
        ];

        if (null !== $searchAfter) {
            $pqbOptions['search_after'] = [
                \sprintf('product_%s', $searchAfter),
            ];
        }

        $pqb = $this->productQueryBuilderFactory->create($pqbOptions);
        $pqb->addSorter('id', Directions::ASCENDING);

        $results = $pqb->execute();

        return \array_map(
            fn (IdentifierResult $result): string => $this->getUuidFromIdentifierResult($result->getId()),
            \iterator_to_array($results),
        );
    }

    /**
     * @return array<mixed>
     */
    private function getUpdatedFilters(?string $updatedAfter, ?string $updatedBefore): array
    {
        [$operator, $value] = $this->parseUpdatedParameters($updatedAfter, $updatedBefore);

        if (null === $operator || null === $value) {
            return [];
        }

        return [
            [
                'field' => 'updated',
                'operator' => $operator,
                'value' => $value,
            ],
        ];
    }

    /**
     * @return array{string|null, string|array<string>|null}
     */
    private function parseUpdatedParameters(?string $updatedAfter, ?string $updatedBefore): array
    {
        $updatedAfterDateTime = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, (string) $updatedAfter);
        $updatedBeforeDateTime = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, (string) $updatedBefore);

        if (false !== $updatedAfterDateTime) {
            $updatedAfterDateTime = $updatedAfterDateTime->setTimezone(new \DateTimeZone('UTC'));
        }
        if (false !== $updatedBeforeDateTime) {
            $updatedBeforeDateTime = $updatedBeforeDateTime->setTimezone(new \DateTimeZone('UTC'));
        }

        if (null !== $updatedAfter && null !== $updatedBefore) {
            if (false !== $updatedAfterDateTime && false !== $updatedBeforeDateTime) {
                return [
                    Operators::BETWEEN,
                    [
                        $updatedAfterDateTime->format('Y-m-d H:i:s'),
                        $updatedBeforeDateTime->format('Y-m-d H:i:s'),
                    ],
                ];
            }
        } elseif (null !== $updatedAfter) {
            if (false !== $updatedAfterDateTime) {
                return [
                    Operators::GREATER_THAN,
                    $updatedAfterDateTime->format('Y-m-d H:i:s'),
                ];
            }
        } elseif (null !== $updatedBefore) {
            if (false !== $updatedBeforeDateTime) {
                return [
                    Operators::LOWER_THAN,
                    $updatedBeforeDateTime->format('Y-m-d H:i:s'),
                ];
            }
        }

        return [null, null];
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
