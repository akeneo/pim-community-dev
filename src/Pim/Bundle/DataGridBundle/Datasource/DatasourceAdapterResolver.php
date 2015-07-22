<?php

namespace Pim\Bundle\DataGridBundle\Datasource;

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
class DatasourceAdapterResolver
{
    /** @var DatasourceSupportResolver */
    protected $supportResolver;

    /** @var string */
    protected $ormAdapter;

    /** @var string */
    protected $mongoAdapter;

    /** @var string */
    protected $productOrmAdapter;

    /** @var string */
    protected $productMongoAdapter;

    /** @var array */
    protected $productDatasources = [];

    /**
     * @param DatasourceSupportResolver $supportResolver
     * @param string                    $ormAdapter
     * @param string                    $productOrmAdapter
     */
    public function __construct(
        DatasourceSupportResolver $supportResolver,
        $ormAdapter,
        $productOrmAdapter
    ) {
        $this->supportResolver = $supportResolver;
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
    public function getAdapterClass($datasourceType)
    {
        if (DatasourceSupportResolver::DATASOURCE_SUPPORT_ORM ===
            $this->supportResolver->getSupport($datasourceType)
        ) {
            if (in_array($datasourceType, $this->productDatasources)) {
                return $this->productOrmAdapter;
            } else {
                return $this->ormAdapter;
            }
        } elseif (null === $this->mongoAdapter) {
            throw new InvalidConfigurationException('The MongoDB adapter class should be registered.');
        }

        if (DatasourceSupportResolver::DATASOURCE_SUPPORT_MONGODB ===
            $this->supportResolver->getSupport($datasourceType)) {
            if (in_array($datasourceType, $this->productDatasources)) {
                return $this->productMongoAdapter;
            } else {
                return $this->mongoAdapter;
            }
        }

        return $this->ormAdapter;
    }

    /**
     * @param string $mongoAdapter
     */
    public function setMongodbAdapterClass($mongoAdapter)
    {
        $this->mongoAdapter = $mongoAdapter;
    }

    /**
     * @param string $productMongoAdapter
     */
    public function setProductMongodbAdapterClass($productMongoAdapter)
    {
        $this->productMongoAdapter = $productMongoAdapter;
    }

    /**
     * Define a product datasource which should use the product adapter
     *
     * @param mixed $datasource
     */
    public function addProductDatasource($datasource)
    {
        $this->productDatasources[] = $datasource;
    }
}
