<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\DependencyInjection\Compiler;

use PimEnterprise\Bundle\ProductAssetBundle\DependencyInjection\Compiler\RegisterUserPreferencePass;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterUserPreferencePassSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RegisterUserPreferencePass::class);
    }

    function it_is_a_compiler_pass()
    {
        $this->shouldImplement(CompilerPassInterface::class);
    }

    function it_registers_the_user_preferences_subscriber(
        ContainerBuilder $container,
        Definition $userSubscriberPreference
    ) {
        $container->getDefinition('pim_user.form.type.user')->willreturn($userSubscriberPreference);
        $userSubscriberPreference->addMethodCall(
            'addEventSubscribers',
            ['pimee_product_asset.form_event_listener.user_preference_subscriber']
        )->shouldBeCalled();

        $this->process($container)->shouldReturn(null);
    }
}
