<?php

namespace Pim\Bundle\GridBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;
use Pim\Bundle\GridBundle\Route\DatagridRouteRegistryBuilder;
use Pim\Bundle\GridBundle\Route\DatagridRouteRegistry;

/**
 * Creates cache of datagrid routes
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridRouteCacheWarmer extends CacheWarmer
{
    /**
     * @var DatagridRouteRegistryBuilder
     */
    protected $builder;

    /**
     * Constructor
     *
     * @param DatagridRouteRegistryBuilder $builder
     */
    public function __construct(DatagridRouteRegistryBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $this->writeCacheFile(
            sprintf('%s/%s', $cacheDir, DatagridRouteRegistry::CACHE_FILE),
            sprintf('<?php return %s;', var_export($this->builder->getRegexps(), true))
        );
    }
}
