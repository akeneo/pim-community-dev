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
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\AttributeMappingStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributeMapping;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Pim\Automation\FranklinInsights\Specification\Builder\AttributeBuilder;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class AttributeMappingSpec extends ObjectBehavior
{
    public function it_is_initializable_with_an_attribute(): void
    {
        $this->beConstructedWith('target', 'text', AttributeBuilder::fromCode('code'));
        $this->shouldBeAnInstanceOf(AttributeMapping::class);
    }

    public function it_is_initializable_without_attribute(): void
    {
        $this->beConstructedWith('target', 'multiselect', null);
        $this->shouldBeAnInstanceOf(AttributeMapping::class);
    }

    public function it_returns_an_active_status_when_attribute_is_mapped(): void
    {
        $this->beConstructedWith('target', 'multiselect', AttributeBuilder::fromCode('code'));
        $this->getStatus()->shouldReturn(AttributeMappingStatus::ATTRIBUTE_ACTIVE);
    }

    public function it_returns_a_pending_status_when_attribute_is_not_mapped(): void
    {
        $this->beConstructedWith('target', 'multiselect', null);
        $this->getStatus()->shouldReturn(AttributeMappingStatus::ATTRIBUTE_PENDING);
    }

    public function it_returns_the_target_attribute_code(): void
    {
        $this->beConstructedWith('target', 'multiselect', null);
        $this->getTargetAttributeCode()->shouldReturn('target');
    }

    public function it_throws_an_exception_if_franklin_attribute_type_is_invalid(): void
    {
        $this->beConstructedWith('target', 'invalid-type', AttributeBuilder::fromCode('code'));
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->duringInstantiation();
    }

    public function it_throws_an_exception_if_pim_attribute_type_is_invalid(): void
    {
        $attribute = (new AttributeBuilder())->withType(AttributeTypes::DATE)->build();
        $this->beConstructedWith('target', 'text', $attribute);
        $this
            ->shouldThrow(
                AttributeMappingException::incompatibleAttributeTypeMapping(AttributeTypes::DATE)
            )
            ->duringInstantiation();
    }

    public function it_throws_an_exception_if_attribute_is_localizable(): void
    {
        $attribute = (new AttributeBuilder())->isLocalizable()->build();

        $this->beConstructedWith('target', 'text', $attribute);
        $this
            ->shouldThrow(AttributeMappingException::localizableAttributeNotAllowed())
            ->duringInstantiation();
    }

    public function it_throws_an_exception_if_attribute_is_scopable(): void
    {
        $attribute = (new AttributeBuilder())->isScopable()->build();

        $this->beConstructedWith('target', 'text', $attribute);
        $this
            ->shouldThrow(AttributeMappingException::scopableAttributeNotAllowed())
            ->duringInstantiation();
    }

    public function it_throws_an_exception_if_attribute_is_locale_specific(): void
    {
        $attribute = (new AttributeBuilder())->isLocaleSpecific()->build();

        $this->beConstructedWith('target', 'text', $attribute);
        $this
            ->shouldThrow(AttributeMappingException::localeSpecificAttributeNotAllowed())
            ->duringInstantiation();
    }
}
