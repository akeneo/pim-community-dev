<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Query\Elasticsearch;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\AbstractEntityWithValuesQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderOptionsResolverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\SorterRegistryInterface;
use Akeneo\Pim\Enrichment\Product\Domain\PQB\ProductQueryBuilderInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;

/**
 * This class is an adapter between the former implementation of PQB and the new one.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductQueryBuilderAdapter extends AbstractEntityWithValuesQueryBuilder implements ProductQueryBuilderInterface
{
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        FilterRegistryInterface $filterRegistry,
        SorterRegistryInterface $sorterRegistry,
        ProductQueryBuilderOptionsResolverInterface $optionResolver
    ) {
        $cursorFactory = new class implements CursorFactoryInterface {
            public function createCursor($queryBuilder, array $options = [])
            {
                throw new \RuntimeException('This class should not be called anymore');
            }
        };

        parent::__construct(
            $attributeRepository,
            $filterRegistry,
            $sorterRegistry,
            $cursorFactory,
            $optionResolver,
            [
                'locale' => null,
                'scope'  => null,
            ]
        );
        $this->setQueryBuilder(new SearchQueryBuilder());
    }

    public function buildQuery(): array
    {
        $this->addFilter('entity_type', Operators::EQUALS, ProductInterface::class);

        return $this->getQueryBuilder()->getQuery();
    }
}
