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
    protected $ormAdapterClass;

    /** @var string */
    protected $mongodbAdapterClass;

    /** @var string */
    protected $productOrmAdapterClass;

    /** @var string */
    protected $productMongodbAdapterClass;

    /** @var array */
    protected $productDatasources = [];

    /**
     * @param DatasourceSupportResolver $supportResolver
     * @param string                    $ormAdapterClass
     * @param string                    $productOrmAdapterClass
     */
    public function __construct(
        DatasourceSupportResolver $supportResolver,
        $ormAdapterClass,
        $productOrmAdapterClass
    ) {
        $this->supportResolver = $supportResolver;
        $this->ormAdapterClass = $ormAdapterClass;
        $this->productOrmAdapterClass = $productOrmAdapterClass;
    }

    /**
     * @param string $datasourceType
     *
     * @return string
     *
     * @throws InvalidConfigurationException
     */
    public function getAdapterClass($datasourceType)
    {
        if (DatasourceSupportResolver::DATASOURCE_SUPPORT_ORM ===
            $this->supportResolver->getSupport($datasourceType)
        ) {
            if (in_array($datasourceType, $this->productDatasources)) {
                return $this->productOrmAdapterClass;
            } else {
                return $this->ormAdapterClass;
            }
        } elseif (null === $this->mongodbAdapterClass) {
            throw new InvalidConfigurationException('The MongoDB adapter class should be registered.');
        }

        if (DatasourceSupportResolver::DATASOURCE_SUPPORT_MONGODB ===
            $this->supportResolver->getSupport($datasourceType)) {
            if (in_array($datasourceType, $this->productDatasources)) {
                return $this->productMongodbAdapterClass;
            } else {
                return $this->mongodbAdapterClass;
            }
        }

        return $this->ormAdapterClass;
    }

    /**
     * @param string $mongodbAdapterClass
     */
    public function setMongodbAdapterClass($mongodbAdapterClass)
    {
        $this->mongodbAdapterClass = $mongodbAdapterClass;
    }

    /**
     * @param string $productMongodbAdapterClass
     */
    public function setProductMongodbAdapterClass($productMongodbAdapterClass)
    {
        $this->productMongodbAdapterClass = $productMongodbAdapterClass;
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
