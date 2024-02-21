<?php

namespace Specification\Akeneo\Pim\Structure\Component\Model;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Model\FamilyTranslation;
use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Akeneo\Tool\Component\StorageUtils\Model\ReferableInterface;
use Akeneo\Tool\Component\Versioning\Model\TimestampableInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use PhpSpec\ObjectBehavior;

class FamilySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Family::class);
        $this->shouldImplement(TimestampableInterface::class);
        $this->shouldImplement(TranslatableInterface::class);
        $this->shouldImplement(ReferableInterface::class);
        $this->shouldImplement(VersionableInterface::class);
    }

    function it_contains_attributes(AttributeInterface $sku, AttributeInterface $name)
    {
        $this->addAttribute($sku)->shouldReturn($this);
        $this->addAttribute($name)->shouldReturn($this);
        $this->hasAttribute($sku)->shouldReturn(true);
        $this->hasAttribute($name)->shouldReturn(true);
    }

    function it_allows_removing_attributes(AttributeInterface $name)
    {
        $name->getType()->willReturn('pim_catalog_text');
        $name->getCode()->willReturn('code1');
        $this->addAttribute($name)->shouldReturn($this);
        $this->hasAttribute($name)->shouldReturn(true);
        $this->removeAttribute($name)->shouldReturn($this);
        $this->hasAttribute($name)->shouldReturn(false);
    }

    function it_allows_defining_an_attribute_to_use_as_label(AttributeInterface $name)
    {
        $name->getCode()->willReturn('pim_catalog_text');
        $this->getAttributeAsLabel()->shouldReturn(null);
        $this->setAttributeAsLabel($name)->shouldReturn($this);
    }

    function it_updates_a_family_by_adding_an_attribute(
        AttributeInterface $skuAttribute,
        AttributeInterface $nameAttribute,
        AttributeInterface $descAttribute,
    ) {
        $skuAttribute->getCode()->willReturn('sku');
        $nameAttribute->getCode()->willReturn('name');
        $descAttribute->getCode()->willReturn('description');
        $descAttribute->getType()->willReturn(AttributeTypes::TEXTAREA);

        $this->addAttribute($skuAttribute);
        $this->addAttribute($nameAttribute);
        $this->getAttributeCodes()->shouldReturn(['sku', 'name']);

        $this->updateAttributes([$skuAttribute, $nameAttribute, $descAttribute]);

        $this->getAttributeCodes()->shouldReturn(['sku', 'name', 'description']);
    }

    function it_updates_a_family_by_removing_an_attribute(
        AttributeInterface $skuAttribute,
        AttributeInterface $nameAttribute,
        AttributeInterface $descAttribute,
    ) {
        $skuAttribute->getCode()->willReturn('sku');
        $nameAttribute->getCode()->willReturn('name');
        $descAttribute->getCode()->willReturn('description');
        $descAttribute->getType()->willReturn(AttributeTypes::TEXTAREA);

        $this->addAttribute($skuAttribute);
        $this->addAttribute($nameAttribute);
        $this->addAttribute($descAttribute);
        $this->getAttributeCodes()->shouldReturn(['sku', 'name', 'description']);

        $this->updateAttributes([$skuAttribute, $nameAttribute]);

        $this->getAttributeCodes()->shouldReturn(['sku', 'name']);
    }

    public function it_gets_a_translation_even_if_the_locale_case_is_wrong(
        FamilyTranslation $translationEn,
    )
    {
        $translationEn->getLocale()->willReturn('EN_US');
        $this->addTranslation($translationEn);

        $this->getTranslation('en_US')->shouldReturn($translationEn);
    }
}
