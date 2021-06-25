<?php

namespace Akeneo\Tool\Bundle\BatchQueueBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Batch queue bundle
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoBatchQueueBundle extends Bundle
{
    /**
     * {@inheritdoc}
     *
     * @TODO CPM-154: to remove
     */
    public function build(ContainerBuilder $container)
    {
        $mappings = [
            realpath(__DIR__ . '/Resources/config/model/doctrine') => 'Akeneo\Tool\Component\BatchQueue\Queue'
        ];
        $container
            ->addCompilerPass(
                DoctrineOrmMappingsPass::createYamlMappingDriver(
                    $mappings,
                    ['doctrine.orm.entity_manager'],
                    false
                )
            );
    }
}
