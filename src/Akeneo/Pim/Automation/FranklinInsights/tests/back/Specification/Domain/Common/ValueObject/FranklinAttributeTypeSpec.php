<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeType;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeType;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class FranklinAttributeTypeSpec extends ObjectBehavior
{
    public function it_is_a_franklin_attribute_type(): void
    {
        $this->beConstructedWith(FranklinAttributeType::AVAILABLE_TYPES[0]);
        $this->shouldBeAnInstanceOf(FranklinAttributeType::class);
    }

    public function it_throws_an_exception_when_type_is_empty(): void
    {
        $this->beConstructedWith('');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_when_type_is_not_available(): void
    {
        $this->beConstructedWith('foo');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_builds_a_franklin_attribute_type_of_boolean_type()
    {
        $this->beConstructedWith('boolean');
        $this->shouldBeAnInstanceOf(FranklinAttributeType::class);
    }

    public function it_builds_a_franklin_attribute_type_of_metric_type()
    {
        $this->beConstructedWith('metric');
        $this->shouldBeAnInstanceOf(FranklinAttributeType::class);
    }

    public function it_builds_a_franklin_attribute_type_of_multiselect_type()
    {
        $this->beConstructedWith('multiselect');
        $this->shouldBeAnInstanceOf(FranklinAttributeType::class);
    }

    public function it_builds_a_franklin_attribute_type_of_number_type()
    {
        $this->beConstructedWith('number');
        $this->shouldBeAnInstanceOf(FranklinAttributeType::class);
    }

    public function it_builds_a_franklin_attribute_type_of_select_type()
    {
        $this->beConstructedWith('select');
        $this->shouldBeAnInstanceOf(FranklinAttributeType::class);
    }

    public function it_builds_a_franklin_attribute_type_of_text_type()
    {
        $this->beConstructedWith('text');
        $this->shouldBeAnInstanceOf(FranklinAttributeType::class);
    }

    public function it_converts_text_to_pim_attribute_type()
    {
        $this->beConstructedWith('text');
        $this->convertToPimAttributeType()->shouldBeLike(new AttributeType('pim_catalog_text'));
    }

    public function it_converts_number_to_pim_attribute_type()
    {
        $this->beConstructedWith('number');
        $this->convertToPimAttributeType()->shouldBeLike(new AttributeType('pim_catalog_number'));
    }

    public function it_converts_metric_to_pim_attribute_type()
    {
        $this->beConstructedWith('metric');
        $this->convertToPimAttributeType()->shouldBeLike(new AttributeType('pim_catalog_text'));
    }

    public function it_converts_select_to_pim_attribute_type()
    {
        $this->beConstructedWith('select');
        $this->convertToPimAttributeType()->shouldBeLike(new AttributeType('pim_catalog_simpleselect'));
    }

    public function it_converts_multiselect_to_pim_attribute_type()
    {
        $this->beConstructedWith('multiselect');
        $this->convertToPimAttributeType()->shouldBeLike(new AttributeType('pim_catalog_multiselect'));
    }

    public function it_converts_boolean_to_pim_attribute_type()
    {
        $this->beConstructedWith('boolean');
        $this->convertToPimAttributeType()->shouldBeLike(new AttributeType('pim_catalog_boolean'));
    }

    public function it_returns_the_type(): void
    {
        $this->beConstructedWith('boolean');
        $this->__toString()->shouldReturn('boolean');
    }
}
