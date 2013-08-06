<?php

namespace Oro\Bundle\EmailBundle;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Oro\Bundle\EmailBundle\DependencyInjection\Compiler\EmailOwnerConfigurationPass;
use Symfony\Component\Filesystem\Filesystem;

class OroEmailBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new EmailOwnerConfigurationPass());
        $this->addDoctrineOrmMappingsPass($container);

    }

    /**
     * Add a compiler pass handles annotations of extended entities
     *
     * @param ContainerBuilder $container
     */
    protected function addDoctrineOrmMappingsPass(ContainerBuilder $container)
    {
        $cacheDir = sprintf('%s/entities', $container->getParameter('kernel.root_dir'));
        $entityCacheNamespace = 'Extend\Cache\OroEmailBundle\Entity';

        $container->setParameter('oro_email.entity.cache_dir', $cacheDir);
        $container->setParameter('oro_email.entity.cache_namespace', $entityCacheNamespace);

        $entityCacheDir = sprintf('%s/%s', $cacheDir, str_replace('\\', '/', $entityCacheNamespace));
        // Ensure the cache directory exists
        $fs = new Filesystem();
        if (!is_dir($entityCacheDir)) {
            $fs->mkdir($entityCacheDir, 0777);
        }

        $container->addCompilerPass(
            $this->createAnnotationMappingDriver(
                array($entityCacheNamespace),
                array($entityCacheDir)
            )
        );
    }

    /**
     * Create DoctrineOrmMappingsPass object
     *
     * @param array $namespaces List of namespaces that are handled with annotation mapping
     * @param array $directories List of directories to look for annotated classes
     * @param string[] $managerParameters List of parameters that could which object manager name your bundle uses.
     *     This compiler pass will automatically append the parameter name for the default entity manager to this list.
     * @param bool|string $enabledParameter Service container parameter that must be present to enable the mapping
     *     Set to false to not do any check, optional.
     * @return DoctrineOrmMappingsPass
     */
    protected function createAnnotationMappingDriver(array $namespaces, array $directories, array $managerParameters = array(), $enabledParameter = false)
    {
        $reader = new Reference('annotation_reader');
        $driver = new Definition('Doctrine\ORM\Mapping\Driver\AnnotationDriver', array($reader, $directories));

        return new DoctrineOrmMappingsPass($driver, $namespaces, $managerParameters, $enabledParameter);
    }
}
