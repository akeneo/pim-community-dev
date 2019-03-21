<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Akeneo\ReferenceEntity\Infrastructure\Symfony;

use Akeneo\ReferenceEntity\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterAttributeFactoryPass;
use Akeneo\ReferenceEntity\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterAttributeHydratorPass;
use Akeneo\ReferenceEntity\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterAttributeUpdaterPass;
use Akeneo\ReferenceEntity\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterCreateAttributeCommandFactoryPass;
use Akeneo\ReferenceEntity\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterEditAttributeCommandFactoryPass;
use Akeneo\ReferenceEntity\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterEditRecordValueCommandFactoryPass;
use Akeneo\ReferenceEntity\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterRecordItemValueHydratorPass;
use Akeneo\ReferenceEntity\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterSerializerPass;
use Akeneo\ReferenceEntity\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterValueDataHydratorPass;
use Akeneo\ReferenceEntity\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterValueUpdaterPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Register the bundle
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class AkeneoReferenceEntityBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterSerializerPass('reference_entity_serializer'));
        $container->addCompilerPass(new RegisterCreateAttributeCommandFactoryPass());
        $container->addCompilerPass(new RegisterEditAttributeCommandFactoryPass());
        $container->addCompilerPass(new RegisterAttributeFactoryPass());
        $container->addCompilerPass(new RegisterAttributeUpdaterPass());
        $container->addCompilerPass(new RegisterAttributeHydratorPass());
        $container->addCompilerPass(new RegisterEditRecordValueCommandFactoryPass());
        $container->addCompilerPass(new RegisterValueDataHydratorPass());
        $container->addCompilerPass(new RegisterRecordItemValueHydratorPass());
        $container->addCompilerPass(new RegisterValueUpdaterPass());
    }
}
