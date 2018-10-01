<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Symfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RegisterEditRecordValueCommandFactoryPass implements CompilerPassInterface
{
    private const RECORD_VALUE_COMMAND_FACTORY_REGISTRY = 'akeneo_referenceentity.application.registry.record.edit_record_value_command_factory_registry';
    private const RECORD_VALUE_COMMAND_FACTORY_TAG = 'akeneo_referenceentity.edit_record_value_command_factory';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $registry = $container->getDefinition(self::RECORD_VALUE_COMMAND_FACTORY_REGISTRY);
        $recordValueFactories = $container->findTaggedServiceIds(self::RECORD_VALUE_COMMAND_FACTORY_TAG);

        foreach (array_keys($recordValueFactories) as $recordValueFactoryId) {
            $registry->addMethodCall('register', [new Reference($recordValueFactoryId)]);
        }
    }
}
