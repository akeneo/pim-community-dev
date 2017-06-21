<?php

namespace spec\Pim\Component\Catalog\Model;

use Akeneo\Component\Localization\Model\TranslatableInterface;
use Pim\Component\Catalog\Model\FamilyVariant;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FamilyVariantSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyVariant::class);
    }

    function it_is_a_variant_family()
    {
        $this->shouldImplement(FamilyVariantInterface::class);
    }

    function its_code_is_translatable()
    {
        $this->shouldImplement(TranslatableInterface::class);
    }
}
