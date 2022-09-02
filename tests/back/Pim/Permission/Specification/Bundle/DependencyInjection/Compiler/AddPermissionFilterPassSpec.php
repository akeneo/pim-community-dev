<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\DependencyInjection\Compiler;

use Akeneo\Pim\Permission\Bundle\Datagrid\Filter\PermissionFilter;
use Akeneo\Pim\Permission\Bundle\DependencyInjection\Compiler\AddPermissionFilterPass;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class AddPermissionFilterPassSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AddPermissionFilterPass::class);
    }

    function it_is_a_compiler_pass()
    {
        $this->shouldImplement(CompilerPassInterface::class);
    }
}
