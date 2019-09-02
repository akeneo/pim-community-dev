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
namespace Akeneo\AssetManager\Infrastructure\Symfony;

use Akeneo\AssetManager\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterAssetItemValueHydratorPass;
use Akeneo\AssetManager\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterAttributeFactoryPass;
use Akeneo\AssetManager\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterAttributeHydratorPass;
use Akeneo\AssetManager\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterAttributeUpdaterPass;
use Akeneo\AssetManager\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterCreateAttributeCommandFactoryPass;
use Akeneo\AssetManager\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterEditAssetValueCommandFactoryPass;
use Akeneo\AssetManager\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterEditAttributeCommandFactoryPass;
use Akeneo\AssetManager\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterPreviewGeneratorPass;
use Akeneo\AssetManager\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterSerializerPass;
use Akeneo\AssetManager\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterValueDataHydratorPass;
use Akeneo\AssetManager\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterValueUpdaterPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Register the bundle
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class AkeneoAssetManagerBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterSerializerPass('asset_family_serializer'));
        $container->addCompilerPass(new RegisterCreateAttributeCommandFactoryPass());
        $container->addCompilerPass(new RegisterEditAttributeCommandFactoryPass());
        $container->addCompilerPass(new RegisterAttributeFactoryPass());
        $container->addCompilerPass(new RegisterAttributeUpdaterPass());
        $container->addCompilerPass(new RegisterAttributeHydratorPass());
        $container->addCompilerPass(new RegisterEditAssetValueCommandFactoryPass());
        $container->addCompilerPass(new RegisterPreviewGeneratorPass());
        $container->addCompilerPass(new RegisterValueDataHydratorPass());
        $container->addCompilerPass(new RegisterAssetItemValueHydratorPass());
        $container->addCompilerPass(new RegisterValueUpdaterPass());
    }
}
