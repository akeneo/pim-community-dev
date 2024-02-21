<?php

namespace Oro\Bundle\PimDataGridBundle\Datasource;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Determine which datasource adapter class to use.
 *
 * TODO : This resolver and related adapters should be removed after a filter system re-working
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatasourceAdapterResolver implements DatasourceAdapterResolverInterface
{
    /** @var string */
    protected $ormAdapter;

    /** @var string */
    protected $productOrmAdapter;

    /** @var array */
    protected $productDatasources = [];

    /**
     * @param string $ormAdapter
     * @param string $productOrmAdapter
     */
    public function __construct(
        $ormAdapter,
        $productOrmAdapter
    ) {
        $this->ormAdapter = $ormAdapter;
        $this->productOrmAdapter = $productOrmAdapter;
    }

    /**
     * @param string $datasourceType
     *
     * @throws InvalidConfigurationException
     *
     * @return string
     */
    public function getAdapterClass($datasourceType): string
    {
        if (in_array($datasourceType, $this->productDatasources)) {
            return $this->productOrmAdapter;
        }

        return $this->ormAdapter;
    }

    /**
     * Define a product datasource which should use the product adapter
     *
     * @param mixed $datasource
     */
    public function addProductDatasource($datasource): void
    {
        $this->productDatasources[] = $datasource;
    }
}
