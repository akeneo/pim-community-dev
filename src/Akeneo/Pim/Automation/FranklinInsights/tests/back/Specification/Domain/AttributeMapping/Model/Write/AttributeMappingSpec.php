<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Exception\AttributeMappingException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributeMapping;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class AttributeMappingSpec extends ObjectBehavior
{
    public function it_is_initializable_with_an_attribute(AttributeInterface $attribute): void
    {
        $attribute->getType()->willReturn(AttributeTypes::TEXT);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocaleSpecific()->willReturn(false);

        $this->beConstructedWith('target', 'text', $attribute);
        $this->shouldBeAnInstanceOf(AttributeMapping::class);
    }

    public function it_is_initializable_without_attribute(): void
    {
        $this->beConstructedWith('target', 'multiselect', null);
        $this->shouldBeAnInstanceOf(AttributeMapping::class);
    }

    public function it_returns_an_active_status_when_attribute_is_mapped(AttributeInterface $attribute): void
    {
        $attribute->getType()->willReturn(AttributeTypes::TEXT);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocaleSpecific()->willReturn(false);

        $this->beConstructedWith('target', 'multiselect', $attribute);

        $this->getStatus()->shouldReturn(AttributeMapping::ATTRIBUTE_MAPPED);
    }

    public function it_returns_a_pending_status_when_attribute_is_not_mapped(): void
    {
        $this->beConstructedWith('target', 'multiselect', null);

        $this->getStatus()->shouldReturn(AttributeMapping::ATTRIBUTE_PENDING);
    }

    public function it_returns_the_target_attribute_code(): void
    {
        $this->beConstructedWith('target', 'multiselect', null);

        $this->getTargetAttributeCode()->shouldReturn('target');
    }

    public function it_throws_an_exception_if_franklin_attribute_type_is_invalid(AttributeInterface $attribute): void
    {
        $this->beConstructedWith('target', 'invalid-type', $attribute);
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->duringInstantiation();
    }

    public function it_throws_an_exception_if_pim_attribute_type_is_invalid(AttributeInterface $attribute): void
    {
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocaleSpecific()->willReturn(false);
        $attribute->getType()->willReturn(AttributeTypes::DATE);

        $this->beConstructedWith('target', 'text', $attribute);
        $this
            ->shouldThrow(
                AttributeMappingException::incompatibleAttributeTypeMapping(AttributeTypes::DATE)
            )
            ->duringInstantiation();
    }

    public function it_throws_an_exception_if_attribute_is_localizable(AttributeInterface $attribute): void
    {
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocaleSpecific()->willReturn(false);
        $attribute->getType()->willReturn(AttributeTypes::TEXT);

        $this->beConstructedWith('target', 'text', $attribute);
        $this
            ->shouldThrow(AttributeMappingException::localizableAttributeNotAllowed())
            ->duringInstantiation();
    }

    public function it_throws_an_exception_if_attribute_is_scopable(AttributeInterface $attribute): void
    {
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocaleSpecific()->willReturn(false);
        $attribute->getType()->willReturn(AttributeTypes::TEXT);

        $this->beConstructedWith('target', 'text', $attribute);
        $this
            ->shouldThrow(AttributeMappingException::scopableAttributeNotAllowed())
            ->duringInstantiation();
    }

    public function it_throws_an_exception_if_attribute_is_locale_specific(AttributeInterface $attribute): void
    {
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocaleSpecific()->willReturn(true);
        $attribute->getType()->willReturn(AttributeTypes::TEXT);

        $this->beConstructedWith('target', 'text', $attribute);
        $this
            ->shouldThrow(AttributeMappingException::localeSpecificAttributeNotAllowed())
            ->duringInstantiation();
    }
}
