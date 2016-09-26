<?php

namespace spec\Akeneo\ActivityManager\Bundle\DependencyInjection\Compiler;

use Akeneo\ActivityManager\Bundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
use PhpSpec\ObjectBehavior;

class ResolveDoctrineTargetModelPassSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ResolveDoctrineTargetModelPass::class);
    }

    function it_is_doctrine_target_model_resolver()
    {
        $this->shouldHaveType('Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetModelPass');
    }
}
