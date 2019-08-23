<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Bundle\Query\PublicApi\Family\Cache;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetRequiredAttributesMasks;
use PhpSpec\ObjectBehavior;

class LRUCachedGetRequiredAttributesMasksSpec extends ObjectBehavior
{
    function let(GetRequiredAttributesMasks $getRequiredAttributesMasks)
    {
        $this->beConstructedWith($getRequiredAttributesMasks);
    }

    function it_gets_the_uncached_masks(
        GetRequiredAttributesMasks $getRequiredAttributesMasks
    ) {
        $getRequiredAttributesMasks->fromFamilyCodes(['familyA'])->shouldBeCalledTimes(1)->willReturn(['familyA' => 'maskFamilyA']);
        $this->fromFamilyCodes(['familyA'])->shouldReturn(['familyA' => 'maskFamilyA']);
    }

    function it_does_not_get_cached_masks(
        GetRequiredAttributesMasks $getRequiredAttributesMasks
    ) {
        $getRequiredAttributesMasks->fromFamilyCodes(['familyA'])->shouldBeCalledTimes(1)->willReturn(['familyA' => 'maskFamilyA']);
        $this->fromFamilyCodes(['familyA']);
        $this->fromFamilyCodes(['familyA']);
    }

    function it_gets_only_uncached_masks(
        GetRequiredAttributesMasks $getRequiredAttributesMasks
    ) {
        $getRequiredAttributesMasks->fromFamilyCodes(['familyA'])->shouldBeCalledTimes(1)->willReturn(['familyA' => 'maskFamilyA']);
        $getRequiredAttributesMasks->fromFamilyCodes(['familyB'])->shouldBeCalledTimes(1)->willReturn(['familyB' => 'maskFamilyB']);
        $this->fromFamilyCodes(['familyA']);
        $this->fromFamilyCodes(['familyA', 'familyB']);
    }
}
