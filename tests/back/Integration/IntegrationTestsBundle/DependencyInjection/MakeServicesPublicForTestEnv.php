<?php

namespace Akeneo\Test\IntegrationTestsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Make all services public for test environment.
 * Dirty trick made to upgrade to Symfony 4 :(
 * See https://github.com/akeneo/pim-community-dev/pull/10773.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MakeServicesPublicForTestEnv implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $id => $definition) {
            if (0 === stripos($id, 'pim') || 0 === stripos($id, 'akeneo') || 0 === stripos($id, 'oro')) {
                $definition->setPublic(true);
            }
        }
    }
}
