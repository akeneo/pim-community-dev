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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Write;

use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Write\AttributeMapping;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
class AttributeMappingSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->beConstructedWith('target', 'multiselect', 'pim');
        $this->shouldBeAnInstanceOf(AttributeMapping::class);
    }

    public function it_is_initializable_without_a_nullable_pim_attribute_code(): void
    {
        $this->beConstructedWith('target', 'multiselect', null);
        $this->shouldBeAnInstanceOf(AttributeMapping::class);
    }

    public function it_returns_an_active_status_when_attribute_is_mapped(): void
    {
        $this->beConstructedWith('target', 'multiselect', 'foobar');

        $this->getStatus()->shouldReturn(AttributeMapping::ATTRIBUTE_MAPPED);
    }

    public function it_returns_an_inactive_status_when_no_attribute_mapped(): void
    {
        $this->beConstructedWith('target', 'multiselect', null);

        $this->getStatus()->shouldReturn(AttributeMapping::ATTRIBUTE_UNMAPPED);
    }

    public function it_returns_the_pim_attribute_code(): void
    {
        $this->beConstructedWith('target', 'multiselect', 'foobar');

        $this->getPimAttributeCode()->shouldReturn('foobar');
    }

    public function it_returns_the_target_attribute_code(): void
    {
        $this->beConstructedWith('target', 'multiselect', 'foobar');

        $this->getTargetAttributeCode()->shouldReturn('target');
    }

    public function it_sets_attribute_to_the_attribute_mapping(AttributeInterface $attribute): void
    {
        $this->beConstructedWith('target', 'multiselect', 'foobar');

        $this->setAttribute($attribute)->shouldReturn($this);
        $this->getAttribute()->shouldReturn($attribute);
    }

    public function it_throws_an_exception_if_type_is_invalid(): void
    {
        $this->beConstructedWith('target', 'invalid-type', 'pim');

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->duringInstantiation();
    }
}
