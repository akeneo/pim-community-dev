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

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Anael Chardan <anael.chardan@akeneo.com>
 */
class ConfigureUserServicePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $userNormalizer = $container->getDefinition('pim_user.normalizer.user');
        $userNormalizer->addArgument($container->getDefinition('pimee_security.repository.category_access'));
    }
}
