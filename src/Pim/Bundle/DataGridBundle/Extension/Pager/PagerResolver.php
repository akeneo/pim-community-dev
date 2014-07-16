<?php

namespace Pim\Bundle\DataGridBundle\Extension\Pager;

use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use Pim\Bundle\CatalogBundle\DependencyInjection\PimCatalogExtension;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Resolver for pager : determine which pager should be used depending on the grid and the storage driver.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PagerResolver
{
    /** @var string */
    protected $storageDriver;

    /** @var PagerInterface */
    protected $ormPager;

    /** @var PagerInterface */
    protected $mongoPager;

    /**
     * @param string         $storageDriver
     * @param PagerInterface $ormPager
     */
    public function __construct($storageDriver, PagerInterface $ormPager)
    {
        $this->storageDriver = $storageDriver;
        $this->ormPager = $ormPager;
    }

    /**
     * @param $gridType
     *
     * @return PagerInterface
     * @throws InvalidConfigurationException
     */
    public function getPager($gridType)
    {
        if (PimCatalogExtension::DOCTRINE_ORM === $this->storageDriver) {
            return $this->ormPager;
        } elseif (null === $this->mongoPager) {
            throw new InvalidConfigurationException('The MongoDB pager should be registered.');
        }

        if (DatasourceInterface::DATASOURCE_SMART === $gridType ||
            DatasourceInterface::DATASOURCE_PRODUCT === $gridType) {
            return $this->mongoPager;
        }

        return $this->ormPager;
    }

    /**
     * @param PagerInterface $mongoPager
     */
    public function setMongoPager(PagerInterface $mongoPager)
    {
        $this->mongoPager = $mongoPager;
    }
}
