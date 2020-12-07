<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * PIM Kernel
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        $bundles = require $this->getProjectDir() . '/config/bundles.php';
        $bundles += require $this->getProjectDir() . '/config/bundles.test.php';
        foreach ($bundles as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }

    public function getProjectDir(): string
    {
        return \dirname(__DIR__);
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->addResource(new FileResource($this->getProjectDir() . '/config/bundles.php'));
        $container->setParameter('container.dumper.inline_class_loader', true);

        $eeConfDir = $this->getProjectDir() . '/config';
        $ceConfDir = $this->getProjectDir() . '/vendor/akeneo/pim-community-dev/config';

        $this->loadPackagesConfigurationExceptSecurity($loader, $ceConfDir);
        $this->loadPackagesConfiguration($loader, $eeConfDir);

        $this->loadContainerConfiguration($loader, $ceConfDir);
        $this->loadContainerConfiguration($loader, $eeConfDir);
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $this->loadRoutesConfiguration($routes, $this->getProjectDir() . '/vendor/akeneo/pim-community-dev/config');
        $this->loadRoutesConfiguration($routes, $this->getProjectDir() . '/config');
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir(): string
    {
        return $this->getProjectDir() . '/var/cache/' . $this->environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/var/logs';
    }

    private function loadRoutesConfiguration(RouteCollectionBuilder $routes, string $confDir): void
    {
        $routes->import($confDir . '/{routes}/' . $this->environment . '/**/*.yml', '/', 'glob');
        $routes->import($confDir . '/{routes}/*.yml', '/', 'glob');
    }

    private function loadPackagesConfiguration(LoaderInterface $loader, string $confDir): void
    {
        $loader->load($confDir . '/{packages}/*.yml', 'glob');
        $loader->load($confDir . '/{packages}/' . $this->environment . '/**/*.yml', 'glob');
    }

    /**
     * "security.yml" is the only configuration file that can not be override
     * Thus, we don't load it from the Community Edition.
     * We copied/pasted its content into Enterprise Edition and added what was missing.
     */
    private function loadPackagesConfigurationExceptSecurity(LoaderInterface $loader, string $confDir): void
    {
        $files = array_merge(
            glob($confDir . '/{packages}/*.yml', GLOB_BRACE),
        );

        $files = array_filter(
            $files,
            function ($file) {
                return 'security.yml' !== basename($file);
            }
        );

        foreach ($files as $file) {
            $loader->load($file, 'yaml');
        }
    }

    private function loadContainerConfiguration(LoaderInterface $loader, string $confDir): void
    {
        $loader->load($confDir . '/{services}/*.yml', 'glob');
        $loader->load($confDir . '/{services}/' . $this->environment . '/**/*.yml', 'glob');
    }
}
