<?php

namespace Pim\Bundle\DataGridBundle\Datasource;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Determine which datasource adapter class to use.
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

    /**
     * @param DatasourceSupportResolver $supportResolver
     * @param string                    $ormAdapterClass
     */
    public function __construct(DatasourceSupportResolver $supportResolver, $ormAdapterClass)
    {
        $this->supportResolver = $supportResolver;
        $this->ormAdapterClass = $ormAdapterClass;
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
            return $this->ormAdapterClass;
        } elseif (null === $this->mongodbAdapterClass) {
            throw new InvalidConfigurationException('The MongoDB adapter class should be registered.');
        }

        if (DatasourceSupportResolver::DATASOURCE_SUPPORT_MONGODB ===
            $this->supportResolver->getSupport($datasourceType)) {
            return $this->mongodbAdapterClass;
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
}
