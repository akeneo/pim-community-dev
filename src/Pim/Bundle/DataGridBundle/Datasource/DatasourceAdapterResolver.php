<?php

namespace Pim\Bundle\DataGridBundle\Datasource;

use Pim\Bundle\CatalogBundle\DependencyInjection\PimCatalogExtension;

/**
 * Resolver for datasource adapters
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatasourceAdapterResolver
{
    /** @var string */
    protected $storageDriver;

    /** @var string */
    protected $ormAdapterClass;

    /** @var string */
    protected $odmAdapterClass;

    /**
     * @param $storageDriver
     * @param $ormAdapterClass
     * @param $odmAdapterClass
     */
    public function __construct($storageDriver, $ormAdapterClass, $odmAdapterClass)
    {
        $this->storageDriver = $storageDriver;
        $this->ormAdapterClass = $ormAdapterClass;
        $this->odmAdapterClass = $odmAdapterClass;
    }

    /**
     * @param $datasourceType
     *
     * @return string
     */
    public function getDatasourceClass($datasourceType)
    {
        if (PimCatalogExtension::DOCTRINE_ORM === $this->storageDriver) {
            return $this->ormAdapterClass;
        }

        if ('pim_version' === $datasourceType || 'pim_product' === $datasourceType) {
            return $this->odmAdapterClass;
        }

        return $this->ormAdapterClass;
    }
}
