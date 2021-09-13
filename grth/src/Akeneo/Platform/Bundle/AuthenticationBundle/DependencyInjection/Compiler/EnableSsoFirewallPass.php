<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\AuthenticationBundle\DependencyInjection\Compiler;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\FirewallMap;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class EnableSsoFirewallPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('security.firewall.map')) {
            return;
        }

        $mapDef = $container->getDefinition('security.firewall.map');
        $mapDef->setClass(FirewallMap::class);
        $mapDef->addArgument(new Reference('akeneo_authentication.sso.configuration.repository'));
    }
}
