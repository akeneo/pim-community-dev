<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\DependencyInjection\Compiler;

use PimEnterprise\Bundle\ActivityManagerBundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetModelPass;
use PhpSpec\ObjectBehavior;

class ResolveDoctrineTargetModelPassSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ResolveDoctrineTargetModelPass::class);
    }

    function it_is_doctrine_target_model_resolver()
    {
        $this->shouldHaveType(AbstractResolveDoctrineTargetModelPass::class);
    }
}
