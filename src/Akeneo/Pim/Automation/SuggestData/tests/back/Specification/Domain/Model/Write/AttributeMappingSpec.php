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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Domain\Model\Write;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\AttributeMapping;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
class AttributeMappingSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('target', 1, 'pim');
        $this->shouldBeAnInstanceOf(AttributeMapping::class);
    }

    function it_is_initializable_without_a_nullable_pim_attribute_code()
    {
        $this->beConstructedWith('target', 0, null);
        $this->shouldBeAnInstanceOf(AttributeMapping::class);
    }

    function it_sets_null_as_pim_attribute_code_if_status_is_unmapped()
    {
        $this->beConstructedWith('target', AttributeMapping::ATTRIBUTE_PENDING, 'foobar');

        $this->getPimAttributeCode()->shouldReturn(null);
    }

    function it_returns_the_status()
    {
        $this->beConstructedWith('target', AttributeMapping::ATTRIBUTE_UNMAPPED, 'foobar');

        $this->getStatus()->shouldReturn(AttributeMapping::ATTRIBUTE_UNMAPPED);
    }

    function it_returns_the_pim_attribute_code()
    {
        $this->beConstructedWith('target', AttributeMapping::ATTRIBUTE_MAPPED, 'foobar');

        $this->getPimAttributeCode()->shouldReturn('foobar');
    }

    function it_returns_the_target_attribute_code()
    {
        $this->beConstructedWith('target', AttributeMapping::ATTRIBUTE_MAPPED, 'foobar');

        $this->getTargetAttributeCode()->shouldReturn('target');
    }

    function it_sets_attribute_to_the_attribute_mapping(AttributeInterface $attribute)
    {
        $this->beConstructedWith('target', AttributeMapping::ATTRIBUTE_MAPPED, 'foobar');

        $this->setAttribute($attribute)->shouldReturn($this);
        $this->getAttribute()->shouldReturn($attribute);
    }

    function it_throws_an_exception_if_status_is_invalid()
    {
        $this->beConstructedWith('target', 4, 'pim');

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->duringInstantiation();
    }

    function it_throws_an_exception_if_status_is_mapped_without_pim_attribute()
    {
        $this->beConstructedWith('target', AttributeMapping::ATTRIBUTE_MAPPED, null);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->duringInstantiation();
    }
}
