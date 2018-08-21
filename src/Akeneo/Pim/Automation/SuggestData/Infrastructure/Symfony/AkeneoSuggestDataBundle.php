<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Symfony;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Command\FetchProductsCommand;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterDataProviderPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Enterprise Suggest Data Bundle
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class AkeneoSuggestDataBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new RegisterDataProviderPass());

        $productMappings = [
            realpath(__DIR__ . '/Resources/config/doctrine/model') => 'Akeneo\Pim\Automation\SuggestData\Domain\Model'
        ];
        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                $productMappings,
                ['doctrine.orm.entity_manager'],
                false
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function registerCommands(Application $application)
    {
        $application->add(new FetchProductsCommand());
    }
}
