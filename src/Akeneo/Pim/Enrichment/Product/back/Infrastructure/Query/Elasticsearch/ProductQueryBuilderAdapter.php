<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Query\Elasticsearch;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\AbstractEntityWithValuesQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Product\Domain\PQB\ProductQueryBuilderInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductQueryBuilderAdapter extends AbstractEntityWithValuesQueryBuilder implements ProductQueryBuilderInterface
{
    public function buildQuery(): array
    {
        $this->addFilter('entity_type', Operators::EQUALS, ProductInterface::class);

        return $this->getQueryBuilder()->getQuery();
    }
}
