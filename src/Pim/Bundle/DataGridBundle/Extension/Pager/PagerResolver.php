<?php

namespace Pim\Bundle\DataGridBundle\Extension\Pager;

use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceSupportResolver;
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
    /** @var DatasourceSupportResolver */
    protected $supportResolver;

    /** @var PagerInterface */
    protected $ormPager;

    /** @var PagerInterface */
    protected $mongodbPager;

    /**
     * @param DatasourceSupportResolver $supportResolver
     * @param PagerInterface            $ormPager
     */
    public function __construct(DatasourceSupportResolver $supportResolver, PagerInterface $ormPager)
    {
        $this->supportResolver = $supportResolver;
        $this->ormPager = $ormPager;
    }

    /**
     * @param string $datasourceType
     *
     * @throws InvalidConfigurationException
     *
     * @return PagerInterface
     */
    public function getPager($datasourceType)
    {
        if (DatasourceSupportResolver::DATASOURCE_SUPPORT_ORM ===
            $this->supportResolver->getSupport($datasourceType)
        ) {
            return $this->ormPager;
        } elseif (null === $this->mongodbPager) {
            throw new InvalidConfigurationException('The MongoDB pager should be registered.');
        }

        if (DatasourceSupportResolver::DATASOURCE_SUPPORT_MONGODB ===
            $this->supportResolver->getSupport($datasourceType)) {
            return $this->mongodbPager;
        }

        return $this->ormPager;
    }

    /**
     * @param PagerInterface $mongodbPager
     */
    public function setMongodbPager(PagerInterface $mongodbPager)
    {
        $this->mongodbPager = $mongodbPager;
    }
}
