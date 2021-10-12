<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Datagrid;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\AbstractFilter;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;

class ProductValueTableFilter extends AbstractFilter
{
    protected function getFormType(): string
    {
        return ProductValueTableFilterType::class;
    }

    public function apply(FilterDatasourceAdapterInterface $ds, $data): bool
    {
        $operator = $data['operator'] ?? null;
        if (null === $operator
            || (!isset($data['value']) && !in_array($operator, [Operators::IS_NOT_EMPTY, Operators::IS_EMPTY]))
        ) {
            return false;
        }

        unset($data['operator']);
        unset($data['type']);
        $ds->generateParameterName($this->getName());

        $this->util->applyFilter(
            $ds,
            $this->get(ProductFilterUtility::DATA_NAME_KEY),
            $operator,
            $data
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata(): array
    {
        return [
            'name' => $this->getName(),
            'label' => ucfirst($this->name),
            'type' => 'table',
            FilterUtility::ENABLED_KEY => true,
        ];
    }
}
