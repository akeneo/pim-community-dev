<?php

namespace Oro\Bundle\EntityExtendBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;

use Symfony\Component\ClassLoader\UniversalClassLoader;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;

use Oro\Bundle\EntityExtendBundle\DependencyInjection\Compiler\EntityManagerPass;
use Oro\Bundle\EntityExtendBundle\Exception\RuntimeException;

class OroEntityExtendBundle extends Bundle
{
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function boot()
    {
        $this->checkCache();
        $this->loadAlias();

        $loader = new UniversalClassLoader();

        $loader->registerNamespaces(
            array('Extend\\' => $this->kernel->getCacheDir() . '/entities')
        );

        $loader->register();
    }


    public function build(ContainerBuilder $container)
    {
        $this->checkCache();

        $container->addCompilerPass(new EntityManagerPass());
        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                array(
                    $this->kernel->getCacheDir() . '/entities/Extend/Entity' => 'Extend\Entity'
                )
            )
        );
    }

    private function loadAlias()
    {
        $aliasPath = $this->kernel->getCacheDir() . '/entities/Extend/Entity/alias.yml';
        if (file_exists($aliasPath)) {
            $aliases = \Symfony\Component\Yaml\Yaml::parse(
                file_get_contents($aliasPath, FILE_USE_INCLUDE_PATH)
            );

            if (is_array($aliases)) {
                foreach ($aliases as $className => $alias) {
                    if (class_exists($className)) {
                        class_alias($className, $alias);
                    }
                }
            }
        }
    }

    private function checkCache()
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
}
