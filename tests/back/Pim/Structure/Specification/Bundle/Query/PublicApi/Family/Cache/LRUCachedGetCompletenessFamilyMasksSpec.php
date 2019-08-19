<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Bundle\Query\PublicApi\Family\Cache;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Family\GetCompletenessFamilyMasks;
use PhpSpec\ObjectBehavior;

class LRUCachedGetCompletenessFamilyMasksSpec extends ObjectBehavior
{
    function let(GetCompletenessFamilyMasks $getCompletenessFamilyMasks)
    {
        $this->beConstructedWith($getCompletenessFamilyMasks);
    }

    function it_gets_the_uncached_masks(
        GetCompletenessFamilyMasks $getCompletenessFamilyMasks
    ) {
        $getCompletenessFamilyMasks->fromFamilyCodes(['familyA'])->shouldBeCalledTimes(1)->willReturn(['familyA' => 'maskFamilyA']);
        $this->fromFamilyCodes(['familyA'])->shouldReturn(['familyA' => 'maskFamilyA']);
    }

    function it_does_not_get_cached_masks(
        GetCompletenessFamilyMasks $getCompletenessFamilyMasks
    ) {
        $getCompletenessFamilyMasks->fromFamilyCodes(['familyA'])->shouldBeCalledTimes(1)->willReturn(['familyA' => 'maskFamilyA']);
        $this->fromFamilyCodes(['familyA']);
        $this->fromFamilyCodes(['familyA']);
    }

    function it_gets_only_uncached_masks(
        GetCompletenessFamilyMasks $getCompletenessFamilyMasks
    ) {
        $getCompletenessFamilyMasks->fromFamilyCodes(['familyA'])->shouldBeCalledTimes(1)->willReturn(['familyA' => 'maskFamilyA']);
        $getCompletenessFamilyMasks->fromFamilyCodes(['familyB'])->shouldBeCalledTimes(1)->willReturn(['familyB' => 'maskFamilyB']);
        $this->fromFamilyCodes(['familyA']);
        $this->fromFamilyCodes(['familyA', 'familyB']);
    }
}
