<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductUuidsQueryInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductUuidsQuery implements GetProductUuidsQueryInterface
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
    public function execute(
        string $catalogId,
        ?string $searchAfter = null,
        int $limit = 100,
        ?string $updatedAfter = null,
        ?string $updatedBefore = null,
    ): array {
        $pqbOptions = [
            'filters' => \array_merge(
                $this->getUpdatedFilters($updatedAfter, $updatedBefore),
                $this->getFilters($catalogId)
            ),
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
            fn (IdentifierResult $result) => $this->getUuidFromIdentifierResult($result->getId()),
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

        /** @var mixed|false $identifier */
        $identifier = $this->connection->fetchOne($sql, [
            'uuid' => Uuid::fromString($uuid)->getBytes(),
        ]);

        if (false === $identifier) {
            throw new \InvalidArgumentException('Unknown uuid');
        }

        return (string) $identifier;
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

        if (!\is_array($criteria = \json_decode($raw, true, 512, JSON_THROW_ON_ERROR))) {
            throw new \LogicException('Invalid JSON in product_selection_criteria column');
        }

        return $criteria;
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
                    ]
                ];
            }
        } elseif (null !== $updatedAfter) {
            if (false !== $updatedAfterDateTime) {
                return [
                    Operators::GREATER_THAN,
                    $updatedAfterDateTime->format('Y-m-d H:i:s')
                ];
            }
        } elseif (null !== $updatedBefore) {
            if (false !== $updatedBeforeDateTime) {
                return [
                    Operators::LOWER_THAN,
                    $updatedBeforeDateTime->format('Y-m-d H:i:s')
                ];
            }
        }

        return [null, null];
    }

    /**
     * @return array<mixed>
     */
    private function getFilters(string $catalogId): array
    {
        $filters = [];
        /** @var array<array-key, array{field: string, operator: string, value?: mixed, scope?: string|null, locale?: string|null}> $productSelectionCriteria */
        $productSelectionCriteria = $this->findProductSelectionCriteria($catalogId);
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

    private function getUuidFromIdentifierResult(string $esId): string
    {
        $matches = [];
        if (!\preg_match(
            '/^product_(?P<uuid>[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})$/',
            $esId,
            $matches
        )) {
            throw new \InvalidArgumentException(\sprintf('Invalid Elasticsearch identifier %s', $esId));
        }

        return $matches['uuid'];
    }
}
