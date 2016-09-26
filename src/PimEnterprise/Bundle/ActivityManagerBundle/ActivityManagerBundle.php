<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Bundle;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ActivityManagerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $mappingConfig = [
            realpath(__DIR__.'/Resources/config/doctrine') => 'Akeneo\ActivityManager\Component\Model',
        ];

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                $mappingConfig,
                ['doctrine.orm.entity_manager'],
                'akeneo_storage_utils.storage_driver.doctrine/orm'
            )
        );
    }
}
