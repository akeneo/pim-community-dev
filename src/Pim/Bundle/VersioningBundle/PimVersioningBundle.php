<?php

namespace Pim\Bundle\VersioningBundle;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\StorageMappingsPass;
use Akeneo\Bundle\StorageUtilsBundle\Storage;
use Pim\Bundle\TransformBundle\DependencyInjection\Compiler\SerializerPass;
use Pim\Bundle\VersioningBundle\DependencyInjection\Compiler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Pim Versioning Bundle
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimVersioningBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $mappings = [
            realpath(__DIR__ . '/Resources/config/model/doctrine') => 'Pim\Bundle\VersioningBundle\Model'
        ];

        $ormMappingsPass = StorageMappingsPass::getMappingsPass(Storage::STORAGE_DOCTRINE_ORM, $mappings, true);
        $mongoMappingsPass = StorageMappingsPass::getMappingsPass(Storage::STORAGE_DOCTRINE_MONGODB_ODM, $mappings, true);

        $container
            ->addCompilerPass(new Compiler\RegisterUpdateGuessersPass())
            ->addCompilerPass(new SerializerPass('pim_versioning.serializer'))
            ->addCompilerPass($ormMappingsPass)
            ->addCompilerPass($mongoMappingsPass)
        ;
    }
}
