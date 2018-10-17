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

namespace Akeneo\Asset\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ConfigureUserServicesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $userUpdater = $container->getDefinition('pim_user.updater.user');
        $userUpdater->addArgument($container->getDefinition('pimee_product_asset.repository.category'));
    }
}
