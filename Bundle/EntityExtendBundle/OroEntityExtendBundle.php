<?php

namespace Oro\Bundle\EntityExtendBundle;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Symfony\Component\ClassLoader\UniversalClassLoader;
use Symfony\Component\Process\PhpExecutableFinder;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;

use Oro\Bundle\EntityExtendBundle\Tools\Generator;
use Oro\Bundle\EntityExtendBundle\Exception\RuntimeException;

use Oro\Bundle\EntityExtendBundle\DependencyInjection\Compiler\EntityManagerPass;
use Symfony\Component\Process\Process;

class OroEntityExtendBundle extends Bundle
{
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function boot()
    {
        $this->initExtend();
    }

    public function build(ContainerBuilder $container)
    {
        $this->initExtend();

        $container->addCompilerPass(new EntityManagerPass());
        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                array(
                    $this->kernel->getCacheDir() . '/entities/Extend/Entity' => 'Extend\Entity'
                )
            )
        );
    }

    private function initExtend()
    {
        $this->checkCacheFolder();
        $this->checkCache();
        $this->registerAutoloader();
        $this->loadAlias();
    }

    private function registerAutoloader()
    {
        $loader = new UniversalClassLoader();
        $loader->registerNamespaces(
            array('Extend\\' => $this->kernel->getCacheDir() . '/entities')
        );
        $loader->register();
    }

    private function loadAlias()
    {
        $aliasPath = $this->kernel->getCacheDir() . '/entities/Extend/Entity/alias.yml';
        if (file_exists($aliasPath)
            && (!isset($_SERVER['argv']) || !in_array('oro:entity-extend:update-config', $_SERVER['argv']))
        ) {
            $aliases = \Symfony\Component\Yaml\Yaml::parse(
                file_get_contents($aliasPath, FILE_USE_INCLUDE_PATH)
            );

            if (is_array($aliases)) {
                foreach ($aliases as $className => $alias) {
                    if (class_exists($className) && !class_exists($alias, false)) {
                        $aliasArr = explode('\\', $alias);
                        $shortAlias = array_pop($aliasArr);

                        class_alias($className, $shortAlias);
                        class_alias($className, $alias);
                    }
                }
            }
        }
    }

    private function checkCacheFolder()
    {
        $cacheDirs = array(
            $this->kernel->getCacheDir() . '/entities/Extend/Entity',
            $this->kernel->getCacheDir() . '/entities/Extend/Validator',
        );

        foreach ($cacheDirs as $dir) {
            if (!is_dir($dir)) {
                if (false === @mkdir($dir, 0777, true)) {
                    throw new RuntimeException(sprintf('Could not create cache directory "%s".', $dir));
                }
            }
        }
    }

    private function checkCache()
    {
        $cacheDir = $this->kernel->getCacheDir() . '/entities';
        if (!file_exists($cacheDir . '/entity_config.yml')
            && (!isset($_SERVER['argv'])  || !in_array('oro:entity-extend:dump', $_SERVER['argv']))
        ) {
            if (file_exists($cacheDir . '/Extend/Entity/alias.yml')) {
                unlink($cacheDir . '/Extend/Entity/alias.yml');
            }
            $console = escapeshellarg($this->getPhp()) . ' ' . escapeshellarg($this->kernel->getRootDir() . '/console');
            $env     = $this->kernel->getEnvironment();

            $process = new Process($console . ' oro:entity-extend:dump' . ' --env ' . $env);
            $process->run();
        }

        if (count(scandir($cacheDir . '/Extend/Entity')) == 2) {
            $generator = new Generator($cacheDir);
            $generator->generate();
        }
    }

    private function getPhp()
    {
        $phpFinder = new PhpExecutableFinder();
        if (!$phpPath = $phpFinder->find()) {
            throw new \RuntimeException('The php executable could not be found, add it to your PATH environment variable and try again');
        }

        return $phpPath;
    }
}
