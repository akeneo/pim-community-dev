<?php

namespace Pim\Bundle\DataGridBundle\Datasource;

use Pim\Bundle\CatalogBundle\DependencyInjection\PimCatalogExtension;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Determine the datasource suport to use depending on the datasource and the storage driver.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatasourceSupportResolver
{
    /** @staticvar string */
    const DATASOURCE_SUPPORT_ORM = 'orm';

    /** @staticvar string */
    const DATASOURCE_SUPPORT_MONGODB = 'mongodb';

    /** @var string */
    protected $storageDriver;

    /** @var array */
    protected $smartDatasources = [];

    /**
     * @param string $storageDriver
     * @param array  $mongodbEligibles
     */
    public function __construct(
        $storageDriver,
        array $smartDatasources = [DatasourceTypes::DATASOURCE_SMART, DatasourceTypes::DATASOURCE_PRODUCT]
    ) {
        $this->storageDriver    = $storageDriver;
        $this->smartDatasources = $smartDatasources;
    }

    /**
     * @param $datasourceType
     *
     * @return string
     *
     * @throws InvalidConfigurationException
     */
    public function getSupport($datasourceType)
    {
        if (PimCatalogExtension::DOCTRINE_ORM === $this->storageDriver) {
            return self::DATASOURCE_SUPPORT_ORM;
        }

        if (in_array($datasourceType, $this->smartDatasources)) {
            return self::DATASOURCE_SUPPORT_MONGODB;
        }

        return self::DATASOURCE_SUPPORT_ORM;
    }

    /**
     * Define a datasource as eligible to the MongoDB support.
     *
     * @param $datasource
     */
    public function addMongoEligibleDatasources($datasource)
    {
        $this->smartDatasources[] = $datasource;
    }
}
