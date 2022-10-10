<?php

namespace Akeneo\Tool\Bundle\BatchBundle;

use Akeneo\Tool\Bundle\BatchBundle\DependencyInjection\Compiler;
use Akeneo\Tool\Bundle\BatchBundle\DependencyInjection\Compiler\PushBatchLogHandlerPass;
use Akeneo\Tool\Bundle\BatchBundle\DependencyInjection\Compiler\RegisterJobParametersPass;
use Akeneo\Tool\Bundle\BatchBundle\DependencyInjection\Compiler\RegisterJobsPass;
use Akeneo\Tool\Bundle\BatchBundle\DependencyInjection\Compiler\RegisterNotifiersPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
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
            realpath(__DIR__ . '/Resources/config/model/doctrine') => 'Akeneo\Tool\Component\Batch\Model'
        ];
        $container
            ->addCompilerPass(new RegisterNotifiersPass())
            ->addCompilerPass(new PushBatchLogHandlerPass())
            ->addCompilerPass(new RegisterJobsPass())
            ->addCompilerPass(new RegisterJobParametersPass('default_values_provider'))
            ->addCompilerPass(new RegisterJobParametersPass('constraint_collection_provider'))
            ->addCompilerPass(
                DoctrineOrmMappingsPass::createYamlMappingDriver(
                    $mappings,
                    ['doctrine.orm.entity_manager'],
                    false
                )
            );
    }
}
