<?php

namespace Specification\Akeneo\Asset\Bundle\DependencyInjection\Compiler;

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
        Definition $userUpdater,
        Definition $assetCategoryRepository,
        Definition $userFactory
    ) {
        $container->getDefinition('pim_user.updater.user')
            ->willreturn($userUpdater);

        $container->getDefinition('pimee_product_asset.repository.asset_category')
            ->willreturn($assetCategoryRepository);

        $userUpdater->addArgument($assetCategoryRepository)->shouldBeCalled();

        $container->getDefinition('pim_user.factory.user')->willReturn($userFactory);
        $userFactory->addArgument($assetCategoryRepository)->shouldBeCalled();


        $this->process($container)->shouldReturn(null);
    }
}
