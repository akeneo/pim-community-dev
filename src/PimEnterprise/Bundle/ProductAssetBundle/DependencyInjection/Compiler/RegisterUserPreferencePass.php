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

namespace PimEnterprise\Bundle\ProductAssetBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class RegisterUserPreferencePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $userPreferenceDefintion = $container->getDefinition('pim_user.form.type.user');
        $userPreferenceDefintion->addMethodCall(
            'addEventSubscribers',
            [$container->getDefinition('pimee_product_asset.form_event_listener.user_preference_subscriber')]
        );
    }
}
