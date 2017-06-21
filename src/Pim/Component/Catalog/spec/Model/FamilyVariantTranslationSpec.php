<?php

namespace spec\Pim\Component\Catalog\Model;

use Pim\Component\Catalog\Model\FamilyVariantTranslation;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\FamilyVariantTranslationInterface;
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
