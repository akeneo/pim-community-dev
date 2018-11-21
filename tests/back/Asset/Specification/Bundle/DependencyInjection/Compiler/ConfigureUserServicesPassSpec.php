<?php

namespace Specification\Akeneo\Asset\Bundle\DependencyInjection\Compiler;

use Akeneo\Asset\Bundle\DependencyInjection\Compiler\ConfigureUserServicesPass;
use Akeneo\Asset\Component\Factory\DefaultAssetTree;
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
        Definition $userUpdater,
        Definition $userNormalizer,
        Definition $userFactory,
        Definition $defaultAssetTree,
        Definition $defaultAssetDelayReminder
    ) {
        $container->getDefinition('pim_user.updater.user')->willReturn($userUpdater);
        $userUpdater->addArgument('asset_delay_reminder')->shouldBeCalled();
        $userUpdater->addArgument('default_asset_tree')->shouldBeCalled();
        $userUpdater->addArgument('email_notifications')->shouldBeCalled();

        $container->getDefinition('pim_user.normalizer.user')->willReturn($userNormalizer);
        $userNormalizer->addArgument('asset_delay_reminder')->shouldBeCalled();
        $userNormalizer->addArgument('default_asset_tree')->shouldBeCalled();
        $userNormalizer->addArgument('email_notifications')->shouldBeCalled();

        $container->getDefinition('pim_user.factory.user')->willReturn($userFactory);
        $container->getDefinition(DefaultAssetTree::class)->willReturn($defaultAssetTree);
        $container->getDefinition('pimee_asset.factory.user.default_asset_delay_reminder')->willReturn($defaultAssetDelayReminder);

        $userFactory->addArgument($defaultAssetTree)->shouldBeCalled();
        $userFactory->addArgument($defaultAssetDelayReminder)->shouldBeCalled();

        $this->process($container)->shouldReturn(null);
    }
}
