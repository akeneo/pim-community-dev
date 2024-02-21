<?php

namespace Specification\Akeneo\Pim\Structure\Component\Model;

use Akeneo\Pim\Structure\Component\Model\FamilyVariantTranslation;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantTranslationInterface;
use Prophecy\Argument;

class FamilyVariantTranslationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyVariantTranslation::class);
    }

    function it_is_a_translation()
    {
        $this->shouldImplement(FamilyVariantTranslationInterface::class);
    }
}
