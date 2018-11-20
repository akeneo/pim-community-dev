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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Symfony;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Enterprise Suggest Data Bundle.
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class AkeneoSuggestDataBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $productMappings = [
            realpath(__DIR__ . '/Resources/config/doctrine/subscription') => 'Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model',
            realpath(__DIR__ . '/Resources/config/doctrine/identifier_mapping') => 'Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Model',
        ];
        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                $productMappings,
                ['doctrine.orm.entity_manager'],
                false
            )
        );
    }
}
