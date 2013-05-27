<?php

namespace Oro\Bundle\NavigationBundle\Title\TitleReader;

use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class Reader
 * @package Oro\Bundle\NavigationBundle\Title\TitleReader
 */
abstract class Reader
{
    /**
     * @var array
     */
    protected $bundles;

    public function __construct(KernelInterface $kernel)
    {
        $this->bundles = $kernel->getBundles();
    }

    /**
     * Returns data from source
     *
     * @param  array $routes
     * @return array
     */
    abstract public function getData(array $routes);

    /**
     * Get dir array of bundles
     *
     * @return array
     */
    protected function getScanDirectories()
    {
        $directories = false;
        $bundles = $this->bundles;

        foreach ($bundles as $bundle) {
            /** @var $bundle \Symfony\Component\HttpKernel\Bundle\BundleInterface  */
            $directories[] = $bundle->getPath();
        }

        return $directories;
    }
}
