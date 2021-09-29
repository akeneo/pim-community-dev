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

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\AbstractFilter;

class ProductValueTableFilter extends AbstractFilter
{
    protected function getFormType(): string
    {
        return ProductValueTableFilterType::class;
    }

    public function apply(FilterDatasourceAdapterInterface $ds, $data): bool
    {
        // TODO CPM-377
        throw new \Exception('NEED TO BE IMPLEMENTED');
    }
}
