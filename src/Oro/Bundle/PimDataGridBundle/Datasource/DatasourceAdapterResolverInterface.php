<?php

declare(strict_types=1);

/*
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Oro\Bundle\PimDataGridBundle\Datasource;

interface DatasourceAdapterResolverInterface
{
    public function getAdapterClass(string $datasourceType): string;

    public function addProductDatasource(string $datasource): void;
}
