<?php

namespace Pim\Bundle\DataGridBundle\Datasource;

use Pim\Bundle\CatalogBundle\DependencyInjection\PimCatalogExtension;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

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
     * @param string $storageDriver
     * @param string $ormAdapterClass
     */
    public function __construct($storageDriver, $ormAdapterClass)
    {
        $this->storageDriver = $storageDriver;
        $this->ormAdapterClass = $ormAdapterClass;
    }

    /**
     * @param $datasourceType
     *
     * @return string
     * @throws InvalidConfigurationException
     */
    public function getDatasourceClass($datasourceType)
    {
        if (PimCatalogExtension::DOCTRINE_ORM === $this->storageDriver) {
            return $this->ormAdapterClass;
        } elseif (null === $this->odmAdapterClass) {
            throw new InvalidConfigurationException('The MongoDB adapter class should be registered.');
        }

        if (DatasourceInterface::DATASOURCE_SMART === $datasourceType ||
            DatasourceInterface::DATASOURCE_PRODUCT === $datasourceType) {
            return $this->odmAdapterClass;
        }

        return $this->ormAdapterClass;
    }

    /**
     * @param string $odmAdapterClass
     */
    public function setOdmAdapterClass($odmAdapterClass)
    {
        $this->odmAdapterClass = $odmAdapterClass;
    }
}
