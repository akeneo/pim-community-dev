<?php

namespace Oro\Bundle\DistributionBundle\Routing;

use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class OroAutoLoader extends YamlFileLoader
{
    /**
     * @var HttpKernelInterface
     */
    protected $kernel;

    /**
     *
     * @param FileLocatorInterface $locator
     */
    public function __construct(FileLocatorInterface $locator, HttpKernelInterface $kernel)
    {
        parent::__construct($locator);

        $this->kernel = $kernel;
    }

    public function load($file, $type = null)
    {
        $routes = new RouteCollection();

        foreach ($this->kernel->getBundles() as $bundle) {
            $path = $bundle->getPath() . '/Resources/config/oro/routing.yml';

            if (is_file($path)) {
                $routes->addCollection(parent::load($path, $type));
            }
        }

        return $routes;
    }

    public function supports($resource, $type = null)
    {
        return 'oro_auto' == $type;
    }
}
