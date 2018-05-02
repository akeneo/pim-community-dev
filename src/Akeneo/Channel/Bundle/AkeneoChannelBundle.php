<?php

namespace Akeneo\Channel\Bundle;

use Akeneo\Channel\Bundle\DependencyInjection\CompilerPass\ResolveDoctrineTargetModelPass;
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
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new ResolveDoctrineTargetModelPass())
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
