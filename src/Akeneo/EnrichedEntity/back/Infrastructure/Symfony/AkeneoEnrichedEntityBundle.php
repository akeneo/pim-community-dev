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
namespace Akeneo\EnrichedEntity\Infrastructure\Symfony;

use Akeneo\EnrichedEntity\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterAttributeFactoryPass;
use Akeneo\EnrichedEntity\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterCreateAttributeCommandFactoryPass;
use Akeneo\EnrichedEntity\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterEditAttributeCommandFactoryPass;
use Akeneo\EnrichedEntity\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterSerializerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Register the bundle
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class AkeneoEnrichedEntityBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterSerializerPass('enriched_entity_serializer'));
        $container->addCompilerPass(new RegisterAttributeFactoryPass());
        $container->addCompilerPass(new RegisterCreateAttributeCommandFactoryPass());
        $container->addCompilerPass(new RegisterEditAttributeCommandFactoryPass());
    }
}
