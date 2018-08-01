<?php

namespace spec\Akeneo\Pim\WorkOrganization\Workflow\Bundle\DependencyInjection\Compiler;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\DependencyInjection\Compiler\RegisterUserPreferencePass;
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
        Definition $userFormType,
        Definition $userSubscriberPreference
    ) {
        $container->getDefinition('pim_user.form.type.user')
            ->willreturn($userFormType);

        $container->getDefinition('pimee_workflow.form.subscriber.user_preferences')
            ->willreturn($userSubscriberPreference);

        $userFormType->addMethodCall(
            'addEventSubscribers',
            [$userSubscriberPreference]
        )->shouldBeCalled();

        $this->process($container)->shouldReturn(null);
    }

}
