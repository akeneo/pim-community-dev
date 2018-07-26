<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class RegisterUserPreferencePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $userFormType = $container->getDefinition('pim_user.form.type.user');
        $userFormType->addMethodCall(
            'addEventSubscribers',
            [$container->getDefinition('pimee_workflow.form.subscriber.user_preferences')]
        );
    }
}
