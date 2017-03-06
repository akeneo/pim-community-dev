<?php

namespace spec\PimEnterprise\Bundle\TeamworkAssistantBundle\DependencyInjection\Compiler;

use PimEnterprise\Bundle\TeamworkAssistantBundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
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
