<?php

declare(strict_types=1);

namespace Akeneo\Channel\Bundle;

use Akeneo\Channel\Bundle\DependencyInjection\CompilerPass\ResolveDoctrineTargetModelPass;
use Akeneo\Tool\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\ResolveDoctrineTargetRepositoryPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoChannelBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new ResolveDoctrineTargetModelPass())
            ->addCompilerPass(new ResolveDoctrineTargetRepositoryPass('channel_repository'))
        ;

        $channelMappings = [
            realpath(__DIR__ . '/Resources/config/doctrine/model/') => 'Akeneo\Channel\Component\Model'
        ];

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                $channelMappings,
                ['doctrine.orm.entity_manager'],
                false
            )
        );
    }
}
