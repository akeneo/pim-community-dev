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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Symfony;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Enterprise Franklin Insights Bundle.
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class AkeneoFranklinInsightsBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $productMappings = [
            realpath(__DIR__ . '/Resources/config/doctrine/subscription') => 'Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model',
            realpath(__DIR__ . '/Resources/config/doctrine/identifier_mapping') => 'Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model',
            realpath(__DIR__ . '/Resources/config/doctrine/subscription_id') => 'Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject',
            realpath(__DIR__ . '/Resources/config/doctrine/common') => 'Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject',
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
