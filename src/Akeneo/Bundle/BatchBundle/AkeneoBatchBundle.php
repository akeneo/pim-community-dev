<?php

namespace Akeneo\Bundle\BatchBundle;

use Akeneo\Bundle\BatchBundle\DependencyInjection\Compiler;
use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Batch Bundle
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class AkeneoBatchBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $mappings = [
            realpath(__DIR__ . '/Resources/config/model/doctrine') => 'Akeneo\Component\Batch\Model'
        ];
        $container
            ->addCompilerPass(new Compiler\RegisterNotifiersPass())
            ->addCompilerPass(new Compiler\PushBatchLogHandlerPass())
            ->addCompilerPass(new Compiler\RegisterJobsPass())
            ->addCompilerPass(
                DoctrineOrmMappingsPass::createYamlMappingDriver(
                    $mappings,
                    ['doctrine.orm.entity_manager'],
                    false
                )
            );
    }
}
