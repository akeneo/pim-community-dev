<?php

namespace spec\Akeneo\Asset\Bundle\DependencyInjection\Compiler;

use Akeneo\Asset\Bundle\DependencyInjection\Compiler\ConfigureUserServicesPass;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ConfigureUserServicesPassSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ConfigureUserServicesPass::class);
    }

    function it_is_a_compiler_pass()
    {
        $this->shouldImplement(CompilerPassInterface::class);
    }

    function it_registers_the_user_preferences_subscriber(
        ContainerBuilder $container,
        Definition $userFormType,
        Definition $userSubscriberPreference,
        Definition $userUpdater,
        Definition $assetCategoryRepository
    ) {
        $container->getDefinition('pim_user.updater.user')
            ->willreturn($userUpdater);

        $container->getDefinition('pimee_product_asset.repository.category')
            ->willreturn($assetCategoryRepository);

        $userUpdater->addArgument($assetCategoryRepository)->shouldBeCalled();

        $container->getDefinition('pim_user.form.type.user')
            ->willreturn($userFormType);

        $container->getDefinition('pimee_product_asset.form_event_listener.user_preference_subscriber')
            ->willreturn($userSubscriberPreference);

        $userFormType->addMethodCall(
            'addEventSubscribers',
            [$userSubscriberPreference]
        )->shouldBeCalled();

        $this->process($container)->shouldReturn(null);
    }
}
