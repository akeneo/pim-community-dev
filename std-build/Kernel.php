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

    protected static $supportedEnvs = ['dev', 'test', 'test_fake', 'behat', 'prod'];

    public function registerBundles(): iterable
    {
        $bundles = require $this->getProjectDir() . '/vendor/akeneo/pim-enterprise-dev/config/bundles.php';
        $bundles += require $this->getProjectDir() . '/config/bundles.php';
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
        if (!in_array($this->environment, self::$supportedEnvs)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Unsupported environment:%s. The supported environments are:%s',
                    $this->environment,
                    implode(' ', self::$supportedEnvs)
                )
            );
        }

        $container->addResource(new FileResource($this->getProjectDir() . '/config/bundles.php'));
        $container->setParameter('container.dumper.inline_class_loader', true);

        $baseEnv = $this->getBaseEnv($this->environment);

        $ceConfDir = $this->getProjectDir() . '/vendor/akeneo/pim-community-dev/config';
        $eeConfDir = $this->getProjectDir() . '/vendor/akeneo/pim-enterprise-dev/config';
        $projectConfDir = $this->getProjectDir() . '/config';

        $this->loadAkeneoCommunityPackagesConfiguration($loader, $ceConfDir);
        $this->loadAkeneoEnterprisePackagesConfiguration($loader, $eeConfDir, $projectConfDir, $baseEnv);

        $this->loadPackagesConfiguration($loader, $projectConfDir, $this->environment);

        $this->loadServicesConfiguration($loader, $ceConfDir, $baseEnv);
        $this->loadServicesConfiguration($loader, $eeConfDir, $baseEnv);
        $this->loadServicesConfiguration($loader, $projectConfDir, $this->environment);
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $baseEnv = $this->getBaseEnv($this->environment);

        $this->loadRoutesConfiguration(
            $routes,
            $this->getProjectDir() . '/vendor/akeneo/pim-community-dev/config',
            $baseEnv
        );
        $this->loadRoutesConfiguration(
            $routes,
            $this->getProjectDir() . '/vendor/akeneo/pim-enterprise-dev/config',
            $baseEnv
        );
        $this->loadRoutesConfiguration($routes, $this->getProjectDir() . '/config', $this->environment);
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

    private function loadRoutesConfiguration(RouteCollectionBuilder $routes, string $confDir, string $environment): void
    {
        $routes->import($confDir . '/{routes}/' . $environment . '/**/*.yml', '/', 'glob');
        $routes->import($confDir . '/{routes}/*.yml', '/', 'glob');
    }

    private function loadPackagesConfiguration(LoaderInterface $loader, string $confDir, string $environment): void
    {
        $loader->load($confDir . '/{packages}/*.yml', 'glob');
        $loader->load($confDir . '/{packages}/' . $environment . '/*.yml', 'glob');
    }

    /**
     * Load default package configuration from CE root packages config
     * - security configuration doesn't support multiple loads
     */
    private function loadAkeneoCommunityPackagesConfiguration(
        LoaderInterface $loader,
        string $confDir
    ): void {
        $files = array_filter(
            glob($confDir . '/packages/*.yml'),
            function ($file) {
                return 'security.yml' !== basename($file);
            }
        );

        foreach ($files as $file) {
            $loader->load($file, 'yaml');
        }
    }

    /**
     * Load default package configuration from EE root packages config and environment specific config
     * - security configuration doesn't support multiple loads and MUST exist in project
     * - Messenger configuration doesn't support multiple loads and CAN exists in project
     *
     * @see https://akeneo.atlassian.net/browse/PIM-9751
     * @see https://akeneo.atlassian.net/browse/PIM-9795
     */
    private function loadAkeneoEnterprisePackagesConfiguration(
        LoaderInterface $loader,
        string $confDir,
        string $projectConfDir,
        string $environment
    ): void {
        $files = array_merge(
            glob($confDir . '/packages/*.yml'),
            glob($confDir . '/packages/' . $environment . '/*.yml')
        );

        $files = array_filter($files, function ($file) use ($projectConfDir) {
            if ('security.yml' === basename($file)) {
                return false;
            }
            if ('messenger.yml' === basename($file)
                && file_exists($projectConfDir . '/packages/' . $this->environment . '/messenger.yml')) {
                return false;
            }

            return true;
        });

        foreach ($files as $file) {
            $loader->load($file, 'yaml');
        }
    }

    private function loadServicesConfiguration(LoaderInterface $loader, string $confDir, string $environment): void
    {
        $loader->load($confDir . '/{services}/*.yml', 'glob');
        $loader->load($confDir . '/{services}/' . $environment . '/*.yml', 'glob');
    }

    protected function isFlexibility(): bool
    {
        return (getenv('PAPO_PROJECT_CODE_HASHED') !== false);
    }

    /**
     * Return the base env matching the project env.
     * The base env is configured at the level of pim-enterprise-dev
     *
     * The base env is the same as the project env, except for prod environment,
     * where it depends if it's on premise or Flexibility
     */
    protected function getBaseEnv(string $projectEnv): string
    {
        if ('prod' === $projectEnv) {
            if ($this->isFlexibility()) {
                return 'prod_flex';
            } else {
                return 'prod_onprem';
            }
        }

        return $projectEnv;
    }
}
